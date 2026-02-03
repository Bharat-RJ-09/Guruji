// assets/js/wallet.js
const API_WALLET = "api/wallet";

window.onload = loadWalletInfo;

async function loadWalletInfo() {
    try {
        const res = await fetch(`${API_WALLET}/info.php`);
        const data = await res.json();

        if (data.status === "success") {
            // Update Balance
            document.getElementById("wallet-balance").innerText = "₹ " + data.balance;

            // Update History
            const list = document.getElementById("trans-history");
            list.innerHTML = "";

            if (data.history.length === 0) {
                list.innerHTML = "<p style='text-align:center; color:#777;'>No transactions yet.</p>";
            } else {
                data.history.forEach(t => {
                    let color = t.status === 'success' ? '#00ff88' : (t.status === 'pending' ? '#ffd700' : '#ff4444');
                    let icon = t.type === 'deposit' ? 'fa-arrow-down' : 'fa-arrow-up';
                    
                    let html = `
                        <div class="trans-item ${t.status}">
                            <div>
                                <h4 style="margin:0; text-transform:capitalize;">${t.type} <span style="font-size:0.8rem; color:${color}">(${t.status})</span></h4>
                                <small style="color:#777;">${t.date} • ${t.description}</small>
                            </div>
                            <div style="font-weight:bold; color:${t.type === 'deposit' ? '#00ff88' : '#fff'};">
                                ${t.type === 'deposit' ? '+' : '-'} ₹${t.amount}
                            </div>
                        </div>
                    `;
                    list.innerHTML += html;
                });
            }
        } else {
            alert(data.message);
        }
    } catch (e) {
        console.error(e);
    }
}

async function submitDeposit() {
    const amount = document.getElementById("dep_amount").value;
    const utr = document.getElementById("dep_utr").value;

    if (!amount || !utr) return alert("Please enter Amount and UTR.");

    try {
        const res = await fetch(`${API_WALLET}/deposit.php`, {
            method: "POST",
            body: JSON.stringify({ amount, utr })
        });
        const result = await res.json();
        
        if (result.status === "success") {
            alert("✅ Request Submitted! Please wait for admin approval.");
            location.reload();
        } else {
            alert("❌ " + result.message);
        }
    } catch (e) { alert("Server Error"); }
}

function scrollToDeposit() {
    document.getElementById("deposit-area").scrollIntoView({ behavior: 'smooth' });
}