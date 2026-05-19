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
            'audio' => ['required', 'file', 'mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/mp4,audio/webm,audio/aac,audio/flac,audio/opus,audio/x-m4a', 'max:10240'],
        ]);

        $transcript = Transcription::fromUpload($request->file('audio'))->generate();

        return response()->json(['text' => (string) $transcript]);
    }
}
