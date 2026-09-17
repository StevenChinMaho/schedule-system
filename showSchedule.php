<?php
    require_once('includes/config.php');

    /**
     * 將星期數字轉為中文標籤，例如 1 -> 一。
     */
    function weekday_label( int $weekday ): string
    {
        $numerals = [1 => '一', '二', '三', '四', '五', '六', '日'];

        return $numerals[$weekday] ?? (string)$weekday;
    }

    // 班級以資料庫為準，找不到就退回該校的第一個班級
    $stmt = $pdo->prepare("SELECT class_id, class_name, class_code FROM class WHERE class_id = ?");
    $stmt->execute([ (int)( $_GET['class_id'] ?? 0 ) ]);
    $class = $stmt->fetch();

    if ( $class === false )
    {
        $class = $pdo->query("SELECT class_id, class_name, class_code FROM class ORDER BY class_code LIMIT 1")->fetch();
    }

    if ( $class === false )
    {
        render_error_page('尚無課表資料', '此學校尚未匯入班級資料，請聯繫系統管理員。', 503);
    }

    $class_id = $class["class_id"];
    $class_name = $class["class_name"];
    $class_code = $class["class_code"];

    /*
     * 課表的天數與每日節數直接由 timeslot 資料表推得，不寫死在程式裡，
     * 各校作息不同時無須改動程式碼。
     *
     * $slot_ids[節次][星期] 存放真正的 timeslot_id，供表格儲存格使用；
     * 過去是以 (星期-1)*8+節次 推算，等同假設 timeslot_id 必須照該公式編號。
     */
    $timeslots = $pdo->query("SELECT timeslot_id, weekday, period FROM timeslot")->fetchAll();

    $slot_ids = [];
    $max_period = 0;
    $max_weekday = 0;

    foreach( $timeslots as $slot )
    {
        $slot_ids[ $slot["period"] ][ $slot["weekday"] ] = $slot["timeslot_id"];

        $max_period = max( $max_period, (int)$slot["period"] );
        $max_weekday = max( $max_weekday, (int)$slot["weekday"] );
    }

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
            $class_schedule[$course['period']][$course['weekday']] = [
                "timeslot_id" => $course["timeslot_id"],
                "subject_name" => $course["subject_name"],
                "teacher_name" => $course["teacher_name"],
                "teacher_id" => $course["teacher_id"]
            ];
        }
    }

    // 「排除最後一節」與「排除社團課與班級活動」所對應的實際時段
    $last_period_slots = array_values( $slot_ids[$max_period] ?? [] );

    $activity_slots = [];

    foreach( SCHOOL_ACTIVITY_SLOTS as [$weekday, $period] )
    {
        if( isset($slot_ids[$period][$weekday]) )
        {
            $activity_slots[] = $slot_ids[$period][$weekday];
        }
    }

    /**
     * 記錄課表查詢。輸出至 stderr，由容器的 log driver 收集：
     * 以 `docker logs schedule-php` 檢視。
     */
    function log_access($class_id) 
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        error_log(sprintf(
            "ACCESS | School: %s | IP: %s | Class: %s | UA: %s",
            SCHOOL_KEY,
            $ip,
            $class_id,
            $user_agent
        ));
    }

    log_access($class_id);
?>
<!DOCTYPE html>
<html>
<head lang='zh-TW'>
    <meta charset="UTF-8">
    <meta name='viewpoint' content='width=device-width, initial-scale=1.0'>
    <title>課表查詢系統 - <?php echo htmlspecialchars($class_code)?></title>
    <link rel='stylesheet' href="css/frame.css?v=<?php echo $asset_versions["frame.css"];?>">
    <link rel='stylesheet' href='css/showSchedule-style.css?v=<?php echo $asset_versions["showSchedule-style.css"];?>'>
    <script src='js/main.js?v=<?php echo $asset_versions["main.js"];?>' defer></script>
</head>
<body>
    <a href="index.php" class="back-button">← 返回首頁</a>

    <div class='container'>
        <header class='page-header'>
            <h1><?php echo htmlspecialchars($class_name); ?> 的課表</h1>
            <div class='header-decoration'></div>
        </header>

        <div class="control-panel">
            <div class="control-content">
                <div class="control-item">
                    <input type="checkbox" id="periodChk" class="toggle-checkbox">
                    <label for="periodChk" class="toggle-text">排除第<?php echo htmlspecialchars($max_period); ?>節</label>
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
                            <?php for( $w = 1; $w <= $max_weekday; $w++ ): ?>
                                <th><?php echo htmlspecialchars(weekday_label($w)); ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            for( $p = 1; $p <= $max_period; $p++ )
                            {
                                echo "<tr>";
                                echo "<td class='period-cell'>$p</td>";

                                for( $w = 1; $w <= $max_weekday; $w++ ) 
                                {
                                    // 該校此時段不存在（例如某天節數較少）
                                    if( !isset($slot_ids[$p][$w]) )
                                    {
                                        echo "<td class='course-cell empty-cell'></td>";
                                        continue;
                                    }

                                    $index = htmlspecialchars( $slot_ids[$p][$w] );

                                    if( isset($class_schedule[$p][$w]) )
                                    {
                                        $c = $class_schedule[$p][$w];
                                        echo "<td class='course-cell class-cell' data-left-index='" . $index . "' data-tid='". htmlspecialchars( $c['teacher_id'] ) ."'>"; 
                                        echo "<div class='subject-name'>" . htmlspecialchars($c['subject_name']) . "</div>";
                                        echo "<div class='teacher-name'>" . htmlspecialchars($c['teacher_name']) . "</div>"; 
                                        echo "</td>";
                                    }
                                    else
                                    {
                                        echo "<td class='course-cell class-cell empty-cell' data-left-index='" . $index . "'></td>";
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
                            <?php for( $w = 1; $w <= $max_weekday; $w++ ): ?>
                                <th><?php echo htmlspecialchars(weekday_label($w)); ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            for( $p = 1; $p <= $max_period; $p++ )
                            {
                                echo "<tr>";
                                echo "<td class='period-cell'>$p</td>";

                                for( $w = 1; $w <= $max_weekday; $w++ ) 
                                {
                                    if( !isset($slot_ids[$p][$w]) )
                                    {
                                        echo "<td class='course-cell empty-cell'></td>";
                                        continue;
                                    }

                                    echo "<td class='course-cell teacher-cell empty-cell' data-right-index='" . htmlspecialchars( $slot_ids[$p][$w] ) . "'></td>";
                                }
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="exchange-panel">

        </div>

        <footer class='page-footer'>
            <p>© 2025 Steven Chin | 調課查詢系統 | Licensed under MIT License</p>
        </footer>
    </div>
    <script>
        const rawSchedule = <?php echo json_encode( $raw_schedule, JSON_UNESCAPED_UNICODE ); ?>;

        // 各校作息不同，兩個排除選項對應的實際時段由後端算出
        const scheduleConfig = {
            lastPeriodSlots: <?php echo json_encode( $last_period_slots ); ?>,
            activitySlots: <?php echo json_encode( $activity_slots ); ?>
        };
    </script>
</body>
</html>