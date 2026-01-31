// Base API URL (Apne hisab se adjust karna agar folder structure alag ho)
const API_BASE = "../api/auth"; 

// --- Helper Functions ---
function showMessage(type, text) {
    const box = document.getElementById("msgBox");
    box.className = `message-box ${type}`;
    box.innerText = text;
    box.style.display = "block";
}

function setLoading(btnId, isLoading) {
    const btn = document.getElementById(btnId);
    if(isLoading) {
        btn.disabled = true;
        btn.innerText = "Processing...";
    } else {
        btn.disabled = false;
        btn.innerText = "Submit";
    }
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
            const res = await fetch(`${API_BASE}/register.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            const result = await res.json();

            if (result.status === "success") {
                showMessage("success", "Success! Redirecting to verify...");
                // Testing ke liye console mein OTP dikhega (Debug Mode)
                if(result.debug_otp) console.log("Debug OTP:", result.debug_otp);
                
                setTimeout(() => {
                    window.location.href = `verify.html?email=${encodeURIComponent(data.email)}`;
                }, 1500);
            } else {
                showMessage("error", result.message);
                setLoading("submitBtn", false);
            }
        } catch (error) {
            console.error(error);
            showMessage("error", "Server Error. Try again later.");
            setLoading("submitBtn", false);
        }
    });
}

// --- 2. VERIFY LOGIC ---
const verifyForm = document.getElementById("verifyForm");
if (verifyForm) {
    verifyForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        setLoading("submitBtn", true);

        const data = {
            email: document.getElementById("email").value,
            otp: document.getElementById("otp").value
        };

        try {
            const res = await fetch(`${API_BASE}/verify_otp.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            const result = await res.json();

            if (result.status === "success") {
                showMessage("success", "Verified! Redirecting to login...");
                setTimeout(() => {
                    window.location.href = "login.html"; // Agle step mein banayenge
                }, 1500);
            } else {
                showMessage("error", result.message);
                setLoading("submitBtn", false);
            }
        } catch (error) {
            showMessage("error", "Server Error.");
            setLoading("submitBtn", false);
        }
    });
}