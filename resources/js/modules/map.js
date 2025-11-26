// resources/js/modules/map.js
import L from "leaflet";
import "leaflet-control-geocoder";
import Openrouteservice from "openrouteservice-js";

// -------------------------
// Exported variables
// -------------------------
export let map;
export let currentLocation = null;
export let currentMarker = null;
export let routeLayer = null;

// -------------------------
// ORS Directions
// -------------------------
const orsDirections = new Openrouteservice.Directions({
  api_key: "eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6IjdmOTc2NjE0OTZlNjQzOTg5MmRiNDgyMDMyYzdlYmFmIiwiaCI6Im11cm11cjY0In0=" // replace with your ORS API key
});

const DirectionsControl = L.Control.extend({
  options: { position: 'topright' },
  onAdd: function (map) {
    const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control directions-control');
    container.id = 'mapDirections';
    container.style.background = 'rgba(255,255,255,0.97)';
    container.style.padding = '12px';
    container.style.margin = '10px 12px 10px 12px';
    container.style.borderRadius = '9px';
    container.style.maxWidth = '345px';
    container.style.maxHeight = '220px';
    container.style.overflowY = 'auto';
    container.style.fontSize = '1em';
    container.style.boxShadow = '0 2px 14px rgba(40,40,40,0.14)';
    container.style.display = 'none'; // Start hidden
    container.innerHTML = `<strong>Directions:</strong><br><span></span>`;
    L.DomEvent.disableClickPropagation(container);
    return container;
  }
});

const InstructionsToggle = L.Control.extend({
  options: { position: 'topright' },
  onAdd: function(map) {
    const btn = L.DomUtil.create('button', 'leaflet-bar leaflet-control inst-btn');
    btn.textContent = 'Show Directions';
    btn.style.cursor = 'pointer';
    btn.style.padding = '7px 18px';
    btn.style.margin = '12px';
    btn.style.fontSize = '1rem';
    btn.style.background = '#c82333';
    btn.style.color = '#fff';
    btn.style.border = 'none';
    btn.style.borderRadius = '7px';

    btn.onclick = function() {
      const dirBox = document.getElementById('mapDirections');
      if (dirBox) {
        if (dirBox.style.display === 'none' || !dirBox.style.display) {
          dirBox.style.display = 'block';
          btn.textContent = 'Hide Directions';
        } else {
          dirBox.style.display = 'none';
          btn.textContent = 'Show Directions';
        }
      }
    };
    return btn;
  }
});


// -------------------------
// Initialize map
// -------------------------
export function initMap() {
  map = L.map("map").setView([14.5995, 120.9842], 13);

  map.addControl(new DirectionsControl());
map.addControl(new InstructionsToggle());


  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors",
  }).addTo(map);

  // Add geocoder/search box
  L.Control.geocoder({ defaultMarkGeocode: true }).addTo(map);

  // Get current location
  if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(
    position => {
      const { latitude: lat, longitude: lng } = position.coords;
      currentLocation = [lat, lng];

      map.setView(currentLocation, 15);

      currentMarker = L.marker(currentLocation)
        .addTo(map)
        .bindPopup("You are here")
        .openPopup();

      // Fill 'From' input automatically
      const fromInput = document.getElementById("fromPlace");
      fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
        .then(res => res.json())
        .then(data => {
          if (data?.display_name && fromInput) fromInput.value = data.display_name;
        });
    },
    () => {
      console.warn("GPS failed — fallback to manual input only");
      const fromInput = document.getElementById("fromPlace");
      if (fromInput) fromInput.value = "";
    },
    { enableHighAccuracy: true, timeout: 6000 }
  );
} else {
  console.warn("Geolocation not supported — manual input only");
}

}

// -------------------------
// Live Rider Tracking
// -------------------------
export function startRiderTracking(sendToServer = false) {
  if (!navigator.geolocation) return;

  navigator.geolocation.watchPosition(
    position => {
      const { latitude: lat, longitude: lng } = position.coords;
      currentLocation = [lat, lng];

      if (!currentMarker) {
        currentMarker = L.marker(currentLocation).addTo(map);
      } else {
        currentMarker.setLatLng(currentLocation);
      }

      map.setView(currentLocation, 15);

      if (sendToServer) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
        if (csrfToken) {
          fetch("/api/rider/location", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ lat, lng })
          }).catch(err => console.error("Error sending location:", err));
        } else {
          console.warn("No CSRF token found — skipping location send");
        }
      }
    },
    error => console.warn("Live tracking failed:", error),
    { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 }
  );
}

// -------------------------
// Display Turn-by-Turn Instructions
// -------------------------
function displayInstructions(geojson) {
  const steps = geojson.features[0].properties.segments[0].steps;
  const routeInfo = document.getElementById("routeInfo");
  if (!routeInfo) return;
  routeInfo.classList.remove("d-none");
  routeInfo.innerHTML = steps
    .map(
      (s, i) =>
        `<strong>Step ${i + 1}:</strong> ${s.instruction} (${s.distance.toFixed(0)} m)`
    )
    .join("<br>");
}

// -------------------------
// Calculate Route
// -------------------------
export function calculateRoute(to) {
  return new Promise(resolve => {
    if (!currentLocation) {
      alert("Current location not available yet.");
      return resolve(false);
    }
    if (!to) {
      alert("Please enter a destination.");
      return resolve(false);
    }

    const loading = document.getElementById("loadingSpinner");
    const routeInfo = document.getElementById("routeInfo");

    if (loading) loading.style.display = "block";
    if (routeInfo) {
      routeInfo.classList.add("d-none");
      routeInfo.innerHTML = "";
    }

    // Geocode destination
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(to)}`)
      .then(res => res.json())
      .then(result => {
        if (!result?.length) {
          alert("Destination not found.");
          if (loading) loading.style.display = "none";
          return resolve(false);
        }

        const fromLngLat = [currentLocation[1], currentLocation[0]];
        const toLngLat = [parseFloat(result[0].lon), parseFloat(result[0].lat)];

        if (routeLayer && map.hasLayer(routeLayer)) {
          map.removeLayer(routeLayer);
        }

        // ORS Directions
        orsDirections
          .calculate({
            coordinates: [fromLngLat, toLngLat],
            profile: "driving-car",
            format: "geojson",
            instructions: true
          })
          .then(response => {
            // Draw polyline route
            const coords = response.features[0].geometry.coordinates.map(
              ([lng, lat]) => [lat, lng]
            );
            routeLayer = L.polyline(coords, { color: "#ff2445", weight: 5 }).addTo(map);
            map.fitBounds(routeLayer.getBounds());

            if (loading) loading.style.display = "none";

            // ---- SHOW DIRECTIONS in the overlay
            const dirBox = document.getElementById('mapDirections');
            const steps =
              response.features?.[0]?.properties?.segments?.[0]?.steps || [];
            const directionsHtml = steps.length
              ? `<ol style="margin:0;padding-left:22px;">${steps
                  .map(
                    (s) =>
                      `<li style="margin-bottom:0.4em">${s.instruction} <small style="color:#888;">(${(s.distance / 1000).toFixed(1)} km)</small></li>`
                  )
                  .join("")}</ol>`
              : "<em style='color:#888'>No travel guide available.</em>";
            if (dirBox) {
              dirBox.querySelector("span").innerHTML = directionsHtml;
              dirBox.style.display = 'block';
              // Flip button state
              const btn = document.querySelector('.inst-btn');
              if (btn) btn.textContent = 'Hide Directions';
            }

            resolve(true);
          })
          .catch(err => {
            console.error("ORS error:", err);
            alert("Error calculating route.");
            if (loading) loading.style.display = "none";
            resolve(false);
          });
      })
      .catch(err => {
        console.error(err);
        alert("Error calculating route.");
        if (loading) loading.style.display = "none";
        resolve(false);
      });
  });
}

document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("calculateRouteBtn");
  btn?.addEventListener("click", async () => {
    const fromText = document.getElementById("fromPlace").value;
    const toText = document.getElementById("toPlace").value;

    if (!fromText || !toText) {
      alert("Please enter both FROM and TO locations.");
      return;
    }

    // Geocode 'FROM' only if GPS not available
    if (!currentLocation) {
      const fromCoords = await geocodeAddress(fromText);
      if (!fromCoords) {
        alert("Starting location not found.");
        return;
      }
      currentLocation = [fromCoords.lat, fromCoords.lng];
    }

    // Call existing calculateRoute() function using 'to' value
    calculateRoute(toText);
  });
});


function geocodeAddress(query) {
  return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
    .then(res => res.json())
    .then(results => {
      if (!results.length) return null;
      return {
        lat: parseFloat(results[0].lat),
        lng: parseFloat(results[0].lon)
      };
    });
}

