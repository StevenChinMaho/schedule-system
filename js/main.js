const teachersSchedule = rawSchedule.reduce( ( acc, course ) => {
    const key = course["teacher_id"];
    ( acc[key] ||= [] ).push(course);
    return acc;
}, {});

const classCells = document.querySelectorAll(".class-cell");
const teacherCells = document.querySelectorAll(".teacher-cell");
const teacherSchedleTitle = document.getElementById("teacherTitle");
const periodChk = document.getElementById("periodChk");
const classActiveChk = document.getElementById("classActiveChk");
const specialChk = document.getElementById("specialChk");
let selected = null;

function checkExchange() 
{
    const tid = selected.dataset.tid;
    const selectedName = selected.querySelector(".subject-name").innerHTML;

    if ( specialChk.checked && (selectedName === "國文" || selectedName === "數學") ) {
        classCells.forEach( cell => {
            const SN = cell.querySelector(".subject-name").innerHTML;
            if ( SN !== "國文" && SN !== "數學") cell.classList.add("excludeSpecial");
        });
    }

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
    if ( selected ) selected.classList.remove("selected");

    selected = null;

    classCells.forEach( cell => 
    {
        cell.classList.remove( "unavailable", "available", "excludeSpecial" );
    });
}

function clickCell() 
{
    if( this.classList.contains("empty-cell") ) return 0;

    if( selected === null && !this.classList.contains("excludePeriod") && !this.classList.contains("excludeClassActive") ) 
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

function clearTeacherSchedule() 
{
    teacherSchedleTitle.textContent = "選取左側課堂來顯示課表";

    teacherCells.forEach( cell => 
    {
        cell.innerHTML = "";
    });
}

periodChk.addEventListener( "change", function () {
    if (periodChk.checked) {
        classCells.forEach( cell => {
            if (cell.dataset.leftIndex % 8 === 0) {
                if (cell.classList.contains("selected")) resetExchange();
                cell.classList.add("excludePeriod");
            }
        })
    } else {
        classCells.forEach( cell => {
            if (cell.dataset.leftIndex % 8 === 0) cell.classList.remove("excludePeriod");
        })
    }
});

classActiveChk.addEventListener( "change", function () {
    if (classActiveChk.checked) {
        classCells.forEach( cell => {
            if (cell.dataset.leftIndex == 14 || cell.dataset.leftIndex == 15) {
                if (cell.classList.contains("selected")) resetExchange();
                cell.classList.add("excludeClassActive");
            }
        })
    } else {
        classCells.forEach( cell => {
            if (cell.dataset.leftIndex == 14 || cell.dataset.leftIndex == 15) cell.classList.remove("excludeClassActive");
        })
    }
});

specialChk.addEventListener("change", resetExchange);

classCells.forEach(cell => 
{
    cell.addEventListener("click", clickCell );
    cell.addEventListener("mouseenter", displayTeacherSchedule );
    cell.addEventListener("mouseleave", clearTeacherSchedule );
});
