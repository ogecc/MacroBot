<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Ai\Transcription;

test('guest cannot access voice transcription endpoint', function () {
    $this->post(route('voice.transcribe'), [])->assertRedirect(route('login'));
});

test('voice transcription requires an audio file', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->post(route('voice.transcribe'), [])
        ->assertSessionHasErrors('audio');
});

test('voice transcription returns transcribed text', function () {
    Transcription::fake(['grilled chicken 150g with rice and broccoli']);

    $user = User::factory()->withProfile()->create();

    $file = UploadedFile::fake()->create('audio.webm', 50, 'audio/webm');

    $this->actingAs($user)
        ->post(route('voice.transcribe'), ['audio' => $file])
        ->assertOk()
        ->assertJson(['text' => 'grilled chicken 150g with rice and broccoli']);
});

test('voice transcription rejects files over 10mb', function () {
    $user = User::factory()->withProfile()->create();

    $file = UploadedFile::fake()->create('audio.webm', 11000, 'audio/webm');

    $this->actingAs($user)
        ->post(route('voice.transcribe'), ['audio' => $file])
        ->assertSessionHasErrors('audio');
});
