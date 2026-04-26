<x-layout>
    <div class="container mx-auto mt-6">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Témakörök</h1>

            <a href="{{ route('forum.myforums') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Saját fórumbejegyzések
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

        @if ($hasPastTours)
            <div class="mb-6">
                <a href="{{ route('tour.ratings') }}"
                   class="block w-full text-center bg-blue-600 text-white py-3 rounded-lg
                          text-lg font-semibold hover:bg-blue-700 transition">
                    Túra visszajelzések
                </a>
            </div>
        @endif

        @foreach ($categories as $category)
            <div class="mb-10">

                <h2 class="block w-full text-center bg-blue-600 text-white py-3 rounded-lg
                        text-lg font-semibold hover:bg-blue-700 transition mb-4">
                    <a href="{{ route('forum.show', $category->id) }}"
                    class="text-black-600 hover:underline">
                        {{ $category->name }}
                    </a>
                </h2>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">

                    @foreach ($category->forums as $forum)
                        <x-forum-card :forum="$forum" />
                    @endforeach

                    @if ($category->forums()->count() > 5)
                        <a href="{{ route('forum.show', $category->id) }}"
                        class="flex items-center justify-center border rounded-xl p-4 shadow-sm 
                                hover:shadow-lg transition bg-gray-100 text-1xl font-bold text-gray-600">
                            További bejegyzések →
                        </a>
                    @endif

                </div>
            </div>
        @endforeach

    </div>
</x-layout>
