// assets/js/profile.js

const API_SECURE_PF = "api/secure"; 

window.onload = async () => {
    try {
        const res = await fetch(`${API_SECURE_PF}/profile.php`);
        
        // If not logged in, redirect to login page
        if (res.status === 401) {
            window.location.href = "login.html";
            return;
        }

        const data = await res.json();
        
        if(data.status === "success") {
            const u = data.user;
            const plan = u.subscription_plan || 'free';

            // ------------------------------------------------
            // 1. SMART SUBSCRIPTION DISPLAY LOGIC
            // ------------------------------------------------
            const ui = {
                badge: document.getElementById("plan_badge"),
                name: document.getElementById("plan_name"),
                limit: document.getElementById("plan_limit"),
                btn: document.getElementById("btn_upgrade"),
                ai_feat: document.getElementById("feat_ai")
            };

            // Define Plan Configs
            const planConfig = {
                'free': {
                    label: "FREE PLAN",
                    color: "#999",
                    title: "Free Tier",
                    limitText: "Limit: 3 Quizzes / Day",
                    hasAI: false
                },
                'standard': {
                    label: "STANDARD PLAN",
                    color: "#00f0ff",
                    title: "Standard Tier",
                    limitText: "Limit: 10 Quizzes / Day",
                    hasAI: false
                },
                'prime': {
                    label: "PRIME MEMBER",
                    color: "#bd00ff", // Purple for Prime
                    title: "Prime Access 💎",
                    limitText: "⚡ Unlimited Quizzes & AI",
                    hasAI: true
                }
            };

            const config = planConfig[plan] || planConfig['free'];

            // Apply to UI
            if(ui.badge) {
                ui.badge.innerText = config.label;
                ui.badge.style.color = config.color;
                ui.badge.style.borderColor = config.color;
                ui.badge.style.background = `rgba(255,255,255,0.05)`;
            }

            if(ui.name) {
                ui.name.innerText = config.title;
                if(plan === 'prime') {
                    ui.name.style.background = "linear-gradient(to right, #bd00ff, #00f0ff)";
                    ui.name.style.webkitBackgroundClip = "text";
                    ui.name.style.webkitTextFillColor = "transparent";
                } else {
                    ui.name.style.color = "#fff";
                }
            }

            if(ui.limit) ui.limit.innerText = config.limitText;

            // Handle AI Feature Lock/Unlock
            if(ui.ai_feat) {
                if(config.hasAI) {
                    ui.ai_feat.classList.remove("locked");
                    ui.ai_feat.innerHTML = `<i class="fa-solid fa-robot" style="color:#bd00ff"></i> <span>AI Tutor Enabled</span>`;
                } else {
                    ui.ai_feat.classList.add("locked");
                    ui.ai_feat.innerHTML = `<i class="fa-solid fa-lock"></i> <span>AI Tutor (Prime Only)</span>`;
                }
            }

            // Handle Upgrade Button
            if(ui.btn) {
                if(plan === 'prime') {
                    ui.btn.innerHTML = "<i class='fa-solid fa-check-circle'></i> You have the Best Plan";
                    ui.btn.style.background = "#222";
                    ui.btn.style.color = "#bd00ff";
                    ui.btn.style.boxShadow = "none";
                    ui.btn.disabled = true;
                    ui.btn.style.cursor = "default";
                } else {
                    ui.btn.innerHTML = "<i class='fa-solid fa-arrow-up'></i> Upgrade Plan";
                }
            }

            // ------------------------------------------------
            // 2. FILL PERSONAL INFO
            // ------------------------------------------------
            if(document.getElementById("p_username")) 
                document.getElementById("p_username").value = "@" + (u.username || "User");

            if(document.getElementById("p_fullname")) 
                document.getElementById("p_fullname").value = u.full_name || "";

            if(document.getElementById("p_email")) 
                document.getElementById("p_email").value = u.email || "No Email";

            if(document.getElementById("p_joined")) 
                document.getElementById("p_joined").value = u.created_at ? new Date(u.created_at).toLocaleDateString() : "Recently";

            // Header Name
            const headerName = document.querySelector(".welcome h1");
            const userDisplay = document.getElementById("userDisplay");
            
            if(headerName && u.full_name) {
                headerName.innerHTML = "Settings for <span style='color:var(--neon-blue)'>" + u.full_name.split(' ')[0] + "</span>";
            }
            if(userDisplay) {
                userDisplay.innerText = u.full_name;
            }

        } else {
            console.error("Profile Load Failed");
            document.getElementById("plan_badge").innerText = "Error Loading";
        }
    } catch(e) {
        console.error("Network Error:", e);
        document.getElementById("plan_badge").innerText = "Network Error";
    }
};

// --- UPDATE NAME ---
async function updateProfile() {
    const newName = document.getElementById("p_fullname").value;
    const btn = document.querySelector("button[onclick='updateProfile()']");
    const originalText = btn.innerText;

    if(!newName) { alert("Name cannot be empty"); return; }

    btn.innerText = "Saving...";
    btn.disabled = true;

    try {
        const res = await fetch(`${API_SECURE_PF}/update_info.php`, {
            method: "POST",
            body: JSON.stringify({ full_name: newName })
        });
        const result = await res.json();
        
        if(result.status === "success") {
            alert("✅ Profile Updated!");
            location.reload();
        } else {
            alert("❌ " + result.message);
        }
    } catch(e) { alert("Server Error"); }
    finally { btn.innerText = originalText; btn.disabled = false; }
}

// --- CHANGE PASSWORD ---
async function changePassword() {
    const curr = document.getElementById("curr_pass").value;
    const newP = document.getElementById("new_pass").value;

    if(!curr || !newP) { alert("Please fill both password fields"); return; }

    try {
        const res = await fetch(`${API_SECURE_PF}/change_password.php`, {
            method: "POST",
            body: JSON.stringify({ current_pass: curr, new_pass: newP })
        });
        const result = await res.json();
        
        if(result.status === "success") {
            alert("✅ Password Changed! Please login again.");
            window.location.href = "login.html";
        } else {
            alert("❌ " + result.message);
        }
    } catch(e) { alert("Server Error"); }
}