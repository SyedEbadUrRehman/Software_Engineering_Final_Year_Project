<?php

namespace App\Services;

use App\Models\PostFeedback;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OwnerScoreService
{
    /**
     * Scale: 1 = good ... 10 = bad.
     */
    public const SCALE_MIN = 1;
    public const SCALE_MAX = 10;

    /**
     * Default score for a user who has received no feedback yet.
     * 1.00 = good, so brand-new posters get full reach by default.
     */
    public const DEFAULT_SCORE = 1.00;

    /**
     * A user's score can't actually affect their reach until they've
     * received at least this many ratings. Below this, they always get
     * full reach (100%) regardless of what their (still-noisy) average
     * says. This exists specifically to stop a single bad-faith rating
     * from burying a brand-new poster's very first post.
     */
    public const MIN_RATINGS_FOR_THROTTLE = 5;

    /**
     * Anchor points mapping owner_score -> reach fraction.
     * Scores between two anchors are linearly interpolated; scores
     * exactly on an anchor return that anchor's value exactly.
     *
     * score 1  (best)  -> 100% reach
     * score 4          ->  96% reach
     * score 6          ->  80% reach
     * score 8          ->  50% reach
     * score 10 (worst) ->  40% reach
     *
     * Kept as an ordered [score => fraction] map, rather than a formula,
     * so the curve can be re-tuned later just by editing this table —
     * no math to re-derive.
     */
    private const REACH_CURVE = [
        1  => 1.0,
        4  => 0.96,
        6  => 0.80,
        8  => 0.50,
        10 => 0.40,
    ];

    /**
     * Recompute and persist a user's owner_score from scratch, based on
     * every PostFeedback row ever given about their posts.
     *
     * Recomputing from scratch (rather than an incremental running
     * average) is deliberate here: feedback is EDITABLE, so an
     * incremental "add this new rating in" formula would be wrong the
     * moment someone changes an existing rating instead of adding a new
     * one. A full AVG() is cheap enough at this scale and is always
     * correct regardless of create vs update.
     */
    public function recalculateFor(User $user): void
    {
        $stats = PostFeedback::query()
            ->where('post_owner_id', $user->id)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as rating_count')
            ->first();

        $count = (int) ($stats->rating_count ?? 0);

        $score = $count > 0
            ? round((float) $stats->avg_rating, 2)
            : self::DEFAULT_SCORE;

        $user->forceFill([
            'owner_score'       => $score,
            'owner_score_count' => $count,
        ])->save();
    }

    /**
     * Decide what fraction of followers should receive a share, based on
     * the author's CURRENT score at the moment of sharing. This is a
     * snapshot taken once, at share-time — it is never revisited later
     * even if the author's score subsequently changes.
     *
     * Below MIN_RATINGS_FOR_THROTTLE ratings, always returns 1.0 (full
     * reach) regardless of score, since the average is still too noisy
     * to trust. Above that, the score is mapped to a fraction via
     * linear interpolation over REACH_CURVE.
     *
     * @return float Reach fraction, e.g. 1.0 = 100%, 0.5 = 50%.
     */
    public function reachFractionFor(User $author): float
    {
        if ($author->owner_score_count < self::MIN_RATINGS_FOR_THROTTLE) {
            return 1.0;
        }

        return $this->interpolateReach((float) $author->owner_score);
    }

    /**
     * Linearly interpolate a reach fraction for an arbitrary score using
     * the REACH_CURVE anchor points. The score is clamped to
     * [SCALE_MIN, SCALE_MAX] first as a defensive measure — every
     * individual rating is already constrained to that range by
     * PostFeedback::ALLOWED_RATINGS, so an average of them can never
     * actually fall outside it, but clamping costs nothing and removes
     * any possibility of extrapolating beyond the curve's defined ends.
     */
    private function interpolateReach(float $score): float
    {
        $score = max(self::SCALE_MIN, min(self::SCALE_MAX, $score));

        $anchors = self::REACH_CURVE;
        $scores  = array_keys($anchors);

        // Exact match on an anchor (e.g. a single rater whose post owner
        // has exactly one rating of "6") — return it directly, no
        // interpolation needed.
        if (array_key_exists((int) $score, $anchors) && (float) (int) $score === $score) {
            return $anchors[(int) $score];
        }

        // Find the two anchors the score falls between, then interpolate.
        for ($i = 0; $i < count($scores) - 1; $i++) {
            $lowScore  = $scores[$i];
            $highScore = $scores[$i + 1];

            if ($score >= $lowScore && $score <= $highScore) {
                $lowReach  = $anchors[$lowScore];
                $highReach = $anchors[$highScore];

                // Standard linear interpolation between the two points.
                $progress = ($score - $lowScore) / ($highScore - $lowScore);

                return round($lowReach + ($progress * ($highReach - $lowReach)), 4);
            }
        }

        // Defensive fallback — should be unreachable given the clamp above,
        // but guarantees a sane value rather than a missing return.
        return end($anchors);
    }
}