document.addEventListener("DOMContentLoaded", () => {

    const searchInput   = document.getElementById('forumSearch');
    const filterPanel   = document.getElementById('filterPanel');
    const toggleFilters = document.getElementById('toggleFilters');
    const filterChecks  = document.querySelectorAll('.filter-check');
    const container     = document.getElementById('forumListContainer');
    const cards         = Array.from(document.querySelectorAll('.forum-card'));

    if (!searchInput || !container || cards.length === 0) {
        return;
    }

    const sortFilters = [
        "az",
        "za",
        "updated_oldest",
        "updated_newest",
        "most_likes"
    ];

    toggleFilters?.addEventListener('click', () => {
        filterPanel?.classList.toggle('hidden');
    });

    function handleSortExclusivity(changed) {
        const filter = changed.dataset.filter;

        if (!sortFilters.includes(filter)) return;

        if (!changed.checked) {
            changed.checked = true;
            return;
        }

        sortFilters.forEach(f => {
            if (f !== filter) {
                const other = document.querySelector(`input[data-filter="${f}"]`);
                if (other) other.checked = false;
            }
        });
    }

    function applyAll() {

        const query = searchInput.value.toLowerCase();

        const activeFilters = Array
            .from(document.querySelectorAll('.filter-check:checked'))
            .map(chk => chk.dataset.filter);

        let sortedCards = [...cards];

        if (activeFilters.includes("az")) {
            sortedCards.sort((a, b) =>
                a.dataset.name.localeCompare(b.dataset.name)
            );
        }
        else if (activeFilters.includes("za")) {
            sortedCards.sort((a, b) =>
                b.dataset.name.localeCompare(a.dataset.name)
            );
        }
        else if (activeFilters.includes("updated_newest")) {
            sortedCards.sort((a, b) =>
                Number(b.dataset.updated) - Number(a.dataset.updated)
            );
        }
        else if (activeFilters.includes("updated_oldest")) {
            sortedCards.sort((a, b) =>
                Number(a.dataset.updated) - Number(b.dataset.updated)
            );
        }
        else if (activeFilters.includes("most_likes")) {
            sortedCards.sort((a, b) =>
                Number(b.dataset.likes) - Number(a.dataset.likes)
            );
        }

        container.innerHTML = "";

        sortedCards.forEach(card => {

            let visible = true;

            if (query && !card.dataset.name.includes(query)) {
                visible = false;
            }

            if (activeFilters.includes("hide_reported") && card.dataset.reported === "1") {
                visible = false;
            }

            if (activeFilters.includes("hide_disliked") && card.dataset.disliked === "1") {
                visible = false;
            }

            card.style.display = visible ? "block" : "none";
            container.appendChild(card);
        });
    }

    searchInput.addEventListener("input", applyAll);

    filterChecks.forEach(chk => {
        chk.addEventListener("change", () => {
            handleSortExclusivity(chk);
            applyAll();
        });
    });

    const anySortActive = sortFilters.some(f =>
        document.querySelector(`input[data-filter="${f}"]`)?.checked
    );

    if (!anySortActive) {
        const defaultSort = document.querySelector('input[data-filter="az"]');
        if (defaultSort) defaultSort.checked = true;
    }

    applyAll();

});
