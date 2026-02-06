// assets/js/auth.js

const API_BASE = "api/auth"; 
const BOT_USERNAME = "NextEdu_verifier_bot"; // Your Bot Username

// --- Helper Functions ---

function showMessage(type, text) {
    const box = document.getElementById("msgBox");
    if (box) {
        box.className = `message-box ${type}`;
        box.innerHTML = type === 'error' ? `❌ ${text}` : `✅ ${text}`;
        box.style.display = "block";
        if(type === 'success') {
            setTimeout(() => { box.style.display = 'none'; }, 3000); // Increased time slightly
        }
    } else {
        alert(text);
    }
}

function setLoading(btnId, isLoading) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    
    if (isLoading) {
        btn.dataset.originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = "Processing...";
        btn.style.opacity = "0.7";
        btn.style.cursor = "wait";
    } else {
        btn.disabled = false;
        btn.innerText = btn.dataset.originalText || "Submit";
        btn.style.opacity = "1";
        btn.style.cursor = "pointer";
    }
}

// --- MAIN LOGIC ---
document.addEventListener("DOMContentLoaded", () => {
    
    // 1. REMEMBER ME
    const savedUser = localStorage.getItem("remember_user");
    const loginInput = document.getElementById("login_id");
    const rememberCheckbox = document.getElementById("rememberMe");

    if (savedUser && loginInput) {
        loginInput.value = savedUser;
        if (rememberCheckbox) rememberCheckbox.checked = true;
    }

    // 2. SIGNUP LOGIC (Updated Redirection)
    const signupForm = document.getElementById("signupForm");
    if (signupForm) {
        signupForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            setLoading("submitBtn", true);

            const data = {
                full_name: document.getElementById("full_name").value,
                username: document.getElementById("username").value,
                email: document.getElementById("email").value,
                telegram_chat_id: document.getElementById("telegram_chat_id")?.value || "", 
                password: document.getElementById("password").value,
                confirm_password: document.getElementById("confirm_password").value 
            };

            if(data.password !== data.confirm_password){
                showMessage("error", "Passwords do not match!");
                setLoading("submitBtn", false);
                return;
            }

            try {
                const res = await fetch(`${API_BASE}/register.php`, { 
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                });

                const result = await res.json();

                if (result.status === "success") {
                    // ✨ NEW REDIRECTION LOGIC
                    showMessage("success", "Account Created! Redirecting to Telegram...");
                    
                    setTimeout(() => {
                        // Redirect to Bot so user presses "START"
                        // This ensures we can send them OTPs later
                        window.location.href = `https://t.me/${BOT_USERNAME}?start=welcome`; 
                    }, 1500);
                } else {
                    showMessage("error", result.message || "Registration failed");
                }
            } catch (error) {
                console.error(error);
                showMessage("error", "Network Error. Please try again.");
            } finally {
                setLoading("submitBtn", false);
            }
        });
    }

    // 3. LOGIN LOGIC
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            setLoading("submitBtn", true);

            const usernameVal = document.getElementById("login_id").value;
            const passwordVal = document.getElementById("password").value;
            const isRemember = document.getElementById("rememberMe")?.checked;

            const data = { username: usernameVal, password: passwordVal };

            try {
                const res = await fetch(`${API_BASE}/login.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                });

                const result = await res.json();

                if (result.status === "success") {
                    showMessage("success", "Login Successful! Redirecting...");
                    
                    if (isRemember) {
                        localStorage.setItem("remember_user", usernameVal);
                    } else {
                        localStorage.removeItem("remember_user");
                    }

                    setTimeout(() => {
                        window.location.href = "dashboard.html"; 
                    }, 1000);
                } else {
                    showMessage("error", result.message || "Invalid Credentials");
                }
            } catch (error) {
                console.error(error);
                showMessage("error", "Server Connection Failed.");
            } finally {
                setLoading("submitBtn", false);
            }
        });
    }
});

// 4. TELEGRAM LOGIN HANDLER (Global)
window.onTelegramLogin = function(user) {
    fetch(`${API_BASE}/login.php`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ telegram_user: user })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            window.location.href = "dashboard.html";
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert("Telegram Login Failed.");
    });
};