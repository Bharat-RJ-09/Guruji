// assets/js/dashboard.js

// API Paths
const API_SECURE = "api/secure";
const API_LB = "api/quiz";
const API_AUTH = "api/auth";
const API_HIST = "api/quiz"; // Base path for history

// ✅ Run immediately when page loads
document.addEventListener("DOMContentLoaded", () => {
    
    // 1. DASHBOARD Page Checks
    if (document.getElementById("welcomeName")) {
        loadProfile();
        loadStats();
    }

    // 2. LEADERBOARD Page Checks
    if (document.getElementById("lb-body")) {
        loadLeaderboard();
    }

    // 3. HISTORY Page Checks (This was likely missing!)
    if (document.getElementById("history-body")) {
        loadHistory();
    }
});

// --- PROFILE LOGIC ---
async function loadProfile() {
    try {
        const res = await fetch(`${API_SECURE}/profile.php`);
        if (res.status === 401) { window.location.href = "login.html"; return; }
        const data = await res.json();
        if (data.status === "success") {
            const nameEl = document.getElementById("welcomeName");
            const userEl = document.getElementById("userDisplay");
            if(nameEl) nameEl.innerHTML = data.user.full_name;
            if(userEl) userEl.innerText = "@" + data.user.username;
        }
    } catch (error) { console.error("Profile Error:", error); }
}

// --- STATS LOGIC ---
async function loadStats() {
    try {
        const res = await fetch(`${API_SECURE}/stats.php`);
        const data = await res.json();
        if (data.status === "success") {
            const scoreEl = document.getElementById("stat-score");
            const playedEl = document.getElementById("stat-played");
            const rankEl = document.getElementById("stat-rank");
            if(scoreEl) scoreEl.innerText = data.stats.score || 0;
            if(playedEl) playedEl.innerText = data.stats.played || 0;
            if(rankEl) rankEl.innerText = "#" + (data.stats.rank || "--");
        }
    } catch (error) { console.error("Stats Error:", error); }
}

// --- LEADERBOARD LOGIC ---
async function loadLeaderboard() {
    const filterEl = document.getElementById("subjectFilter");
    const subject = filterEl ? filterEl.value : 'all';
    const tbody = document.getElementById("lb-body");
    if(!tbody) return;

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

                let timeDisplay = "N/A";
                if(user.total_time) {
                    const m = Math.floor(user.total_time / 60);
                    const s = user.total_time % 60;
                    timeDisplay = m > 0 ? `${m}m ${s}s` : `${s}s`;
                }

                tbody.innerHTML += `
                    <tr>
                        <td class="${rowClass}" style="font-weight:bold;">${rankIcon}</td>
                        <td style="text-transform: capitalize;">${user.full_name} <br><span style="font-size:0.7rem; color:#777;">@${user.username}</span></td>
                        <td style="color: #00ffe1; font-weight:bold;">${user.total_score}</td>
                        <td style="color: #ccc;">⏱ ${timeDisplay}</td>
                    </tr>`;
                rank++;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding: 20px;">No records found yet.</td></tr>`;
        }
    } catch (e) { tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:red;">Server Error ❌</td></tr>`; }
}

// --- HISTORY LOGIC ---
async function loadHistory() {
    const tbody = document.getElementById("history-body");
    try {
        const res = await fetch(`${API_HIST}/history.php`);
        const data = await res.json();

        if (data.status === "success" && data.data.length > 0) {
            tbody.innerHTML = "";
            
            data.data.forEach(row => {
                let timeDisplay = "-";
                if(row.time_taken) {
                    const m = Math.floor(row.time_taken / 60);
                    const s = row.time_taken % 60;
                    timeDisplay = m > 0 ? `${m}m ${s}s` : `${s}s`;
                }

                tbody.innerHTML += `
                    <tr>
                        <td style="color:#aaa; font-size:0.9rem;">${row.date_formatted}</td>
                        <td style="text-transform: capitalize; font-weight:bold;">${row.subject}</td>
                        <td><span style="background: rgba(0,255,225,0.1); color:#00ffe1; padding:5px 10px; border-radius:10px;">${row.score} / ${row.total_questions}</span></td>
                        <td style="color:#ccc;">⏱ ${timeDisplay}</td>
                    </tr>`;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:20px;">No games played yet.</td></tr>`;
        }
    } catch (e) {
        console.error(e);
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:red;">Server Error</td></tr>`;
    }
}

// --- UTILS ---
async function logout() {
    if (confirm("Logout?")) { await fetch(`${API_AUTH}/logout.php`); window.location.href = "login.html"; }
}
function startQuiz(subject) { window.location.href = `quiz.html?sub=${subject}`; }