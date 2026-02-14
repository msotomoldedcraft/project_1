document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('participants');
    const addButton = document.getElementById('add-participant');

    let index = container.children.length;

    addButton.addEventListener('click', function () {
        const prototype = container.dataset.prototype;
        const newForm = prototype.replace(/__name__/g, index);

        const div = document.createElement('div');
        div.classList.add('participant-item');
        div.innerHTML = newForm;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = 'Remove';
        removeBtn.className = 'remove-participant btn btn-danger btn-sm';
        removeBtn.addEventListener('click', () => div.remove());

        div.appendChild(removeBtn);
        container.appendChild(div);

        index++;
    });

    document.querySelectorAll('.remove-participant').forEach(btn => {
        btn.addEventListener('click', function () {
            btn.parentElement.remove();
        });
    });
});
