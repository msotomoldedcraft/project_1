document.addEventListener("DOMContentLoaded", function () {

    const container = document.getElementById("wishlist-container");
    const addBtn = document.getElementById("add-wishlist-btn");

    addBtn.addEventListener("click", function () {
        const div = document.createElement("div");
        div.classList.add("wishlist-item");

        div.innerHTML = `
            <input name="wishlist[]" placeholder="Wishlist item">
            <button type="button" class="remove-btn">×</button>
        `;

        container.appendChild(div);
    });

    container.addEventListener("click", function (e) {
        if (e.target.classList.contains("remove-btn")) {
            e.target.parentElement.remove();
        }
    });

});