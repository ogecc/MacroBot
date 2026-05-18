<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Transcription;

class VoiceTranscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => ['required', 'file', 'max:10240'],
        ]);

        $transcript = Transcription::fromUpload($request->file('audio'))->generate();

        return response()->json(['text' => (string) $transcript]);
    }
}
