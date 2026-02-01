// assets/js/auth.js

const API_BASE = "api/auth"; 

// --- Helper Functions ---
function showMessage(type, text) {
    const box = document.getElementById("msgBox");
    if (box) {
        box.className = `message-box ${type}`;
        box.innerText = text;
        box.style.display = "block";
    } else {
        alert(text);
    }
}

function setLoading(btnId, isLoading) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn.disabled = isLoading;
    btn.innerText = isLoading ? "Processing..." : "Submit";
}

// --- 1. SIGNUP LOGIC ---
const signupForm = document.getElementById("signupForm");
if (signupForm) {
    signupForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        setLoading("submitBtn", true);

        const data = {
            full_name: document.getElementById("full_name").value,
            username: document.getElementById("username").value,
            email: document.getElementById("email").value,
            password: document.getElementById("password").value
        };

        try {
            const res = await fetch(`${API_BASE}/signup.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            const result = await res.json();

            if (result.status === "success") {
                // 🛠️ DEV MODE FEATURE: Show OTP instantly on screen!
                if(result.debug_otp) {
                    alert("📢 DEVELOPER OTP: " + result.debug_otp);
                }

                showMessage("success", "Success! Redirecting to verify...");
                
                setTimeout(() => {
                    window.location.href = `verify.html?email=${encodeURIComponent(data.email)}`;
                }, 1000);
            } else {
                showMessage("error", result.message);
                setLoading("submitBtn", false);
            }
        } catch (error) {
            console.error(error);
            showMessage("error", "Server Error. Check Console.");
            setLoading("submitBtn", false);
        }
    });
}

// --- 2. VERIFY LOGIC ---
// (Already inside verify.html script tag usually, but if separated:)
// ... verify logic ...

// --- 3. LOGIN LOGIC ---
const loginForm = document.getElementById("loginForm");
if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        setLoading("submitBtn", true);

        const data = {
            username: document.getElementById("login_id").value,
            password: document.getElementById("password").value
        };

        try {
            const res = await fetch(`${API_BASE}/login.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            const result = await res.json();

            if (result.status === "success") {
                showMessage("success", "Login Successful! Redirecting...");
                setTimeout(() => {
                    window.location.href = "dashboard.html"; 
                }, 1000);
            } else {
                showMessage("error", result.message);
                if(result.redirect) {
                    setTimeout(() => { window.location.href = result.redirect; }, 2000);
                }
                setLoading("submitBtn", false);
            }
        } catch (error) {
            console.error(error);
            showMessage("error", "Server Error.");
            setLoading("submitBtn", false);
        }
    });
}