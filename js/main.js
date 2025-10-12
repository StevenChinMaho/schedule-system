const teachersSchedule = rawSchedule.reduce( ( acc, course ) => {
    const key = course["teacher_id"];
    ( acc[key] ||= [] ).push(course);
    return acc;
}, {});

let selected = null;
let classCells = document.querySelectorAll(".class-cell");
let teacherCells = document.querySelectorAll(".teacher-cell");
let teacherSchedleTitle = document.getElementById("teacherTitle");
let periodChk = document.getElementById("periodChk");
let classActiveChk = document.getElementById("classActiveChk");
let specialChk = document.getElementById("specialChk");

function checkExchange() 
{
    let tid = selected.dataset.tid;

    teachersSchedule[tid].forEach( course => 
    {
        let timeslot_id = course["timeslot_id"];

        let cell = document.querySelector("[data-left-index='" + timeslot_id + "']");

        if( !cell.classList.contains( "selected" ) )
        {
            cell.classList.add( "unavailable" );
        }
    });

    classCells.forEach( cell => 
    {
        if( !cell.classList.contains( "empty-cell" )&& !cell.classList.contains( "selected" ) && !cell.classList.contains( "unavailable" ) )
        {
            let exchangeTid = cell.dataset.tid;

            if( teachersSchedule[exchangeTid].some( course => course["timeslot_id"] == selected.dataset.leftIndex ) )
            {
                cell.classList.add( "unavailable" );
            }
        }
    });
}

function resetExchange()
{
    selected.classList.remove("selected");

    selected = null;

    classCells.forEach( cell => 
    {
        cell.classList.remove( "unavailable", "available" );
    });
}

function clickCell() 
{
    if( this.classList.contains("empty-cell") ) return 0;

    if( selected === null ) 
    {
        selected = this;

        selected.classList.add("selected");

        checkExchange();
    } 
    else 
    {
        if( selected === this )
        {
            resetExchange();
        }
    }
}

function displayTeacherSchedule() 
{
    if ( this.classList.contains("empty-cell") ) return 0;

    let tid = this.dataset.tid;

    teacherSchedleTitle.textContent = teachersSchedule[tid][0]["teacher_name"] + " 老師的課表";
    
    teachersSchedule[tid].forEach( course => {
        let HTML = "<div class='subject-name'>" + course["subject_name"] + "</div>" +
                    "<div class='teacher-name'>" + course["class_code"] + "</div>";

        document.querySelector("[data-right-index='" + course["timeslot_id"] + "']").innerHTML = HTML;
    });

}

document.querySelector("#periodChk").addEventListener( "change", () => {
    console.log("changed!");
});

document.querySelector("#classActiveChk").addEventListener( "change", () => {
    console.log("active!")
})

function clearTeacherSchedule() 
{
    teacherSchedleTitle.textContent = "選取左側課堂來顯示課表";

    teacherCells.forEach( cell => 
    {
        cell.innerHTML = "";
    });
}

classCells.forEach(cell => 
{
    cell.addEventListener("click", clickCell );
    cell.addEventListener("mouseenter", displayTeacherSchedule );
    cell.addEventListener("mouseleave", clearTeacherSchedule );
});
