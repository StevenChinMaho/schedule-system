let selected = null;

const teachersSchedule = rawSchedule.reduce( ( acc, course ) => {
    const key = course['teacher_id'];
    ( acc[key] ||= [] ).push(course);
    return acc;
}, {});

function clickToSelect () 
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

document.querySelectorAll('.course-cell').forEach(cell => {
  cell.addEventListener('click', clickToSelect );
});
