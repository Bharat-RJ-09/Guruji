// assets/js/dashboard.js

// ✅ CORRECT PATHS (Relative to the HTML file)
const API_SECURE = "api/secure";
const API_LB = "api/quiz";
const API_AUTH = "api/auth";

// --- 1. DASHBOARD LOAD ---
// Only run this if we are on the Dashboard Page
if (window.location.pathname.includes("dashboard.html")) {
    window.onload = async () => {
        const isLocalhost = window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1";

        try {
            const res = await fetch(`${API_SECURE}/profile.php`);

            if (res.status === 401) {
                window.location.href = "login.html";
                return;
            }

            const data = await res.json();

            if (data.status === "success") {
                updateUI(data.user.full_name, data.user.username);
                loadStats(); // Load scores
            } else {
                window.location.href = "login.html";
            }

        } catch (error) {
            console.error("Dashboard Error:", error);
        }
    };
}

// --- 2. LEADERBOARD LOAD ---
// Only run this if we are on the Leaderboard Page
if (window.location.pathname.includes("leaderboard.html")) {
    window.onload = () => {
        loadLeaderboard();
    };
}

function updateUI(fullName, username) {
    const nameEl = document.getElementById("welcomeName");
    const userEl = document.getElementById("userDisplay");
    
    if(nameEl) nameEl.innerHTML = fullName;
    if(userEl) userEl.innerText = "@" + username;
}

// --- STATS LOGIC ---
async function loadStats() {
    try {
        const res = await fetch(`${API_SECURE}/stats.php`);
        const data = await res.json();

        if (data.status === "success") {
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

// --- LEADERBOARD LOGIC ---
async function loadLeaderboard() {
    const filterEl = document.getElementById("subjectFilter");
    const subject = filterEl ? filterEl.value : 'all';
    const tbody = document.getElementById("lb-body");

    if(!tbody) return; // Exit if not on leaderboard page

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
                        <td style="text-transform: capitalize;">
                            ${user.full_name} 
                            <br><span style="font-size:0.7rem; color:#777;">@${user.username}</span>
                        </td>
                        <td style="color: #00ffe1; font-weight:bold;">${user.total_score}</td>
                        <td>${getBadge(user.total_score)}</td>
                    </tr>
                `;
                tbody.innerHTML += html;
                rank++;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding: 20px;">No records found yet. <br> <a href="dashboard.html" style="color:#00ffe1;">Play a Quiz First! 🎮</a></td></tr>`;
        }
    } catch (e) {
        console.error(e);
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:red;">Server Error ❌</td></tr>`;
    }
}

function getBadge(score) {
    if (score > 100) return "🔥 Legend";
    if (score > 50) return "🌟 Expert";
    if (score > 20) return "🚀 Pro";
    return "👶 Rookie";
}

async function logout() {
    if (confirm("Are you sure you want to logout?")) {
        await fetch(`${API_AUTH}/logout.php`);
        window.location.href = "login.html";
    }
}

// Quiz Start Helper
function startQuiz(subject) {
    window.location.href = `quiz.html?sub=${subject}`;
}