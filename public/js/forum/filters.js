document.addEventListener("DOMContentLoaded", () => {

    const sortMostLikedBtn = document.getElementById("sortMostLiked");
    const sortNewestBtn = document.getElementById("sortNewest");
    const commentContainer = document.querySelector(".space-y-4");

    if (!sortMostLikedBtn || !sortNewestBtn || !commentContainer) {
        return;
    }

    function resetSortButtons() {
        [sortMostLikedBtn, sortNewestBtn].forEach(btn => {
            btn.classList.remove("bg-blue-600", "text-white");
            btn.classList.add("bg-white", "text-gray-700");
        });
    }

    function activate(btn) {
        btn.classList.add("bg-blue-600", "text-white");
        btn.classList.remove("bg-white", "text-gray-700");
    }

    function sortCommentsByMostLiked() {
        const comments = Array.from(commentContainer.children);

        comments.sort((a, b) => {
            const aLikes = parseInt(a.querySelector(".comment-like-count").textContent) || 0;
            const bLikes = parseInt(b.querySelector(".comment-like-count").textContent) || 0;
            return bLikes - aLikes;
        });

        comments.forEach(comment => commentContainer.appendChild(comment));
    }

    function sortCommentsByNewest() {
        const comments = Array.from(commentContainer.children);

        comments.sort((a, b) => {
            const aTime = new Date(a.dataset.updated);
            const bTime = new Date(b.dataset.updated);
            return bTime - aTime;
        });

        comments.forEach(comment => commentContainer.appendChild(comment));
    }

    sortMostLikedBtn.addEventListener("click", () => {
        resetSortButtons();
        activate(sortMostLikedBtn);
        sortCommentsByMostLiked();
    });

    sortNewestBtn.addEventListener("click", () => {
        resetSortButtons();
        activate(sortNewestBtn);
        sortCommentsByNewest();
    });

    activate(sortMostLikedBtn);
    sortCommentsByMostLiked();
});
