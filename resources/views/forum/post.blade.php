<x-layout>
    <div class="container mx-auto mt-6">

        <div class="mb-4">
            <a href="{{ route('forum.show', $forum->category_id) }}"
               class="inline-block text-gray-600 hover:text-gray-800 border rounded px-3 py-1">
                Vissza a témakör fórumbejegyzéseihez
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
        {{-- Fórum rész --}}
        <div class="bg-white shadow rounded-xl p-6 mb-6 relative">

            {{-- Szerkesztés/törlés/jelentés/mentés gombok --}}
            @if (auth()->id() === $forum->owner)
                <div class="absolute top-3 right-3 forum-menu-wrapper">
                    <button class="forum-menu-btn p-1 hover:bg-gray-200 rounded-full">
                        ⋮
                    </button>

                    <div class="forum-menu hidden absolute right-0 mt-2 w-32 bg-white
                                border rounded shadow-md z-10">
                        <button class="open-forum-edit block w-full text-left px-4 py-2 hover:bg-gray-100">
                            Szerkesztés
                        </button>

                        <button class="open-forum-delete block w-full text-left px-4 py-2 hover:bg-red-100 text-red-600"
                                data-url="{{ route('forum.destroy', $forum->id) }}">
                            Törlés
                        </button>
                    </div>
                </div>
            @else
                <div class="absolute top-3 right-3 flex flex-col space-y-2 item-center text-center">

                    {{-- Mentés/mentés eltávolítása --}}
                    @if (!$alreadySaved)
                        <form method="POST" action="{{ route('forum.save', $forum->id) }}">
                            @csrf
                            <button type="submit"
                                    class="text-gray-600 hover:text-blue-600 relative group">
                                <i class="fa-regular fa-bookmark text-xl"></i>

                                <span class="absolute left-1/2 -translate-x-1/2 mt-2
                                            bg-black text-white text-xs font-normal
                                            px-2 py-1 rounded shadow-lg whitespace-nowrap
                                            z-20 hidden group-hover:block">
                                    Fórumbejegyzés mentése
                                </span>
                            </button>
                        </form>
                    @else
                        <button type="button"
                                data-forum-id="{{ $forum->id }}"
                                class="openUnsaveModal text-blue-600 hover:text-red-600 relative group">
                            <i class="fa-solid fa-bookmark text-xl"></i>

                            <span class="absolute left-1/2 -translate-x-1/2 mt-2
                                        bg-black text-white text-xs font-normal
                                        px-2 py-1 rounded shadow-lg whitespace-nowrap
                                        z-20 hidden group-hover:block">
                                Mentés visszavonása
                            </span>
                        </button>
                    @endif

                    {{-- Jelentés --}}
                    @if ($userReport?->reported)
                        <span class="text-red-600 text-sm font-semibold">
                            Már jelentetted ezt a fórumbejegyzést
                        </span>
                    @else
                        <button id="openReportModal"
                                class="text-red-600 hover:text-red-800 text-xl font-bold relative group">
                            ❗

                            <span class="absolute left-1/2 -translate-x-1/2 mt-2
                                        bg-black text-white text-xs font-normal
                                        px-2 py-1 rounded shadow-lg whitespace-nowrap
                                        z-20 hidden group-hover:block">
                                Fórumbejegyzés jelentése
                            </span>
                        </button>
                    @endif

                </div>
            @endif

            {{-- Fórum attribútumok --}}
            <div id="forum-view">
                <h1 class="text-2xl font-bold mb-2">{{ $forum->name }}</h1>

                <div class="flex items-center mb-3">
                    <img src="{{ $forum->user->avatar_url }}" class="w-10 h-10 rounded-full mr-3">
                    <a href="{{ route('profile.show', $forum->user->id) }}"
                    class="text-gray-700 font-semibold hover:underline"
                    title="{{ $forum->user->name }}">
                        {{ $forum->user->name }}
                    </a>
                </div>

                <p class="text-gray-800 mb-4">{{ $forum->description ?? '' }}</p>

                <div class="text-gray-900 whitespace-pre-line mb-4">
                    {{ $forum->content }}
                </div>

                @if ($forum->updated_at != $forum->created_at)
                    <p class="text-xs text-gray-500">(Szerkesztve) {{ $forum->updated_at->format('Y.m.d H:i') }}</p>
                @else
                    <p class="text-xs text-gray-500">Közzétéve: {{ $forum->created_at->format('Y.m.d H:i') }}</p>
                @endif

                <div class="mt-4 flex items-center justify-start like-dislike-group space-x-2"
                    data-type="forum"
                    data-id="{{ $forum->id }}"
                    data-url="{{ route('impressions.save', $forum->id) }}"
                    data-liked="{{ $userImpression?->like ? 'true' : 'false' }}"
                    data-disliked="{{ $userImpression?->dislike ? 'true' : 'false' }}">

                    <button type="button"
                            class="like-btn flex items-center space-x-1 px-3 py-1 rounded-full border bg-white text-gray-700 text-sm">
                        <span>👍</span>
                        <span class="like-count">{{ $forum->impression->likes }}</span>
                    </button>

                    <button type="button"
                            class="dislike-btn flex items-center space-x-1 px-3 py-1 rounded-full border bg-white text-gray-700 text-sm">
                        <span>👎</span>
                        <span class="dislike-count">{{ $forum->impression->dislikes }}</span>
                    </button>

                </div>

            </div>

            {{-- Fórum szerkesztés --}}
            <div id="forum-edit" class="hidden">
                <form method="POST" action="{{ route('forum.update', $forum->id) }}">
                    @csrf
                    @method('PUT')

                    <label class="block mb-2 text-sm font-semibold">Cím</label>
                    <input type="text" name="name" value="{{ $forum->name }}"
                        class="w-full border rounded p-2 mb-4">

                    <label class="block mb-2 text-sm font-semibold">Leírás (opcionális)</label>
                    <input type="text" name="description" value="{{ $forum->description }}"
                        class="w-full border rounded p-2 mb-4">

                    <label class="block mb-2 text-sm font-semibold">Tartalom</label>
                    <textarea name="content" rows="6"
                            class="w-full border rounded p-2 mb-4">{{ $forum->content }}</textarea>

                    <div class="flex justify-end space-x-3">
                        <button type="button"
                                class="cancel-forum-edit px-4 py-2 bg-gray-300 rounded">
                            Mégse
                        </button>

                        <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Mentés
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Kommentek --}}
        <div class="bg-white border rounded-2xl p-6 shadow-sm mt-8" >

            <h2 class="text-xl font-semibold mb-3">Hozzászólások</h2>

            <form method="POST" action="{{ route('forum.comment.store', $forum->id) }}" class="mb-6">
                @csrf
                <textarea name="content" rows="3"
                          placeholder="Írj egy hozzászólást..."
                          class="w-full border rounded p-2 focus:ring-blue-200"></textarea>

                @error('content')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Közzététel
                </button>
            </form>

            <div class="mb-4 flex space-x-3" id="commentSortButtons">
                <button
                    id="sortMostLiked"
                    class="px-3 py-1 rounded-full border bg-blue-600 text-white text-sm font-semibold">
                    Legkedveltebb
                </button>

                <button
                    id="sortNewest"
                    class="px-3 py-1 rounded-full border bg-white text-gray-700 text-sm font-semibold">
                    Legújabb
                </button>
            </div>

            {{-- Kommentek szerkesztése/törlése/jelentése gombok --}}
            <div class="space-y-4">
                @foreach ($comments as $comment)
                    <div class="bg-gray-50 border rounded-xl p-4 shadow-sm relative" id="comment-card-{{ $comment->id }}" data-updated="{{ $comment->updated_at }}">
                        @if (auth()->id() === $comment->user_id)
                            <div class="absolute top-2 right-2 comment-menu-wrapper">
                                <button class="comment-menu-btn p-1 hover:bg-gray-200 rounded-full"
                                        data-comment-id="{{ $comment->id }}">
                                    ⋮
                                </button>

                                <div class="comment-menu hidden absolute right-0 mt-2 w-28 bg-white 
                                            border rounded shadow-md z-10"
                                    id="comment-menu-{{ $comment->id }}">

                                    <button class="open-edit-comment w-full text-left px-3 py-2 hover:bg-gray-100 text-sm"
                                            data-id="{{ $comment->id }}">
                                        Szerkesztés
                                    </button>

                                    <button class="open-comment-delete w-full text-left px-3 py-2 hover:bg-red-100 text-sm text-red-600"
                                            data-url="{{ route('comment.destroy', $comment->id) }}">
                                        Törlés
                                    </button>
                                </div>
                            </div>
                        @else                            
                            @if (auth()->id() !== $comment->user_id) 
                                <div class="absolute top-2 right-2">

                                    @if ($comment->current_user_report?->reported)
                                        <span class="text-red-600 text-xs font-semibold">
                                            Ezt a hozzászólást már jelentetted
                                        </span>
                                    @else
                                        <button class="open-comment-report text-red-600 text-lg font-bold"
                                                data-comment-id="{{ $comment->id }}">
                                            ❗
                                        </button>
                                    @endif
                                </div>
                            @endif    
                        @endif

                        {{-- Kommentek --}}
                        <div id="comment-view-{{ $comment->id }}">
                            <div class="flex items-center mb-1">
                                <img src="{{ $comment->user->avatar_url }}"
                                    class="w-8 h-8 rounded-full mr-2">
                                <div>
                                    <a href="{{ route('profile.show', $comment->user->id) }}"
                                    class="font-semibold text-gray-800 hover:underline"
                                    title="{{ $comment->user->name }}">
                                        {{ $comment->user->name }}
                                    </a>
                                    <p class="text-xs text-gray-500">
                                        @if ($comment->created_at != $comment->updated_at)
                                            (Szerkesztve) {{ $comment->updated_at->format('Y.m.d H:i') }}
                                        @else
                                            {{ $comment->created_at->format('Y.m.d H:i') }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <p class="text-gray-700 mb-4 whitespace-pre-line">{{ $comment->content }}</p>

                            <div class="mt-2 flex items-center space-x-2 like-dislike-group"
                                data-type="comment"
                                data-id="{{ $comment->id }}"
                                data-url="{{ route('comment.impressions.save', $comment->id) }}"
                                data-liked="{{ $comment->current_user_impression?->like ? 'true' : 'false' }}"
                                data-disliked="{{ $comment->current_user_impression?->dislike ? 'true' : 'false' }}">

                                <button type="button"
                                        class="comment-like-btn flex items-center space-x-1 px-2 py-1 rounded-full border bg-white text-gray-700 text-xs">
                                    <span>👍</span>
                                    <span class="comment-like-count">{{ $comment->impression->likes ?? 0 }}</span>
                                </button>

                                <button type="button"
                                        class="comment-dislike-btn flex items-center space-x-1 px-2 py-1 rounded-full border bg-white text-gray-700 text-xs">
                                    <span>👎</span>
                                    <span class="comment-dislike-count">{{ $comment->impression->dislikes ?? 0 }}</span>
                                </button>

                            </div>
                        </div>

                        {{-- Komment szerkesztése --}}
                        <div id="comment-edit-{{ $comment->id }}" class="hidden">
                            <form method="POST" action="{{ route('comment.update', $comment->id) }}">
                                @csrf
                                @method('PUT')

                                <textarea name="content" class="w-full border p-2 rounded mb-2"
                                        rows="3">{{ $comment->content }}</textarea>

                                <div class="flex justify-end space-x-2">
                                    <button type="button"
                                            class="cancel-edit bg-gray-300 px-3 py-1 rounded"
                                            data-id="{{ $comment->id }}">
                                        Mégse
                                    </button>

                                    <button type="submit"
                                            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                        Mentés
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- modalok --}}
    
    <x-modals.confirmation
        id="deleteModal"
        title="Biztosan törölni szeretnéd?"
        message="Ez a művelet végleges. A fórum és minden hozzátartozó komment törlődni fog."
        formId="deleteForumForm"
        action="{{ route('forum.destroy', $forum->id) }}"
        method="DELETE"
        cancelId="cancelDelete"
        confirmLabel="Törlés"
    />

    <x-modals.confirmation
        id="deleteCommentModal"
        title="Biztosan törölni szeretnéd?"
        message="A komment és minden hozzá tartozó adat törlésre kerül."
        formId="deleteCommentForm"
        action=""
        method="DELETE"
        cancelId="cancelCommentDelete"
        confirmLabel="Igen, törlöm"
    />

    <x-modals.confirmation
        id="unsaveModal"
        title="Eltávolítod a mentettek közül?"
        message=""
        formId="unsaveForm"
        action=""
        method="POST"
        cancelId="cancelUnsave"
        confirmLabel="Igen"
    />

    <x-modals.report
        id="reportModal"
        title="Fórumbejegyzés jelentése"
        formId="reportForm"
        action="{{ route('forum.report', $forum->id) }}"
        :reasons="[
            'Gyűröletbeszéd', 
            'Illegális tartalom', 
            'Politikai propaganda', 
            'NSFW tartalom', 
            'Egyéb']"
        checkboxClass="report-check"
        submitId="submitReport"
        cancelId="cancelReport"
    />

    <x-modals.report
        id="commentReportModal"
        title="Hozzászólás jelentése"
        formId="commentReportForm"
        action=""
        :reasons="[
            'Sértő tartalom', 
            'Spam', 
            'Zaklatás', 
            'Átverés / csalás', 
            'Félrevezető információ']"
        checkboxClass="comment-report-check"
        submitId="submitCommentReport"
        cancelId="cancelCommentReport"
    />

    <script src="/js/forum/like-dislike.js"></script>
    <script src="/js/forum/modals.js"></script>
    <script src="/js/forum/confirmations.js"></script>
    <script src="/js/forum/filters.js"></script>
    <script src="/js/forum/edits.js"></script>
    <script src="/js/forum/menus.js"></script>
</x-layout>
