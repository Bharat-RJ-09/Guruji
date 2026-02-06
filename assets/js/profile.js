// assets/js/profile.js

const API_PROFILE = "api/secure/profile.php";
const API_STATS = "api/secure/stats.php";
const API_AVATAR = "api/secure/update_avatar.php";
const API_UPDATE_INFO = "api/secure/update_info.php";

// 🎭 12 Avatar Presets
const AVATARS = {
    'av1': 'https://cdn-icons-png.flaticon.com/512/4140/4140048.png',
    'av2': 'https://cdn-icons-png.flaticon.com/512/4140/4140037.png',
    'av3': 'https://cdn-icons-png.flaticon.com/512/4140/4140047.png',
    'av4': 'https://cdn-icons-png.flaticon.com/512/4140/4140051.png',
    'av5': 'https://cdn-icons-png.flaticon.com/512/1999/1999625.png',
    'av6': 'https://cdn-icons-png.flaticon.com/512/4140/4140055.png',
    'av7': 'https://cdn-icons-png.flaticon.com/512/4140/4140040.png',
    'av8': 'https://cdn-icons-png.flaticon.com/512/6997/6997662.png',
    'av9': 'https://cdn-icons-png.flaticon.com/512/4140/4140072.png',
    'av10': 'https://cdn-icons-png.flaticon.com/512/4140/4140061.png',
    'av11': 'https://cdn-icons-png.flaticon.com/512/4140/4140076.png',
    'av12': 'https://cdn-icons-png.flaticon.com/512/4140/4140053.png'
};

document.addEventListener("DOMContentLoaded", () => {
    loadProfile();
    loadStats();
});

// 1. Load Profile
async function loadProfile() {
    try {
        const res = await fetch(API_PROFILE);
        const data = await res.json();
        
        if(data.status === "success") {
            const u = data.user;
            
            // Fix "Loading..." logic
            document.getElementById("user-fullname").innerText = u.full_name || "Unknown";
            document.getElementById("edit_fullname").value = u.full_name || "";
            document.getElementById("user-username").innerText = "@" + u.username;
            
            // Set Avatar (Default to av1 if null)
            const avKey = u.avatar || 'av1';
            document.getElementById("user-avatar-img").src = AVATARS[avKey] || AVATARS['av1'];
        }
    } catch(e) { console.error(e); }
}

// 2. Load Stats & Level
async function loadStats() {
    try {
        const res = await fetch(API_STATS);
        const data = await res.json();
        
        if(data.status === "success") {
            const s = data.stats;
            document.getElementById("lvl-num").innerText = s.level;
            document.getElementById("xp-val").innerText = s.xp;
            document.getElementById("xp-max").innerText = s.next_level_xp;
            document.getElementById("xp-bar").style.width = s.progress + "%";
            
            if(s.level > 5) document.getElementById("lvl-badge").innerText = "Scholar 🎓";
            if(s.level > 10) document.getElementById("lvl-badge").innerText = "Master 🛡️";
        }
    } catch(e) { console.error(e); }
}

// 3. Update Name (FIXED)
async function updateProfileName() {
    const name = document.getElementById("edit_fullname").value;
    const btn = document.querySelector(".btn-save");
    
    if(!name) return alert("Name cannot be empty");

    btn.innerText = "Saving...";
    btn.disabled = true;

    try {
        const res = await fetch(API_UPDATE_INFO, {
            method: "POST",
            headers: { "Content-Type": "application/json" }, // 🚨 THIS WAS MISSING
            body: JSON.stringify({ full_name: name })
        });
        const result = await res.json();

        if(result.status === "success") {
            alert("✅ Name Updated!");
            loadProfile(); // Refresh UI
        } else {
            alert("❌ " + result.message);
        }
    } catch(e) { alert("Server Error"); }
    
    btn.innerText = "Save Name";
    btn.disabled = false;
}

// 4. Avatar Logic
function openAvatarModal() { document.getElementById("avatarModal").classList.add("active"); }
function closeAvatarModal() { document.getElementById("avatarModal").classList.remove("active"); }

async function selectAvatar(key) {
    // Optimistic UI Update
    document.getElementById("user-avatar-img").src = AVATARS[key];
    closeAvatarModal();

    // Save to DB
    try {
        await fetch(API_AVATAR, {
            method: "POST",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({ avatar: key })
        });
    } catch(e) { alert("Failed to save avatar"); }
}

// 5. Support Bot
function openSupportBot() {
    // Replace with your actual Support Bot Username
    window.open("https://t.me/NextEdu_Support_Bot", "_blank");
}
