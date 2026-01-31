const API_SECURE_PF = "../api/secure";

window.onload = async () => {
    // 1. Data Fetch karo
    try {
        const res = await fetch(`${API_SECURE_PF}/profile.php`);
        const data = await res.json();
        
        if(data.status === "success") {
            const u = data.user;
            document.getElementById("p_username").value = u.username;
            document.getElementById("p_fullname").value = u.full_name;
            
            // Subscription Logic
            if(u.subscription_plan === 'pro') {
                document.getElementById("plan_name").innerText = "PRO MEMBER 🌟";
                document.getElementById("plan_name").style.textShadow = "0 0 10px #FFD700";
            }
        } else {
            window.location.href = "login.html";
        }
    } catch(e) {
        console.error(e);
    }
};

// 2. Update Name
async function updateProfile() {
    const newName = document.getElementById("p_fullname").value;
    
    try {
        const res = await fetch(`${API_SECURE_PF}/update_info.php`, {
            method: "POST",
            body: JSON.stringify({ full_name: newName })
        });
        const result = await res.json();
        alert(result.message);
    } catch(e) { alert("Error updating profile"); }
}

// 3. Change Password
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
            alert("Password Changed! Please Login again.");
            // Logout user for security
            fetch('../api/auth/logout.php').then(() => window.location.href = "login.html");
        } else {
            alert(result.message);
        }
    } catch(e) { alert("Server Error"); }
}

// 4. Upgrade Plan Placeholder
function upgradePlan() {
    alert("Payment Gateway is integrating... Stay tuned! 💳");
}