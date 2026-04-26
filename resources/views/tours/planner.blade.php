@push('head')
    <link
        rel="stylesheet"
        href="{{ asset('leaflet/leaflet.css') }}"
    >
    <link
        rel="stylesheet"
        href="{{ asset('leaflet-routing-machine/leaflet-routing-machine.css') }}"
    >
@endpush

@push('scripts')
    <script src="{{ asset('leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('leaflet-routing-machine/leaflet-routing-machine.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mapContainer = document.getElementById('tour-route-map');
            const routeInput = document.getElementById('route');
            const form = document.getElementById('tour-form');
            const clearRouteBtn = document.getElementById('clear-route');
            const segmentPanel = document.getElementById('segment-panel');
            const segmentTitle = document.getElementById('segment-title');
            const segmentDifficulty = document.getElementById('segment-difficulty');
            const segmentQuality = document.getElementById('segment-quality');

            if (!mapContainer || !routeInput || !form) {
                return;
            }

            const defaultCenter = [47.497913, 19.040236]; // Budapest fallback

            const map = L.map(mapContainer).setView(defaultCenter, 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            let segmentPolylines = [];
            let clickablePolylines = [];
            let selectedSegmentIndex = null;
            let segmentDifficulties = {};
            let segmentQualities = {};

            const routingControl = L.Routing.control({
                waypoints: [],
                routeWhileDragging: true,
                show: true,
                addWaypoints: true,
                fitSelectedRoutes: true,
                lineOptions: {
                    styles: [{ color: 'transparent', opacity: 0, weight: 0 }],
                },
            }).addTo(map);

            const difficultyStyles = {
                'average': {
                    weight: 6,
                    opacity: 0.9,
                    dashArray: null
                },
                'attention': {
                    weight: 8,
                    opacity: 0.9,
                    dashArray: '10, 10'
                }
            };

            const qualityColors = {
                'asphalt': '#3b82f6',
                'dirt': '#d97706'
            };

            const difficultyLabels = {
                'average': 'Átlagos',
                'attention': 'Fokozott figyelmet igényel'
            };

            const qualityLabels = {
                'asphalt': 'Aszfalt (Kék)',
                'dirt': 'Földút (Narancs)'
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

            const safeParse = (value) => {
                if (!value) {
                    return null;
                }

                try {
                    return JSON.parse(value);
                } catch (error) {
                    return null;
                }
            };

            const clearSegments = () => {
                segmentPolylines.forEach(poly => map.removeLayer(poly));
                clickablePolylines.forEach(poly => map.removeLayer(poly));
                segmentPolylines = [];
                clickablePolylines = [];
                selectedSegmentIndex = null;
                segmentPanel.classList.add('hidden');
            };

            const highlightSegment = (index) => {
                segmentPolylines.forEach((poly, i) => {
                    const difficulty = segmentDifficulties[i] || 'average';
                    const quality = segmentQualities[i] || 'asphalt';
                    const style = difficultyStyles[difficulty];
                    const color = qualityColors[quality];

                    if (i === index) {
                        poly.setStyle({ 
                            color: color,
                            weight: style.weight + 2,
                            opacity: 1,
                            dashArray: style.dashArray
                        });
                        poly.bringToFront();
                    } else {
                        poly.setStyle({ 
                            color: color,
                            weight: style.weight,
                            opacity: style.opacity,
                            dashArray: style.dashArray
                        });
                    }
                });
            };

            const updateRouteInput = (waypoints, coordinates, summary) => {
                const segments = [];
                for (let i = 0; i < waypoints.length - 1; i++) {
                    segments.push({
                        from: i,
                        to: i + 1,
                        difficulty: segmentDifficulties[i] || 'average',
                        quality: segmentQualities[i] || 'asphalt'
                    });
                }

                const payload = {
                    waypoints,
                    coordinates,
                    summary,
                    segments
                };

                routeInput.value = JSON.stringify(payload);
            };

            const createSegments = (coordinates) => {
                clearSegments();

                const waypoints = routingControl.getWaypoints().filter(wp => wp.latLng);
                
                if (waypoints.length < 2) {
                    return;
                }

                let currentIndex = 0;
                
                for (let i = 0; i < waypoints.length - 1; i++) {
                    const segmentCoords = [];
                    const start = waypoints[i].latLng;
                    const end = waypoints[i + 1].latLng;

                    for (let j = currentIndex; j < coordinates.length; j++) {
                        const coord = coordinates[j];
                        segmentCoords.push([coord.lat, coord.lng]);

                        const distToEnd = map.distance(
                            [coord.lat, coord.lng],
                            [end.lat, end.lng]
                        );

                        if (distToEnd < 50 || j === coordinates.length - 1) {
                            currentIndex = j + 1;
                            break;
                        }
                    }

                    const difficulty = segmentDifficulties[i] || 'average';
                    const quality = segmentQualities[i] || 'asphalt';
                    const style = difficultyStyles[difficulty];
                    const color = qualityColors[quality];

                    const polyline = L.polyline(segmentCoords, {
                        color: color,
                        weight: style.weight,
                        opacity: style.opacity,
                        dashArray: style.dashArray,
                        segmentIndex: i
                    }).addTo(map);

                    // Invisible wider line for easier clicking
                    const clickablePolyline = L.polyline(segmentCoords, {
                        color: 'transparent',
                        weight: 20,
                        opacity: 0,
                        segmentIndex: i
                    }).addTo(map);

                    const handleSegmentClick = function (e) {
                        L.DomEvent.stopPropagation(e);
                        selectedSegmentIndex = this.options.segmentIndex;
                        highlightSegment(selectedSegmentIndex);
                        
                        segmentPanel.classList.remove('hidden');
                        segmentTitle.textContent = `${selectedSegmentIndex + 1}. szakasz`;
                        
                        const currentDifficulty = segmentDifficulties[selectedSegmentIndex] || 'average';
                        segmentDifficulty.value = currentDifficulty;

                        const currentQuality = segmentQualities[selectedSegmentIndex] || 'asphalt';
                        if (segmentQuality) {
                            segmentQuality.value = currentQuality;
                        }
                    };

                    const handleMouseOver = function(e) {
                        if (selectedSegmentIndex !== this.options.segmentIndex) {
                            this.setStyle({ weight: style.weight + 2 });
                        }
                        tooltip.textContent = `${this.options.segmentIndex + 1}. szakasz\nNehézség: ${difficultyLabels[difficulty]}\nMinőség: ${qualityLabels[quality]}`;
                        tooltip.style.display = 'block';
                    };

                    const handleMouseMove = function(e) {
                        tooltip.style.left = (e.originalEvent.clientX + 15) + 'px';
                        tooltip.style.top = (e.originalEvent.clientY + 15) + 'px';
                    };

                    const handleMouseOut = function(e) {
                        if (selectedSegmentIndex !== this.options.segmentIndex) {
                            this.setStyle({ 
                                weight: style.weight,
                                opacity: style.opacity
                            });
                        }
                        tooltip.style.display = 'none';
                    };

                    polyline.on('click', handleSegmentClick);
                    polyline.on('mouseover', handleMouseOver);
                    polyline.on('mousemove', handleMouseMove);
                    polyline.on('mouseout', handleMouseOut);
                    
                    clickablePolyline.on('click', handleSegmentClick);
                    clickablePolyline.on('mouseover', handleMouseOver);
                    clickablePolyline.on('mousemove', handleMouseMove);
                    clickablePolyline.on('mouseout', handleMouseOut);

                    segmentPolylines.push(polyline);
                    clickablePolylines.push(clickablePolyline);
                }
            };



            routingControl.on('routesfound', function (event) {
                const primaryRoute = event.routes[0];
                if (!primaryRoute) {
                    routeInput.value = '';
                    clearSegments();
                    return;
                }

                const waypoints = routingControl
                    .getWaypoints()
                    .filter((wp) => wp.latLng)
                    .map((wp) => ({ lat: wp.latLng.lat, lng: wp.latLng.lng }));

                if (!waypoints.length) {
                    routeInput.value = '';
                    clearSegments();
                    return;
                }

                const coordinates = primaryRoute.coordinates.map((coord) => [coord.lat, coord.lng]);
                
                createSegments(primaryRoute.coordinates);

                const summary = {
                    totalDistance: primaryRoute.summary.totalDistance,
                    totalTime: primaryRoute.summary.totalTime,
                };

                updateRouteInput(waypoints, coordinates, summary);
            });

            routingControl.on('waypointschanged', function () {
                const complete = routingControl
                    .getWaypoints()
                    .every((wp) => Boolean(wp.latLng));

                if (complete) {
                    routingControl.route();
                } else {
                    routeInput.value = '';
                    clearSegments();
                }
            });

            routingControl.on('routingerror', function () {
                routeInput.value = '';
                clearSegments();
            });

            map.on('click', function (event) {
                if (selectedSegmentIndex !== null) {
                    selectedSegmentIndex = null;
                    highlightSegment(null);
                    segmentPanel.classList.add('hidden');
                    return;
                }

                const current = routingControl
                    .getWaypoints()
                    .filter((wp) => wp.latLng)
                    .map((wp) => wp.latLng);

                current.push(event.latlng);
                routingControl.setWaypoints(current);
            });

            if (clearRouteBtn) {
                clearRouteBtn.addEventListener('click', function () {
                    routingControl.setWaypoints([]);
                    routeInput.value = '';
                    map.setView(defaultCenter, 13);
                    clearSegments();
                    segmentDifficulties = {};
                    segmentQualities = {};
                });
            }

            if (segmentDifficulty) {
                segmentDifficulty.addEventListener('change', function () {
                    if (selectedSegmentIndex !== null) {
                        segmentDifficulties[selectedSegmentIndex] = this.value;
                        highlightSegment(selectedSegmentIndex);

                        const stored = safeParse(routeInput.value);
                        if (stored) {
                            updateRouteInput(stored.waypoints, stored.coordinates, stored.summary);
                        }
                    }
                });
            }

            if (segmentQuality) {
                segmentQuality.addEventListener('change', function () {
                    if (selectedSegmentIndex !== null) {
                        segmentQualities[selectedSegmentIndex] = this.value;
                        highlightSegment(selectedSegmentIndex);

                        const stored = safeParse(routeInput.value);
                        if (stored) {
                            updateRouteInput(stored.waypoints, stored.coordinates, stored.summary);
                        }
                    }
                });
            }

            form.addEventListener('submit', function (event) {
                if (!routeInput.value) {
                    event.preventDefault();
                    alert('Kérjük, jelöld ki az útvonalat a térképen.');
                }
            });

            const restoreRoute = () => {
                const stored = safeParse(routeInput.value);
                if (!stored || !Array.isArray(stored.waypoints) || !stored.waypoints.length) {
                    return;
                }

                if (Array.isArray(stored.segments)) {
                    segmentDifficulties = {};
                    segmentQualities = {};
                    stored.segments.forEach(segment => {
                        segmentDifficulties[segment.from] = segment.difficulty || 'average';
                        segmentQualities[segment.from] = segment.quality || 'asphalt';
                    });
                }

                routingControl.setWaypoints(
                    stored.waypoints.map((point) => L.latLng(point.lat, point.lng))
                );

                if (Array.isArray(stored.coordinates) && stored.coordinates.length) {
                    const bounds = L.latLngBounds(
                        stored.coordinates.map((pair) => L.latLng(pair[0], pair[1]))
                    );
                    map.fitBounds(bounds, { padding: [40, 40] });
                }
            };

            restoreRoute();
        });
    </script>
@endpush

<x-layout>
    <div class="max-w-7xl mx-auto pt-2 pb-10 px-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <h1 class="text-3xl font-semibold text-gray-900">Túratervező</h1>
            <a
                href="{{ route('tours.create') }}"
                class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
                Új túra létrehozása
            </a>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-md bg-green-100 px-4 py-3 text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-100 px-4 py-3 text-red-700">
                <p class="font-medium">Kérjük, javítsd az alábbi hibákat:</p>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-3">
                <form id="tour-form" method="POST" action="{{ isset($tour) ? route('tours.update', $tour) : route('tours.store') }}" class="space-y-6 bg-white px-6 pt-3 pb-6 shadow-md rounded-md">
                    @csrf
                    @if(isset($tour))
                        @method('PUT')
                    @endif

                    <div>
                        <label for="tour-route-map" class="block text-sm font-medium text-gray-700 mb-1">Útvonal kiválasztása</label>
                        <p class="text-sm text-gray-500 mb-1">Kattints a térképre pontok megadásához. Minimum 2 pont szükséges (kezdő- és célpont).</p>
                        <div id="tour-route-map" class="h-96 w-full rounded-md border border-gray-300 shadow-inner"></div>
                        <div class="flex justify-end mt-2">
                            <button type="button" id="clear-route" class="text-sm text-red-600 hover:text-red-700">Útvonal törlése</button>
                        </div>
                        <input type="hidden" id="route" name="route" value="{{ old('route', isset($tour) ? json_encode($tour->route_geometry) : '') }}">
                        @error('route')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="segment-panel" class="hidden border border-gray-200 rounded-md p-4 bg-gray-50">
                        <h3 id="segment-title" class="text-base font-medium text-gray-900 mb-3">Szakasz szerkesztése</h3>
                        
                        <div class="mb-4">
                            <label for="segment-difficulty" class="block text-sm font-medium text-gray-700 mb-1">Nehézségi szint</label>
                            <select 
                                id="segment-difficulty" 
                                class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="average" selected>Átlagos</option>
                                <option value="attention">Fokozott figyelmet igényel</option>
                            </select>
                        </div>

                        <div>
                            <label for="segment-quality" class="block text-sm font-medium text-gray-700 mb-1">Út minősége</label>
                            <select 
                                id="segment-quality" 
                                class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="asphalt" selected>Aszfalt</option>
                                <option value="dirt">Földút</option>
                            </select>
                        </div>

                        <div class="mt-3 space-y-2 text-sm">
                            <p class="font-semibold text-xs text-gray-500 uppercase tracking-wider mb-1">Nehézség (Vonal stílusa)</p>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-1 bg-gray-800 rounded flex-shrink-0"></div>
                                <span class="text-gray-600">Átlagos</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg width="48" height="4" class="flex-shrink-0">
                                    <line x1="0" y1="2" x2="48" y2="2" stroke="#1f2937" stroke-width="2" stroke-dasharray="4,4" />
                                </svg>
                                <span class="text-gray-600">Fokozott figyelmet igényel</span>
                            </div>
                            
                            <p class="font-semibold text-xs text-gray-500 uppercase tracking-wider mt-3 mb-1">Minőség (Szín)</p>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-1 bg-[#3b82f6] rounded flex-shrink-0"></div>
                                <span class="text-gray-600">Aszfalt</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-1 bg-[#d97706] rounded flex-shrink-0"></div>
                                <span class="text-gray-600">Földút</span>
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-gray-500">Kattints egy szakaszra a térképen a nehézségi szint beállításához. Kattints a térképre máshova a szerkesztés befejezéséhez.</p>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Túra neve</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            required
                            maxlength="255"
                            value="{{ old('name', $tour->name ?? '') }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Túra időpontja</label>
                        <input
                            id="date"
                            name="date"
                            type="date"
                            required
                            min="{{ $minDate }}"
                            value="{{ old('date', isset($tour) ? $tour->date->format('Y-m-d') : $minDate) }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Túra leírása</label>
                        <textarea id="description" name="description" required rows="4" class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $tour->description ?? '') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_participants" class="block text-sm font-medium text-gray-700 mb-1">Maximális résztvevők száma</label>
                        <input
                            id="max_participants"
                            name="max_participants"
                            type="number"
                            required
                            min="1"
                            max="20"
                            value="{{ old('max_participants', $tour->max_participants ?? 3) }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        @error('max_participants')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Helyszín (indulási pont)</label>
                        <input
                            id="location"
                            name="location"
                            type="text"
                            required
                            maxlength="255"
                            value="{{ old('location', $tour->location ?? '') }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">Publikus státusz</span>
                        <div class="flex items-center space-x-6">
                            <label class="inline-flex items-center">
                                <input
                                    type="radio"
                                    name="is_public"
                                    value="1"
                                    {{ old('is_public', isset($tour) ? (string)$tour->is_public : '1') == '1' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="ml-2 text-gray-700">Publikus</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input
                                    type="radio"
                                    name="is_public"
                                    value="0"
                                    {{ old('is_public', isset($tour) ? (string)$tour->is_public : '1') == '0' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="ml-2 text-gray-700">Privát</span>
                            </label>
                        </div>
                        @error('is_public')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400">
                            Túra mentése
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>