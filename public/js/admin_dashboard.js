document.addEventListener("DOMContentLoaded", function () {

    // =========================
    // REMOVE GROUP CONFIRMATION
    // =========================
    const removeButtons = document.querySelectorAll(".btn-remove-group");

    removeButtons.forEach(button => {
        button.addEventListener("click", function (e) {
            e.preventDefault(); 

            const groupId = this.dataset.groupId;
            const row = button.closest("tr");

            // Create confirmation overlay
            const overlay = document.createElement("div");
            overlay.className = "confirmation-overlay";
            overlay.innerHTML = `
                <div class="confirmation-box">
                    <p>Are you sure you want to remove this group?</p>
                    <div class="confirmation-actions">
                        <button class="btn btn-confirm-yes">Yes</button>
                        <button class="btn btn-confirm-no">No</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            // YES button
            overlay.querySelector(".btn-confirm-yes").addEventListener("click", () => {
                if (row) {
                    // Add fade animation class
                    row.classList.add("fade-out");

                    // Remove overlay immediately so fade is visible
                    document.body.removeChild(overlay);

                    // Wait for fade to finish, then redirect
                    setTimeout(() => {
                        window.location.href = `/exchange/admin/group/${groupId}/delete`;
                    }, 400); // matches CSS transition
                } else {
                    window.location.href = `/exchange/admin/group/${groupId}/delete`;
                }
            });

            // NO button
            overlay.querySelector(".btn-confirm-no").addEventListener("click", () => {
                document.body.removeChild(overlay);
            });
        });
    });

    // =========================
    // LOGOUT CONFIRMATION
    // =========================
    const logoutButton = document.querySelector(".btn-logout-confirm");

    if (logoutButton) {
        logoutButton.addEventListener("click", function(e) {
            e.preventDefault(); // prevent immediate logout

            // Create confirmation overlay
            const overlay = document.createElement("div");
            overlay.className = "confirmation-overlay";
            overlay.innerHTML = `
                <div class="confirmation-box">
                    <p>Are you sure you want to logout?</p>
                    <div class="confirmation-actions">
                        <button class="btn btn-confirm-yes">Yes</button>
                        <button class="btn btn-confirm-no">No</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            // YES button
            overlay.querySelector(".btn-confirm-yes").addEventListener("click", () => {
                document.body.removeChild(overlay);
                // Optionally: fade out admin container before logout
                const container = document.querySelector(".admin-container");
                if (container) {
                    container.style.transition = "opacity 0.4s ease";
                    container.style.opacity = 0;
                    setTimeout(() => {
                        window.location.href = logoutButton.href;
                    }, 400);
                } else {
                    window.location.href = logoutButton.href;
                }
            });

            // NO button
            overlay.querySelector(".btn-confirm-no").addEventListener("click", () => {
                document.body.removeChild(overlay);
            });
        });
    }

});