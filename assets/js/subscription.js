// assets/js/subscription.js
const API_PLANS = "api/wallet/get_plans.php";
const API_WALLET = "api/wallet/info.php";
const API_BUY = "api/wallet/buy_plan.php";

let userBalance = 0;
let standardPrice = 99;
let primePrice = 199; // Fallback

document.addEventListener("DOMContentLoaded", async () => {
    // 1. Fetch User Balance (Fixed ID)
    loadUserBalance();
    
    // 2. Fetch Dynamic Prices
    try {
        const res = await fetch(API_PLANS);
        const data = await res.json();
        if(data.status === "success") {
            // Update Standard Price
            if(data.plans.standard) {
                standardPrice = data.plans.standard;
            }
            // Update Prime Price
            if(data.plans.prime) {
                primePrice = data.plans.prime;
            }
        }
    } catch(e) { console.error("Price fetch error", e); }
});

async function loadUserBalance() {
    try {
        const res = await fetch(API_WALLET);
        const data = await res.json();
        if(data.status === "success") {
            userBalance = parseFloat(data.balance);
            
            // 🚨 FIX: Targeted 'nav-bal' instead of 'user-balance'
            const balEl = document.getElementById("nav-bal");
            if(balEl) balEl.innerText = "₹ " + userBalance;
        }
    } catch(e) {}
}

// --- BUY LOGIC ---
async function buyPlan(planType) {
    const price = planType === 'prime' ? primePrice : standardPrice;
    
    // 1. CHECK BALANCE
    if(userBalance < price) {
        const needed = price - userBalance;
        if(confirm(`⚠️ Low Balance!\n\nYou have ₹${userBalance}. You need ₹${needed} more.\n\nGo to Wallet to add funds?`)) {
            window.location.href = "wallet.html";
        }
        return;
    }

    // 2. CONFIRM
    if(!confirm(`Confirm Upgrade?\n\nPlan: ${planType.toUpperCase()}\nPrice: ₹${price}`)) return;

    // 3. PROCESS
    // Select the button that was clicked to show loading
    const btn = document.querySelector(`.btn-${planType}`);
    const originalText = btn.innerText;
    btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...`;
    btn.disabled = true;

    try {
        const res = await fetch(API_BUY, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ plan: planType })
        });
        const result = await res.json();

        if(result.status === "success") {
            alert(`🎉 SUCCESS!\n\nYou are now a ${planType.toUpperCase()} member.`);
            window.location.href = "dashboard.html"; 
        } else {
            alert("❌ " + result.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch(e) {
        alert("Server Error");
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}