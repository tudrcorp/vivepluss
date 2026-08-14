<?php

use App\Http\Controllers\Api\AffiliationDocumentWebhookController;
use App\Http\Middleware\VerifyIntegracorpDocumentWebhook;
use Illuminate\Support\Facades\Route;

Route::post('/documents/webhook', [AffiliationDocumentWebhookController::class, 'store'])
    ->middleware([VerifyIntegracorpDocumentWebhook::class, 'throttle:60,1'])
    ->name('api.documents.webhook');
