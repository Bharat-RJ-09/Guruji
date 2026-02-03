// assets/js/quiz.js

const API_BASE = "api/quiz"; 

// 1. Get Subject
const urlParams = new URLSearchParams(window.location.search);
const subject = urlParams.get('sub'); 

if (!subject) {
    alert("No Subject Selected!");
    window.location.href = "dashboard.html";
}

let questions = [];
let currentIndex = 0;
let userAnswers = {}; 
let timerInterval;
let startTime; // Store when quiz started

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
            
            // Start Clock
            startTime = Date.now(); 
            startTimer(); 
            loadQuestion();
        } else {
            alert(data.message || "No questions found!");
            window.location.href = "dashboard.html";
        }
    } catch (e) {
        console.error(e);
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
        btn.className = "option-btn"; // Reset classes
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

// 6. Submit Logic (With Time Calculation)
async function submitQuiz() {
    clearInterval(timerInterval);
    
    // Calculate Time Taken in Seconds
    const endTime = Date.now();
    const timeTakenSeconds = Math.floor((endTime - startTime) / 1000);

    document.getElementById("quiz-area").innerHTML = "<h2 style='color:#fff;'>Submitting Score... 🔄</h2>";

    const payload = {
        subject: subject,
        answers: userAnswers,
        time_taken: timeTakenSeconds // ✅ Sending time to backend
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
                <div style="text-align:center;">
                    <h1 style="color:#00ffe1;">🎉 Quiz Completed!</h1>
                    <h2 style="font-size:3rem; color:#fff; margin:10px 0;">${result.score} / ${result.total}</h2>
                    <p style="color:#aaa; font-size:1.2rem;">⏱ Time: ${timeTakenSeconds} sec</p>
                    <button class="btn-control btn-next" onclick="location.href='dashboard.html'" style="margin-top:20px;">Leaderboard 🏆</button>
                </div>
            `;
        } else {
            alert("Submission Failed: " + result.message);
            location.href = "dashboard.html";
        }
    } catch (e) {
        console.error(e);
        alert("Server Error");
    }
}

// 7. Timer UI (Count Up)
function startTimer() {
    timerInterval = setInterval(() => {
        const now = Date.now();
        const diff = Math.floor((now - startTime) / 1000);
        
        const min = Math.floor(diff / 60);
        const sec = diff % 60;
        
        const timerEl = document.getElementById("timer");
        if(timerEl) timerEl.innerText = `${min}:${sec < 10 ? '0'+sec : sec}`;
    }, 1000);
}

window.quitQuiz = () => {
    if(confirm("Quit Quiz?")) location.href = "dashboard.html";
};

// 8. Keyboard Navigation
document.addEventListener('keydown', (e) => {
    if(e.key >= '1' && e.key <= '4') {  
        const options = ['a', 'b', 'c', 'd'];
        selectOption(options[parseInt(e.key) - 1]);
    } else if(e.key === 'ArrowRight') {
        nextQuestion();
    }    else if(e.key === 'q' || e.key === 'Q') {
        quitQuiz();
    }
});

// End of quiz.js