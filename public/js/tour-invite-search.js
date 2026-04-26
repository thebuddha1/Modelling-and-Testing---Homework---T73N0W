document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('invite-user-search');
    const resultsBox = document.getElementById('invite-search-results');
    const friendsOnlyCheckbox = document.getElementById('friends-only-checkbox');
    const inviteForm = document.getElementById('invite-form');
    const inviteUserIdInput = document.getElementById('invite-user-id');
    
    let debounceTimer = null;

    if (!input || !resultsBox) return;

    function clearResults() {
        resultsBox.innerHTML = '';
        resultsBox.classList.add('hidden');
    }

    function renderResults(users) {
        if (!users.length) {
            resultsBox.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">Nincs találat</div>';
            resultsBox.classList.remove('hidden');
            return;
        }

        resultsBox.innerHTML = users.map(user => {
            return `
                <button
                    type="button"
                    data-user-id="${user.id}"
                    class="w-full text-left px-3 py-2 flex items-center gap-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
                >
                    <img
                        src="${user.avatar_url}"
                        alt="${user.name} profilképe"
                        class="w-8 h-8 rounded-full object-cover border border-gray-200"
                    >
                    <span class="text-sm text-gray-800 flex-1">${user.name}</span>
                    <span class="text-xs text-blue-600 font-medium">Meghívás</span>
                </button>
            `;
        }).join('');

        resultsBox.classList.remove('hidden');

        resultsBox.querySelectorAll('button[data-user-id]').forEach(btn => {
            btn.addEventListener('click', () => {
                const userId = btn.getAttribute('data-user-id');
                inviteUserIdInput.value = userId;
                inviteForm.submit();
            });
        });
    }

    function doSearch() {
        const q = input.value.trim();
        const tourId = input.getAttribute('data-tour-id');
        const friendsOnly = friendsOnlyCheckbox ? friendsOnlyCheckbox.checked : false;

        if (q.length < 2) {
            clearResults();
            return;
        }

        if (debounceTimer) clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            const url = `/tours/${tourId}/search-users?q=${encodeURIComponent(q)}&friends_only=${friendsOnly}`;
            
            fetch(url, {
                headers: { 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(data => renderResults(data))
                .catch(() => clearResults());
        }, 250);
    }

    input.addEventListener('input', doSearch);

    if (friendsOnlyCheckbox) {
        friendsOnlyCheckbox.addEventListener('change', doSearch);
    }

    document.addEventListener('click', function (e) {
        if (!resultsBox.contains(e.target) && e.target !== input) {
            clearResults();
        }
    });
});
