    const card = document.getElementById("loginCard");
    let startY, moveY = 0, isDragging = false;

    card.addEventListener("touchstart", startDrag);
    card.addEventListener("touchmove", onDrag);
    card.addEventListener("touchend", endDrag);

    card.addEventListener("mousedown", startDrag);
    window.addEventListener("mousemove", onDrag);
    window.addEventListener("mouseup", endDrag);

    function startDrag(e) {
      isDragging = true;
      startY = e.touches ? e.touches[0].clientY : e.clientY;
    }

    function onDrag(e) {
      if (!isDragging) return;
      const currentY = e.touches ? e.touches[0].clientY : e.clientY;
      moveY = currentY - startY;

      if (moveY < 0) { // swipe up
        card.style.transform = `translateY(${Math.max(0, 55 + moveY / 5)}%)`;
      }
    }

    function endDrag() {
      if (!isDragging) return;
      isDragging = false;

      if (moveY < -50) {
        card.classList.add("active");
      } else {
        card.classList.remove("active");
      }

      moveY = 0;
    }
