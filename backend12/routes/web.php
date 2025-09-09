<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/sanctum/csrf-token',function(){
    return csrf_token();
});
Route::get('/', function () {
    // This can return a view for your SPA's entry point,
    // or be handled by Nginx to serve index.html directly.
    // For clarity, we'll assume Nginx handles this.
    // return view('welcome');
});
