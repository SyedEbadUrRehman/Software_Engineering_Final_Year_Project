<?php

namespace App\Http\Controllers\Admin;

use App\Events\ContentModerated;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\ModerationLog;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class ModerationController extends Controller
{
    /** Short, URL-safe type -> model class map. */
    protected const TYPE_MAP = [
        'post'    => Post::class,
        'comment' => Comment::class,
    ];

    protected const STATUSES = ['approved', 'flagged', 'deleted'];

    /**
     * moderation_logs only stores `action_taken` (no separate status
     * column), so a normalized status is derived from it everywhere.
     * Automatic runs write 'allow'/'flag'/'delete'; manual admin edits
     * write 'manual_allow'/'manual_flag'/'manual_delete'. Both map to
     * the same three buckets below.
     */
    protected const STATUS_ACTIONS = [
        'approved' => ['allow', 'manual_allow'],
        'flagged'  => ['flag', 'manual_flag'],
        'deleted'  => ['delete', 'manual_delete'],
    ];

    protected const STATUS_TO_MANUAL_ACTION = [
        'approved' => 'manual_allow',
        'flagged'  => 'manual_flag',
        'deleted'  => 'manual_delete',
    ];

    public function index(Request $request)
    {
        $status = strtolower((string) $request->query('status', 'all'));
        $status = in_array($status, self::STATUSES, true) ? $status : 'all';

        return Inertia::render('Admin/Moderation/Index', [
            'filters' => [
                'status' => $status,
            ],
            'logs' => $this->latestLogs($status),
            'stats' => [
                'dailyModerations' => $this->dailyModerationCounts(),
                'statusRatio'      => $this->statusRatio(),
                'postsVsComments'  => $this->postsVsCommentsThisMonth(),
            ],
        ]);
    }

    public function updateStatus(Request $request, string $type, int $id)
    {
        abort_unless(array_key_exists($type, self::TYPE_MAP), 404);

        $data = $request->validate([
            'status' => 'required|in:approved,flagged,deleted',
        ]);

        $modelClass = self::TYPE_MAP[$type];
        $item = $modelClass::withTrashed()->findOrFail($id);

        $wasDeleted = $item->trashed();
        $willDelete = $data['status'] === 'deleted';

        if ($wasDeleted && ! $willDelete) {
            $item->restore();
        }

        $item->status = $data['status'];
        $item->save();

        if ($willDelete && ! $wasDeleted) {
            $item->delete();
        }

        ModerationLog::create([
            'moderatable_type' => $modelClass,
            'moderatable_id'   => $item->id,
            'action_taken'     => self::STATUS_TO_MANUAL_ACTION[$data['status']],
            'api_response'     => null,
        ]);

        $this->notifyAndBroadcast($item, $type, $data['status']);

        return back()->with('success', ucfirst($type) . ' #' . $item->id . ' is now ' . $data['status'] . '.');
    }

    /**
     * One row per moderated item, always reflecting its most recent log entry.
     */
    protected function latestLogs(string $status)
    {
        $latestIdsPerItem = ModerationLog::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('moderatable_type', 'moderatable_id');

        $query = ModerationLog::query()
            ->joinSub($latestIdsPerItem, 'latest', function ($join) {
                $join->on('moderation_logs.id', '=', 'latest.id');
            })
            ->with([
                'moderatable' => function ($morphTo) {
                    $morphTo->morphWith([
                        Post::class    => ['user:id,name'],
                        Comment::class => ['user:id,name', 'post:id,text'],
                    ]);
                },
            ])
            ->orderByDesc('moderation_logs.created_at')
            ->select('moderation_logs.*');

        if ($status !== 'all') {
            $query->whereIn('moderation_logs.action_taken', self::STATUS_ACTIONS[$status]);
        }

        return $query->paginate(10)
            ->withQueryString()
            ->through(fn (ModerationLog $log) => $this->presentLog($log));
    }

    protected function presentLog(ModerationLog $log): array
    {
        $item = $log->moderatable;
        $type = $item instanceof Comment ? 'comment' : 'post';

        // api_response is null for manual admin actions, and an array
        // (cast on the model) for automatic API-moderated ones, e.g.
        // { user_id, action, reason, toxic_score, hate_score, processing_time_ms }
        $api = $log->api_response;
        $toxicScore = isset($api['toxic_score']) ? round($api['toxic_score'] * 100, 1) : null;
        $hateScore  = isset($api['hate_score']) ? round($api['hate_score'] * 100, 1) : null;

        return [
            'id'          => $log->id,
            'type'        => $type,
            'itemId'      => $log->moderatable_id,
            'status'      => $this->normalizeStatus($log->action_taken),
            'actionTaken' => $log->action_taken,
            'createdAt'   => optional($log->created_at)->toIso8601String(),
            'author'      => $item?->user?->name ?? 'Unknown',
            'text'        => $item?->text,
            'trashed'     => (bool) $item?->trashed(),
            'context'     => $type === 'comment' ? $item?->post?->text : null,
            'aiReason'        => $api['reason'] ?? null,
            'toxicScore'      => $toxicScore,
            'hateScore'       => $hateScore,
            'riskScore'       => max($toxicScore ?? 0, $hateScore ?? 0),
            'processingTimeMs' => $api['processing_time_ms'] ?? null,
        ];
    }

    protected function normalizeStatus(?string $actionTaken): string
    {
        foreach (self::STATUS_ACTIONS as $status => $actions) {
            if (in_array($actionTaken, $actions, true)) {
                return $status;
            }
        }

        return 'flagged';
    }

    protected function statusRatio(): array
    {
        $latestIdsPerItem = ModerationLog::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('moderatable_type', 'moderatable_id');

        $actionCounts = ModerationLog::query()
            ->joinSub($latestIdsPerItem, 'latest', function ($join) {
                $join->on('moderation_logs.id', '=', 'latest.id');
            })
            ->selectRaw('moderation_logs.action_taken as action_taken, COUNT(*) as total')
            ->groupBy('moderation_logs.action_taken')
            ->pluck('total', 'action_taken');

        $ratio = ['approved' => 0, 'flagged' => 0, 'deleted' => 0];

        foreach ($actionCounts as $action => $total) {
            $ratio[$this->normalizeStatus($action)] += (int) $total;
        }

        return $ratio;
    }

    /**
     * Daily count of moderation actions performed this month (every row
     * written to moderation_logs, automatic or manual) — not post volume.
     */
    protected function dailyModerationCounts(): array
    {
        $start = Carbon::now()->startOfMonth();
        $today = Carbon::now();

        $counts = ModerationLog::query()
            ->whereBetween('created_at', [$start, $today])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = [];
        for ($date = $start->copy(); $date->lte($today); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $days[] = [
                'date'  => $key,
                'label' => $date->format('j M'),
                'total' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $days;
    }

    protected function postsVsCommentsThisMonth(): array
    {
        $start = Carbon::now()->startOfMonth();
        $today = Carbon::now();

        $posts = Post::query()->withTrashed()
            ->whereBetween('created_at', [$start, $today])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')->pluck('total', 'day');

        $comments = Comment::query()->withTrashed()
            ->whereBetween('created_at', [$start, $today])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')->pluck('total', 'day');

        $days = [];
        for ($date = $start->copy(); $date->lte($today); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $days[] = [
                'date'     => $key,
                'label'    => $date->format('j M'),
                'posts'    => (int) ($posts[$key] ?? 0),
                'comments' => (int) ($comments[$key] ?? 0),
            ];
        }

        return $days;
    }

    /**
     * Mirrors the broadcast/notification behaviour already used by
     * ModerateContentJob, so manual admin edits behave the same way
     * an automatic moderation decision would.
     */
    protected function notifyAndBroadcast($item, string $type, string $newStatus): void
    {
        $circleIds = [];
        $postOwnerId = $item->user_id;
        $parentPost = $type === 'post' ? $item : Post::withTrashed()->find($item->post_id);

        if ($type === 'post') {
            $circleIds = $item->sharedCircles()->pluck('circles.id')->toArray();
        } elseif ($parentPost) {
            $circleIds = $parentPost->sharedCircles()->pluck('circles.id')->toArray();
            $postOwnerId = $parentPost->user_id;
        }

        broadcast(new ContentModerated(
            $item->id,
            $type,
            $newStatus,
            $item->user_id,
            $circleIds,
            $postOwnerId
        ));

        if ($newStatus === 'deleted' && $parentPost) {
            $author = User::find($item->user_id);
            $author?->notify(new \App\Notifications\PostActivityNotification(
                $parentPost,
                $author,
                $type === 'post' ? 'moderation_post_deleted' : 'moderation_comment_deleted'
            ));
        }
    }
}