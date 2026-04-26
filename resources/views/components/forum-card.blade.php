@props(['forum'])

<a href="{{ route('forum.post', $forum->id) }}"
   {{ $attributes->merge([
       'class' => 'forum-card block border rounded-xl p-4 shadow-sm hover:shadow-lg transition bg-white'
   ]) }}
   data-name="{{ strtolower($forum->name) }}"
   data-updated="{{ $forum->updated_at->timestamp }}"
   data-likes="{{ $forum->impression->likes ?? 0 }}"
   data-reported="{{ $forum->userReports->where('user_id', auth()->id())->count() ? '1' : '0' }}"
   data-disliked="{{ $forum->userImpressions->where('user_id', auth()->id())->where('dislike', true)->count() ? '1' : '0' }}"
>

    <div class="flex justify-between items-center mb-2 gap-3">

        <h3 class="text-lg font-semibold text-gray-800 truncate min-w-0 flex-1">
            {{ $forum->name }}
        </h3>

        <div class="flex items-center space-x-2 flex-shrink-0 max-w-[140px]">

            <img src="{{ $forum->user->avatar_url }}"
                 class="w-8 h-8 rounded-full object-cover flex-shrink-0"
                 alt="pfp">

            <span class="text-sm text-gray-600 truncate">
                {{ $forum->user->name }}
            </span>
        </div>
    </div>

    <p class="text-gray-700 text-sm truncate mb-3">
        {{ $forum->description ?? 'Nincs leírás' }}
    </p>

    <div class="flex items-center justify-between mt-3">
        <div class="flex items-center space-x-2 like-dislike-group">
            <button type="button"
                    class="like-btn flex items-center space-x-1 px-3 py-1 rounded-full
                           border border-gray-300 bg-white text-gray-700 text-sm">
                <span>👍</span>
                <span class="like-count">{{ $forum->impression->likes ?? 0 }}</span>
            </button>

            <button type="button"
                    class="dislike-btn flex items-center space-x-1 px-3 py-1 rounded-full
                           border border-gray-300 bg-white text-gray-700 text-sm">
                <span>👎</span>
                <span class="dislike-count">{{ $forum->impression->dislikes ?? 0 }}</span>
            </button>
        </div>

        @if ($forum->updated_at != $forum->created_at)
            <p class="text-xs text-gray-500 whitespace-nowrap">
                (Szerkesztve) {{ $forum->updated_at->format('Y.m.d H:i') }}
            </p>
        @else
            <p class="text-xs text-gray-500 whitespace-nowrap">
                Közzétéve: {{ $forum->created_at->format('Y.m.d H:i') }}
            </p>
        @endif
    </div>

</a>
