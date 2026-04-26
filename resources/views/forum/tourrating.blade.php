<x-layout>
    <link rel="stylesheet" href="{{ asset('css/tourratings.css') }}">

    <div class="container mx-auto mt-6">

        <div class="mb-4">
            <a href="{{ route('tour.ratings') }}"
               class="inline-block text-gray-600 hover:text-gray-800 border rounded px-3 py-1">
                Vissza az előző oldalra
            </a>
        </div>

        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2">{{ $tour->name }}</h1>

            <p class="text-gray-600 text-lg">
                {{ $tour->date ? $tour->date->format('Y.m.d H:i') : 'Nincs dátum' }}
            </p>

            <div class="mt-4 text-gray-800 leading-relaxed whitespace-pre-line">
                {{ $tour->description }}
            </div>
        </div>

        <h2 class="text-2xl font-semibold mb-4 mt-10">
            Résztvevők értékelése
        </h2>

        @foreach ($participants as $user)

            @if (!$user->is_self)

                <div class="border rounded-xl p-5 mb-6 bg-white shadow">

                    <div class="flex items-center space-x-4 mb-4">
                        <img src="{{ $user->avatar_url }}"
                             class="w-12 h-12 rounded-full object-cover">

                        <a href="#"
                           class="text-lg font-semibold text-black hover:underline">
                            {{ $user->name }}
                        </a>
                    </div>

                    @if ($user->existing_rating)

                        <form method="POST"
                              action="{{ route('tour.rating.delete', [$tour->id, $user->id]) }}">
                            @csrf
                            @method('DELETE')

                            <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                Ezt a felhasználót már értékelted. Visszavonod?
                            </button>
                        </form>

                    @else

                        <form method="POST"
                              action="{{ route('tour.rate', [$tour->id, $user->id]) }}"
                              class="ratingForm space-y-6">
                            @csrf

                            {{-- Pontosság --}}
                            <div>
                                <p class="font-medium mb-2">Pontosság</p>
                                <div class="flex space-x-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <label class="rating-option">
                                            <input type="radio"
                                                   name="precision"
                                                   value="{{ $i }}"
                                                   class="rating-radio">
                                            <span>{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            {{-- Vezetési készség --}}
                            <div>
                                <p class="font-medium mb-2">Vezetési készség</p>
                                <div class="flex space-x-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <label class="rating-option">
                                            <input type="radio"
                                                   name="driving"
                                                   value="{{ $i }}"
                                                   class="rating-radio">
                                            <span>{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            {{-- Közösségi szellem --}}
                            <div>
                                <p class="font-medium mb-2">Közösségi szellem</p>
                                <div class="flex space-x-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <label class="rating-option">
                                            <input type="radio"
                                                   name="social"
                                                   value="{{ $i }}"
                                                   class="rating-radio">
                                            <span>{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            <button type="submit"
                                    class="submitRatingBtn bg-blue-600 text-white px-4 py-2 rounded
                                           opacity-50 cursor-not-allowed"
                                    disabled>
                                Értékelés leadása
                            </button>

                        </form>

                    @endif

                </div>

            @endif

        @endforeach

    </div>

    <script src="{{ asset('js/forum/tourrating.js') }}"></script>

</x-layout>
