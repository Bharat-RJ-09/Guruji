// assets/js/wallet.js
const API_WALLET = "api/wallet";
const API_PROFILE = "api/secure/profile.php";

document.addEventListener("DOMContentLoaded", async () => {
    loadWalletInfo();
    
    // ✨ Load User Theme (Visual Tier System)
    try {
        const res = await fetch(API_PROFILE);
        const data = await res.json();
        if(data.status === "success" && data.user.subscription_plan && window.applyTheme) {
            window.applyTheme(data.user.subscription_plan);
        }
    } catch(e) { console.log("Theme load error"); }
});

async function loadWalletInfo() {
    try {
        const res = await fetch(`${API_WALLET}/info.php`);
        const data = await res.json();
        
        if (data.status === "success") {
            // Animate Balance Count Up? (Simple implementation)
            document.getElementById("balance").innerText = "₹" + data.balance;
            
            const list = document.getElementById("history-list");
            list.innerHTML = "";
            
            if(data.history.length === 0) {
                list.innerHTML = "<p style='text-align:center; color:#555; padding:20px;'>No transactions yet.</p>";
            }

            data.history.forEach(tx => {
                const isDeposit = tx.type === 'deposit';
                const color = isDeposit ? '#00ff88' : '#ff4444'; // Green or Red
                const icon = isDeposit ? 'fa-arrow-down' : 'fa-arrow-up';
                const sign = isDeposit ? '+' : '-';
                const bgClass = isDeposit ? 'bg-green' : 'bg-red';

                list.innerHTML += `
                    <div class="tx-item">
                        <div class="tx-icon ${bgClass}">
                            <i class="fa-solid ${icon}"></i>
                        </div>
                        <div class="tx-details">
                            <h4>${tx.description}</h4>
                            <small>${tx.date}</small>
                        </div>
                        <div class="tx-amount" style="color:${color}">
                            ${sign}₹${tx.amount}
                        </div>
                    </div>
                `;
            });
        }
    } catch (e) {
        console.error(e);
        document.getElementById("history-list").innerHTML = "<p style='text-align:center; color:red;'>Server Error</p>";
    }
}

// --- MODAL LOGIC ---
function openDepositModal() {
    document.getElementById("depositModal").classList.add("active");
    document.getElementById("depositAmount").focus();
}

function closeDepositModal() {
    document.getElementById("depositModal").classList.remove("active");
    document.getElementById("depositAmount").value = "";
}

// --- SECURE DEPOSIT ---
async function processDeposit() {
    const amountInput = document.getElementById("depositAmount");
    const amount = parseFloat(amountInput.value);
    const btn = document.getElementById("btn-process-deposit");

    // 1. Validation
    if(!amount || isNaN(amount) || amount <= 0) {
        alert("Please enter a valid amount (Min ₹1).");
        return;
    }

    // 2. Disable Button (Prevent Double Click)
    btn.disabled = true;
    btn.innerHTML = `Processing... <i class="fa-solid fa-circle-notch fa-spin"></i>`;

    try {
        const res = await fetch(`${API_WALLET}/deposit.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ amount: amount })
        });
        const result = await res.json();
        
        if(result.status === "success") {
            // Success!
            closeDepositModal();
            loadWalletInfo(); 
            // Optional: Show a nice "Success" toast here
        } else {
            alert("❌ " + result.message);
        }
    } catch(e) { 
        alert("Server Error. Check connection."); 
    } finally {
        // Reset Button
        btn.disabled = false;
        btn.innerHTML = `Add Securely <i class="fa-solid fa-lock"></i>`;
    }
}