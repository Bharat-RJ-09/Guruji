// assets/js/wallet.js
const API_WALLET = "api/wallet";

document.addEventListener("DOMContentLoaded", () => {
    loadWalletInfo();
});

async function loadWalletInfo() {
    try {
        const res = await fetch(`${API_WALLET}/info.php`);
        const data = await res.json();
        
        if (data.status === "success") {
            document.getElementById("balance").innerText = "₹" + data.balance;
            
            // Render History
            const list = document.getElementById("history-list");
            list.innerHTML = "";
            
            if(data.history.length === 0) {
                list.innerHTML = "<p style='text-align:center; color:#555;'>No transactions yet.</p>";
            }

            data.history.forEach(tx => {
                const color = tx.type === 'deposit' ? '#00ff88' : '#ff4444';
                const sign = tx.type === 'deposit' ? '+' : '-';
                
                list.innerHTML += `
                    <div class="history-item">
                        <div>
                            <h4>${tx.description}</h4>
                            <small>${tx.date}</small>
                        </div>
                        <h3 style="color:${color}">${sign}₹${tx.amount}</h3>
                    </div>
                `;
            });
        }
    } catch (e) {
        console.error(e);
    }
}

// Deposit Function
async function depositMoney() {
    const amount = prompt("Enter amount to deposit (₹):");
    if(!amount || isNaN(amount) || amount <= 0) return alert("Invalid Amount");

    try {
        const res = await fetch(`${API_WALLET}/deposit.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ amount: amount })
        });
        const result = await res.json();
        
        if(result.status === "success") {
            alert("✅ Deposit Successful!");
            loadWalletInfo();
        } else {
            alert("❌ " + result.message);
        }
    } catch(e) { alert("Server Error"); }
}

// ✨ BUY PLAN FUNCTION (Updated Redirect)
async function buyPlan(planType) {
    if(!confirm(`Are you sure you want to buy ${planType.toUpperCase()} plan?`)) return;

    try {
        const res = await fetch(`${API_WALLET}/buy_plan.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ plan: planType })
        });
        const result = await res.json();

        if(result.status === "success") {
            // Redirect to Dashboard with SUCCESS FLAG
            window.location.href = "dashboard.html?upgrade_success=" + result.new_plan;
        } else {
            alert("❌ " + result.message);
        }
    } catch(e) { alert("Server Error"); }
}