<?php
use Illuminate\Support\Facades\Route;

Route::view('/', 'index')->name('home');
Route::view('/historia', 'historia')->name('historia');
Route::view('/mision-vision', 'mision-vision')->name('mision.vision');