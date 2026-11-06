<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductoController;

Route::get('/', function () {
    return view('welcome');
});

Route::apiResource('productos', ProductoController::class);

Route::view('/inventario', 'inventario');
