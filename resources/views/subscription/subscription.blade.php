<x-layout>
    <div class="max-w-6xl mx-auto mt-12 px-4">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-3">
                Fizess elő, és élvezd a korlátlan létszámú túrákat!
            </h1>
            <p class="text-lg text-gray-600">
                Hozz létre túrákat kötöttségek nélkül, hívd meg barátaidat és formáld a motoros közösséget.
            </p>
        </div>

        @if(auth()->user()->subscription_end_at)
            <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-lg p-4 mb-10 text-center">
                <span class="font-semibold">Előfizetésed érvényes:</span>
                {{ \Carbon\Carbon::parse(auth()->user()->subscription_end_at)->locale('hu')->isoFormat('LL') }}-ig
            </div>
        @endif

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white shadow-lg rounded-lg p-6 hover:scale-105 transition transform">
                <h2 class="text-2xl font-semibold mb-2">Havi csomag</h2>
                <p class="text-gray-500 mb-4 text-lg">2000 Ft / hónap</p>
                <ul class="mb-6 space-y-2 text-gray-600">
                    <li>✅ Korlátlan létszámú túrák</li>
                    <li>✅ Hozzájárulsz az oldal fenntartásához és az új funkciók fejlesztéséhez</li>
                    <li>✅ Tökéletes kipróbáláshoz, rövid távú elköteleződés</li>
                </ul>
                <button class="subscription-btn relative w-full py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600 font-semibold flex justify-center items-center" data-plan="monthly">
                    <span class="btn-text">Vásárlás</span>
                    <svg class="spinner hidden absolute w-5 h-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </button>
            </div>

            <div class="bg-gradient-to-r from-green-400 to-green-600 shadow-lg rounded-lg p-6 text-white transform hover:scale-105 transition relative">
                <div class="absolute top-0 right-0 bg-yellow-300 text-yellow-900 px-3 py-1 text-xs font-semibold rounded-bl-lg">Legnépszerűbb</div>
                <h2 class="text-2xl font-semibold mb-2">Féléves csomag</h2>
                <p class="mb-4 text-lg">12 000 Ft / 6 hónap</p>
                <ul class="mb-6 space-y-2">
                    <li>✅ Korlátlan létszámú túrák</li>
                    <li>✅ Hosszabb távon is támogatod az oldal működését és fejlődését</li>
                    <li>✅ Kényelmesebb opció folyamatos használathoz</li>
                </ul>
                <button class="subscription-btn relative w-full py-2 px-4 bg-white text-green-600 rounded font-semibold hover:bg-gray-100 flex justify-center items-center" data-plan="semiannual">
                    <span class="btn-text">Vásárlás</span>
                    <svg class="spinner hidden absolute w-5 h-5 animate-spin text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </button>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-700 shadow-lg rounded-lg p-6 text-white transform hover:scale-105 transition">
                <h2 class="text-2xl font-semibold mb-2">Éves csomag</h2>
                <p class="mb-4 text-lg">24 000 Ft / év</p>
                <ul class="mb-6 space-y-2">
                    <li>✅ Korlátlan létszámú túrák</li>
                    <li>✅ Aktívan segíted az oldal fennmaradását és a jövőbeli fejlesztéseket</li>
                    <li>✅ A legjobb választás a hosszú távú elköteleződéshez</li>
                </ul>
                <button class="subscription-btn relative w-full py-2 px-4 bg-white text-purple-700 rounded font-semibold hover:bg-gray-100 flex justify-center items-center" data-plan="yearly">
                    <span class="btn-text">Vásárlás</span>
                    <svg class="spinner hidden absolute w-5 h-5 animate-spin text-purple-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed top-6 right-6 hidden bg-green-500 text-white px-5 py-3 rounded-lg shadow-lg transition-all duration-300 transform">
        <span id="toast-message"></span>
    </div>

    <script>
        const buttons = document.querySelectorAll('.subscription-btn');
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toast-message');

        buttons.forEach(button => {
            button.addEventListener('click', async () => {
                const plan = button.dataset.plan;
                const spinner = button.querySelector('.spinner');
                const btnText = button.querySelector('.btn-text');

                spinner.classList.remove('hidden');
                btnText.classList.add('opacity-0');
                buttons.forEach(btn => btn.disabled = true);

                const response = await fetch("{{ route('subscription.buy') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ plan })
                });

                const data = await response.json();
                spinner.classList.add('hidden');
                btnText.classList.remove('opacity-0');
                buttons.forEach(btn => btn.disabled = false);

                if (data.success) {
                    showToast(`✅ ${data.message}`);
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showToast('❌ Hiba történt a vásárlás során.', true);
                }
            });
        });

        function showToast(message, error = false) {
            toastMessage.textContent = message;
            toast.classList.remove('hidden', 'opacity-0', 'translate-y-3');
            toast.classList.remove('bg-green-500', 'bg-red-500');
            toast.classList.add(error ? 'bg-red-500' : 'bg-green-500');

            setTimeout(() => toast.classList.add('opacity-0', 'translate-y-3'), 2500);
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }
    </script>
</x-layout>
