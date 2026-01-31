const API_SECURE = "../api/secure";
const API_AUTH = "../api/auth";

// Page Load hone par turant check karo
window.onload = async () => {
    try {
        const res = await fetch(`${API_SECURE}/profile.php`);
        
        // Agar status 401 (Unauthorized) hai to login pe bhejo
        if (res.status === 401) {
            window.location.href = "login.html";
            return;
        }

        const data = await res.json();

        if (data.status === "success") {
            // UI Update karo
            document.getElementById("welcomeName").innerText = data.user.full_name;
            document.getElementById("userDisplay").innerText = data.user.username;
            
            // Loader hatao
            document.getElementById("loader").style.display = "none";
        } else {
            // Agar koi aur error hai
            alert("Session Error. Please login again.");
            window.location.href = "login.html";
        }

    } catch (error) {
        console.error("Dashboard Error:", error);
        window.location.href = "login.html";
    }
};

// Logout Function
async function logout() {
    if(confirm("Are you sure you want to logout?")) {
        try {
            await fetch(`${API_AUTH}/logout.php`);
            localStorage.clear(); // Frontend cleanup
            window.location.href = "login.html";
        } catch (error) {
            alert("Logout failed");
        }
    }
}