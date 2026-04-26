document.addEventListener("DOMContentLoaded", () => {
    const openReportBtn = document.getElementById('openReportModal');
    const reportModal   = document.getElementById('reportModal');
    const cancelReport  = document.getElementById('cancelReport');
    const submitReport  = document.getElementById('submitReport');
    const reportChecks  = document.querySelectorAll('.report-check');

    openReportBtn?.addEventListener('click', () => {
        reportModal.classList.remove('hidden');
    });

    cancelReport?.addEventListener('click', () => {
        reportModal.classList.add('hidden');
    });

    reportChecks.forEach(chk => {
        chk.addEventListener('change', () => {
            const checked = [...reportChecks].filter(c => c.checked).length;

            if (checked >= 3) {
                reportChecks.forEach(c => {
                    if (!c.checked) c.disabled = true;
                });
            } else {
                reportChecks.forEach(c => c.disabled = false);
            }

            if (checked >= 1) {
                submitReport.disabled = false;
                submitReport.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitReport.disabled = true;
                submitReport.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
    });


    const commentReportModal = document.getElementById('commentReportModal');
    const cancelCommentReport = document.getElementById('cancelCommentReport');
    const submitCommentReport = document.getElementById('submitCommentReport');
    const commentChecks = document.querySelectorAll('.comment-report-check');

    document.querySelectorAll('.open-comment-report').forEach(btn => {
        btn.addEventListener('click', () => {
            const cid = btn.dataset.commentId;

            document.getElementById('commentReportForm').action =
                `/comment/${cid}/report`;

            commentReportModal.classList.remove('hidden');
        });
    });

    cancelCommentReport?.addEventListener('click', () => {
        commentReportModal.classList.add('hidden');
    });

    commentChecks.forEach(c => {
        c.addEventListener('change', () => {
            const n = [...commentChecks].filter(x => x.checked).length;

            if (n >= 3) {
                commentChecks.forEach(x => {
                    if (!x.checked) x.disabled = true;
                });
            } else {
                commentChecks.forEach(x => x.disabled = false);
            }

            if (n >= 1) {
                submitCommentReport.disabled = false;
                submitCommentReport.classList.remove('opacity-50','cursor-not-allowed');
            } else {
                submitCommentReport.disabled = true;
                submitCommentReport.classList.add('opacity-50','cursor-not-allowed');
            }
        });
    });

});
