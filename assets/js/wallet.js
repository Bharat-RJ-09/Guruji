// assets/js/wallet.js
const API_WALLET = "api/wallet";
const UPI_ID = "edu.elf@ptyes";
const MERCHANT_NAME = "NextEdu";

document.addEventListener("DOMContentLoaded", () => {
    loadWalletInfo();
});

// 1. Quick Chips
function setAmount(val) {
    const input = document.getElementById("dep_amount");
    input.value = val;
    updatePaymentLinks();
    input.focus();
}

// 2. Update Links & QR
function updatePaymentLinks() {
    const amount = document.getElementById("dep_amount").value;
    const qrImg = document.querySelector(".qr-box img");
    const links = document.querySelectorAll(".app-link");
    
    let baseUrl = `upi://pay?pa=${UPI_ID}&pn=${MERCHANT_NAME}`;
    if(amount > 0) baseUrl += `&am=${amount}&cu=INR`;

    if(qrImg) qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(baseUrl)}`;
    links.forEach(link => link.href = baseUrl);
}

// 3. Load Data & Render Chart
async function loadWalletInfo() {
    try {
        const res = await fetch(`${API_WALLET}/info.php`);
        const data = await res.json();
        
        if (data.status === "success") {
            document.getElementById("wallet-balance").innerText = "₹ " + data.balance;
            renderHistory(data.history);
            renderChart(data.history);
        }
    } catch (e) { console.error(e); }
}

// 4. Render History
function renderHistory(txs) {
    const list = document.getElementById("trans-history");
    list.innerHTML = "";
    if(txs.length === 0) return list.innerHTML = "<p style='text-align:center; padding:20px; color:#777;'>No transactions.</p>";

    txs.forEach(tx => {
        const color = tx.type === 'deposit' ? '#00ff88' : '#ff4444';
        const sign = tx.type === 'deposit' ? '+' : '-';
        
        list.innerHTML += `
            <div class="history-item">
                <div>
                    <h4 style="margin:0; color:#fff; font-size:0.95rem;">${tx.description}</h4>
                    <small style="color:#888;">${tx.date}</small>
                    <span style="font-size:0.7rem; border:1px solid #555; padding:2px 6px; border-radius:4px; margin-left:5px;">${tx.status.toUpperCase()}</span>
                </div>
                <h3 style="margin:0; color:${color};">${sign}₹${tx.amount}</h3>
            </div>
        `;
    });
}

// 5. Render Chart
function renderChart(txs) {
    const ctx = document.getElementById('spendChart');
    if(!ctx) return;
    
    const amounts = txs.slice(0, 5).map(t => t.amount);
    const labels = txs.slice(0, 5).map(t => t.date.split(',')[0]);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels.reverse(),
            datasets: [{
                label: 'Activity',
                data: amounts.reverse(),
                borderColor: '#00f0ff',
                backgroundColor: 'rgba(0, 240, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } },
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// 6. Submit Deposit
async function submitDeposit() {
    const amount = document.getElementById("dep_amount").value;
    const utr = document.getElementById("dep_utr").value.trim();
    const btn = document.querySelector(".btn-verify");

    if(!amount || amount <= 0) return alert("Enter amount");
    if(utr.length < 8) return alert("Enter valid UTR");

    btn.disabled = true;
    btn.innerText = "Verifying...";

    try {
        const res = await fetch(`${API_WALLET}/manual_deposit.php`, {
            method: "POST",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({ amount: amount, utr: utr })
        });
        const d = await res.json();
        if(d.status === "success") {
            alert("✅ " + d.message);
            location.reload();
        } else {
            alert("❌ " + d.message);
        }
    } catch(e) { alert("Server Error"); }
    btn.disabled = false;
    btn.innerText = "Verify & Add Funds";
}

function scrollToDeposit() {
    document.getElementById("deposit-area").scrollIntoView({behavior:"smooth"});
}