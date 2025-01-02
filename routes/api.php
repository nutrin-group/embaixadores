<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/discounts', [ShopifyController::class, 'createDiscount']);
});
