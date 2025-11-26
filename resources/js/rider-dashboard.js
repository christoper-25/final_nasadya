// resources/js/rider-dashboard.js
import $ from "jquery";
window.$ = window.jQuery = $;

// Import map functions and modules
import { initMap, startRiderTracking, calculateRoute } from "./modules/map.js";
import "./modules/logout.js";
import "./modules/theme.js";
import "./modules/fullscreen.js";
import "./modules/tabs.js";
import "./modules/history.js";
import "./modules/phonemodal.js";

// Initialize map
initMap();

// Start live tracking (set to false if you don’t want auto sending to server yet)
startRiderTracking(false);

// Route calculation
document.getElementById("calculateRouteBtn")?.addEventListener("click", () => {
    const from = document.getElementById("fromPlace").value || null;
    const to = document.getElementById("toPlace").value;
    calculateRoute(to, from); // from is optional
});



document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const setRouteButtons = document.querySelectorAll('.set-route-btn');
    const modalElement = document.getElementById('customerModal');
    const modal = new bootstrap.Modal(modalElement);
    const modalName = document.getElementById('modalCustomerName');
    const modalAddress = document.getElementById('modalCustomerAddress');
    const modalContact = document.getElementById('modalCustomerContact');
    const modalTransactionId = document.getElementById('modalTransactionId');
    const proofInput = document.getElementById('proofInput');
    const confirmSetInTransitBtn = document.getElementById('confirmSetInTransitBtn');
    const uploadDeliverBtn = document.getElementById('uploadDeliverBtn');
    const toPlaceInput = document.getElementById('toPlace');
    const calculateBtn = document.getElementById('calculateRouteBtn');

    let selectedTransactionId = null;



setRouteButtons.forEach(btn => {
    btn.addEventListener('click', async () => {
        selectedTransactionId = btn.dataset.transactionId;

        // SET DESTINATION
        if (toPlaceInput) toPlaceInput.value = btn.dataset.address;

        // TRY ROUTE CALCULATION FIRST
        const success = await calculateRoute(btn.dataset.address);

        if (!success) {
            // ❌ FAILED = NO MODAL
            return;
        }

        // SUCCESS → FILL MODAL THEN SHOW
        modalName.textContent = btn.dataset.name;
        modalAddress.textContent = btn.dataset.address;
        modalContact.textContent = btn.dataset.contact;
        modalTransactionId.value = selectedTransactionId;
        proofInput.value = null;

        modal.show();
    });
});


    // Start Delivery button
    confirmSetInTransitBtn.addEventListener('click', async () => {
        if (!selectedTransactionId) return Swal.fire('Error', 'Transaction not set', 'error');
        try {
            const res = await fetch('/set-in-transit', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ transaction_id: selectedTransactionId })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed');

            document.querySelectorAll('.set-route-btn').forEach(b => b.disabled = true);
            const badge = document.querySelector(`.set-route-btn[data-transaction-id="${selectedTransactionId}"]`)
                            .closest('.accordion-item').querySelector('.badge');
            if (badge) badge.textContent = 'ongoing';

            modal.hide();
            document.querySelectorAll('.modal-backdrop').forEach(e => e.remove());
            Swal.fire({ icon: 'success', text: data.message || 'Delivery started' });
        } catch (err) {
            Swal.fire({ icon: 'error', text: err.message });
        }
    });

    // Upload Proof button
    uploadDeliverBtn.addEventListener('click', async () => {
        if (!selectedTransactionId) return Swal.fire('Error', 'Transaction not set', 'error');
        if (!proofInput.files.length) return Swal.fire('Error', 'Please upload a proof photo.', 'error');

        const formData = new FormData();
        formData.append('transaction_id', selectedTransactionId);
        formData.append('photo', proofInput.files[0]);

        try {
            const res = await fetch('/mark-delivered', {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed');

            document.querySelectorAll('.set-route-btn').forEach(b => b.disabled = false);
            const badge = document.querySelector(`.set-route-btn[data-transaction-id="${selectedTransactionId}"]`)
                        .closest('.accordion-item').querySelector('.badge');
            if (badge) badge.textContent = 'delivered';

            modal.hide();
            document.querySelectorAll('.modal-backdrop').forEach(e => e.remove());
            Swal.fire({ icon: 'success', text: data.message || 'Marked as delivered' });
        } catch (err) {
            Swal.fire({ icon: 'error', text: err.message });
        }
    });
});
