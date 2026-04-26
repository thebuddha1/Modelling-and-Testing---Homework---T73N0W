document.addEventListener("DOMContentLoaded", () => {

    const editButtons = document.querySelectorAll(".open-edit-comment");
    const cancelButtons = document.querySelectorAll(".cancel-edit");

    editButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.dataset.id;

            document.querySelectorAll('[id^="comment-edit-"]').forEach(el => el.classList.add("hidden"));
            document.querySelectorAll('[id^="comment-view-"]').forEach(el => el.classList.remove("hidden"));

            const view = document.getElementById("comment-view-" + id);
            const edit = document.getElementById("comment-edit-" + id);
            const card = document.getElementById("comment-card-" + id);
            const menuWrapper = card?.querySelector(".comment-menu-wrapper");

            if (view && edit) {
                view.classList.add("hidden");
                edit.classList.remove("hidden");
            }

            if (menuWrapper) {
                menuWrapper.classList.add("hidden");
            }
        });
    });

    cancelButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.dataset.id;

            const view = document.getElementById("comment-view-" + id);
            const edit = document.getElementById("comment-edit-" + id);
            const card = document.getElementById("comment-card-" + id);
            const menuWrapper = card?.querySelector(".comment-menu-wrapper");

            if (edit && view) {
                edit.classList.add("hidden");
                view.classList.remove("hidden");
            }

            if (menuWrapper) {
                menuWrapper.classList.remove("hidden");
            }
        });
    });

    const openForumEdit = document.querySelector(".open-forum-edit");
    const cancelForumEdit = document.querySelector(".cancel-forum-edit");
    const forumViewPanel = document.getElementById("forum-view");
    const forumEditPanel = document.getElementById("forum-edit");
    const forumMenuWrapper = document.querySelector(".forum-menu-wrapper");
    const forumMenu = document.querySelector(".forum-menu");

    openForumEdit?.addEventListener("click", () => {
        forumViewPanel?.classList.add("hidden");
        forumEditPanel?.classList.remove("hidden");

        forumMenuWrapper?.classList.add("hidden");
        forumMenu?.classList.add("hidden");
    });

    cancelForumEdit?.addEventListener("click", () => {
        forumEditPanel?.classList.add("hidden");
        forumViewPanel?.classList.remove("hidden");
        forumMenuWrapper?.classList.remove("hidden");
    });

});
