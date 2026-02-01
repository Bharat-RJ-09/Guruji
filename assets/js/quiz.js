// assets/js/quiz.js

const API_BASE = "api/quiz";

// 1. Get Subject from URL
const urlParams = new URLSearchParams(window.location.search);
const subject = urlParams.get('sub'); 

if (!subject) {
    alert("No Subject Selected! Redirecting...");
    window.location.href = "dashboard.html";
}

let questions = [];
let currentIndex = 0;
let userAnswers = {}; 
let timerInterval;
let timeLeft = 45;

// 2. Start Quiz
window.onload = async () => {
    try {
        const res = await fetch(`${API_BASE}/start.php?subject=${subject}`);
        const data = await res.json();
        
        if(data.status === "success" && data.data.length > 0) {
            questions = data.data;
            document.getElementById("total-q").innerText = questions.length;
            document.getElementById("loader").style.display = "none";
            document.getElementById("quiz-area").style.display = "block";
            
            loadQuestion();
            startTimer();
        } else {
            alert(data.message || "No questions found!");
            window.location.href = "dashboard.html";
        }
    } catch (e) {
        console.error(e);
        alert("Server Error: Check Console");
    }
};

// 3. Load Question
function loadQuestion() {
    const q = questions[currentIndex];
    document.getElementById("current-q").innerText = currentIndex + 1;
    document.getElementById("question-text").innerText = q.question_text;
    
    ['a','b','c','d'].forEach(opt => {
        const btn = document.getElementById(`btn-${opt}`);
        btn.innerText = q[`option_${opt}`];
        btn.classList.remove("selected");
        if(userAnswers[q.id] === opt) btn.classList.add("selected");
    });
}

// 4. Select Option
window.selectOption = (opt) => {
    const qId = questions[currentIndex].id;
    userAnswers[qId] = opt;
    ['a','b','c','d'].forEach(o => document.getElementById(`btn-${o}`).classList.remove("selected"));
    document.getElementById(`btn-${opt}`).classList.add("selected");
};

// 5. Next Button
window.nextQuestion = () => {
    if(currentIndex < questions.length - 1) {
        currentIndex++;
        loadQuestion();
    } else {
        submitQuiz();
    }
};

// 6. Submit Logic (Fixed)
async function submitQuiz() {
    clearInterval(timerInterval);
    document.getElementById("quiz-area").innerHTML = "<h2 style='color:#fff;'>Submitting Score... 🔄</h2>";

    const payload = {
        subject: subject,
        answers: userAnswers
    };

    try {
        const res = await fetch(`${API_BASE}/submit.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        
        const result = await res.json();
        
        if(result.status === "success") {
            document.getElementById("quiz-area").innerHTML = `
                <h1 style="color:#00ffe1;">🎉 Quiz Completed!</h1>
                <h2 style="font-size:3rem; color:#fff;">${result.score} / ${result.total}</h2>
                <p style="color:#aaa;">Score saved successfully</p>
                <button class="btn-control btn-next" onclick="location.href='dashboard.html'" style="margin-top:20px;">Back to Dashboard</button>
            `;
        } else {
            document.getElementById("quiz-area").innerHTML = `
                <h2 style="color:red;">❌ Submission Failed</h2>
                <p>${result.message}</p>
                <button class="btn-control" onclick="location.href='dashboard.html'">Go Back</button>
            `;
        }
    } catch (e) {
        console.error(e);
        document.getElementById("quiz-area").innerHTML = `
            <h2 style="color:red;">⚠️ Server Error</h2>
            <p>Could not save score. Check console.</p>
            <button class="btn-control" onclick="location.href='dashboard.html'">Go Back</button>
        `;
    }
}

// 7. Timer
function startTimer() {
    timerInterval = setInterval(() => {
        timeLeft--;
        const min = Math.floor(timeLeft / 60);
        const sec = timeLeft % 60;
        const timerEl = document.getElementById("timer");
        if(timerEl) timerEl.innerText = `${min}:${sec < 10 ? '0'+sec : sec}`;
        
        if(timeLeft <= 0) {
            clearInterval(timerInterval);
            alert("Time's Up! Submitting automatically.");
            submitQuiz();
        }
    }, 1000);
}

window.quitQuiz = () => {
    if(confirm("Quit Quiz? Progress will be lost.")) location.href = "dashboard.html";
};