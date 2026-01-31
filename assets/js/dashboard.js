const API_SECURE = "../api/secure";
const API_AUTH = "../api/auth";

window.onload = async () => {
    // 🔍 Check karo: Kya hum Localhost par hain?
    const isLocalhost = window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1";

    try {
        const res = await fetch(`${API_SECURE}/profile.php`);
        
        // Agar Server bole "Login nahi hai" (401 Error)
        if (res.status === 401) {
            
            // ✅ BYPASS: Agar Localhost hai, to Login page par mat bhejo!
            if (isLocalhost) {
                console.warn("⚠️ DEV MODE: Bypassing Login Check for Localhost");
                loadDummyData(); // Fake data load karo
                return; 
            }

            // Agar Live Server hai, to Login page par bhejo
            window.location.href = "login.html";
            return;
        }

        const data = await res.json();

        if (data.status === "success") {
            // Asli Data Load karo
            updateUI(data.user.full_name, data.user.username);
        } else {
            if (isLocalhost) { loadDummyData(); return; }
            window.location.href = "login.html";
        }

    } catch (error) {
        console.error("Dashboard Error:", error);
        // Agar PHP server band hai, tab bhi localhost pe dashboard dikhao
        if (isLocalhost) { 
            console.warn("⚠️ Server Error: Loading Dummy Data");
            loadDummyData(); 
        } else {
            window.location.href = "login.html";
        }
    }
};

// --- Helper Functions ---

// 1. Asli Data UI Function
function updateUI(fullName, username) {
    document.getElementById("welcomeName").innerText = fullName;
    document.getElementById("userDisplay").innerText = username;
    document.getElementById("loader").style.display = "none";
}

// 2. Fake Data Function (Sirf Design ke liye)
function loadDummyData() {
    updateUI("Developer Bhai", "dev_admin");
    
    // Toast notification dikhao ki ye Dev Mode hai
    const msg = document.createElement("div");
    msg.innerText = "🛠️ Developer Mode: Login Bypassed";
    msg.style.cssText = "position:fixed; bottom:20px; right:20px; background:#ff9800; color:#000; padding:10px 20px; border-radius:5px; font-weight:bold; z-index:3000;";
    document.body.appendChild(msg);
}

// Logout Function
async function logout() {
    if(confirm("Are you sure you want to logout?")) {
        try {
            await fetch(`${API_AUTH}/logout.php`);
            localStorage.clear();
            window.location.href = "login.html";
        } catch (error) {
            alert("Logout failed");
        }
    }
}