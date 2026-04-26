<div id="{{ $id }}"
     class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

    <div class="bg-white rounded-xl p-6 w-80 shadow-lg text-center">

        <h2 class="text-lg font-semibold mb-4">{{ $title }}</h2>

        @if($message)
            <p class="text-gray-700 mb-6">{{ $message }}</p>
        @endif

        <div class="flex justify-between">
            <button id="{{ $cancelId }}"
                    type="button"
                    class="px-4 py-2 border rounded hover:bg-gray-100">
                Mégse
            </button>

            <form id="{{ $formId }}" method="POST" action="{{ $action }}">
                @csrf
                @if($method)
                    @method($method)
                @endif

                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    {{ $confirmLabel }}
                </button>
            </form>
        </div>

    </div>
</div>
