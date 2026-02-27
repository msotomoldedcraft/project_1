document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector(".edit-container");
    container.classList.add("loaded");
});

function addNewWishlist() {
    const container = document.getElementById('new-wishlist-container');

    const wrapper = document.createElement('div');
    wrapper.className = 'wishlist-item';

    const input = document.createElement('input');
    input.name = 'wishlist_new[]';
    input.placeholder = 'New wishlist item';

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'remove-btn';
    removeBtn.textContent = 'Remove';
    removeBtn.onclick = () => wrapper.remove();

    wrapper.appendChild(input);
    wrapper.appendChild(removeBtn);

    container.appendChild(wrapper);
}

function removeWishlistItem(button) {
    button.parentElement.remove();
}