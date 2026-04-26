document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll('.ratingForm').forEach(form => {

        const button = form.querySelector('.submitRatingBtn');
        const radios = form.querySelectorAll('.rating-radio');

        if (!button || radios.length === 0) return;

        function checkCompletion() {
            const groups = {
                precision: false,
                driving: false,
                social: false
            };

            radios.forEach(radio => {
                if (radio.checked && groups.hasOwnProperty(radio.name)) {
                    groups[radio.name] = true;
                }
            });

            const complete =
                groups.precision &&
                groups.driving &&
                groups.social;

            if (complete) {
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                button.disabled = true;
                button.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        radios.forEach(radio => {
            radio.addEventListener('change', checkCompletion);
        });

    });

});
