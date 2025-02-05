<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function index()
    {
        return view('weather.index');
    }

    public function getWeather(Request $request)
    {
        $request->validate([
            'city' => 'required|string'
        ]);

        $apiKey = env('OPENWEATHER_API_KEY');
        $city = $request->input('city');

        $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}&units=metric";

        try {
            $response = Http::get($weatherUrl);

            if ($response->failed()) {
                return back()->withErrors(['error' => 'Gagal mengambil data cuaca. Pastikan kota yang dimasukkan benar.']);
            }

            $weatherData = $response->json(); // Pastikan variabel ini ada
            return view('weather.index', compact('weatherData', 'city'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengambil data cuaca.']);
        }
    }

    public function getWeatherByCoords(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric'
        ]);

        $apiKey = env('OPENWEATHER_API_KEY');
        $lat = $request->lat;
        $lon = $request->lon;

        $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$apiKey}&units=metric";

        try {
            $weatherResponse = Http::get($weatherUrl);

            if ($weatherResponse->failed()) {
                return response()->json(['error' => 'Gagal mengambil data dari OpenWeatherMap.'], 400);
            }

            return response()->json($weatherResponse->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data.'], 500);
        }
    }
}
