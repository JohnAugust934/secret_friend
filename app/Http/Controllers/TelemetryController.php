<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelemetryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:2000'],
            'url' => ['nullable', 'string', 'max:2000'],
            'line' => ['nullable', 'integer'],
            'column' => ['nullable', 'integer'],
            'stack' => ['nullable', 'string', 'max:12000'],
            'metrics' => ['nullable', 'array'],
        ]);

        Log::warning('frontend_telemetry', [
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $data,
        ]);

        return response()->json(['ok' => true], 202);
    }
}
