// assets/js/subscription.js
const API_WALLET_SUB = "api/wallet";

// On Load: Fetch Balance to show in Sidebar
window.onload = async () => {
    try {
        const res = await fetch(`${API_WALLET_SUB}/info.php`);
        const data = await res.json();
        if(data.status === "success") {
            const balEl = document.getElementById("nav-bal");
            if(balEl) balEl.innerText = "₹" + data.balance;
        }
    } catch(e) { console.error(e); }
};

// Handle Purchase
async function buyPlan(planType) {
    if(!confirm(`Are you sure you want to upgrade to the ${planType.toUpperCase()} plan?`)) return;

    const btn = document.querySelector(`.btn-${planType}`);
    const originalText = btn.innerText;
    btn.innerText = "Processing...";
    btn.disabled = true;

    try {
        const res = await fetch(`${API_WALLET_SUB}/buy_plan.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ plan: planType })
        });

        const result = await res.json();

        if (result.status === "success") {
            alert(result.message);
            window.location.href = "profile.html"; // Redirect to profile to see badge
        } else {
            alert("❌ " + result.message);
            // If low balance, offer to go to wallet
            if(result.message.includes("Insufficient Balance")) {
                if(confirm("Go to Wallet to add money?")) window.location.href = "wallet.html";
            }
        }

    } catch (e) {
        alert("Server Error");
    } finally {
        btn.innerText = originalText;
        btn.disabled = false;
    }
}