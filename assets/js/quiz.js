const API_BASE = "../api/quiz";
const urlParams = new URLSearchParams(window.location.search);
const subject = urlParams.get('sub') || 'gk'; // Default GK

let questions = [];
let currentIndex = 0;
let userAnswers = {}; // { q_id: 'a', q_id: 'b' }
let timerInterval;
let timeLeft = 45;

// 1. Start Quiz
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
            alert("No questions found for this subject!");
            window.location.href = "dashboard.html";
        }
    } catch (e) {
        console.error(e);
        alert("Error loading quiz.");
    }
};

// 2. Load Single Question
function loadQuestion() {
    const q = questions[currentIndex];
    document.getElementById("current-q").innerText = currentIndex + 1;
    document.getElementById("question-text").innerText = q.question_text;
    
    // Reset Buttons
    ['a','b','c','d'].forEach(opt => {
        const btn = document.getElementById(`btn-${opt}`);
        btn.innerText = q[`option_${opt}`];
        btn.classList.remove("selected");
        // Agar pehle select kiya tha to highlight karo
        if(userAnswers[q.id] === opt) btn.classList.add("selected");
    });
}

// 3. Option Selection
window.selectOption = (opt) => {
    const qId = questions[currentIndex].id;
    userAnswers[qId] = opt;
    
    // UI Update
    ['a','b','c','d'].forEach(o => document.getElementById(`btn-${o}`).classList.remove("selected"));
    document.getElementById(`btn-${opt}`).classList.add("selected");
};

// 4. Next / Submit
window.nextQuestion = () => {
    if(currentIndex < questions.length - 1) {
        currentIndex++;
        loadQuestion();
    } else {
        submitQuiz();
    }
};

// 5. Submit Logic
async function submitQuiz() {
    clearInterval(timerInterval);
    document.getElementById("quiz-area").innerHTML = "<h2>Submitting... 🔄</h2>";

    const payload = {
        subject: subject,
        answers: userAnswers
    };

    try {
        const res = await fetch(`${API_BASE}/submit.php`, {
            method: "POST",
            body: JSON.stringify(payload)
        });
        const result = await res.json();
        
        if(result.status === "success") {
            document.getElementById("quiz-area").innerHTML = `
                <h1>🎉 Quiz Completed!</h1>
                <h2 style="font-size:3rem; color:#00ffe1;">${result.score} / ${result.total}</h2>
                <button class="btn-control btn-next" onclick="location.href='dashboard.html'">Back to Dashboard</button>
            `;
        }
    } catch (e) {
        alert("Submission Failed");
    }
}

// 6. Timer
function startTimer() {
    timerInterval = setInterval(() => {
        timeLeft--;
        const min = Math.floor(timeLeft / 60);
        const sec = timeLeft % 60;
        document.getElementById("timer").innerText = `${min}:${sec < 10 ? '0'+sec : sec}`;
        
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