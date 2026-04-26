<div class="mt-4">
    <button id="toggleFilters"
            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
        Szűrési opciók ▼
    </button>

    <div id="filterPanel"
         class="mt-2 p-4 border rounded bg-gray-50 hidden">

        <p class="font-semibold mb-2">Rendezés:</p>

        <div class="grid grid-cols-2 gap-3">
            @foreach ([
                'az' => 'A–Z',
                'za' => 'Z–A',
                'updated_newest' => 'Legújabb → Legrégebbi',
                'updated_oldest' => 'Legrégebbi → Legújabb',
                'most_likes' => 'Legtöbb kedvelés'
            ] as $key => $label)
                <label class="flex items-center space-x-2">
                    <input type="checkbox"
                           class="filter-check"
                           data-filter="{{ $key }}">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <p class="font-semibold mt-4 mb-2">Elrejtés:</p>

        <div class="grid grid-cols-2 gap-3">
            <label class="flex items-center space-x-2">
                <input type="checkbox"
                       class="filter-check"
                       data-filter="hide_reported">
                <span>Már jelentett fórumbejegyzések elrejtése</span>
            </label>

            <label class="flex items-center space-x-2">
                <input type="checkbox"
                       class="filter-check"
                       data-filter="hide_disliked">
                <span>Nem tetsző fórumbejegyzések elrejtése</span>
            </label>
        </div>

    </div>
</div>
