document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;

    const desktopBtn = document.getElementById("themeToggle");
    const desktopIcon = document.getElementById("themeIcon");

    const mobileBtn = document.getElementById("mobileThemeToggle");
    const mobileIcon = document.getElementById("mobileThemeIcon");

    const savedTheme = localStorage.getItem("theme") || "light";
    body.classList.add(savedTheme === "dark" ? "dark-mode" : "light-mode");

    function updateIcons(theme) {
        const iconClass = theme === "dark" ? "bi bi-moon-fill" : "bi bi-sun-fill";
        if (desktopIcon) desktopIcon.className = iconClass;
        if (mobileIcon) mobileIcon.className = iconClass;
    }

    updateIcons(savedTheme);

    function toggleTheme() {
        body.classList.toggle("dark-mode");
        body.classList.toggle("light-mode");

        const newTheme = body.classList.contains("dark-mode") ? "dark" : "light";
        localStorage.setItem("theme", newTheme);
        updateIcons(newTheme);
    }

    if (desktopBtn) desktopBtn.addEventListener("click", toggleTheme);
    if (mobileBtn) mobileBtn.addEventListener("click", toggleTheme);
});
