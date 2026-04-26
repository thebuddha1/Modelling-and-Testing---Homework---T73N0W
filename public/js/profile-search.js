document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('user-search');
    const resultsBox = document.getElementById('user-search-results');
    let debounceTimer = null;

    if (!input || !resultsBox) return;

    function clearResults() {
        resultsBox.innerHTML = '';
        resultsBox.classList.add('hidden');
    }

    function renderResults(users) {
        if (!users.length) {
            clearResults();
            return;
        }

        resultsBox.innerHTML = users.map(user => {
            return `
                <button
                    type="button"
                    data-url="${user.profile_url}"
                    class="w-full text-left px-3 py-2 flex items-center gap-3 hover:bg-gray-50"
                >
                    <img
                        src="${user.avatar_url}"
                        alt="${user.name} profilképe"
                        class="w-8 h-8 rounded-full object-cover border border-gray-200"
                    >
                    <span class="text-sm text-gray-800">${user.name}</span>
                </button>
            `;
        }).join('');

        resultsBox.classList.remove('hidden');

        resultsBox.querySelectorAll('button[data-url]').forEach(btn => {
            btn.addEventListener('click', () => {
                window.location.href = btn.getAttribute('data-url');
            });
        });
    }

    input.addEventListener('input', function () {
        const q = this.value.trim();

        if (q.length < 2) {
            clearResults();
            return;
        }

        if (debounceTimer) clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            fetch(`/profile-search?q=${encodeURIComponent(q)}`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(data => renderResults(data))
                .catch(() => clearResults());
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!resultsBox.contains(e.target) && e.target !== input) {
            clearResults();
        }
    });
});
