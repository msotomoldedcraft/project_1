document.addEventListener('DOMContentLoaded', () => {

    const container = document.getElementById('wishlist-items');
    const addButton = document.getElementById('add-wishlist-item');

    if (!container || !addButton) return;

    //  Use Symfony-controlled index
    let index = parseInt(container.dataset.index);

    function removeWishlistItem(itemDiv) {
        itemDiv.style.transition = 'opacity 0.3s';
        itemDiv.style.opacity = '0';
        setTimeout(() => itemDiv.remove(), 300);
    }

    function createWishlistItem() {

        const prototype = container.dataset.prototype;

        // Replace __name__ with current index
        const newForm = prototype.replace(/__name__/g, index);

        const wrapper = document.createElement('div');
        wrapper.classList.add('grid-x', 'align-middle', 'wishlist-item');

        const inputCell = document.createElement('div');
        inputCell.classList.add('cell', 'auto');
        inputCell.innerHTML = newForm;

        const buttonCell = document.createElement('div');
        buttonCell.classList.add('cell', 'shrink');

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'button alert small';
        removeBtn.textContent = 'Remove';

        removeBtn.addEventListener('click', () => {
            removeWishlistItem(wrapper);
        });

        buttonCell.appendChild(removeBtn);
        wrapper.appendChild(inputCell);
        wrapper.appendChild(buttonCell);

        container.appendChild(wrapper);

        //  Update index properly
        index++;
        container.dataset.index = index;
    }

    // Attach remove buttons for existing items
    container.querySelectorAll('.remove-wishlist').forEach(btn => {
        btn.addEventListener('click', function () {
            const itemDiv = btn.closest('.wishlist-item');
            if (itemDiv) removeWishlistItem(itemDiv);
        });
    });

    // Add new item
    addButton.addEventListener('click', createWishlistItem);

});