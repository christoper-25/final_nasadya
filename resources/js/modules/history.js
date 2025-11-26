document.addEventListener("DOMContentLoaded", () => {
    const historyContainer = document.querySelector("#historyAccordion");
    if (!historyContainer) return;

    // Select only history items
    const getHistoryItems = () => [...historyContainer.querySelectorAll(".accordion-item")];

    // SEARCH (history only)
    const historySearch = document.querySelector("#historySearch");
    if (historySearch) {
        historySearch.addEventListener("input", function () {
            const q = this.value.toLowerCase();
            getHistoryItems().forEach(item => {
                const text = item.innerText.toLowerCase();
                item.style.display = text.includes(q) ? "" : "none";
            });
        });
    }

    // FILTER BUTTONS (history only)
    document.querySelectorAll(".history-filter").forEach(btn => {
        btn.addEventListener("click", () => {
            const filter = btn.dataset.filter;

            document.querySelectorAll(".history-filter").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            const now = new Date();
            getHistoryItems().forEach(item => {
                const date = item.dataset.date;
                const d = new Date(date);

                let show = true;
                if (filter === "today") {
                    show = d.toDateString() === now.toDateString();
                } else if (filter === "week") {
                    const diff = (now - d) / (1000 * 60 * 60 * 24);
                    show = diff <= 7;
                } else if (filter === "month") {
                    show = d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
                }

                item.style.display = show ? "" : "none";
            });
        });
    });

    // SORT DROPDOWN BUTTON (history only)
    document.querySelectorAll(".sort-option").forEach(option => {
        option.addEventListener("click", function (e) {
            e.preventDefault();

            const container = historyContainer;

            // Update dropdown button text
            const dropdownBtn = document.getElementById("historySortDropdown");
            if (dropdownBtn) dropdownBtn.textContent = this.textContent;

            // Mark active
            document.querySelectorAll(".sort-option").forEach(o => o.classList.remove("active"));
            this.classList.add("active");

            // Sort items
            const sortValue = this.dataset.value;
            const sorted = getHistoryItems().sort((a, b) => {
                const d1 = new Date(a.dataset.date);
                const d2 = new Date(b.dataset.date);
                return sortValue === "latest" ? d2 - d1 : d1 - d2;
            });

            container.innerHTML = "";
            sorted.forEach(i => container.appendChild(i));
        });
    });
});
