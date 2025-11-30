// resources/js/rider-dashboard.js
import $ from "jquery";
window.$ = window.jQuery = $;

// Map + modules
import { initMap, startRiderTracking, calculateRoute } from "./modules/map.js";
import "./modules/logout.js";
import "./modules/theme.js";
import "./modules/fullscreen.js";
import "./modules/tabs.js";
import "./modules/history.js";
import "./modules/phonemodal.js";

document.addEventListener("DOMContentLoaded", () => {
  // 1) Init map + live tracking
  initMap();
  startRiderTracking(false); // or false kung ayaw mag-send sa server

  // 2) Elements
  const setRouteButtons = document.querySelectorAll(".set-route-btn");
  const modalElement = document.getElementById("customerModal");
  const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
  const modalName = document.getElementById("modalCustomerName");
  const modalAddress = document.getElementById("modalCustomerAddress");
  const modalContact = document.getElementById("modalCustomerContact");
  const modalTransactionId = document.getElementById("modalTransactionId");
  const proofInput = document.getElementById("proofInput");
  const confirmSetInTransitBtn = document.getElementById("confirmSetInTransitBtn");
  const uploadDeliverBtn = document.getElementById("uploadDeliverBtn");
  const toPlaceInput = document.getElementById("toPlace");
  const calculateBtn = document.getElementById("calculateRouteBtn");

  let selectedTransactionId = null;

  // 3) "Set as Destination" buttons
  setRouteButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      selectedTransactionId = btn.dataset.transactionId;

      const address = btn.dataset.address;
      const lat = btn.dataset.lat;
      const lng = btn.dataset.lng;

      if (!lat || !lng) {
        Swal.fire("Error", "This order has no coordinates.", "error");
        return;
      }

      // Set destination input + dataset
      if (toPlaceInput) {
        toPlaceInput.value = address || "";
        toPlaceInput.dataset.lat = lat;
        toPlaceInput.dataset.lng = lng;
      }

      // Calculate route muna; pag success, saka buksan modal
      calculateRoute(lat, lng).then(success => {
        if (!success || !modal) return;

        modalName.textContent = btn.dataset.name;
        modalAddress.textContent = address;
        modalContact.textContent = btn.dataset.contact;
        modalTransactionId.value = selectedTransactionId;
        if (proofInput) proofInput.value = null;

        modal.show();
      });
    });
  });

  // 4) Calculate Route button (manual click sa Dashboard)
  if (calculateBtn && toPlaceInput) {
    calculateBtn.addEventListener("click", () => {
      const lat = toPlaceInput.dataset.lat;
      const lng = toPlaceInput.dataset.lng;


      calculateRoute(lat, lng);
    });
  }

  // 5) Start Delivery
  if (confirmSetInTransitBtn) {
    confirmSetInTransitBtn.addEventListener("click", async () => {
      if (!selectedTransactionId) {
        return Swal.fire("Error", "Transaction not set", "error");
      }

      try {
        const res = await fetch("/set-in-transit", {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              .getAttribute("content"),
            Accept: "application/json",
            "Content-Type": "application/json"
          },
          body: JSON.stringify({ order_id: selectedTransactionId })
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || "Failed");

        document
          .querySelectorAll(".set-route-btn")
          .forEach(b => (b.disabled = true));

        const btn = document.querySelector(
          `.set-route-btn[data-transaction-id="${selectedTransactionId}"]`
        );
        const badge = btn
          ?.closest(".accordion-item")
          ?.querySelector(".badge");
        if (badge) badge.textContent = "ongoing";

        if (modal) modal.hide();
        document
          .querySelectorAll(".modal-backdrop")
          .forEach(e => e.remove());

        Swal.fire({ icon: "success", text: data.message || "Delivery started" });
      } catch (err) {
        Swal.fire({ icon: "error", text: err.message });
      }
    });
  }

  // 6) Upload proof (Mark Delivered)
  if (uploadDeliverBtn) {
    uploadDeliverBtn.addEventListener("click", async () => {
      if (!selectedTransactionId) {
        return Swal.fire("Error", "Transaction not set", "error");
      }
      if (!proofInput || !proofInput.files.length) {
        return Swal.fire("Error", "Please upload a proof photo.", "error");
      }

      const formData = new FormData();
      formData.append("order_id", selectedTransactionId);
      formData.append("photo", proofInput.files[0]);

      try {
        const res = await fetch("/mark-delivered", {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              .getAttribute("content")
          },
          body: formData
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || "Failed");

        document
          .querySelectorAll(".set-route-btn")
          .forEach(b => (b.disabled = false));

        const btn = document.querySelector(
          `.set-route-btn[data-transaction-id="${selectedTransactionId}"]`
        );
        const badge = btn
          ?.closest(".accordion-item")
          ?.querySelector(".badge");
        if (badge) badge.textContent = "delivered";

        if (modal) modal.hide();
        document
          .querySelectorAll(".modal-backdrop")
          .forEach(e => e.remove());

        Swal.fire({
          icon: "success",
          text: data.message || "Marked as delivered"
        }).then(() => {
          window.location.reload();
        });
      } catch (err) {
        Swal.fire({ icon: "error", text: err.message });
      }
    });
  }
});

document.addEventListener("DOMContentLoaded", () => {
    const TAB_KEY = "riderActiveTab";

    // Helper: show tab by target (e.g. "#dashboard")
    function showTab(target) {
        if (!target) return;

        // Hanap kahit alin: sidebar o mobile button
        const triggerEl = document.querySelector(
            `[data-bs-toggle="pill"][data-bs-target="${target}"]`
        );
        if (!triggerEl) return;

        const tab = new bootstrap.Tab(triggerEl);
        tab.show();

        // Sync mobile bottom nav .active
        document.querySelectorAll(".mobile-bottom-nav .nav-btn")
            .forEach(btn => {
                btn.classList.toggle(
                    "active",
                    btn.getAttribute("data-bs-target") === target
                );
            });
    }

    // ========== 1. RESTORE LAST ACTIVE TAB ==========
    const lastTab = localStorage.getItem(TAB_KEY);

    if (lastTab) {
        showTab(lastTab);
    } else {
        // default dashboard
        showTab("#dashboard");
    }

    // ========== 2. LISTEN SA LAHAT NG TABS (SIDEBAR + MOBILE) ==========
    document
        .querySelectorAll('[data-bs-toggle="pill"]')
        .forEach(tabTriggerEl => {
            tabTriggerEl.addEventListener("shown.bs.tab", e => {
                const target = e.target.getAttribute("data-bs-target");
                if (!target) return;

                // Save current tab
                localStorage.setItem(TAB_KEY, target);

                // Sync .active sa bottom nav
                document.querySelectorAll(".mobile-bottom-nav .nav-btn")
                    .forEach(btn => {
                        btn.classList.toggle(
                            "active",
                            btn.getAttribute("data-bs-target") === target
                        );
                    });
            });
        });

    // ========== 3. EXPLICIT CLICK HANDLER SA MOBILE BOTTOM NAV ==========
    // (Optional pero nakakatulong sa mga mobile quirks)
    document
        .querySelectorAll(".mobile-bottom-nav .nav-btn")
        .forEach(btn => {
            btn.addEventListener("click", () => {
                const target = btn.getAttribute("data-bs-target");
                showTab(target); // ensure Bootstrap tab + classes updated
            });
        });
});


document.querySelectorAll('.proofmodal').forEach(modalEl => {
    modalEl.addEventListener('hidden.bs.modal', function () {
        const modalBackdrop = document.querySelector('.modal-backdrop');
        if(modalBackdrop) modalBackdrop.remove();
    });
});
