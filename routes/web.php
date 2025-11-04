<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Website\Payment\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/payment-success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment-failed', [PaymentController::class, 'failed'])->name('payment.failed');
