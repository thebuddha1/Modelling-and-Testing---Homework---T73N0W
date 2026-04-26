<x-layout>
    <div class="max-w-xl mx-auto mt-20 bg-white shadow p-6 rounded-xl text-center">

        <h1 class="text-2xl font-bold mb-4 text-red-600">
            Ez a fórum törölve lett
        </h1>

        <p class="text-gray-700 mb-6">
            Úgy tünik a megtekintett fórumot annak tulajdonosa vagy a moderátorok törölték.
        </p>

        <a href="{{ route('forum.index') }}"
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">
            Vissza a fórumokhoz
        </a>

    </div>
</x-layout>
