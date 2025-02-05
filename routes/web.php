<?php

use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [WeatherController::class, 'index']);
Route::post('/weather', [WeatherController::class, 'getWeather'])->name('weather.search');

Route::post('/weather/coords', [WeatherController::class, 'getWeatherByCoords'])->name('weather.coords');
