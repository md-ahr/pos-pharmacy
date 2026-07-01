<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('tyro-dashboard.index');
    }

    return view('welcome');
});
