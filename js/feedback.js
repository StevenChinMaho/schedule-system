const form = document.getElementById("feedbackForm");
const textarea = document.getElementById("feedback_content");
const charCount = document.getElementById("char_count");

let isDirty = false;
let isSubmitting = false;

function changed() {
    isDirty = true;
}

function updateCharCount() {
    const count = textarea.value.trim().length;
    charCount.textContent = count;
    if(count >= 1000) charCount.style.color = "#e74c3c";
    else if (count > 900) charCount.style.color = "#f39c12";
    else charCount.style.color = "#667eea";
}

updateCharCount();

textarea.addEventListener("input", updateCharCount);

form.addEventListener("submit", (e) => {
    if (textarea.value.trim().length < 10) {
        e.preventDefault();
        alert("回饋內容請至少輸入10個字");
        return;
    } else if (textarea.value.trim().length > 1000) {
        e.preventDefault();
        alert("回饋內容請勿超過1000個字");
        return;
    }
    isSubmitting = true;
});

window.addEventListener("beforeunload", (e) => {
    if (isDirty && !isSubmitting) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});