<?php
    require_once("includes/config.php");
?>

<!DOCTYPE html>
<html lang="zh-TW">
    <head>
        <meta charset="UTF-8">
        <title>咕嚕靈波測試網站</title>
    </head>
    <body>
        <h1>I Feel Good</h1>
        <p>
            <a href="tamamitsune.html">Click Me!</a>
            <br>
            <a href="learning_files">練習用</a>
            <br>
        </p>
        <hr>
        <form method="post" action="sendForm.php">
            name: <input type="text" name="name"/><br>
            age: <input type="text" name="age"/><br>
            <input type="submit">
        </form>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>班級ID</th>
                    <th>科目ID</th>
                    <th>教師ID</th>
                    <th>時段ID</th>
                </tr>    
            </thead>
            <tbody>
            <?php
                $sql = "SELECT class_id, subject_id, teacher_id, timeslot_id FROM schedule LIMIT 40";
                $stmt = $pdo->query($sql);
                $schedules = $stmt->fetchAll();

                foreach($schedules as $s)
                {
                    echo "<tr>";
                    echo "<td>" . $s['class_id'] . "</td>";
                    echo "<td>" . $s['subject_id'] . "</td>";
                    echo "<td>" . $s['teacher_id'] . "</td>";
                    echo "<td>" . $s['timeslot_id'] . "</td>";
                    echo "</tr>";
                }
                
            ?>
            </tbody>
        </table>
        <?php
            // $sql = "SELECT COUNT(DISTINCT subject_id) FROM schedule";
            // $stmt = $pdo->query($sql);
            // $schedules = $stmt->fetchALL();
            
            // var_dump($schedules);
        ?>
    </body>
</html>