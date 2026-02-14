document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.add-item').forEach(button => {
        button.addEventListener('click', function () {
            const participant = button.dataset.target;
            const container = document.querySelector(
                `.wishlist-block[data-name="${participant}"]`
            );

            const prototype = container.dataset.prototype;
            const index = container.children.length;

            const newForm = prototype.replace(/__name__/g, index);

            const div = document.createElement('div');
            div.classList.add('wishlist-item');
            div.innerHTML = newForm;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.textContent = 'Remove';
            removeBtn.className = 'remove-item btn btn-danger btn-sm';
            removeBtn.onclick = () => div.remove();

            div.appendChild(removeBtn);
            container.appendChild(div);
        });
    });

    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', () => btn.parentElement.remove());
    });

});
