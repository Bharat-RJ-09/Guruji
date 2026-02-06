// assets/js/dashboard.js

// API Paths
const API_SECURE = "api/secure";
const API_LB = "api/quiz";
const API_AUTH = "api/auth";
const API_HIST = "api/quiz";

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
        // We still load profile to get the theme/nav bar info if needed
        loadProfile();
    }

    // 3. HISTORY Page Checks
    if (document.getElementById("history-body")) {
        loadHistory();
        loadProfile();
    }

    // 4. ✨ CHECK FOR UPGRADE CELEBRATION (Runs on Dashboard only)
    const urlParams = new URLSearchParams(window.location.search);
    const newPlan = urlParams.get('upgrade_success');
    if (newPlan) {
        showCelebration(newPlan);
    }
});

// --- PROFILE LOGIC (Updated with Themes & Plans) ---
async function loadProfile() {
    try {
        const res = await fetch(`${API_SECURE}/profile.php`);
        if (res.status === 401) { window.location.href = "login.html"; return; }

        const data = await res.json();

        if (data.status === "success") {
            // A. Update Names
            const nameEl = document.getElementById("welcomeName");
            const userEl = document.getElementById("userDisplay");

            if (nameEl) nameEl.innerText = data.user.full_name;
            if (userEl) userEl.innerText = "@" + data.user.username;

            // B. ✨ APPLY THEME (Visual Tier)
            // We pass the plan to the theme engine
            if (data.user.subscription_plan) {
                applyTheme(data.user.subscription_plan);

                // C. ✨ UPDATE PLAN CARD (Only if on Dashboard)
                if (document.getElementById("stat-plan")) {
                    updatePlanDisplay(data.user.subscription_plan);
                }
            }
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

            if (scoreEl) scoreEl.innerText = data.stats.score || 0;
            if (playedEl) playedEl.innerText = data.stats.played || 0;
            if (rankEl) rankEl.innerText = "#" + (data.stats.rank || "--");
        }
    } catch (error) { console.error("Stats Error:", error); }
}

// --- LEADERBOARD LOGIC ---

// assets/js/dashboard.js -> Find loadLeaderboard() and Replace it with this:

async function loadLeaderboard() {
    const filterEl = document.getElementById("subjectFilter");
    const subject = filterEl ? filterEl.value : 'all';
    const tbody = document.getElementById("lb-body");
    const podium = document.getElementById("podium-view");
    
    if(!tbody) return;

    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Fetching Data... 🚀</td></tr>`;
    if(podium) podium.style.display = "none"; // Hide while loading

    try {
        const res = await fetch(`${API_LB}/leaderboard.php?subject=${subject}`);
        const data = await res.json();
        
        if (data.status === "success" && data.data.length > 0) {
            tbody.innerHTML = "";
            let rank = 1;

            // ✨ 1. UPDATE PODIUM (If we have top 3)
            if (data.data.length >= 3 && podium) {
                podium.style.display = "flex";

                // Rank 1
                document.getElementById("p1-name").innerText = data.data[0].full_name;
                document.getElementById("p1-score").innerText = data.data[0].total_score;
                
                // Rank 2
                document.getElementById("p2-name").innerText = data.data[1].full_name;
                document.getElementById("p2-score").innerText = data.data[1].total_score;

                // Rank 3
                document.getElementById("p3-name").innerText = data.data[2].full_name;
                document.getElementById("p3-score").innerText = data.data[2].total_score;
            } else if(podium) {
                podium.style.display = "none"; // Hide if less than 3 players
            }

            // ✨ 2. POPULATE TABLE
            data.data.forEach(user => {
                let rankIcon = `#${rank}`;
                let rowClass = "";
                
                // Special Icons for Top 3 in Table too
                if (rank === 1) { rankIcon = "👑"; rowClass = "rank-1"; }
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
            if(podium) podium.style.display = "none";
        }
    } catch (e) { 
        console.error(e);
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:red;">Server Error ❌</td></tr>`; 
    }
}

// --- HISTORY LOGIC ---
// assets/js/dashboard.js -> Replace loadHistory()

// --- HISTORY LOGIC (With Chart) ---
async function loadHistory() {
    const tbody = document.getElementById("history-body");
    try {
        const res = await fetch(`${API_HIST}/history.php`);
        const data = await res.json();

        if (data.status === "success" && data.data.length > 0) {
            tbody.innerHTML = "";
            
            // Arrays for Chart
            const chartLabels = [];
            const chartScores = [];
            
            // Process Data (Reverse to show oldest first in chart, if API sends newest first)
            // But we want Table to show Newest first.
            // So we clone data for chart.
            const chartData = [...data.data].reverse().slice(-10); // Last 10 games

            chartData.forEach(game => {
                chartLabels.push(game.subject); // Or date
                // Calculate Percentage
                let pct = Math.round((game.score / game.total_questions) * 100);
                chartScores.push(pct);
            });

            // 1. Render Table
            data.data.forEach(row => {
                let timeDisplay = "-";
                if(row.time_taken) {
                    const m = Math.floor(row.time_taken / 60);
                    const s = row.time_taken % 60;
                    timeDisplay = m > 0 ? `${m}m ${s}s` : `${s}s`;
                }

                // Add Color Badge for Score
                let scoreColor = "#ff4444"; // Bad
                let pct = (row.score / row.total_questions) * 100;
                if(pct >= 80) scoreColor = "#00ff88"; // Good
                else if(pct >= 50) scoreColor = "#ffd700"; // Average

                tbody.innerHTML += `
                    <tr>
                        <td style="color:#aaa; font-size:0.9rem;">${row.date_formatted}</td>
                        <td style="text-transform: capitalize; font-weight:bold;">
                            ${row.subject}
                        </td>
                        <td>
                            <span style="background: ${scoreColor}20; color:${scoreColor}; padding:5px 12px; border-radius:15px; font-weight:bold; font-size:0.85rem; border:1px solid ${scoreColor}40;">
                                ${row.score} / ${row.total_questions}
                            </span>
                        </td>
                        <td style="color:#ccc;">⏱ ${timeDisplay}</td>
                    </tr>`;
            });

            // 2. Render Chart (If canvas exists)
            renderChart(chartLabels, chartScores);

        } else {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:20px;">No games played yet. Play a quiz!</td></tr>`;
            // Hide Chart if no data
            if(document.querySelector('.chart-container')) {
                document.querySelector('.chart-container').style.display = 'none';
            }
        }
    } catch (e) {
        console.error(e);
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:red;">Server Error</td></tr>`;
    }
}

// ✨ Helper to Render Chart.js
let myChart = null; // Store instance to destroy later if needed

function renderChart(labels, dataPoints) {
    const ctx = document.getElementById('historyChart');
    if (!ctx) return;

    // Prevent duplicate charts
    if (myChart) myChart.destroy();

    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Score Percentage (%)',
                data: dataPoints,
                borderColor: '#00f0ff',
                backgroundColor: 'rgba(0, 240, 255, 0.1)',
                borderWidth: 2,
                tension: 0.4, // Smooth curves
                pointBackgroundColor: '#fff',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    ticks: { color: '#8b949e' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#8b949e', textTransform: 'capitalize' }
                }
            },
            plugins: {
                legend: { display: false } // Hide legend for cleaner look
            }
        }
    });
}

// =========================================
// ✨ VISUAL TIER SYSTEM (MERGED FEATURES)
// =========================================

// 1. APPLY THEME (Safely)
function applyTheme(plan) {
    const body = document.body;
    const bgContainer = document.getElementById("theme-bg"); // Might be null on some pages

    // Clear old classes
    body.classList.remove("theme-standard", "theme-prime");

    // Only touch HTML if the container exists (Prevents crashing on other pages)
    if (bgContainer) bgContainer.innerHTML = "";

    if (plan === 'standard') {
        body.classList.add("theme-standard");
    }
    else if (plan === 'prime') {
        body.classList.add("theme-prime");

        // 👑 PRIME: Lottie Animation
        if (bgContainer) {
            bgContainer.innerHTML = `
                <lottie-player 
                    src="https://assets2.lottiefiles.com/packages/lf20_w51pcehl.json"  
                    background="transparent"  
                    speed="0.5"  
                    style="width: 100%; height: 100%; opacity: 0.3;"  
                    loop  
                    autoplay>
                </lottie-player>
            `;
        }
    }
}

// 2. UPDATE PLAN DISPLAY TEXT (Safely)
function updatePlanDisplay(plan) {
    const el = document.getElementById("stat-plan");
    const card = document.getElementById("plan-card");

    if (!el || !card) return; // Exit if elements don't exist

    if (plan === 'prime') {
        el.innerHTML = "PRIME <span style='font-size:1.5rem'>👑</span>";
        el.style.color = "#ffd700";
        card.style.borderColor = "#ffd700";
    } else if (plan === 'standard') {
        el.innerHTML = "STANDARD <span style='font-size:1.5rem'>⚡</span>";
        el.style.color = "#00f0ff";
        card.style.borderColor = "#00f0ff";
    } else {
        el.innerHTML = "FREE";
        el.style.color = "#a0aec0";
    }
}

// 3. SHOW CELEBRATION MODAL
function showCelebration(plan) {
    const modal = document.getElementById("celebration-modal");
    const title = document.getElementById("cel-title");
    const msg = document.getElementById("cel-msg");

    if (!modal) return; // Safety check

    if (plan === 'prime') {
        title.innerText = "Welcome, King! 👑";
        title.style.color = "#ffd700";
        msg.innerText = "Prime features are now UNLOCKED.";
    } else {
        title.innerText = "Level Up! ⚡";
        title.style.color = "#00f0ff";
        msg.innerText = "You are now a Standard Member.";
    }

    modal.classList.add("active");
}

// --- UTILS ---
async function logout() {
    if (confirm("Logout?")) { await fetch(`${API_AUTH}/logout.php`); window.location.href = "login.html"; }
}
function startQuiz(subject) { window.location.href = `quiz.html?sub=${subject}`; }



// Append this to the bottom of assets/js/dashboard.js

// --- GRAMMAR CHECKER LOGIC ---
let currentGrammarLang = "English";

function openGrammarModal() {
    document.getElementById("grammarModal").classList.add("active");
}

function closeGrammarModal() {
    document.getElementById("grammarModal").classList.remove("active");
    document.getElementById("grammarInput").value = "";
    document.getElementById("grammarResult").style.display = "none";
}

function setGrammarLang(lang) {
    currentGrammarLang = lang;

    // Toggle UI
    const btnEn = document.getElementById("btn-lang-en");
    const btnHi = document.getElementById("btn-lang-hi");

    if (lang === 'English') {
        btnEn.style.borderColor = "#00f0ff"; btnEn.style.background = "rgba(0,240,255,0.1)";
        btnHi.style.borderColor = "rgba(255,255,255,0.1)"; btnHi.style.background = "rgba(255,255,255,0.05)";
    } else {
        btnHi.style.borderColor = "#00f0ff"; btnHi.style.background = "rgba(0,240,255,0.1)";
        btnEn.style.borderColor = "rgba(255,255,255,0.1)"; btnEn.style.background = "rgba(255,255,255,0.05)";
    }
}

async function checkGrammar() {
    const text = document.getElementById("grammarInput").value.trim();
    if (!text) return alert("Please enter some text!");

    const btn = document.getElementById("btn-check-grammar");
    const outputBox = document.getElementById("grammarResult");
    const outputText = document.getElementById("grammarOutput");

    // Loading State
    btn.disabled = true;
    btn.innerText = "Fixing...";

    try {
        const res = await fetch("api/ai/grammar.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ text: text, lang: currentGrammarLang })
        });

        const data = await res.json();

        if (data.status === "success") {
            outputText.innerText = data.corrected;
            outputBox.style.display = "block";
        } else {
            alert(data.message);
        }

    } catch (e) {
        console.error(e);
        alert("Server Error");
    } finally {
        btn.disabled = false;
        btn.innerText = "Fix Grammar ✨";
    }
}


// This is my code 
 