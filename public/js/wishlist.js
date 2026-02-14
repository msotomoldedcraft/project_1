document.addEventListener('DOMContentLoaded', function () {

    const container = document.getElementById('wishlist-items');
    const addButton = document.getElementById('add-wishlist-item');

    // Safety check
    if (!container || !addButton) return;

    let index = container.querySelectorAll('.wishlist-item').length;

    // ADD ITEM
    addButton.addEventListener('click', function () {
        const prototype = container.dataset.prototype;
        const newForm = prototype.replace(/__name__/g, index);

        const div = document.createElement('div');
        div.classList.add('wishlist-item');
        div.innerHTML = newForm;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = 'Remove';
        removeBtn.className = 'remove-wishlist btn btn-danger btn-sm';

        div.appendChild(removeBtn);
        container.appendChild(div);

        index++;
    });

    // REMOVE ITEM (Event Delegation - works for all)
    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-wishlist')) {
            e.preventDefault();
            e.target.closest('.wishlist-item').remove();
        }
    });

});
