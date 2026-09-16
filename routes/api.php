<?php

use App\Http\Controllers\NewsletterController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:100,1')
    ->prefix('newsletter')
    ->group(function (): void {
        Route::post('subscribe', [NewsletterController::class, 'subscribe']);
    });
