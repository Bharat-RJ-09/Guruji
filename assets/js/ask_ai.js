// assets/js/ask_ai.js
const API_AI = "api/ai/ask.php";

function toggleAI() {
    document.getElementById("aiWindow").classList.toggle("active");
}

async function askGemini() {
    const input = document.getElementById("aiInput");
    const chatBody = document.getElementById("aiBody");
    const question = input.value.trim();

    if (!question) return;

    // 1. Add User Message
    appendMessage(question, "user");
    input.value = "";
    
    // 2. Show Typing Indicator
    const loadingId = appendMessage("Thinking...", "ai", true);
    
    // 3. Scroll to bottom
    chatBody.scrollTop = chatBody.scrollHeight;

    try {
        const res = await fetch(API_AI, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ question: question })
        });
        const data = await res.json();

        // Remove loading msg
        document.getElementById(loadingId).remove();

        if (data.status === "success") {
            appendMessage(data.reply, "ai");
        } else if (data.status === "forbidden") {
            appendMessage("🔒 " + data.message + " <br><a href='subscription.html' style='color:#ffd700'>Upgrade Now</a>", "ai");
        } else {
            appendMessage("❌ Error: " + data.message, "ai");
        }

    } catch (e) {
        document.getElementById(loadingId).remove();
        appendMessage("⚠️ Connection Failed", "ai");
    }
}

function appendMessage(text, sender, isLoading = false) {
    const chatBody = document.getElementById("aiBody");
    const div = document.createElement("div");
    const id = "msg-" + Date.now();
    
    div.id = id;
    div.className = `msg ${sender}`;
    div.innerHTML = text; // Allow HTML for links
    
    chatBody.appendChild(div);
    chatBody.scrollTop = chatBody.scrollHeight;
    
    return id;
}

// Enter Key to Send
document.getElementById("aiInput").addEventListener("keypress", function (e) {
    if (e.key === "Enter") askGemini();
});