// classesByGrade 由 index.php 依當前學校的資料庫產生

let selectGrade = document.getElementById("grade");
let selectClass = document.getElementById("class_id");
let submitButton = document.getElementById("submitBtn");
let classGroup = document.getElementById("classGroup");

selectGrade.addEventListener("change", function () {
    const selectedGrade = this.value;
    
    selectClass.innerHTML = '<option value="">-- 請先選擇年級 --</option>';

    if (selectedGrade) {
        classesByGrade[selectedGrade].forEach(aClass => {
            selectClass.innerHTML += '<option value="' + aClass['value'] + '">' + aClass['text'] + '</option>';
        });

        classGroup.style.display = "block";
    }
    submitButton.disabled = true;
});

selectClass.addEventListener("change", function () {
    const selectedClass = this.value;

    if (selectedClass) {
        submitButton.disabled = false;
    } else {
        submitButton.disabled = true;
    }
});

document.getElementById("classForm").addEventListener("submit", function (e) {
    if (!selectClass.value) {
        e.preventDefault();
        alert("請選擇班級");
    }
});