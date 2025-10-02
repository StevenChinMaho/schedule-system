<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>SQL JOIN 練習</title>
    <style>
        body {
            font-family: 'Microsoft JhengHei', Arial, sans-serif;
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .query-box {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .query-box h2 {
            color: #4CAF50;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        pre {
            background-color: #f4f4f4;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .comparison {
            display: flex;
            gap: 20px;
        }
        .comparison > div {
            flex: 1;
        }
        .label {
            background-color: #ff9800;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>🔗 SQL JOIN 實戰練習</h1>

    <!-- 範例 1：不使用 JOIN vs 使用 JOIN -->
    <div class="query-box">
        <h2>範例 1：不使用 JOIN vs 使用 JOIN 的差異</h2>
        
        <div class="comparison">
            <div>
                <span class="label">❌ 不使用 JOIN</span>
                <pre>SELECT * FROM schedule 
WHERE class_id = 1 
LIMIT 3;</pre>
                
                <table>
                    <thead>
                        <tr>
                            <th>schedule_id</th>
                            <th>class_id</th>
                            <th>subject_id</th>
                            <th>teacher_id</th>
                            <th>timeslot_id</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM schedule WHERE class_id = 1 LIMIT 3";
                        $stmt = $pdo->query($sql);
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>{$row['schedule_id']}</td>";
                            echo "<td>{$row['class_id']}</td>";
                            echo "<td>{$row['subject_id']}</td>";
                            echo "<td>{$row['teacher_id']}</td>";
                            echo "<td>{$row['timeslot_id']}</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <p style="color: #999;">😕 只看到一堆 ID，不知道實際內容</p>
            </div>
            
            <div>
                <span class="label">✅ 使用 JOIN</span>
                <pre>SELECT 
    s.schedule_id,
    t.weekday, 
    t.period,
    sub.subject_name,
    tea.teacher_name
FROM schedule s
JOIN timeslot t 
    ON s.timeslot_id = t.timeslot_id
JOIN subject sub 
    ON s.subject_id = sub.subject_id
JOIN teacher tea 
    ON s.teacher_id = tea.teacher_id
WHERE s.class_id = 1
LIMIT 3;</pre>
                
                <table>
                    <thead>
                        <tr>
                            <th>schedule_id</th>
                            <th>星期</th>
                            <th>節次</th>
                            <th>科目</th>
                            <th>教師</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT 
                                    s.schedule_id,
                                    t.weekday, 
                                    t.period,
                                    sub.subject_name,
                                    tea.teacher_name
                                FROM schedule s
                                JOIN timeslot t ON s.timeslot_id = t.timeslot_id
                                JOIN subject sub ON s.subject_id = sub.subject_id
                                JOIN teacher tea ON s.teacher_id = tea.teacher_id
                                WHERE s.class_id = 1
                                LIMIT 3";
                        $stmt = $pdo->query($sql);
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>{$row['schedule_id']}</td>";
                            echo "<td>週{$row['weekday']}</td>";
                            echo "<td>第{$row['period']}節</td>";
                            echo "<td>{$row['subject_name']}</td>";
                            echo "<td>{$row['teacher_name']}</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <p style="color: #4CAF50;">😊 清楚易懂，所有資訊一目了然</p>
            </div>
        </div>
    </div>

    <!-- 範例 2：查詢某個班級的完整課表 -->
    <div class="query-box">
        <h2>範例 2：七年一班的課表（前10筆）</h2>
        <pre>SELECT 
    c.class_name AS '班級',
    CASE t.weekday
        WHEN 1 THEN '週一'
        WHEN 2 THEN '週二'
        WHEN 3 THEN '週三'
        WHEN 4 THEN '週四'
        WHEN 5 THEN '週五'
    END AS '星期',
    CONCAT('第', t.period, '節') AS '節次',
    sub.subject_name AS '科目',
    tea.teacher_name AS '教師'
FROM schedule s
JOIN class c ON s.class_id = c.class_id
JOIN timeslot t ON s.timeslot_id = t.timeslot_id
JOIN subject sub ON s.subject_id = sub.subject_id
JOIN teacher tea ON s.teacher_id = tea.teacher_id
WHERE c.class_id = 1
ORDER BY t.weekday, t.period
LIMIT 10;</pre>
        
        <table>
            <thead>
                <tr>
                    <th>班級</th>
                    <th>星期</th>
                    <th>節次</th>
                    <th>科目</th>
                    <th>教師</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT 
                            c.class_name,
                            CASE t.weekday
                                WHEN 1 THEN '週一'
                                WHEN 2 THEN '週二'
                                WHEN 3 THEN '週三'
                                WHEN 4 THEN '週四'
                                WHEN 5 THEN '週五'
                            END AS weekday_text,
                            CONCAT('第', t.period, '節') AS period_text,
                            sub.subject_name,
                            tea.teacher_name
                        FROM schedule s
                        JOIN class c ON s.class_id = c.class_id
                        JOIN timeslot t ON s.timeslot_id = t.timeslot_id
                        JOIN subject sub ON s.subject_id = sub.subject_id
                        JOIN teacher tea ON s.teacher_id = tea.teacher_id
                        WHERE c.class_id = 1
                        ORDER BY t.weekday, t.period
                        LIMIT 10";
                $stmt = $pdo->query($sql);
                while ($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>{$row['class_name']}</td>";
                    echo "<td>{$row['weekday_text']}</td>";
                    echo "<td>{$row['period_text']}</td>";
                    echo "<td>{$row['subject_name']}</td>";
                    echo "<td>{$row['teacher_name']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- 範例 3：查詢某位教師的課表 -->
    <div class="query-box">
        <h2>範例 3：何品萱老師的課表</h2>
        <pre>SELECT 
    c.class_name AS '班級',
    sub.subject_name AS '科目',
    t.weekday AS '星期',
    t.period AS '節次'
FROM schedule s
JOIN class c ON s.class_id = c.class_id
JOIN subject sub ON s.subject_id = sub.subject_id
JOIN timeslot t ON s.timeslot_id = t.timeslot_id
WHERE s.teacher_id = 1  -- 何品萱老師
ORDER BY t.weekday, t.period;</pre>
        
        <table>
            <thead>
                <tr>
                    <th>班級</th>
                    <th>科目</th>
                    <th>星期</th>
                    <th>節次</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT 
                            c.class_name,
                            sub.subject_name,
                            t.weekday,
                            t.period
                        FROM schedule s
                        JOIN class c ON s.class_id = c.class_id
                        JOIN subject sub ON s.subject_id = sub.subject_id
                        JOIN timeslot t ON s.timeslot_id = t.timeslot_id
                        WHERE s.teacher_id = 1
                        ORDER BY t.weekday, t.period";
                $stmt = $pdo->query($sql);
                $count = 0;
                while ($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>{$row['class_name']}</td>";
                    echo "<td>{$row['subject_name']}</td>";
                    echo "<td>週{$row['weekday']}</td>";
                    echo "<td>第{$row['period']}節</td>";
                    echo "</tr>";
                    $count++;
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
