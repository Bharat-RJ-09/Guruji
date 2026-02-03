// assets/js/profile.js

// ✅ FIX: Path relative to profile.html (which is in the root folder)
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

            // ------------------------------------------------
            // 1. CHECK SUBSCRIPTION STATUS (Standard vs Pro)
            // ------------------------------------------------
            // Check if the user has the 'pro' plan in the database
            if (u.subscription_plan === 'pro') {
                
                // A. Change Badge Text & Color
                const planBadge = document.querySelector(".plan-badge");
                const planName = document.getElementById("plan_name");
                
                if(planBadge) {
                    planBadge.innerText = "VIP MEMBER";
                    planBadge.style.background = "rgba(0, 255, 136, 0.2)";
                    planBadge.style.color = "#00ff88";
                    planBadge.style.borderColor = "#00ff88";
                }

                if(planName) {
                    planName.innerHTML = "Pro Plan 💎";
                    planName.style.textShadow = "0 0 15px rgba(0, 255, 136, 0.5)";
                    planName.style.background = "linear-gradient(to right, #fff, #00ff88)";
                    planName.style.webkitBackgroundClip = "text";
                    planName.style.webkitTextFillColor = "transparent";
                }

                // B. Hide "Upgrade" Button and show Active status
                const upgradeBtn = document.querySelector("button[onclick='upgradePlan()']");
                if(upgradeBtn) {
                    upgradeBtn.innerHTML = "<i class='fa-solid fa-check-circle'></i> Active";
                    upgradeBtn.style.background = "#222";
                    upgradeBtn.style.color = "#00ff88";
                    upgradeBtn.disabled = true;
                    upgradeBtn.style.cursor = "default";
                }

                // C. Unlock Features (Remove 'locked' class & update icons)
                document.querySelectorAll(".pro-features li").forEach(li => {
                    li.classList.remove("locked");
                    li.style.opacity = "1";
                    const icon = li.querySelector("i");
                    if(icon) {
                        icon.className = "fa-solid fa-circle-check";
                        icon.style.color = "#00ff88";
                    }
                });
            }

            // ------------------------------------------------
            // 2. FILL PERSONAL INFO FIELDS
            // ------------------------------------------------
            if(document.getElementById("p_username")) 
                document.getElementById("p_username").value = "@" + (u.username || "User");

            if(document.getElementById("p_fullname")) 
                document.getElementById("p_fullname").value = u.full_name || "";

            if(document.getElementById("p_email")) 
                document.getElementById("p_email").value = u.email || "No Email";

            // ------------------------------------------------
            // 3. FORMAT & FILL DATE
            // ------------------------------------------------
            const dateField = document.getElementById("p_joined");
            if(dateField) {
                if(u.created_at) {
                    const date = new Date(u.created_at);
                    const options = { year: 'numeric', month: 'short', day: 'numeric' };
                    dateField.value = date.toLocaleDateString('en-US', options);
                } else {
                    dateField.value = "Recently Joined";
                }
            }

            // ------------------------------------------------
            // 4. UPDATE HEADER GREETING
            // ------------------------------------------------
            const headerName = document.querySelector(".welcome h1");
            if(headerName && u.full_name) {
                headerName.innerHTML = "Settings for <span style='color:var(--neon-blue)'>" + u.full_name.split(' ')[0] + "</span>";
            }

        } else {
            console.error("Profile Load Failed:", data.message);
        }
    } catch(e) {
        console.error("Network/Server Error:", e);
    }
};


// --- UPDATE NAME LOGIC ---
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
            alert("✅ Profile Updated Successfully");
            // Update the name in the header immediately
            const headerName = document.querySelector(".welcome h1");
            if(headerName) headerName.innerHTML = "Settings for <span style='color:var(--neon-blue)'>" + newName.split(' ')[0] + "</span>";
        } else {
            alert("❌ " + result.message);
        }
    } catch(e) { 
        alert("Server Error: Check Console"); 
    } finally {
        btn.innerText = originalText;
        btn.disabled = false;
    }
}


// --- CHANGE PASSWORD LOGIC ---
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
            alert("✅ Password Changed! Logging out for security...");
            fetch('api/auth/logout.php').then(() => window.location.href = "login.html");
        } else {
            alert("❌ " + result.message);
        }
    } catch(e) { alert("Server Error"); }
}


// --- UPGRADE PLAN PLACEHOLDER ---
function upgradePlan() {
    alert("🔒 Premium Gateway is currently in Sandbox Mode.");
}