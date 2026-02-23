document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('wishlist-items');
    const addButton = document.getElementById('add-wishlist-item');

    // Safety check
    if (!container || !addButton) return;

    let index = container.querySelectorAll('.wishlist-item').length;

    // Function to remove an item with fade-out effect
    function removeWishlistItem(itemDiv) {
        itemDiv.style.transition = 'opacity 0.3s';
        itemDiv.style.opacity = '0';
        setTimeout(() => itemDiv.remove(), 300);
    }

    // Function to create a new wishlist item
    function createWishlistItem() {
        const prototype = container.dataset.prototype;
        const newForm = prototype.replace(/__name__/g, index);

        const div = document.createElement('div');
        div.classList.add('wishlist-item', 'mb-2', 'd-flex', 'align-items-center');
        div.innerHTML = newForm;

        // Create remove button
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = 'Remove';
        removeBtn.className = 'remove-wishlist btn btn-danger btn-sm ms-2';
        removeBtn.addEventListener('click', () => removeWishlistItem(div));

        // Append remove button
        div.appendChild(removeBtn);

        // Add to container
        container.appendChild(div);
        
        index++;
    }

    // Initialize remove buttons for existing items
    container.querySelectorAll('.remove-wishlist').forEach(btn => {
        btn.addEventListener('click', function () {
            const itemDiv = btn.closest('.wishlist-item');
            if (itemDiv) removeWishlistItem(itemDiv);
        });
    });

    // Add new item on click
    addButton.addEventListener('click', createWishlistItem);
});
