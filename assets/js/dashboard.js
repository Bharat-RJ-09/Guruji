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
// Function update karo
function updateUI(fullName, username, plan) { // 'plan' parameter add kiya
    let displayName = fullName;

    // Agar Pro hai to Star lagao
    if (plan === 'pro') {
        displayName += " <span style='color:#FFD700; font-size:0.8rem; border:1px solid #FFD700; padding:2px 8px; border-radius:10px; margin-left:5px;'>PRO</span>";
    }

    document.getElementById("welcomeName").innerHTML = displayName; // innerText ki jagah innerHTML
    document.getElementById("userDisplay").innerText = username;
    document.getElementById("loader").style.display = "none";
}

// Jahan se ye call ho raha hai (window.onload mein), wahan bhi data pass karo:
// updateUI(data.user.full_name, data.user.username, data.user.subscription_plan);

// 2. Fake Data Function (Sirf Design ke liye)
function loadDummyData() {
    updateUI("Developer Bhai", "dev_admin");

    // Toast notification dikhao ki ye Dev Mode hai
    const msg = document.createElement("div");
    msg.innerText = "🛠️ Developer Mode: Login Bypassed";
    msg.style.cssText = "position:fixed; bottom:20px; right:20px; background:#ff9800; color:#000; padding:10px 20px; border-radius:5px; font-weight:bold; z-index:3000;";
    document.body.appendChild(msg);
}

const API_LB = "../api/quiz";

window.onload = () => {
    loadLeaderboard();
};

async function loadLeaderboard() {
    const subject = document.getElementById("subjectFilter").value;
    const tbody = document.getElementById("lb-body");

    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Fetching Data... 🚀</td></tr>`;

    try {
        const res = await fetch(`${API_LB}/leaderboard.php?subject=${subject}`);
        const data = await res.json();

        if (data.status === "success" && data.data.length > 0) {
            tbody.innerHTML = "";
            let rank = 1;

            data.data.forEach(user => {
                let rankIcon = `#${rank}`;
                let rowClass = "";

                if (rank === 1) { rankIcon = "🥇"; rowClass = "rank-1"; }
                else if (rank === 2) { rankIcon = "🥈"; rowClass = "rank-2"; }
                else if (rank === 3) { rankIcon = "🥉"; rowClass = "rank-3"; }

                const html = `
                    <tr>
                        <td class="${rowClass}" style="font-weight:bold;">${rankIcon}</td>
                        <td style="text-transform: capitalize;">${user.full_name} <span style="font-size:0.8rem; color:#777;">(@${user.username})</span></td>
                        <td style="color: #00ffe1; font-weight:bold;">${user.total_score}</td>
                        <td>${getBadge(user.total_score)}</td>
                    </tr>
                `;
                tbody.innerHTML += html;
                rank++;
            });

            if (data.status === "success") {
                updateUI(data.user.full_name, data.user.username, data.user.subscription_plan);

                // 👇 YE NAYI LINE JODO: Stats bhi load karo
                loadStats();
                // --- LOAD REAL STATS ---
async function loadStats() {
    try {
        const res = await fetch('../api/secure/stats.php');
        const data = await res.json();

        if (data.status === "success") {
            // HTML me IDs dhoondh kar update karo
            // Note: HTML me IDs add karni padengi (Step 3 dekho)
            if(document.getElementById("stat-score")) 
                document.getElementById("stat-score").innerText = data.stats.score;
            
            if(document.getElementById("stat-played")) 
                document.getElementById("stat-played").innerText = data.stats.played;
            
            if(document.getElementById("stat-rank")) 
                document.getElementById("stat-rank").innerText = "#" + data.stats.rank;
        }
    } catch (error) {
        console.error("Stats Error:", error);
    }
}
            }
        } else {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No records found yet. Play a quiz! 🎮</td></tr>`;
        }
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:red;">Server Error ❌</td></tr>`;
    }
}

function getBadge(score) {
    if (score > 50) return "🔥 Expert";
    if (score > 20) return "🌟 Pro";
    return "👶 Rookie";
}

// Logout Function
async function logout() {
    if (confirm("Are you sure you want to logout?")) {
        try {
            await fetch(`${API_AUTH}/logout.php`);
            localStorage.clear();
            window.location.href = "login.html";
        } catch (error) {
            alert("Logout failed");
        }
    }
}

// --- QUIZ START LOGIC ---
function startQuiz(subject) {
    console.log("Starting Quiz for:", subject); // Debugging ke liye

    // User ko Quiz page par bhejo with Subject Parameter
    // Example: quiz.html?sub=gk
    window.location.href = `quiz.html?sub=${subject}`;
}

 