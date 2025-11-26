// resources/js/modules/tabs.js

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".mobile-bottom-nav .nav-btn").forEach(button => {
    button.addEventListener("click", function () {
      document.querySelectorAll(".mobile-bottom-nav .nav-btn").forEach(btn => btn.classList.remove("active"));
      this.classList.add("active");

      const targetId = this.getAttribute("data-bs-target");
      if (!targetId) return;
      const targetTab = document.querySelector(targetId);
      if (!targetTab) return;

      if (targetTab) {
        document.querySelectorAll(".tab-pane").forEach(tab => tab.classList.remove("show", "active"));
        targetTab.classList.add("show", "active");
      }

      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  });
});
