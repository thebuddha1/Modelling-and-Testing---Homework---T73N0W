<div x-data="{ open: true }" x-show="open" 
         class="fixed top-32 right-4 w-80 bg-white border border-gray-300 rounded-xl shadow-lg p-4 z-50 transition-transform transform"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="translate-x-full opacity-0">
    <div class="flex justify-between items-start">
        <h3 class="text-lg font-bold text-laravel">Mock reklám</h3>
        <button @click="open = false" class="text-gray-400 hover:text-gray-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <hr class="my-2">
    <p class="text-gray-700 text-sm">
        Ez itt egy mock reklám. Várárolj előfizetést, ha nem szeretnéd látni.
    </p>
    <a href="/subscription" class="inline-block mt-3 px-4 py-2 bg-laravel text-white rounded hover:bg-blue-600 transition">
        Érdekel
    </a>
</div>