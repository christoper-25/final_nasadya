document.addEventListener("DOMContentLoaded", () => {
  // Customer Phone Click
  const phoneElements = document.querySelectorAll(".customer-phone");
  const phoneModal = new bootstrap.Modal(document.getElementById("phoneModal"));
  const modalPhoneNumber = document.getElementById("modalPhoneNumber");
  const callBtn = document.getElementById("callBtn");
  const messageBtn = document.getElementById("messageBtn");

  phoneElements.forEach(el => {
    el.addEventListener("click", () => {
      const phone = el.dataset.phone;
      modalPhoneNumber.textContent = phone;
      callBtn.href = `tel:${phone}`;
      messageBtn.href = `sms:${phone}`;
      phoneModal.show();
    });
  });

  // Set as Destination Button
  const setRouteBtns = document.querySelectorAll(".set-route-btn");
  setRouteBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const address = btn.dataset.address;
      const fromInput = document.getElementById("fromPlace");
      const toInput = document.getElementById("toPlace");

      // Use rider current location as FROM if available
      if (fromInput.value === "") fromInput.value = "Current Location";

      toInput.value = address;
      // Optionally trigger the route calculation automatically
      document.getElementById("calculateRouteBtn").click();
    });
  });
});
