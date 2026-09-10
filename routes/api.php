<?php

use App\Http\Controllers\Api\AffiliationDocumentWebhookController;
use App\Http\Controllers\Api\IndividualQuoteStatsController;
use App\Http\Middleware\VerifyIntegracorpDocumentWebhook;
use App\Http\Middleware\VerifyIntegracorpStatsApiToken;
use Illuminate\Support\Facades\Route;

Route::post('/documents/webhook', [AffiliationDocumentWebhookController::class, 'store'])
    ->middleware([VerifyIntegracorpDocumentWebhook::class, 'throttle:60,1'])
    ->name('api.documents.webhook');

Route::get('/stats/individual-quotes', [IndividualQuoteStatsController::class, 'index'])
    ->middleware([VerifyIntegracorpStatsApiToken::class, 'throttle:60,1'])
    ->name('api.stats.individual-quotes');
