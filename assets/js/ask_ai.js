// assets/js/ask_ai.js
const API_AI = "api/ai/ask.php";

function toggleAI() {
    document.getElementById("aiWindow").classList.toggle("active");
    // Auto-focus input when opening
    if(document.getElementById("aiWindow").classList.contains("active")){
        setTimeout(() => document.getElementById("aiInput").focus(), 300);
    }
}

async function askGemini() {
    const input = document.getElementById("aiInput");
    const chatBody = document.getElementById("aiBody");
    const question = input.value.trim();

    if (!question) return;

    // 1. Add User Message immediately
    appendMessage(question, "user");
    input.value = "";
    
    // 2. Show "Thinking" Animation
    const loadingId = showLoading();
    chatBody.scrollTop = chatBody.scrollHeight;

    try {
        const res = await fetch(API_AI, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ question: question })
        });
        const data = await res.json();

        // Remove loading animation
        document.getElementById(loadingId).remove();

        if (data.status === "success") {
            // 3. ✨ Typewriter Effect with Markdown
            const cleanHTML = parseMarkdown(data.reply);
            typeWriter(cleanHTML, "ai");
        } else if (data.status === "forbidden") {
            appendMessage("🔒 " + data.message + " <br><br><a href='subscription.html' style='color:#ffd700; text-decoration:underline;'>👉 Upgrade to Prime</a>", "ai");
        } else {
            appendMessage("❌ " + data.message, "ai");
        }

    } catch (e) {
        if(document.getElementById(loadingId)) document.getElementById(loadingId).remove();
        appendMessage("⚠️ Network Error. Please check your connection.", "ai");
    }
}

// --- VISUAL EFFECTS ---

function showLoading() {
    const chatBody = document.getElementById("aiBody");
    const div = document.createElement("div");
    const id = "loading-" + Date.now();
    div.id = id;
    div.className = "typing-indicator";
    div.innerHTML = `<div class="dot"></div><div class="dot"></div><div class="dot"></div>`;
    chatBody.appendChild(div);
    return id;
}

function appendMessage(html, sender) {
    const chatBody = document.getElementById("aiBody");
    const div = document.createElement("div");
    div.className = `msg ${sender}`;
    div.innerHTML = html;
    chatBody.appendChild(div);
    chatBody.scrollTop = chatBody.scrollHeight;
}

// ✨ Typewriter Effect for AI
function typeWriter(html, sender) {
    const chatBody = document.getElementById("aiBody");
    const div = document.createElement("div");
    div.className = `msg ${sender}`;
    chatBody.appendChild(div);
    
    // We can't type HTML tags one by one (it breaks rendering), 
    // so we dump the parsed HTML directly but scroll smoothly.
    // For a true char-by-char effect with HTML, it's complex.
    // Let's do a "Fast Fade In" effect instead which is safer for Markdown.
    
    div.style.opacity = 0;
    div.innerHTML = html;
    
    // Simple Fade In Animation
    let op = 0.1;
    const timer = setInterval(function () {
        if (op >= 1){
            clearInterval(timer);
        }
        div.style.opacity = op;
        div.style.filter = `alpha(opacity=${op * 100})`;
        op += op * 0.1;
    }, 10);

    chatBody.scrollTop = chatBody.scrollHeight;
}

// ✨ Simple Markdown Parser
function parseMarkdown(text) {
    if (!text) return "";
    
    let html = text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Bold
        .replace(/\*(.*?)\*/g, '<em>$1</em>')             // Italics
        .replace(/```(.*?)```/gs, '<br><code>$1</code><br>') // Code blocks
        .replace(/`(.*?)`/g, '<code>$1</code>')            // Inline code
        .replace(/\n/g, '<br>');                           // Newlines

    // Bullet points (simple handler)
    if (html.includes('* ')) {
        html = html.replace(/\* (.*?)(<br>|$)/g, '<li>$1</li>');
        html = html.replace(/<li>.*?<\/li>/gs, match => `<ul>${match}</ul>`);
        // Cleanup double ULs if any (basic regex limitation fix)
        html = html.replace(/<\/ul><br><ul>/g, ''); 
    }
    
    return html;
}

// Enter Key Handler
document.getElementById("aiInput").addEventListener("keypress", function (e) {
    if (e.key === "Enter") askGemini();
});