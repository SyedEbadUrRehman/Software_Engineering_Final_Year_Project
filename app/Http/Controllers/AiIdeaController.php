<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessUrlWithGrokAi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiIdeaController extends Controller
{
    //
    public function generate(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->input('url');
        $userId = auth()->id();
        $trackingToken = Str::uuid()->toString();

        // Dispatch the background job to Grok
        ProcessUrlWithGrokAi::dispatch($url, $trackingToken, $userId);

        // Return the tracking token to the frontend asynchronously
        return response()->json([
            'trackingToken' => $trackingToken,
            'message' => 'AI is analyzing the URL in the background.'
        ]);
    }
}
