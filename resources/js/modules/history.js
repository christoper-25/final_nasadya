document.addEventListener("DOMContentLoaded", () => {

    const items = [...document.querySelectorAll(".accordion-item")];

    // SEARCH
    document.querySelector("#historySearch").addEventListener("input", function () {
        const q = this.value.toLowerCase();
        items.forEach(item => {
            const text = item.innerText.toLowerCase();
            item.style.display = text.includes(q) ? "" : "none";
        });
    });

    // FILTER BUTTONS
    document.querySelectorAll(".history-filter").forEach(btn => {
        btn.addEventListener("click", () => {
            const filter = btn.dataset.filter;

            document.querySelectorAll(".history-filter").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            items.forEach(item => {
                const date = item.dataset.date;
                const d = new Date(date);
                const now = new Date();

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

    // SORT DROPDOWN BUTTON
    document.querySelectorAll(".sort-option").forEach(option => {
        option.addEventListener("click", function (e) {
            e.preventDefault();
            const container = document.querySelector("#historyAccordion");

            // update dropdown button text
            document.getElementById("historySortDropdown").textContent = this.textContent;

            // mark active
            document.querySelectorAll(".sort-option").forEach(o => o.classList.remove("active"));
            this.classList.add("active");

            // sort items
            const sortValue = this.dataset.value;
            const sorted = items.sort((a, b) => {
                const d1 = new Date(a.dataset.date);
                const d2 = new Date(b.dataset.date);
                return sortValue === "latest" ? d2 - d1 : d1 - d2;
            });

            container.innerHTML = "";
            sorted.forEach(i => container.appendChild(i));
        });
    });

});
