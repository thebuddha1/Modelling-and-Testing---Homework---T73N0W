document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".like-dislike-group").forEach(group => {

        const id   = group.dataset.id;
        const type = group.dataset.type;
        const url  = group.dataset.url;

        const likeBtn        = group.querySelector(".like-btn, .comment-like-btn");
        const dislikeBtn     = group.querySelector(".dislike-btn, .comment-dislike-btn");
        const likeCountEl    = group.querySelector(".like-count, .comment-like-count");
        const dislikeCountEl = group.querySelector(".dislike-count, .comment-dislike-count");

        let state = group.dataset.liked === "true"
            ? "like"
            : group.dataset.disliked === "true"
                ? "dislike"
                : "none";

        let likes    = parseInt(likeCountEl.textContent);
        let dislikes = parseInt(dislikeCountEl.textContent);

        function resetButtons() {
            [likeBtn, dislikeBtn].forEach(btn => {
                btn.classList.remove("is-active", "bg-blue-600", "text-white", "border-blue-600");
                btn.classList.add("bg-white", "text-gray-700", "border-gray-300");
            });
        }

        function activate(btn) {
            resetButtons();
            btn.classList.add("is-active", "bg-blue-600", "text-white", "border-blue-600");
            btn.classList.remove("bg-white", "text-gray-700", "border-gray-300");
        }

        if (state === "like") activate(likeBtn);
        if (state === "dislike") activate(dislikeBtn);

        let abortController = null;

        function send(newState) {

            if (abortController) abortController.abort();
            abortController = new AbortController();

            fetch(url, {
                method: "POST",
                signal: abortController.signal,
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ newState })
            })
            .then(res => res.json())
            .then(data => {

                if (data.deleted) {
                    window.location.reload();
                    return;
                }

            })
            .catch(err => {
                if (err.name === "AbortError") return;
                console.error(`Like/dislike hiba (${type} ${id}):`, err);
            });
        }

        likeBtn?.addEventListener("click", () => {
            let newState = state === "like" ? "none" : "like";

            if (state === "like") likes--;
            if (state === "dislike") dislikes--;
            if (newState === "like") likes++;

            likeCountEl.textContent    = likes;
            dislikeCountEl.textContent = dislikes;

            if (newState === "none") resetButtons();
            else activate(likeBtn);

            state = newState;
            send(newState);
        });
    
        dislikeBtn?.addEventListener("click", () => {
            let newState = state === "dislike" ? "none" : "dislike";

            if (state === "dislike") dislikes--;
            if (state === "like") likes--;
            if (newState === "dislike") dislikes++;

            likeCountEl.textContent    = likes;
            dislikeCountEl.textContent = dislikes;

            if (newState === "none") resetButtons();
            else activate(dislikeBtn);

            state = newState;
            send(newState);
        });

    });

});
