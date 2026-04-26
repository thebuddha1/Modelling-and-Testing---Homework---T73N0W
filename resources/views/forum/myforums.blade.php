<x-layout>
    <div class="container mx-auto mt-6">

        <div class="mb-4">
            <a href="{{ route('forum.index') }}"
               class="inline-block text-gray-600 hover:text-gray-800 border rounded px-3 py-1">
                Vissza a témákhoz
            </a>
        </div>

        <div class="mt-4">
            <button id="forumTypeToggle"
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-left">
                Saját fórumbejegyzések ▼
            </button>

            <div id="forumTypePanel"
                 class="mt-2 p-4 border rounded bg-gray-50 hidden">

                <p class="font-semibold mb-2">Fórumok listázása:</p>

                <div class="grid grid-cols-1 gap-3">
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="forumFilter" value="own" checked>
                        <span>Saját fórumabejegyzések</span>
                    </label>

                    <label class="flex items-center space-x-2">
                        <input type="radio" name="forumFilter" value="saved">
                        <span>Mentett fórumbejegyzések</span>
                    </label>

                    <label class="flex items-center space-x-2">
                        <input type="radio" name="forumFilter" value="liked">
                        <span>Kedvelt fórumbejegyzések</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-6">

            <div id="list-own" class="forumListGroup space-y-10">
                @if ($ownForumsByCategory->isEmpty())
                    <p class="text-gray-600">Nincs saját fórumbejegyzésed.</p>
                @else
                    @foreach ($ownForumsByCategory as $categoryForums)
                        <div>
                            <h2 class="mb-4 text-lg font-semibold bg-blue-600 text-white
                                       py-2 rounded-lg text-center">
                                {{ $categoryForums->first()->category->name }}
                            </h2>

                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($categoryForums as $forum)
                                    @include('components.forum-card', ['forum' => $forum])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div id="list-saved" class="forumListGroup space-y-10 hidden">
                @if ($savedForumsByCategory->isEmpty())
                    <p class="text-gray-600">Nincs mentett fórumbejegyzésed.</p>
                @else
                    @foreach ($savedForumsByCategory as $categoryForums)
                        <div>
                            <h2 class="mb-4 text-lg font-semibold bg-blue-600 text-white
                                       py-2 rounded-lg text-center">
                                {{ $categoryForums->first()->category->name }}
                            </h2>

                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($categoryForums as $forum)
                                    @include('components.forum-card', ['forum' => $forum])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div id="list-liked" class="forumListGroup space-y-10 hidden">
                @if ($likedForumsByCategory->isEmpty())
                    <p class="text-gray-600">Nincs kedvelt fórumbejegyzésed.</p>
                @else
                    @foreach ($likedForumsByCategory as $categoryForums)
                        <div>
                            <h2 class="mb-4 text-lg font-semibold bg-blue-600 text-white
                                       py-2 rounded-lg text-center">
                                {{ $categoryForums->first()->category->name }}
                            </h2>

                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($categoryForums as $forum)
                                    @include('components.forum-card', ['forum' => $forum])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>

    </div>

    <script src="{{ asset('js/forum/myforums.js') }}"></script>
</x-layout>
