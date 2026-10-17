<?php

use App\Http\Controllers\Api\ApiController;
use \Illuminate\Support\Facades\Route;

Route::controller(ApiController::class)->group(function () {
    Route::get('/api', 'getIndex');
    Route::any('/api/{_missing}', 'missingMethod')->where('_missing', '(.*)')->fallback();

    // The OpenAPI 3 spec recommends that the API definition should be named openapi.json
    Route::get('/openapi.json', 'getIndex');
});
