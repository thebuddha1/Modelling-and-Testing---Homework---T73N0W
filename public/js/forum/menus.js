document.addEventListener("DOMContentLoaded", () => {

    const forumMenuWrapper = document.querySelector(".forum-menu-wrapper");
    const forumMenuBtn = forumMenuWrapper?.querySelector(".forum-menu-btn");
    const forumMenu = forumMenuWrapper?.querySelector(".forum-menu");

    if (forumMenuBtn && forumMenu) {

        forumMenuBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            forumMenu.classList.toggle("hidden");
        });

        document.addEventListener("click", (e) => {
            if (!e.target.closest(".forum-menu-wrapper")) {
                forumMenu.classList.add("hidden");
            }
        });
    }

    document.querySelectorAll(".comment-menu-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();

            const commentId = btn.dataset.commentId;

            document.querySelectorAll(".comment-menu").forEach(menu => {
                menu.classList.add("hidden");
            });

            const targetMenu = document.getElementById(`comment-menu-${commentId}`);
            targetMenu?.classList.toggle("hidden");
        });
    });

    document.addEventListener("click", (e) => {
        if (!e.target.closest(".comment-menu-wrapper")) {
            document.querySelectorAll(".comment-menu").forEach(menu => {
                menu.classList.add("hidden");
            });
        }
    });

});
