<?php
    require_once('includes/config.php');
?>
<!DOCTYPE html>
<html>
<head lang='zh-TW'>
    <meta charset="UTF-8">
    <meta name='viewpoint' content='width=device-width, initial-scale=1.0'>
    <title>課表查詢系統</title>
    <link rel='stylesheet' href='css/style.css'>
    <script src='js/main.js' defer></script>
</head>
<body>
    <a href="index.php" class="back-button">← 返回首頁</a>

    <div class='container'>
        <?php
        $class_id = $_GET['class_id'];

        $stmt = $pdo->prepare("SELECT class_name FROM class WHERE class_id = ?");
        $stmt->execute([$class_id]);
        $class_name = $stmt->fetchColumn();
        ?>

        <header class='page-header'>
            <h1><?php echo htmlspecialchars($class_name); ?> 的課表</h1>
            <div class='header-decoration'></div>
        </header>

        <div class="control-panel">
            <!-- 預留給按鈕和輸入框的空間 -->
            <div class="control-content">
                <!-- 這裡之後可以放置按鈕、輸入框等操作元件 -->
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
                            $stmt = $pdo->prepare("SELECT 
                                sub.subject_name,
                                tea.teacher_name
                            FROM schedule s
                            JOIN timeslot tim ON s.timeslot_id = tim.timeslot_id
                            JOIN subject sub ON s.subject_id = sub.subject_id
                            JOIN teacher tea ON s.teacher_id = tea.teacher_id
                            WHERE s.class_id = ? && tim.period = ?
                            ORDER BY tim.weekday");

                            for( $i = 1; $i <= 8; $i++ )
                            {
                                echo "<tr>";
                                echo "<td class='period-cell'>$i</td>";
                                $stmt->execute([$class_id, $i]);
                                $row = $stmt->fetchAll();

                                $courses = array_pad($row, 5, null);

                                $index = $i;
                                
                                foreach( $courses as $c ) 
                                {
                                    if( $c !== NULL )
                                    {
                                    echo "<td class='course-cell' data-left-index='$index'>"; 
                                    echo "<div class='subject-name'>" . htmlspecialchars($c['subject_name']) . "</div>";
                                    echo "<div class='teacher-name'>" . htmlspecialchars($c['teacher_name']) . "</div>"; 
                                    echo "</td>";
                                    }
                                    else
                                    {
                                        echo "<td class='course-cell empty-cell' data-left-index='$index'></td>";
                                    }
                                    $index += 8;
                                }
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class='table-container'>
                <h2 id='table-title-2' class="table-title">調整後課表</h2>
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
                            $stmt = $pdo->prepare("SELECT 
                                sub.subject_name,
                                tea.teacher_name
                            FROM schedule s
                            JOIN timeslot tim ON s.timeslot_id = tim.timeslot_id
                            JOIN subject sub ON s.subject_id = sub.subject_id
                            JOIN teacher tea ON s.teacher_id = tea.teacher_id
                            WHERE s.class_id = ? && tim.period = ?
                            ORDER BY tim.weekday");
                            
                            $index = 1;

                            for( $i = 1; $i <= 8; $i++ )
                            {
                                echo "<tr>";
                                echo "<td class='period-cell'>$i</td>";
                                $stmt->execute([$class_id, $i]);
                                $row = $stmt->fetchAll();

                                $courses = array_pad($row, 5, null);

                                $index = $i;

                                foreach( $courses as $c ) 
                                {
                                    if( false )//if( $c !== NULL )
                                    {
                                    echo "<td class='course-cell' data-right-index='$index'>"; 
                                    echo "<div class='subject-name'>" . htmlspecialchars($c['subject_name']) . "</div>";
                                    echo "<div class='teacher-name'>" . htmlspecialchars($c['teacher_name']) . "</div>"; 
                                    echo "</td>";
                                    }
                                    else
                                    {
                                        echo "<td class='course-cell empty-cell' data-right-index='$index'></td>";
                                    }
                                    $index += 8;
                                }
                                echo "</tr>";
                            }
                            // $stmt = $pdo->prepare("SELECT 
                            //     sub.subject_name,
                            //     tea.teacher_name
                            // FROM schedule s
                            // JOIN timeslot tim ON s.timeslot_id = tim.timeslot_id
                            // JOIN subject sub ON s.subject_id = sub.subject_id
                            // JOIN teacher tea ON s.teacher_id = tea.teacher_id
                            
                            // ORDER BY tim.weekday");

                            // $stmt->execute();
                            // var_dump($stmt->fetchAll());
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <footer class='page-footer'>
            <p>© 2025 課表查詢系統</p>
        </footer>
    </div>
    <!-- <script>
        document.getElementById('table-title-2').innerHTML = "教師課表2";
    </script> -->
</body>
</html>