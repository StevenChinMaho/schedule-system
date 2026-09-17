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
let selectedA = null;
let selectedB = null;

function checkExchange() 
{
    const tid = selectedA.dataset.tid;
    const selectedAName = selectedA.querySelector(".subject-name").innerHTML;

    if ( specialChk.checked && (selectedAName === "國文" || selectedAName === "數學") ) {
        classCells.forEach( cell => {
            const SN = cell.querySelector(".subject-name").innerHTML;
            if ( SN !== "國文" && SN !== "數學") cell.classList.add("excludeSpecial");
        });
    }

    teachersSchedule[tid].forEach( course => 
    {
        let timeslot_id = course["timeslot_id"];

        let cell = document.querySelector("[data-left-index='" + timeslot_id + "']");

        if( !cell.classList.contains( "selectedA" ) )
        {
            cell.classList.add( "unavailable" );
        }
    });

    classCells.forEach( cell => 
    {
        if( !cell.classList.contains( "empty-cell" )&& !cell.classList.contains( "selectedA" ) && !cell.classList.contains( "unavailable" ) )
        {
            let exchangeTid = cell.dataset.tid;

            if( teachersSchedule[exchangeTid].some( course => course["timeslot_id"] == selectedA.dataset.leftIndex ) )
            {
                cell.classList.add( "unavailable" );
            }
        }
    });
}

function resetExchange()
{
    if ( selectedA ) selectedA.classList.remove("selectedA");
    if ( selectedB ) selectedB.classList.remove("selectedB");

    selectedA = null;

    selectedB = null;

    classCells.forEach( cell => 
    {
        cell.classList.remove( "unavailable", "excludeSpecial" );
    });
}

function clickCell() 
{
    if( this.classList.contains("empty-cell") ) return 0;

    if( selectedA === null && !this.classList.contains("excludePeriod") && !this.classList.contains("excludeClassActive") ) 
    {
        selectedA = this;

        selectedA.classList.add("selectedA");

        checkExchange();
    } 
    else 
    {
        if( selectedA === this )
        {
            resetExchange();
        }
        else if( !this.classList.contains("unavailable") && !this.classList.contains("excludePeriod") && !this.classList.contains("excludeClassActive") && !this.classList.contains("excludeSpecial") )
        {
            if( selectedB === null )
            {
                selectedB = this;

                selectedB.classList.add("selectedB");
            }
            else{
                selectedB.classList.remove("selectedB");

                selectedB = this;

                selectedB.classList.add("selectedB");
            }
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

/**
 * 依 scheduleConfig 給定的時段切換排除標記。
 * 時段清單由 showSchedule.php 依當前學校的作息算出，各校節數與社團課
 * 時間不同，故不在此判斷。
 */
function toggleExcluded( slotIds, className, enabled )
{
    const ids = slotIds.map(String);

    classCells.forEach( cell => {
        if ( ids.includes(cell.dataset.leftIndex) ) cell.classList.toggle(className, enabled);
    });
}

periodChk.addEventListener( "change", function () {
    resetExchange();

    toggleExcluded( scheduleConfig.lastPeriodSlots, "excludePeriod", periodChk.checked );
});

classActiveChk.addEventListener( "change", function () {
    resetExchange();

    toggleExcluded( scheduleConfig.activitySlots, "excludeClassActive", classActiveChk.checked );
});

specialChk.addEventListener("change", resetExchange);

classCells.forEach(cell => 
{
    cell.addEventListener("click", clickCell );
    cell.addEventListener("mouseenter", displayTeacherSchedule );
    cell.addEventListener("mouseleave", clearTeacherSchedule );
});
