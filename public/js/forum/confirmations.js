document.addEventListener("DOMContentLoaded", () => {
    const confirmConfigs = [
        {
            trigger: ".open-forum-delete",
            modal: "#deleteModal",
            form: "#deleteForumForm",
            getUrl: (btn) => btn.dataset.url
        },
        {
            trigger: ".open-comment-delete",
            modal: "#deleteCommentModal",
            form: "#deleteCommentForm",
            getUrl: (btn) => btn.dataset.url
        },
        {
            trigger: ".openUnsaveModal",
            modal: "#unsaveModal",
            form: "#unsaveForm",
            getUrl: (btn) => `/forum/${btn.dataset.forumId}/unsave`
        }
    ];

    confirmConfigs.forEach(cfg => {
        const triggers = document.querySelectorAll(cfg.trigger);

        triggers.forEach(btn => {
            btn.addEventListener("click", () => {

                const modal = document.querySelector(cfg.modal);
                const form  = document.querySelector(cfg.form);

                if (!modal || !form) return;

                const actionUrl = cfg.getUrl(btn);
                form.action = actionUrl;

                modal.classList.remove("hidden");
            });
        });
    });

    const cancelButtons = [
        { id: "cancelDelete", modal: "#deleteModal" },
        { id: "cancelCommentDelete", modal: "#deleteCommentModal" },
        { id: "cancelUnsave", modal: "#unsaveModal" }
    ];

    cancelButtons.forEach(c => {
        const btn = document.getElementById(c.id);
        const modal = document.querySelector(c.modal);

        btn?.addEventListener("click", () => {
            modal?.classList.add("hidden");
        });
    });

});
