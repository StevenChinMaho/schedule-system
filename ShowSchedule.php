<?php
    require_once('includes/config.php');
?>
<!DOCTYPE html>
<html>
<head lang='zh-TW'>
    <meta charset="UTF-8">
    <title>課表查詢</title>
</head>
<body>
    <?php
    $class_id = $_GET['class_id'];

    $stmt = $pdo->prepare("SELECT class_name FROM class WHERE class_id = ?");
    $stmt->execute([$class_id]);
    $class_name = $stmt->fetchColumn();

    echo "<h1>" . $class_name . " 的課表</h1><br>";
    ?>
    <div>
        <table>
            <thead>
                <tr>
                    <th>節次</th>
                    <th>一</th>
                    <th>二</th>
                    <th>三</th>
                    <th>四</th>
                    <th>五</th>
                </tr>
            </thead>
            <tbody>
                <!-- <tr>
                <td>1</td>
                    <td><div>數學</div><div>金怡安</div></td>
                    <td><div>數學</div><div>金怡安</div></td>
                    <td><div>數學</div><div>金怡安</div></td>
                    <td><div>數學</div><div>金怡安</div></td>
                    <td><div>數學</div><div>金怡安</div></td>
                </tr> -->
                <?php
                    $stmt = $pdo->prepare("SELECT 
                        sub.subject_name,
                        tea.teacher_name
                    FROM schedule s
                    JOIN timeslot tim ON s.timeslot_id = tim.timeslot_id
                    JOIN subject sub ON s.subject_id = sub.subject_id
                    JOIN teacher tea ON s.teacher_id = tea.teacher_id
                    WHERE s.class_id = ? && tim.period = ?
                    ORDER BY tim.weekday");
                    $stmt->execute([$class_id, 1]);
                    
                    for( $i = 1; $i <= 8; $i++ )
                    {
                        echo "<tr>";
                        echo "<td>$i</td>";
                        $stmt->execute([$class_id, $i]);
                        $row = $stmt->fetchAll(); //array(5) { [0]=> array(2) { ["subject_name"]=> string(6) "國文" ["teacher_name"]=> string(9) "黃佩筠" } [1]=> array(2) { ["subject_name"]=> string(6) "家政" ["teacher_name"]=> string(9) "陳璟賢" } [2]=> array(2) { ["subject_name"]=> string(6) "體育" ["teacher_name"]=> string(9) "梁景彥" } [3]=> array(2) { ["subject_name"]=> string(6) "國文" ["teacher_name"]=> string(9) "黃佩筠" } [4]=> array(2) { ["subject_name"]=> string(12) "生活科技" ["teacher_name"]=> string(9) "黃詩淳" } }

                        foreach( $row as $c ) 
                        {
                            echo "<td><div>" . $c['subject_name'] . "</div><div>" . $c['teacher_name'] . "</div></td>";
                        }
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>