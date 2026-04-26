<x-layout>
    @if (auth()->user())
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <link rel="stylesheet" href="{{ asset('css/map.css') }}" />

        <div class="map-container">
            <div style="flex:1">
                <div id="map"></div>
            </div>
            <div class="map-sidebar">
                <button id="share-fav" class="btn btn-muted">Hely megosztása</button>
                <button id="details-fav" class="btn btn-muted">Részletek</button>
                <div id="share-msg"></div>
                <button id="add-fav" class="btn">Új kedvenc hely hozzáadása</button>
                <div id="instruction" style="margin-top:.5rem; color:#1e293b; font-size:.95rem; display:none">Kattintson a gombra a hely kiválasztásához.</div>

                <div id="favorite-form" style="margin-top:0.75rem">
                    <label>Hely neve</label>
                    <div class="fav-row">
                        <input id="fav-name" type="text" placeholder="Rövid név (pl. Kedvenc kávézó)">
                        <div class="fav-buttons">
                            <button id="save-fav" class="btn">Mentés</button>
                            <button id="cancel-fav" class="btn btn-muted">Mégse</button>
                        </div>
                    </div>
                    <div id="fav-error"></div>
                    <input id="fav-lat" type="hidden">
                    <input id="fav-lng" type="hidden">
                </div>

                <div id="favorites-panel" style="margin-top:1rem; background:#f8fafc; padding:0.5rem; border-radius:6px; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                    <h3 style="margin:0 0 .5rem 0; font-weight:700; font-size:1rem; text-align:left">Kedvenc helyek</h3>
                    <ul id="favorites-list" style="list-style:none; padding:0; margin:0;">
                        <!-- list entries inserted here -->
                    </ul>
                </div>
            </div>
        </div>

        <!-- Share modal -->
        <div id="share-modal-backdrop">
            <div id="share-modal">
                <div>
                    <h3>Kiknek szeretnéd megosztani?</h3>
                    <button id="share-modal-close">✖</button>
                </div>
                <div id="friends-list">
                    <!-- Friends inserted here -->
                </div>
                <div>
                    <button id="share-modal-share" class="btn btn-muted">Megosztás</button>
                    <button id="share-modal-cancel" class="btn btn-muted">Mégse</button>
                </div>
            </div>
        </div>

        <!-- Details modal -->
        <div id="details-modal-backdrop">
            <div id="details-modal">
                <div>
                    <h3 id="details-modal-title">Hely részletei</h3>
                    <button id="details-modal-close">✖</button>
                </div>
                <div id="details-content">
                    <label for="details-description">Leírás</label>
                    <textarea id="details-description" rows="8" readonly></textarea>
                    <div id="details-error" style="color:#b91c1c; font-size:0.9rem; margin-top:0.5rem; display:none;"></div>
                </div>
                <div id="details-actions">
                    <button id="details-edit-btn" class="btn btn-muted" style="display:none;">Leírás módosítása</button>
                    <button id="details-save-btn" class="btn" style="display:none;">Mentés</button>
                    <button id="details-cancel-btn" class="btn btn-muted">Mégse</button>
                </div>
            </div>
        </div>

        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            window.appRoutes = {
                favoritesIndex: '{{ url('/favorites') }}',
                favoritesStore: '{{ url('/favorites') }}'
            };

            window.appConfig = { csrfToken: '{{ csrf_token() }}' };
        </script>
        <script src="{{ asset('js/map.js') }}" defer></script>
    @endif
</x-layout>