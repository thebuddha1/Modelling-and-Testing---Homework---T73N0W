<x-layout>

    <div class="container mx-auto mt-6">

        <div class="mb-4">
            <a href="{{ route('forum.index') }}"
               class="inline-block text-gray-600 hover:text-gray-800 border rounded px-3 py-1">
                Vissza az témákhoz
            </a>
        </div>

        <h1 class="text-3xl font-bold mb-6 text-center">
            Túra visszajelzések
        </h1>

        @if ($tours->isEmpty())
            <p class="text-gray-600 text-center">
                Még nem vettél részt egyetlen lezajlott túrán sem.
            </p>
        @else

            <div class="grid md:grid-cols-2 gap-6">

                @foreach ($tours as $tour)
                    <a href="{{ route('tour.rating.detail', $tour->id) }}"
                       class="block border rounded-xl p-4 shadow hover:shadow-lg transition bg-white">

                        <h2 class="text-xl font-semibold text-gray-800 mb-2">
                            {{ $tour->name }}
                        </h2>

                        <p class="text-gray-700 text-sm mb-3 line-clamp-3">
                            {{ $tour->description }}
                        </p>

                        <p class="text-xs text-gray-500 mt-3">
                            Túra dátuma:
                            <span class="font-medium">
                                {{ $tour->date ? $tour->date->format('Y.m.d H:i') : 'Nincs dátum' }}
                            </span>
                        </p>

                    </a>
                @endforeach

            </div>

        @endif

    </div>

</x-layout>
