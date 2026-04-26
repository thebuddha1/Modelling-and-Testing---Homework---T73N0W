@if ($hasTourRoutes ?? false)
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
@endif

<x-layout>
    @guest
        @include('partials._hero')
    @endguest

    <!-- Mock advertisement -->
    @if(auth()->user() && (!auth()->user()->subscription_end_at || auth()->user()->subscription_end_at->isPast()))
        @include('partials._ad')
    @endif

    <section class="max-w-5xl mx-auto px-4 py-10">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">Aktuális túrák</h2>

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

        @if ($tours->isEmpty())
            <p class="text-gray-600">Jelenleg nincs elérhető publikus túra. Nézz vissza később vagy hozz létre egyet a túratervezőben!</p>
        @else
            <div class="space-y-5">
                @foreach ($tours as $tour)
                    @php
                        $isOwner = auth()->check() && (int) auth()->id() === (int) $tour->user_id;
                        $isParticipant = in_array($tour->id, $joinedTourIds, true);
                        $isPast = optional($tour->date)?->isBefore(now()->startOfDay());
                        
                        $routeData = [
                            'coordinates' => $tour->route_geometry['coordinates'] ?? [],
                            'waypoints' => $tour->route_geometry['waypoints'] ?? [],
                            'segments' => $tour->route_geometry['segments'] ?? []
                        ];
                        
                        $hasValidRoute = !empty($routeData['coordinates']) && count($routeData['coordinates']) >= 2;
                    @endphp
                    <div id="tour-{{ $tour->id }}" class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="space-y-2">
                                <h3 class="text-2xl font-semibold text-gray-900">
                                    <a href="{{ route('tours.show', $tour) }}" class="hover:underline">{{ $tour->name }}</a>
                                    @if ($isOwner)
                                        <span class="ml-2 inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Saját túra</span>
                                    @endif
                                </h3>
                                <p class="text-gray-600">{{ \Illuminate\Support\Str::limit($tour->description, 160) }}</p>
                                <div class="flex flex-wrap gap-4 text-sm text-gray-700">
                                    <span><strong>Időpont:</strong> {{ $tour->date?->format('Y. m. d.') }}</span>
                                    <span><strong>Helyszín:</strong> {{ $tour->location }}</span>
                                    <span><strong>Szervező:</strong> {{ optional($tour->user)->name }}</span>
                                    <span><strong>Telítettség:</strong> {{ $tour->participants_count }}/{{ $tour->max_participants }}</span>
                                    @if (! $tour->is_public)
                                        <span class="inline-flex items-center rounded bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800">Privát túra</span>
                                    @endif
                                    @if ($isPast)
                                        <span class="inline-flex items-center rounded bg-gray-200 px-2 py-1 text-xs font-semibold text-gray-700">Lejárt túra</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 md:justify-end">
                                <a href="{{ route('tours.show', $tour) }}" class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Részletek</a>
                                @if ($isOwner)
                                    <a href="{{ route('tours.edit', $tour) }}" class="rounded-md bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-600">Szerkesztés</a>
                                    <form action="{{ route('tours.destroy', $tour) }}" method="POST" class="inline" onsubmit="return confirm('Biztosan törölni szeretnéd ezt a túrát?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Túra törlése</button>
                                    </form>
                                @endif

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
                                    @elseif (! $tour->is_full)
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
                                    @if ($tour->is_full || $isPast)
                                        <span class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-600">Betelt</span>
                                    @else
                                        <a href="{{ route('login') }}" class="rounded-md bg-blue-600 px-4 py-2 text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                            Bejelentkezés csatlakozáshoz
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        </div>

                        @if ($hasValidRoute)
                            <div>
                                <p class="text-sm text-gray-500 mb-2">Útvonal a térképen</p>
                                <div
                                    class="tour-mini-map h-48 w-full rounded-md border border-gray-200"
                                    data-tour-map
                                    data-route-data='@json($routeData)'
                                    data-is-past="{{ $isPast ? 'true' : 'false' }}"
                                    data-is-full="{{ $tour->is_full ? 'true' : 'false' }}"
                                    id="tour-map-{{ $tour->id }}"
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
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layout>