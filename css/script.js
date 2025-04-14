document.addEventListener("DOMContentLoaded", function () {
    // Search Functionality
    const searchBar = document.getElementById("searchBar");
    if (searchBar) {
        searchBar.addEventListener("input", function () {
            const query = searchBar.value.toLowerCase();
            const jobListings = document.querySelectorAll(".job-card");
            
            jobListings.forEach(job => {
                const title = job.querySelector("h3").textContent.toLowerCase();
                if (title.includes(query)) {
                    job.style.display = "block";
                } else {
                    job.style.display = "none";
                }
            });
        });
    }

    // Job Status Tracking (Mock Data)
    const trackButtons = document.querySelectorAll(".track-progress-btn");
    trackButtons.forEach(button => {
        button.addEventListener("click", function () {
            alert("Tracking job progress...");
            // Future enhancement: Fetch real-time job progress via AJAX
        });
    });

    // Payment Handling
    const payButtons = document.querySelectorAll(".pay-btn");
    payButtons.forEach(button => {
        button.addEventListener("click", function () {
            alert("Redirecting to secure payment gateway...");
            // Future enhancement: Integrate with a payment API
        });
    });

    // Notifications (Mock Example)
    function loadNotifications() {
        const notifications = [
            { message: "John Doe accepted your Plumbing Fix job.", date: "22nd Feb 2025" },
            { message: "Your Electrical Repair job is now completed.", date: "20th Feb 2025" }
        ];
        const notificationContainer = document.querySelector(".notification-list");

        if (notificationContainer) {
            notificationContainer.innerHTML = ""; // Clear previous notifications
            notifications.forEach(notification => {
                const div = document.createElement("div");
                div.classList.add("notification-card");
                div.innerHTML = `<p><strong>${notification.message}</strong></p>
                                 <p><strong>Date:</strong> ${notification.date}</p>`;
                notificationContainer.appendChild(div);
            });
        }
    }

    // Call loadNotifications if on notifications page
    if (document.querySelector(".notification-list")) {
        loadNotifications();
    }

    // Ratings & Reviews Form Validation
    const reviewForm = document.querySelector("form");
    if (reviewForm) {
        reviewForm.addEventListener("submit", function (e) {
            const rating = document.getElementById("rating").value;
            const reviewText = document.getElementById("review").value.trim();

            if (!rating || reviewText.length < 10) {
                e.preventDefault();
                alert("Please provide a rating and a detailed review (at least 10 characters).");
            }
        });
    }
});