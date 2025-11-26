// resources/js/modules/theme.js

document.addEventListener("DOMContentLoaded", () => {
  const body = document.body;
  const toggleBtn = document.getElementById("themeToggle");
  const icon = document.getElementById("themeIcon");

  const savedTheme = localStorage.getItem("theme") || "light";
  body.classList.add(`${savedTheme}-mode`);
  icon.className = savedTheme === "dark" ? "bi bi-moon-fill" : "bi bi-sun-fill";

  toggleBtn?.addEventListener("click", () => {
    body.classList.toggle("dark-mode");
    body.classList.toggle("light-mode");

    const newTheme = body.classList.contains("dark-mode") ? "dark" : "light";
    icon.className = newTheme === "dark" ? "bi bi-moon-fill" : "bi bi-sun-fill";
    localStorage.setItem("theme", newTheme);
  });
});

