<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenAIController;

Route::get('/chat', function () {
    return view('chat');
});

Route::post('/chat', [OpenAIController::class, 'chat']);

Route::get('/chat/history', [OpenAIController::class, 'getChatHistory']);