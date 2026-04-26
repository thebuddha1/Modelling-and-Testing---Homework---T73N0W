document.addEventListener("DOMContentLoaded", () => {

    const toggleBtn   = document.getElementById("forumTypeToggle");
    const panel       = document.getElementById("forumTypePanel");
    const radios      = document.querySelectorAll("input[name='forumFilter']");
    const groups      = document.querySelectorAll(".forumListGroup");

    if (!toggleBtn || !panel || radios.length === 0) {
        return;
    }

    const titles = {
        own: "Saját fórumaim",
        saved: "Mentett fórumaim",
        liked: "Kedvelt fórumaim"
    };

    toggleBtn.addEventListener("click", () => {
        panel.classList.toggle("hidden");
    });

    radios.forEach(radio => {
        radio.addEventListener("change", () => {

            const value = radio.value;

            toggleBtn.innerText = `${titles[value]} ▼`;

            groups.forEach(group => {
                group.classList.add("hidden");
            });

            const active = document.getElementById("list-" + value);
            active?.classList.remove("hidden");
        });
    });

});
