<?php
    require_once('includes/config.php');

    $class_id = (int)$_GET['class_id'] > 0 && (int)$_GET['class_id'] <= 12 ? $_GET['class_id'] : '1';

    $stmt = $pdo->prepare("SELECT 
        tea.teacher_id, 
        tea.teacher_name,
        t.timeslot_id,
        t.weekday,
        t.period,
        sub.subject_name,
        c.class_id,
        c.class_code
    FROM schedule s2
    JOIN teacher tea ON s2.teacher_id = tea.teacher_id
    JOIN timeslot t ON s2.timeslot_id = t.timeslot_id
    JOIN `subject` sub ON s2.subject_id = sub.subject_id 
    JOIN class c ON s2.class_id = c.class_id
    WHERE s2.teacher_id IN (
        SELECT DISTINCT s.teacher_id
        FROM schedule s
        WHERE s.class_id = ?
    )");
    $stmt->execute([$class_id]);
    $raw_schedule = $stmt->fetchAll();

    $class_schedule = [];

    foreach( $raw_schedule as $course )
    {
        if( $course['class_id'] == $class_id )
        {
            if( !isset($class_schedule[$course['period']][$course['weekday']]) )
            {
                $class_schedule[$course['period']][$course['weekday']] = [
                    "timeslot_id" => $course["timeslot_id"],
                    "subject_name" => $course["subject_name"],
                    "teacher_name" => $course["teacher_name"],
                    "teacher_id" => $course["teacher_id"]
                ];
            }
            else
            {
                echo '<script> alert("警告：課表重複"); </script>';
            }
        }
    }
?>
<!DOCTYPE html>
<html>
<head lang='zh-TW'>
    <meta charset="UTF-8">
    <meta name='viewpoint' content='width=device-width, initial-scale=1.0'>
    <title>課表查詢系統</title>
    <link rel='stylesheet' href="css/frame.css">
    <link rel='stylesheet' href='css/ShowSchedule-style.css'>
    <script src='js/main.js' defer></script>
</head>
<body>
    <a href="index.html" class="back-button">← 返回首頁</a>

    <div class='container'>
        <?php
        $stmt = $pdo->prepare("SELECT class_name FROM class WHERE class_id = ?");
        $stmt->execute([$class_id]);
        $class_name = $stmt->fetchColumn();
        ?>

        <header class='page-header'>
            <h1><?php echo htmlspecialchars($class_name); ?> 的課表</h1>
            <div class='header-decoration'></div>
        </header>

        <div class="control-panel">
            <div class="control-content">
                <div class="control-item">
                    <input type="checkbox" id="periodChk" class="toggle-checkbox">
                    <label for="periodChk" class="toggle-text">排除第八節</label>
                </div>

                <div class="control-item">
                    <input type="checkbox" id="classActiveChk" class="toggle-checkbox">
                    <label for="classActiveChk" class="toggle-text">排除社團課與班級活動</label>
                </div>

                <div class="control-item">
                    <input type="checkbox" id="specialChk" class="toggle-checkbox">
                    <label for="specialChk" class="toggle-text">國數調課限制</label>
                </div>
            </div>
        </div>

        <div class='schedule-wrapper'>
            <div class='table-container'>
                <h2 class="table-title">原始課表</h2>
                <table class='schedule-table'>
                    <thead>
                        <tr>
                            <th class='period-header'>節次</th>
                            <th>一</th>
                            <th>二</th>
                            <th>三</th>
                            <th>四</th>
                            <th>五</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            for( $p = 1; $p <= 8; $p++ )
                            {
                                echo "<tr>";
                                echo "<td class='period-cell'>$p</td>";
                                
                                for( $w = 1; $w <= 5; $w++ ) 
                                {
                                    if( isset($class_schedule[$p][$w]) )
                                    {
                                        $c = &$class_schedule[$p][$w];
                                        echo "<td class='course-cell class-cell' data-left-index='" . ($w - 1) * 8 + $p . "' data-tid='". htmlspecialchars( $c['teacher_id'] ) ."'>"; 
                                        echo "<div class='subject-name'>" . htmlspecialchars($c['subject_name']) . "</div>";
                                        echo "<div class='teacher-name'>" . htmlspecialchars($c['teacher_name']) . "</div>"; 
                                        echo "</td>";
                                    }
                                    else
                                    {
                                        echo "<td class='course-cell class-cell empty-cell' data-left-index='" . ($w - 1) * 8 + $p . "'></td>";
                                    }
                                }
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class='table-container'>
                <h2 id='teacherTitle' class="table-title">選取左側課堂來顯示課表</h2>
                <table class='schedule-table'>
                    <thead>
                        <tr>
                            <th class='period-header'>節次</th>
                            <th>一</th>
                            <th>二</th>
                            <th>三</th>
                            <th>四</th>
                            <th>五</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            for( $p = 1; $p <= 8; $p++ )
                            {
                                echo "<tr>";
                                echo "<td class='period-cell'>$p</td>";

                                for( $w = 1; $w <= 5; $w++ ) 
                                {
                                    echo "<td class='course-cell teacher-cell empty-cell' data-right-index='" . ($w - 1) * 8 + $p . "'></td>";
                                }
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <footer class='page-footer'>
            <p>© 2025 Steven Chin | 教師調課系統 | Licensed under MIT License</p>
        </footer>
    </div>
    <script>
        const rawSchedule = <?php echo json_encode( $raw_schedule, JSON_UNESCAPED_UNICODE ); ?>
    </script>
</body>
</html>