<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-pay/{token}', function ($token) {
    return view('pay', compact('token'));
});
