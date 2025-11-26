document.addEventListener("click", e => {
  const btn = e.target.closest(".logout-btn");
  if (!btn) return;

  e.preventDefault();
  const form = btn.closest("form");
  if (!form) return;

  Swal.fire({
    title: "Are you sure?",
    text: "You will be logged out.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, log out",
    background: "#111",
    color: "#fff",
  }).then(result => {
    if (result.isConfirmed) {
      fetch(form.action, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          "Accept": "application/json",
          "Content-Type": "application/json"
        },
        body: JSON.stringify({}) // you can send extra data if needed
      })
      .then(response => {
        if (response.ok) {
          window.location.href = '/rider/login';
        } else {
          Swal.fire("Error", "Logout failed. Try again.", "error");
        }
      })
      .catch(() => {
        Swal.fire("Error", "Logout failed. Try again.", "error");
      });
    }
  });
});
