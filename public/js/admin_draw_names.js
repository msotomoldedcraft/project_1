document.addEventListener('DOMContentLoaded', function () {

    const giverSelect = document.getElementById('giver-select');
    const drawBtn = document.getElementById('draw-btn');
    const canvas = document.getElementById('wheel');
    const groupIdEl = document.getElementById('group-id');
    const resultDiv = document.getElementById('result');

    if (!giverSelect || !drawBtn || !canvas || !groupIdEl) {
        console.error("Missing required elements.");
        return;
    }

    const ctx = canvas.getContext('2d');
    const center = canvas.width / 2;
    const radius = center - 10;
    const groupId = groupIdEl.value;

    const users = (typeof allUsers !== 'undefined') ? allUsers : [];

    let wheelUsers = [];
    let spinning = false;
    let assignedReceivers = {};
    let currentRotation = 0;

    function drawWheel(rotation = 0) {

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (!wheelUsers.length) {
            ctx.font = '20px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('No participants', center, center);
            return;
        }

        const sliceAngle = (2 * Math.PI) / wheelUsers.length;
        const colors = ['#6366f1','#4f8ef3','#4ed8aa','#e9637a','#845be2','#ebcd58'];

        for (let i = 0; i < wheelUsers.length; i++) {

            const start = i * sliceAngle + rotation - Math.PI / 2;
            const end = start + sliceAngle;

            ctx.fillStyle = colors[i % colors.length];

            ctx.beginPath();
            ctx.moveTo(center, center);
            ctx.arc(center, center, radius, start, end);
            ctx.closePath();
            ctx.fill();

            ctx.save();
            ctx.translate(center, center);
            ctx.rotate(start + sliceAngle / 2);
            ctx.fillStyle = '#000';
            ctx.textAlign = 'right';
            ctx.fillText(wheelUsers[i].name, radius - 10, 5);
            ctx.restore();
        }
    }

    function updateWheelUsers() {

        const giverId = parseInt(giverSelect.value);

        if (!giverId) {
            wheelUsers = [];
            drawBtn.disabled = true;
            drawWheel();
            return;
        }

        wheelUsers = users.filter(u =>
            u.id !== giverId && !assignedReceivers[u.id]
        );

        drawBtn.disabled = wheelUsers.length === 0;
        drawWheel(currentRotation);
    }

    function assignReceiver(receiverId, callback) {

        const giverId = parseInt(giverSelect.value);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", `/exchange/admin/group/${groupId}/draw-assign`);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) callback();
                    else alert(data.error || "Error occurred.");
                } catch (e) {
                    alert("Invalid server response.");
                }
            }
        };

        xhr.send(`giverId=${giverId}&receiverId=${receiverId}`);
    }

    drawBtn.addEventListener('click', function () {

        if (!giverSelect.value) {
            alert("Please select a giver.");
            return;
        }

        if (!wheelUsers.length) {
            alert("No participants available.");
            return;
        }

        if (spinning) return;

        spinning = true;
        drawBtn.disabled = true;

        const sliceAngle = (2 * Math.PI) / wheelUsers.length;
        const totalSpins = 6;
        const randomSpin = Math.random() * 2 * Math.PI;
        const targetRotation = (totalSpins * 2 * Math.PI) + randomSpin;

        const duration = 4000;
        const startTime = performance.now();

        function animate(time) {

            const elapsed = time - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 3);

            currentRotation = easeOut * targetRotation;
            drawWheel(currentRotation);

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {

                //  FINAL CORRECT CALCULATION
                const adjustedRotation = currentRotation % (2 * Math.PI);

                const sliceAngle = (2 * Math.PI) / wheelUsers.length;

                // Wheel is offset by -Math.PI/2 in drawing
                const wheelAngle = (adjustedRotation + Math.PI / 2) % (2 * Math.PI);

                // Pointer is at bottom (3π/2)
                const pointerAngle = 3 * Math.PI / 2;

                let relativeAngle =
                    (pointerAngle - wheelAngle + 2 * Math.PI) % (2 * Math.PI);

                const index = Math.floor(relativeAngle / sliceAngle);

                const receiver = wheelUsers[index];

                assignReceiver(receiver.id, function () {

                    if (resultDiv) {
                        resultDiv.innerHTML =
                            `Receiver: <strong>${receiver.name}</strong>`;
                    }

                    assignedReceivers[receiver.id] = true;

                    updateWheelUsers();
                    spinning = false;
                    drawBtn.disabled = wheelUsers.length === 0;
                });
            }
        }

        requestAnimationFrame(animate);
    });

    giverSelect.addEventListener('change', updateWheelUsers);

    updateWheelUsers();
    drawWheel();
});