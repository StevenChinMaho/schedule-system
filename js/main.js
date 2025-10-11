const teachersSchedule = rawSchedule.reduce( ( acc, course ) => {
    const key = course['teacher_id'];
    ( acc[key] ||= [] ).push(course);
    return acc;
}, {});

let selected = null;

function checkExchange() 
{
    let tid = selected.dataset.tid;

    teachersSchedule[tid].forEach( course => 
    {
        console.log(course["timeslot_id"]);

        let timeslot_id = course["timeslot_id"];

        let cell = document.querySelector("[data-left-index='" + timeslot_id + "']");

        if( !cell.classList.contains( "selected" ) )
        {
            cell.classList.add( "unavailable" );
        }
    });

    document.querySelectorAll(".class-cell").forEach( cell => 
    {
        if( !cell.classList.contains( "selected" ) && !cell.classList.contains( "unavailable" ) )
        {
            let exchangeTid = cell.dataset.tid;

            console.log(teachersSchedule[exchangeTid]);

            if( teachersSchedule[exchangeTid].some( course => course["timeslot_id"] == selected.dataset.leftIndex ) )
            {
                cell.classList.add( "unavailable" );
                console.log( "NO" );
            }
            // else
            // {
            //     cell.classList.add( "available" );
            // }
        }
        else console.log(cell);
    });
}

function resetExchange()
{
    selected = null;

    document.querySelectorAll( ".class-cell" ).forEach( cell => 
    {
        cell.classList.remove( "unavailable", "available" );
    });
}

function clickCell() 
{
    if( selected === null ) 
    {
        selected = this;

        selected.classList.add('selected');

        checkExchange();
    } 
    else 
    {
        if( selected === this )
        {
            selected.classList.remove('selected');

            resetExchange();
        }
    }
}

function displayTeacherSchedule() 
{
    let tid = this.dataset.tid;
    
    // console.log(teachersSchedule[tid]);

    document.getElementById("teacher-title").textContent = teachersSchedule[tid][0]['teacher_name'] + " 老師的課表";
    
    teachersSchedule[tid].forEach( course => {
        let HTML = "<div class='subject-name'>" + course['subject_name'] + "</div>" +
                    "<div class='teacher-name'>" + course['class_code'] + "</div>";

        document.querySelector("[data-right-index='" + course['timeslot_id'] + "']").innerHTML = HTML;
    });

}

function clearTeacherSchedule() 
{
    document.getElementById("teacher-title").textContent = "選取左側課堂來顯示課表";

    document.querySelectorAll('.teacher-cell').forEach( cell => 
    {
        cell.innerHTML = "";
    });
}

document.querySelectorAll('.class-cell').forEach(cell => 
{
    cell.addEventListener('click', clickCell );
    cell.addEventListener('mouseenter', displayTeacherSchedule );
    cell.addEventListener('mouseleave', clearTeacherSchedule );
});
