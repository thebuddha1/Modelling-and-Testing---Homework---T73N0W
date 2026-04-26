<x-layout>
    <div class="container mx-auto p-4 max-w-3xl">
        <h1 class="text-2xl font-bold mb-4">Profil szerkesztése</h1>

        @if (session('status'))
            <div class="bg-green-200 text-green-800 p-2 rounded mb-4">{{ session('status') }}</div>
        @endif

        <form method="POST" action="/profile/{{ $user->id }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label class="block mb-1 font-medium">Bemutatkozás</label>
                <textarea name="bio" rows="4" class="w-full border rounded p-2">{{ old('bio', $user->bio) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="block mb-1 font-medium">Tapasztalati szint</label>
                <select name="experience_level" class="w-full border rounded p-2">
                    <option value="">— Válassz —</option>
                    @foreach ($experience as $val => $label)
                        <option value="{{ $val }}" @selected(old('experience_level', $user->experience_level) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="block mb-1 font-medium">Motor típusa</label>
                <input type="text" name="bike_type" class="w-full border rounded p-2"
                       value="{{ old('bike_type', $user->bike_type) }}" placeholder="pl. Yamaha MT-07">
            </div>

            <div class="mb-3">
                <label class="block mb-1 font-medium">Motor kategóriája</label>
                <select name="bike_category" class="w-full border rounded p-2">
                    <option value="">— Válassz —</option>
                    @foreach ($categories as $val => $label)
                        <option value="{{ $val }}" @selected(old('bike_category', $user->bike_category) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="block mb-1 font-medium">Motor évjárata</label>
                <input type="number" name="bike_year" class="w-full border rounded p-2"
                       value="{{ old('bike_year', $user->bike_year) }}">
            </div>

            <div>
                <label for="avatar">Profilkép</label>
                @if ($user->avatar_url ?? false)
                    <div>
                        <img src="{{ $user->avatar_url }}" alt="Profilkép" style="width:100px; height:100px; object-fit:cover; border-radius:50%;">
                    </div>
                @endif
                <input type="file" name="avatar" id="avatar" accept="image/*">
                @error('avatar')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4 flex items-center gap-2">
                <input
                    type="checkbox"
                    id="is_private"
                    name="is_private"
                    value="1"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                    {{ old('is_private', $user->is_private) ? 'checked' : '' }}
                >
                <label for="is_private" class="text-sm text-gray-700">
                    Profilom legyen privát
                </label>
            </div>


            <button class="bg-blue-600 text-white px-4 py-2 rounded">Mentés</button>
        </form>
    </div>
</x-layout>
