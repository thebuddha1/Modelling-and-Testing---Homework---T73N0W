@push('head')
    <link rel="stylesheet" href="{{ asset('leaflet/leaflet.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('leaflet/leaflet.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mapContainers = document.querySelectorAll('[data-tour-map]');

            if (!mapContainers.length) {
                return;
            }

            const difficultyStyles = {
                'average': {
                    color: null,
                    weight: 4,
                    opacity: 0.9,
                    dashArray: null
                },
                'attention': {
                    color: null,
                    weight: 5,
                    opacity: 0.9,
                    dashArray: '10, 8'
                }
            };

            const difficultyLabels = {
                'average': 'Átlagos',
                'attention': 'Fokozott figyelmet igényel'
            };

            const qualityColors = {
                'asphalt': null, // Will use baseColor
                'dirt': '#d97706'
            };

            const qualityLabels = {
                'asphalt': 'Aszfalt',
                'dirt': 'Földút'
            };

            const tooltip = document.createElement('div');
            tooltip.style.position = 'fixed';
            tooltip.style.display = 'none';
            tooltip.style.backgroundColor = 'rgba(0, 0, 0, 0.85)';
            tooltip.style.color = 'white';
            tooltip.style.padding = '6px 10px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '13px';
            tooltip.style.pointerEvents = 'none';
            tooltip.style.zIndex = '10000';
            tooltip.style.whiteSpace = 'pre-line';
            tooltip.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
            document.body.appendChild(tooltip);

            mapContainers.forEach((container) => {
                let routeData = null;

                try {
                    routeData = JSON.parse(container.dataset.routeData || '{}');
                } catch (error) {
                    routeData = null;
                }

                if (!routeData || !routeData.coordinates || !Array.isArray(routeData.coordinates) || routeData.coordinates.length < 2) {
                    return;
                }

                const coordinates = routeData.coordinates
                    .map((point) => {
                        if (Array.isArray(point)) {
                            return L.latLng(point[0], point[1]);
                        }
                        return L.latLng(point.lat, point.lng);
                    })
                    .filter((latLng) => latLng && isFinite(latLng.lat) && isFinite(latLng.lng));

                if (coordinates.length < 2) {
                    return;
                }

                const map = L.map(container, {
                    attributionControl: false,
                    zoomControl: true,
                    dragging: true,
                    scrollWheelZoom: true,
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(map);

                const baseColor = container.dataset.isPast === 'true'
                    ? '#9ca3af'
                    : container.dataset.isFull === 'true'
                        ? '#f97316'
                        : '#2563eb';

                if (routeData.waypoints && Array.isArray(routeData.waypoints) && 
                    routeData.segments && Array.isArray(routeData.segments) && 
                    routeData.waypoints.length > 1) {
                    
                    const waypoints = routeData.waypoints
                        .map(wp => L.latLng(wp.lat, wp.lng))
                        .filter((latLng) => latLng && isFinite(latLng.lat) && isFinite(latLng.lng));

                    if (waypoints.length > 1) {
                        let currentIndex = 0;

                        routeData.segments.forEach((segment, i) => {
                            if (i >= waypoints.length - 1) return;

                            const segmentCoords = [];
                            const start = waypoints[i];
                            const end = waypoints[i + 1];

                            for (let j = currentIndex; j < coordinates.length; j++) {
                                const coord = coordinates[j];
                                segmentCoords.push(coord);

                                const distToEnd = map.distance(coord, end);

                                if (distToEnd < 50 || j === coordinates.length - 1) {
                                    currentIndex = j + 1;
                                    break;
                                }
                            }

                            if (segmentCoords.length < 2) return;

                            const difficulty = segment.difficulty || 'average';
                            const quality = segment.quality || 'asphalt';
                            const style = difficultyStyles[difficulty];
                            const color = qualityColors[quality] || baseColor;

                            const polyline = L.polyline(segmentCoords, {
                                color: color,
                                weight: style.weight,
                                opacity: style.opacity,
                                dashArray: style.dashArray,
                                lineCap: 'round',
                                lineJoin: 'round',
                            }).addTo(map);

                            polyline.on('mouseover', function(e) {
                                this.setStyle({ weight: style.weight + 2 });
                                tooltip.textContent = `Nehézség: ${difficultyLabels[difficulty]}\nMinőség: ${qualityLabels[quality]}`;
                                tooltip.style.display = 'block';
                            });

                            polyline.on('mousemove', function(e) {
                                tooltip.style.left = (e.originalEvent.clientX + 15) + 'px';
                                tooltip.style.top = (e.originalEvent.clientY + 15) + 'px';
                            });

                            polyline.on('mouseout', function(e) {
                                this.setStyle({ weight: style.weight });
                                tooltip.style.display = 'none';
                            });
                        });
                    } else {
                        L.polyline(coordinates, {
                            color: baseColor,
                            weight: 4,
                            opacity: 0.9,
                            lineCap: 'round',
                            lineJoin: 'round',
                        }).addTo(map);
                    }
                } else {
                    L.polyline(coordinates, {
                        color: baseColor,
                        weight: 4,
                        opacity: 0.9,
                        lineCap: 'round',
                        lineJoin: 'round',
                    }).addTo(map);
                }

                const start = coordinates[0];
                const end = coordinates[coordinates.length - 1];

                L.circleMarker(start, { 
                    radius: 5, 
                    color: '#10b981', 
                    fillColor: '#10b981', 
                    fillOpacity: 1,
                    weight: 2
                }).addTo(map);
                
                if (end && (end.lat !== start.lat || end.lng !== start.lng)) {
                    L.circleMarker(end, { 
                        radius: 5, 
                        color: '#ef4444', 
                        fillColor: '#ef4444', 
                        fillOpacity: 1,
                        weight: 2
                    }).addTo(map);
                }

                const bounds = L.latLngBounds(coordinates);
                map.fitBounds(bounds, {
                    padding: [15, 15],
                    maxZoom: 13,
                });
            });
        });
    </script>
@endpush

<x-layout>
    <div class="max-w-4xl mx-auto px-4 py-10">
        <div class="mb-6">
            <a href="{{ route('home') }}" class="text-blue-600 hover:underline">&larr; Vissza a főoldalra</a>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-md bg-green-100 px-4 py-3 text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-md bg-red-100 px-4 py-3 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $tour->name }}</h1>
                    @if ($isOwner)
                        <div class="flex gap-2">
                            <a href="{{ route('tours.edit', $tour) }}" class="rounded-md bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-600">Szerkesztés</a>
                            <form action="{{ route('tours.destroy', $tour) }}" method="POST" class="inline" onsubmit="return confirm('Biztosan törölni szeretnéd ezt a túrát?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Túra törlése</button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Részletek</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li><strong>Időpont:</strong> {{ $tour->date?->format('Y. m. d.') }}</li>
                            <li><strong>Helyszín:</strong> {{ $tour->location }}</li>
                            <li class="flex items-center gap-2">
                                <strong>Szervező:</strong>
                                @if($tour->user)
                                    <a href="{{ url('/profile/' . $tour->user->id) }}" class="flex items-center gap-2 hover:text-blue-600 transition">
                                        <img src="{{ $tour->user->avatar_url }}" alt="{{ $tour->user->name }} profilképe" class="w-6 h-6 rounded-full object-cover border border-gray-200">
                                        <span class="font-medium">{{ $tour->user->name }}</span>
                                    </a>
                                @else
                                    <span>Ismeretlen</span>
                                @endif
                            </li>
                            <li><strong>Telítettség:</strong> {{ $tour->participants->count() }}/{{ $tour->max_participants }}</li>
                            <li><strong>Státusz:</strong> 
                                @if (! $tour->is_public)
                                    <span class="inline-flex items-center rounded bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800">Privát</span>
                                @else
                                    <span class="inline-flex items-center rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">Publikus</span>
                                @endif
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Leírás</h3>
                        <p class="text-gray-600">{{ $tour->description }}</p>
                    </div>
                </div>

                @php
                    $routeData = [
                        'coordinates' => $tour->route_geometry['coordinates'] ?? [],
                        'waypoints' => $tour->route_geometry['waypoints'] ?? [],
                        'segments' => $tour->route_geometry['segments'] ?? []
                    ];
                    
                    $hasValidRoute = !empty($routeData['coordinates']) && count($routeData['coordinates']) >= 2;
                @endphp

                @if ($hasValidRoute)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Útvonal</h3>
                        <div
                            class="h-96 w-full rounded-md border border-gray-200"
                            data-tour-map
                            data-route-data='@json($routeData)'
                            data-is-past="{{ $isPast ? 'true' : 'false' }}"
                            data-is-full="{{ $tour->participants->count() >= $tour->max_participants ? 'true' : 'false' }}"
                        ></div>
                        <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                <span class="inline-block w-3 h-3 rounded-full bg-[#22c55e]"></span>
                                Kezdőpont
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="inline-block w-3 h-3 rounded-full bg-[#ef4444]"></span>
                                Célpont
                            </span>
                            @if (!empty($routeData['segments']))
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-block w-6 h-0.5 bg-gray-800"></span>
                                    Átlagos
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <svg width="24" height="2" class="flex-shrink-0">
                                        <line x1="0" y1="1" x2="24" y2="1" stroke="#1f2937" stroke-width="2" stroke-dasharray="3,3" />
                                    </svg>
                                    Fokozott figyelmet igényel
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-block w-3 h-3 rounded-full bg-[#3b82f6]"></span>
                                    Aszfalt
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-block w-3 h-3 rounded-full bg-[#d97706]"></span>
                                    Földút
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold mb-4">Résztvevők</h3>
                    @if($tour->participants->isEmpty())
                        <p class="text-gray-500">Még nincsenek résztvevők.</p>
                    @else
                        <ul class="space-y-2 mb-4">
                            @foreach($tour->participants as $participant)
                                <li>
                                    <a href="{{ url('/profile/' . $participant->id) }}" class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-100 transition">
                                        <img src="{{ $participant->avatar_url }}" alt="{{ $participant->name }} profilképe" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                        <span class="text-gray-800 font-medium">{{ $participant->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($isOwner)
                        <div class="mt-4 p-4 bg-gray-50 rounded-md">
                            <h4 class="font-semibold mb-2">Résztvevő meghívása</h4>
                            
                            <div class="mb-3">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="friends-only-checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Csak barátok</span>
                                </label>
                            </div>

                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="invite-user-search" 
                                    placeholder="Keresés név alapján..." 
                                    autocomplete="off"
                                    data-tour-id="{{ $tour->id }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2"
                                >
                                <div 
                                    id="invite-search-results" 
                                    class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 hidden"
                                ></div>
                            </div>

                            <form id="invite-form" action="{{ route('tours.invite', $tour) }}" method="POST" class="hidden">
                                @csrf
                                <input type="hidden" name="user_id" id="invite-user-id">
                            </form>
                        </div>
                    @endif
                    
                    <div class="mt-6 flex justify-end">
                         @auth
                            @if ($isPast)
                                @if ($isParticipant)
                                    <span class="rounded-md bg-gray-100 px-4 py-2 text-sm text-gray-600">Részt vettél ezen a túrán</span>
                                @elseif ($isOwner)
                                    <span class="rounded-md bg-gray-100 px-4 py-2 text-sm text-gray-600">Ez a túra lezárult</span>
                                @endif
                            @elseif ($isParticipant)
                                <form method="POST" action="{{ route('tours.leave', $tour) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400">
                                        Jelentkezés visszavonása
                                    </button>
                                </form>
                            @elseif (! ($tour->participants->count() >= $tour->max_participants))
                                <form method="POST" action="{{ route('tours.join', $tour) }}">
                                    @csrf
                                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        Csatlakozás
                                    </button>
                                </form>
                            @else
                                <span class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-600">Betelt</span>
                            @endif
                        @else
                            @if (($tour->participants->count() >= $tour->max_participants) || $isPast)
                                <span class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-600">Betelt</span>
                            @else
                                <a href="{{ route('login') }}" class="rounded-md bg-blue-600 px-4 py-2 text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    Bejelentkezés csatlakozáshoz
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($isOwner)
        <script src="{{ asset('js/tour-invite-search.js') }}"></script>
    @endif
</x-layout>