<?php
use Illuminate\Support\Facades\Route;use App\Http\Controllers\AuthController;
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'loginForm'])->name('login');Route::post('/login',[AuthController::class,'login'])->middleware('throttle:5,1')->name('login.store');Route::get('/register',[AuthController::class,'registerForm'])->name('register');Route::post('/register',[AuthController::class,'register'])->middleware('throttle:5,10')->name('register.store');});
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth')->name('logout');
