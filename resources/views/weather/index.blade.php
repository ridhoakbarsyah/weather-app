<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather App</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="w-full max-w-lg bg-white p-6 rounded-lg shadow-lg relative">
        <h2 class="text-2xl font-bold text-center mb-4">Weather App</h2>

        <!-- Mode Gelap -->
        <button id="toggleTheme" class="absolute top-4 right-4 bg-gray-700 text-white px-4 py-2 rounded-lg">
            🌙 Mode Gelap
        </button>

        <!-- Form Pencarian -->
        <form action="{{ route('weather.search') }}" method="POST" class="mb-4">
            @csrf
            <input type="text" id="cityInput" name="city" placeholder="Masukkan nama kota..."
                class="w-full px-4 py-2 border rounded-lg" required>
            <button type="submit" class="w-full mt-2 bg-blue-500 text-white py-2 rounded-lg">Cari Cuaca</button>
        </form>

        <!-- Button Lokasi -->
        <button id="getLocation" class="w-full bg-green-500 text-white py-2 rounded-lg mt-2">Gunakan Lokasi Saya
            📍</button>

        <!-- Riwayat Pencarian -->
        <div class="mt-4">
            <h3 class="text-lg font-semibold">Riwayat Pencarian:</h3>
            <ul id="searchHistory" class="list-disc pl-5"></ul>
        </div>

        @isset($weatherData)
            <div class="text-center mt-4">
                <h3 class="text-xl font-semibold">{{ $weatherData['name'] }}</h3>
                <img src="http://openweathermap.org/img/wn/{{ $weatherData['weather'][0]['icon'] }}@2x.png" class="mx-auto">
                <p class="text-gray-600">{{ ucfirst($weatherData['weather'][0]['description']) }}</p>
                <p class="text-3xl font-bold">{{ $weatherData['main']['temp'] }}°C</p>
                <p class="text-sm text-gray-500">Kelembapan: {{ $weatherData['main']['humidity'] }}%</p>
            </div>

            <!-- Grafik Cuaca -->
            @isset($forecastData)
                <canvas id="weatherChart" class="mt-4"></canvas>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        let labels = [];
                        let temperatures = [];

                        @foreach ($forecastData['list'] as $hourly)
                            labels.push("{{ \Carbon\Carbon::parse($hourly['dt_txt'])->format('H:i') }}");
                            temperatures.push({{ $hourly['main']['temp'] }});
                        @endforeach

                        const ctx = document.getElementById('weatherChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Pergerakan Suhu (°C)',
                                    data: temperatures,
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: false
                                    }
                                }
                            }
                        });
                    });
                </script>
            @endisset
        @endisset

        <script>
            document.getElementById("getLocation").addEventListener("click", function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            fetch("{{ route('weather.coords') }}", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                    },
                                    body: JSON.stringify({
                                        lat: position.coords.latitude,
                                        lon: position.coords.longitude
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.error) {
                                        alert("Error: " + data.error);
                                    } else {
                                        document.getElementById("cityInput").value = data.name;
                                        document.querySelector("form").submit();
                                    }
                                })
                                .catch(error => alert("Terjadi kesalahan: " + error.message));
                        },
                        function(error) {
                            alert("Gagal mendapatkan lokasi: " + error.message);
                        }
                    );
                } else {
                    alert("Geolocation tidak didukung di browser Anda.");
                }
            });

            document.getElementById("toggleTheme").addEventListener("click", function() {
                document.body.classList.toggle("dark");
                let isDark = document.body.classList.contains("dark");
                this.innerText = isDark ? "☀️ Mode Terang" : "🌙 Mode Gelap";
            });

            document.addEventListener("DOMContentLoaded", function() {
                let historyContainer = document.getElementById("searchHistory");

                function updateHistory(city) {
                    let history = JSON.parse(localStorage.getItem("weatherHistory")) || [];
                    if (!history.includes(city)) {
                        history.push(city);
                        localStorage.setItem("weatherHistory", JSON.stringify(history));
                    }
                    renderHistory();
                }

                function renderHistory() {
                    let history = JSON.parse(localStorage.getItem("weatherHistory")) || [];
                    historyContainer.innerHTML = history.map(city =>
                            `<li class="cursor-pointer text-blue-500" onclick="searchCity('${city}')">${city}</li>`)
                        .join("");
                }
                window.searchCity = function(city) {
                    document.getElementById("cityInput").value = city;
                    document.querySelector("form").submit();
                }
                renderHistory();
            });

            document.querySelector("form").addEventListener("submit", function() {
                let city = document.getElementById("cityInput").value;
                if (city) updateHistory(city);
            });
        </script>

        <style>
            .dark {
                background-color: #1a202c;
                color: white;
            }
        </style>
    </div>
</body>

</html>
