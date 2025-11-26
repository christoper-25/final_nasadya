// resources/js/modules/route.js
import { map, currentLocation } from "./map.js";

let routeLayer = null;

window.calculateRoute = function () {
  const to = document.getElementById("toPlace").value;
  const loading = document.getElementById("loadingSpinner");
  const routeInfo = document.getElementById("routeInfo");

  if (!currentLocation) {
    alert("Current location not available yet.");
    return;
  }
  if (!to) {
    alert("Please enter a destination.");
    return;
  }

  loading.style.display = "block";
  routeInfo.classList.add("d-none");

  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(to)}`)
    .then(res => res.json())
    .then(result => {
      if (!result?.length) {
        alert("Destination not found.");
        loading.style.display = "none";
        return;
      }

      const toCoords = [result[0].lat, result[0].lon];
      if (routeLayer) routeLayer.remove();

      routeLayer = L.Routing.control({
        waypoints: [
          L.latLng(currentLocation[0], currentLocation[1]),
          L.latLng(toCoords[0], toCoords[1])
        ],
        routeWhileDragging: false,
        addWaypoints: false,
      }).addTo(map);

      loading.style.display = "none";
      routeInfo.classList.remove("d-none");
      routeInfo.innerHTML = `<strong>Route set from:</strong> current location<br><strong>to:</strong> ${to}`;
    })
    .catch(() => {
      alert("Error calculating route.");
      loading.style.display = "none";
    });
};
