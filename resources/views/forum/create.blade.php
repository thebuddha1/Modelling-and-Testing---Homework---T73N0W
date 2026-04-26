<x-layout>
    <div class="container mx-auto mt-8 max-w-2xl">
        <h1 class="text-2xl font-bold mb-6">
            Új fórumbejegyzés közzététele {{ $category->name }} témakörben
        </h1>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('forum.store', $category->id) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-gray-700 font-medium">Cím *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full border rounded p-2 mt-1" required>
            </div>

            <div>
                <label for="description" class="block text-gray-700 font-medium">Leírás (opcionális)</label>
                <input type="text" id="description" name="description" value="{{ old('description') }}" class="w-full border rounded p-2 mt-1">
            </div>

            <div>
                <label for="content" class="block text-gray-700 font-medium">Tartalom *</label>
                <textarea id="content" name="content" rows="6" class="w-full border rounded p-2 mt-1" required>{{ old('content') }}</textarea>
            </div>

            <div class="flex justify-between items-center mt-6">
                <a href="{{ route('forum.show', $category->id) }}" class="text-gray-600 hover:underline">Fórumbejegyzés elvetése</a>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Fórumbejegyzés közzététele
                </button>
            </div>
        </form>
    </div>
</x-layout>
