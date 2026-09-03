<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/rooms/create', function () {
    return view('rooms.create');
})->name('rooms.create');

Route::get('/', function () {
    return redirect()->route('rooms.create');
});

Route::post('/rooms', [RoomController::class, 'store']);

Route::get('/rooms/{uuid}', [RoomController::class, 'show'])
    ->name('rooms.show');

Route::post('/rooms/{uuid}/join', [ParticipantController::class, 'join'])
    ->name('rooms.join');

Route::get('/rooms/{uuid}/playback', [RoomController::class, 'getPlayback'])
    ->name('rooms.playback.status');

Route::post('/rooms/{uuid}/playback', [RoomController::class, 'playback'])
    ->name('rooms.playback.update');

Route::post('/rooms/{uuid}/end', [RoomController::class, 'endSession'])
    ->name('rooms.end');

Route::post('/rooms/{uuid}/chat', [ChatController::class, 'send'])
    ->name('rooms.chat.send')
    ->middleware('throttle:10,1');

Route::post(
    '/rooms/{uuid}/participants/{participantId}/mute',
    [ParticipantController::class, 'mute']
)->name('rooms.participants.mute');

Route::delete(
    '/rooms/{uuid}/chat/{messageId}',
    [ChatController::class, 'delete']
)->name('rooms.chat.delete');

Route::post('/rooms/{uuid}/chat/toggle', [ChatController::class, 'toggle'])
    ->name('rooms.chat.toggle');
