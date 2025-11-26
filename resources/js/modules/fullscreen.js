// resources/js/modules/fullscreen.js
import { map } from "./map.js";

document.addEventListener("DOMContentLoaded", () => {
  const fullscreenBtn = document.getElementById("fullscreenBtn");
  const mapContainer = document.getElementById("map");
  const mainContainer = document.getElementById("mainContainer");

  if (fullscreenBtn && mapContainer) {
    fullscreenBtn.addEventListener("click", () => {
      mapContainer.classList.toggle("fullscreen-map");
      mainContainer?.classList.toggle("d-none");

      const iconEl = fullscreenBtn.querySelector("i");
      if (mapContainer.classList.contains("fullscreen-map")) {
        iconEl.classList.replace("bi-arrows-fullscreen", "bi-fullscreen-exit");
      } else {
        iconEl.classList.replace("bi-fullscreen-exit", "bi-arrows-fullscreen");
      }

      setTimeout(() => map.invalidateSize(), 300);
    });
  }
});
