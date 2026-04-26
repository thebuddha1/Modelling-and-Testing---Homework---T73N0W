<div id="{{ $id }}"
     class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

    <div class="bg-white rounded-xl p-6 w-96 shadow-lg">

        <h2 class="text-xl font-semibold mb-4">{{ $title }}</h2>

        <h3 class="font-semibold mb-4">Jelölj be maximum 3 indokot:</h3>

        <form id="{{ $formId }}" method="POST" action="{{ $action }}">
            @csrf

            <div class="space-y-2 mb-4">
                @foreach($reasons as $reason)
                    <label class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            name="reasons[]"
                            value="{{ $reason }}"
                            class="{{ $checkboxClass }}">
                        <span>{{ $reason }}</span>
                    </label>
                @endforeach
            </div>

            <textarea name="details"
                      class="w-full border rounded p-2 mb-4 focus:ring focus:ring-blue-200"
                      rows="4"
                      placeholder="További részletek (opcionális)..."></textarea>

            <div class="flex justify-between mt-6">
                <button id="{{ $cancelId }}"
                        type="button"
                        class="px-4 py-2 border rounded hover:bg-gray-100">
                    Mégse
                </button>

                <button id="{{ $submitId }}"
                        type="submit"
                        disabled
                        class="px-4 py-2 bg-blue-600 text-white rounded opacity-50 cursor-not-allowed">
                    Jelentés
                </button>
            </div>
        </form>

    </div>
</div>
