document.addEventListener("DOMContentLoaded", function () {

    const container = document.getElementById("wishlist-container");
    const addBtn = document.getElementById("add-wishlist-btn");

    if (!container || !addBtn) return;

    addBtn.addEventListener("click", function () {

        const div = document.createElement("div");
        div.classList.add("grid-x", "grid-margin-x", "align-middle", "wishlist-item");

        div.innerHTML = `
            <div class="cell auto">
                <input type="text" name="wishlist[]" placeholder="Wishlist item">
            </div>
            <div class="cell shrink">
                <button type="button" class="button alert tiny remove-btn">
                    ×
                </button>
            </div>
        `;

        container.appendChild(div);
    });

    container.addEventListener("click", function (e) {
        if (e.target.classList.contains("remove-btn")) {
            e.target.closest(".wishlist-item").remove();
        }
    });

});