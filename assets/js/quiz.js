// assets/js/quiz.js
const API_BASE = "api/quiz"; 

// 1. Get Exam & Subject from URL
const urlParams = new URLSearchParams(window.location.search);
const subject = urlParams.get('sub'); 
const exam = urlParams.get('exam') || 'general';

// Redirect if no subject is provided
if (!subject) {
    alert("No Subject Selected!");
    window.location.href = "dashboard.html";
}

let questions = [];
let currentIndex = 0;
let userAnswers = {}; 
let timerInterval;
let startTime; 

// 2. Initialize Quiz on Page Load
window.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch(`${API_BASE}/start.php?subject=${subject}&exam=${exam}`);
        const data = await res.json();
        
        // Handle Limits or Errors
        if(data.status === "error") {
            alert(data.message); // "Daily Limit Reached"
            window.location.href = "dashboard.html"; // Or subscription.html
            return;
        }
        
        // Handle Success
        if(data.status === "success" && data.data && data.data.length > 0) {
            questions = data.data;
            
            // UI Updates
            document.getElementById("total-q").innerText = questions.length;
            document.getElementById("loader").style.display = "none";
            document.getElementById("quiz-area").style.display = "block";
            
            // Start Game
            startTime = Date.now(); 
            startTimer(); 
            loadQuestion();
        } else {
            alert("No questions found for this Exam/Subject!");
            window.location.href = "dashboard.html";
        }
    } catch (e) {
        console.error(e);
        alert("Connection Error");
        window.location.href = "dashboard.html";
    }
});

// 3. Render Current Question
function loadQuestion() {
    const q = questions[currentIndex];
    
    // Update Text
    document.getElementById("current-q").innerText = currentIndex + 1;
    document.getElementById("question-text").innerText = q.question_text;
    
    // Update Options
    ['a','b','c','d'].forEach(opt => {
        const btn = document.getElementById(`btn-${opt}`);
        btn.innerText = q[`option_${opt}`];
        btn.className = "option-btn"; // Reset state
        
        // Highlight if already selected
        if(userAnswers[q.id] === opt) {
            btn.classList.add("selected");
        }
    });
}

// 4. Handle User Selection
window.selectOption = (opt) => {
    const qId = questions[currentIndex].id;
    userAnswers[qId] = opt;
    
    // Visual Feedback
    ['a','b','c','d'].forEach(o => document.getElementById(`btn-${o}`).classList.remove("selected"));
    document.getElementById(`btn-${opt}`).classList.add("selected");
};

// 5. Next Button Logic
window.nextQuestion = () => {
    if(currentIndex < questions.length - 1) {
        currentIndex++;
        loadQuestion();
    } else {
        submitQuiz();
    }
};

// 6. Submit Logic
async function submitQuiz() {
    clearInterval(timerInterval); // Stop Timer
    
    const endTime = Date.now();
    const timeTakenSeconds = Math.floor((endTime - startTime) / 1000);

    // Show Loading
    document.getElementById("quiz-area").innerHTML = "<h2 style='color:#fff; text-align:center;'>Submitting Score... 🔄</h2>";

    const payload = {
        subject: subject,
        exam: exam,
        answers: userAnswers,
        time_taken: timeTakenSeconds
    };

    try {
        const res = await fetch(`${API_BASE}/submit.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        
        const result = await res.json();
        
        if(result.status === "success") {
            // Show Results
            document.getElementById("quiz-area").innerHTML = `
                <div style="text-align:center; padding: 20px;">
                    <h1 style="color:#00ffe1;">🎉 Quiz Completed!</h1>
                    <h2 style="font-size:3rem; color:#fff; margin:10px 0;">${result.score} / ${result.total}</h2>
                    <p style="color:#aaa; font-size:1.2rem;">⏱ Time: ${timeTakenSeconds} sec</p>
                    <button class="btn-control btn-next" onclick="location.href='leaderboard.html'" style="margin-top:20px;">Leaderboard 🏆</button>
                    <br><br>
                    <button class="btn-control" onclick="location.href='dashboard.html'" style="background:#333;">🏠 Home</button>
                </div>
            `;
        } else {
            alert("Submission Failed: " + result.message);
            location.href = "dashboard.html";
        }
    } catch (e) {
        console.error(e);
        alert("Server Error during submission.");
        location.href = "dashboard.html";
    }
}

// 7. Timer UI
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

// 8. Quit Button
window.quitQuiz = () => {
    if(confirm("Are you sure you want to quit? Progress will be lost.")) {
        window.location.href = "dashboard.html";
    }
};

// 9. Keyboard Shortcuts
document.addEventListener('keydown', (e) => {
    if(document.getElementById("quiz-area").style.display === "none") return;

    if(e.key >= '1' && e.key <= '4') {  
        const options = ['a', 'b', 'c', 'd'];
        selectOption(options[parseInt(e.key) - 1]);
    } else if(e.key === 'ArrowRight' || e.key === 'Enter') {
        nextQuestion();
    } else if(e.key === 'q' || e.key === 'Q') {
        quitQuiz();
    }
});