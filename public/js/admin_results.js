document.addEventListener("DOMContentLoaded", function () {

    const rows = document.querySelectorAll(".results-table tbody tr");

    rows.forEach((row, index) => {
        row.style.opacity = 0;
        row.style.transform = "translateY(15px)";
        row.style.transition = "all 0.4s ease";
        
        setTimeout(() => {
            row.style.opacity = 1;
            row.style.transform = "translateY(0)";
        }, 150 * index);
    });

});