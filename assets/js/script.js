document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("searchInput");
    const results = document.getElementById("resultsContainer");

    function fetchWalkers(query = "") {
        fetch(`search_walkers.php?search=${encodeURIComponent(query)}`)
            .then(res => res.text())
            .then(html => {
                results.innerHTML = html;
            });
    }

    // Alapértelmezett lista betöltése
    fetchWalkers();

    input.addEventListener("input", () => {
        const query = input.value.trim();
        fetchWalkers(query);
    });
});