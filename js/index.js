const classes = {
    '7': [
        {value: "1", text: "七年一班"},
        {value: "2", text: "七年二班"},
        {value: "3", text: "七年三班"},
        {value: "4", text: "七年四班"}
    ],
    '8': [
        {value: "5", text: "八年一班"},
        {value: "6", text: "八年二班"},
        {value: "7", text: "八年三班"},
        {value: "8", text: "八年四班"}
    ],
    '9': [
        {value: "9", text: "九年一班"},
        {value: "10", text: "九年二班"},
        {value: "11", text: "九年三班"},
        {value: "12", text: "九年四班"}
    ]
}

let selectGrade = document.getElementById("grade");
let selectClass = document.getElementById("class_id");
let submitButton = document.getElementById("submitBtn");
let classGroup = document.getElementById("classGroup");

selectGrade.addEventListener("change", function () {
    const selectedGrade = this.value;
    
    selectClass.innerHTML = '<option value="">-- 請先選擇年級 --</option>';

    if (selectedGrade) {
        classes[selectedGrade].forEach(aClass => {
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