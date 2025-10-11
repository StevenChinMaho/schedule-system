let selected = null;

const teachersSchedule = rawSchedule.reduce( ( acc, course ) => {
    const key = course['teacher_id'];
    ( acc[key] ||= [] ).push(course);
    return acc;
}, {});

function clickCell () 
{
    if( selected === null ) 
    {
        selected = this;

        selected.classList.add('selected');
    } 
    else 
    {
        if( selected === this )
        {
            selected.classList.remove('selected');

            selected = null;
        }
    }
}

function displayTeacherSchedule () 
{
    let tid = this.dataset.tid;
    let index = this.dataset.leftIndex;

    document.querySelectorAll("[data-right-index='" + index + "']")[0].innerHTML = tid;
}

function clearTeacherSchedule () 
{
    document.querySelectorAll('.teacher-cell').forEach( cell => {
        cell.innerHTML = "";
    });
}

document.querySelectorAll('.class-cell').forEach(cell => 
{
    cell.addEventListener('click', clickCell );
    cell.addEventListener('mouseenter', displayTeacherSchedule );
    cell.addEventListener('mouseleave', clearTeacherSchedule );
});
