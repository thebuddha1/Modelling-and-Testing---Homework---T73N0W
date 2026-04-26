<x-layout>
    <div class="container mx-auto mt-6">

        <div class="mb-4">
            <a href="{{ route('forum.index') }}"
               class="inline-block text-gray-600 hover:text-gray-800 border rounded px-3 py-1">
                Vissza a témákhoz
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('nochange'))
            <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-4">
                {{ session('nochange') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">
                {{ $category->name }} témakör fórumbejegyzései 
            </h1>

            <a href="{{ route('forum.create', $category->id ?? 1) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Új fórumbejegyzés létrehozása
            </a>
        </div>

        <div class="mb-6">
            <input id="forumSearch"
                   type="text"
                   placeholder="Fórumbejegyzés keresése..."
                   class="w-full border rounded-lg p-2">
        </div>

        <x-listfilterpanel />

        <div class="my-6 border-b"></div>

        <div id="forumListContainer"
             class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">

            @forelse ($forums as $forum)
                <x-forum-card
                    :forum="$forum"
                    class="forum-card"
                    searchable="true"
                />
            @empty
                <p class="text-gray-600">
                    Még nincsenek fórumbejegyzések ebben a kategóriában.
                </p>
            @endforelse

        </div>

    </div>

    <script src="/js/forum/listfilter.js"></script>
</x-layout>
