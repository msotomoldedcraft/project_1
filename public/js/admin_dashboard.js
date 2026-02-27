document.addEventListener("DOMContentLoaded", function () {

    /* =========================
       REMOVE GROUP
    ========================== */
    document.querySelectorAll(".btn-remove-group").forEach(button => {
        button.addEventListener("click", function (e) {
            e.preventDefault();

            const groupId = this.dataset.groupId;
            const row = this.closest("tr");

            if (!confirm("Are you sure you want to remove this group?")) {
                return;
            }

            if (row) {
                row.classList.add("fade-out");

                setTimeout(() => {
                    window.location.href = `/exchange/admin/group/${groupId}/delete`;
                }, 400);
            } else {
                window.location.href = `/exchange/admin/group/${groupId}/delete`;
            }
        });
    });

    /* =========================
       LOGOUT CONFIRMATION
    ========================== */
    const logoutButton = document.querySelector(".btn-logout-confirm");

    if (logoutButton) {
        logoutButton.addEventListener("click", function (e) {
            e.preventDefault();

            if (confirm("Are you sure you want to logout?")) {
                window.location.href = this.href;
            }
        });
    }

});