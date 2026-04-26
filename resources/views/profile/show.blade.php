<x-layout>
    <div class="container mx-auto p-4 max-w-4xl">

        @php
            $experienceLabels = [
                'beginner' => 'Kezdő',
                'intermediate' => 'Középhaladó',
                'advanced' => 'Haladó',
                'expert' => 'Expert',
            ];

            $experienceLabel = $user->experience_level
                ? ($experienceLabels[$user->experience_level] ?? $user->experience_level)
                : '—';
        @endphp

        {{-- PROFILE HEADER CARD --}}
        <div class="bg-white/80 rounded-xl shadow p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-6">
                {{-- Avatar --}}
                <div class="flex-shrink-0">
                    <img
                        src="{{ $user->avatar_url }}"
                        alt="{{ $user->name }} profilképe"
                        class="w-28 h-28 rounded-full object-cover border-4 border-gray-100 shadow-sm"
                    >
                </div>

                {{-- Main content --}}
                <div class="flex-1 flex flex-col gap-4">
                    {{-- Name + status + actions --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold">
                                {{ $user->name }}
                            </h1>

                            @if ($user->is_private && auth()->id() == $user->id)
                                <p class="mt-1 text-sm text-gray-500 italic">
                                    A profilod privátra van állítva.
                                </p>
                            @endif
                        </div>

                        {{-- Actions (edit / friend) --}}
                        @auth
                            <div class="flex flex-wrap items-center gap-2">
                                @if (auth()->id() === $user->id)
                                    <a href="/profile/{{ $user->id }}/edit"
                                       class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg shadow-sm transition">
                                        Szerkesztés
                                    </a>
                                @elseif (auth()->id() !== $user->id)
                                    @if (!$isFriend)
                                        {{-- Barát felvétele --}}
                                        <form method="POST" action="{{ route('friends.add') }}">
                                            @csrf
                                            <input type="hidden" name="friend_name" value="{{ $user->name }}">
                                            <button class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg">
                                                Barát felvétele
                                            </button>
                                        </form>
                                    @else
                                        {{-- Barát törlése --}}
                                        <form method="POST" action="{{ route('friends.remove') }}">
                                            @csrf
                                            <input type="hidden" name="friend_name" value="{{ $user->name }}">
                                            <button class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg">
                                                Barát törlése
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        @endauth
                    </div>

                    @if (!$user->is_private || auth()->id() == $user->id)
                        {{-- Bio + Ratings side by side --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Bio card --}}
                            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">
                                <p class="font-semibold mb-1">Bemutatkozás</p>
                                <p class="text-gray-600">
                                    {{ $user->bio ?: 'Még nem adott meg bemutatkozást.' }}
                                </p>
                            </div>

                            {{-- Ratings card --}}
                            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">
                                <p class="font-semibold mb-2">Értékelések</p>

                                @if (is_null($ratings) || empty($ratings))
                                    <p class="text-gray-600">Még nincsenek értékelések.</p>
                                @else
                                    <div class="space-y-2 text-gray-600">
                                        {{-- Pontosság --}}
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs uppercase tracking-wide text-gray-500">Pontosság</span>
                                            <div class="flex items-center gap-1">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <x-rating-circle :value="$ratings['precision']" :index="$i" />
                                                @endfor
                                                <span class="ml-2 text-xs text-gray-500">
                                                    ({{ $ratings['precision'] ?? 0 }}/5.0)
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Vezetési készség --}}
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs uppercase tracking-wide text-gray-500">Vezetési készség</span>
                                            <div class="flex items-center gap-1">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <x-rating-circle :value="$ratings['driving']" :index="$i" />
                                                @endfor
                                                <span class="ml-2 text-xs text-gray-500">
                                                    ({{ $ratings['driving'] ?? 0 }}/5.0)
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Közösségi szellem --}}
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs uppercase tracking-wide text-gray-500">Közösségi szellem</span>
                                            <div class="flex items-center gap-1">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <x-rating-circle :value="$ratings['social']" :index="$i" />
                                                @endfor
                                                <span class="ml-2 text-xs text-gray-500">
                                                    ({{ $ratings['social'] ?? 0 }}/5.0)
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Info boxes: experience + bike data --}}
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-4 gap-3 text-sm">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tapasztalat</p>
                                <p class="mt-1 text-gray-800">
                                    {{ $experienceLabel }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Motor típusa</p>
                                <p class="mt-1 text-gray-800">
                                    {{ $user->bike_type ?: '—' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategória</p>
                                <p class="mt-1 text-gray-800">
                                    {{ $user->bike_category ?: '—' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Gyártási év</p>
                                <p class="mt-1 text-gray-800">
                                    {{ $user->bike_year ?: '—' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-500 italic">
                            Ez a profil privát.
                        </p>
                    @endif
                </div>
            </div>
        </div>
        {{-- NOTIFICATIONS CARD --}}
        @auth
        @if (auth()->id() === $user->id)
        <div class="bg-white/80 rounded-xl shadow p-6" 
            x-data="notificationsComponent()" 
            x-init="init()"
            data-notifications='{!! json_encode($notifications->map(function($n) {
                return [
                    "id" => $n->id,
                    "category" => $n->category,
                    "message" => $n->message,
                    "created_at" => $n->created_at->toIso8601String(),
                ];
            })->values()) !!}'
            data-routes='{!! json_encode([
                "index" => route("notifications.index"),
                "read" => route("notifications.read", ":id"),
                "readAll" => route("notifications.read_all"),
                "mock" => route("notifications.mock")
            ]) !!}'>

            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-xl font-semibold">Értesítések</h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Itt jelennek meg a túrákkal és fórummal kapcsolatos értesítéseid.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!--{{-- Mock notification button --}}
                    <button @click="createMockNotification()"
                            type="button"
                            class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full border border-yellow-300 text-yellow-700 bg-yellow-50 hover:bg-yellow-100 transition">
                        Mock értesítés
                    </button>-->

                    {{-- Mark all as read --}}
                    <button @click="markAllAsRead()"
                            x-show="notifications.length > 0"
                            type="button"
                            class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        Összes elolvasottnak jelölése
                    </button>
                </div>
            </div>

            {{-- Notifications list / empty state --}}
            <div x-show="notifications.length === 0" 
                class="border border-dashed border-gray-300 rounded-lg p-6 text-center text-sm text-gray-500">
                <p>Nincsenek új értesítéseid.</p>
                <p class="mt-1 text-xs">Ha szeretnéd tesztelni a rendszert, hozz létre egy mock értesítést a gombbal.</p>
            </div>

            <ul x-show="notifications.length > 0" class="space-y-3">
                <template x-for="notification in notifications" :key="notification.id">
                    <li 
                        :data-id="notification.id"
                        x-data="{ removing: false }"
                        x-show="!removing"

                        {{-- Belépési animáció --}}
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0"

                        {{-- Kilépési animáció jobbra + fade --}}
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 translate-x-10"

                        class="border rounded-lg p-3 flex items-start justify-between gap-4 bg-gray-50/60">

                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide" 
                            x-text="notification.category"></p>

                            <p class="text-sm mt-1 text-gray-800" 
                            x-text="notification.message"></p>

                            <p class="text-xs text-gray-400 mt-1" 
                            x-text="formatTimeAgo(notification.created_at)"></p>
                        </div>

                        <button @click="markAsRead(notification.id)"
                                type="button"
                                class="text-xs text-blue-600 hover:text-blue-800 underline mt-1">
                            Elolvasva
                        </button>
                    </li>
                </template>
            </ul>
        </div>
        @endif
        @endauth

        {{-- FRIEND FEATURE CARD --}}
        <div class="bg-white/80 rounded-xl shadow p-6 mt-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-xl font-semibold">Barátok</h2>
                </div>
            </div>

            {{-- Flash üzenetek --}}
            @if(session('success'))
                <p class="text-green-600 text-sm mb-2">{{ session('success') }}</p>
            @endif

            @if(session('friendError'))
                <p class="text-red-600 text-sm mb-2">{{ session('friendError') }}</p>
            @endif


            {{-- Friends list --}}
            <label for="user-search" class="block text-sm font-medium text-gray-700 mb-2">
                Felhasználók keresése
            </label>
            <input
                id="user-search"
                type="text"
                placeholder="Felhasználónév keresése..."
                class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                autocomplete="off">
            {{-- TALÁLATOK (JS) --}}
            <div
                id="user-search-results"
                class="absolute left-1/2 -translate-x-1/2 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 hidden w-full max-w-md"
            ></div>
            <div class="border border-dashed border-gray-300 rounded-lg p-4 mt-4">
                <p class="text-sm font-semibold text-gray-700 mb-3">Barátok listája:</p>

                @if ($user->friends->isEmpty())
                    <p class="text-gray-500 text-sm">Még nincsenek barátaid.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($user->friends as $friend)
                            <li>
                                <a href="{{ url('/profile/' . $friend->id) }}" class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-100 transition">
                                    <img src="{{ $friend->avatar_url }}" alt="{{ $friend->name }} profilképe" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                    <span class="text-gray-800 font-medium">{{ $friend->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
    <script src="{{ asset('js/profile-search.js') }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
</x-layout>
