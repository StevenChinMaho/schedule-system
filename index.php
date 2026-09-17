<?php
    require_once("includes/config.php");

    /**
     * 將年級數字轉為中文標籤，例如 7 -> 七年級。
     */
    function grade_label( string $digit ): string
    {
        $numerals = [1 => '一', '二', '三', '四', '五', '六', '七', '八', '九'];

        return ( $numerals[(int)$digit] ?? $digit ) . '年級';
    }

    // 班級清單改由資料庫提供，各校班級數與命名不同，不再寫死於前端
    $stmt = $pdo->query("SELECT class_id, class_code, class_name FROM class ORDER BY class_code");

    $classes_by_grade = [];

    foreach( $stmt->fetchAll() as $class )
    {
        $grade = substr( $class["class_code"], 0, 1 );

        $classes_by_grade[$grade][] = [
            "value" => $class["class_id"],
            "text"  => $class["class_name"]
        ];
    }
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(SCHOOL_NAME); ?>調課查詢系統 - 首頁</title>
    <link rel="stylesheet" href="css/frame.css?v=<?php echo $asset_versions["frame.css"];?>">
    <link rel="stylesheet" href="css/index-style.css?v=<?php echo $asset_versions["index-style.css"];?>">
    <script>
        const classesByGrade = <?php echo json_encode( $classes_by_grade, JSON_UNESCAPED_UNICODE ); ?>;
    </script>
    <script src="js/index.js?v=<?php echo $asset_versions["index.js"];?>" defer></script>
</head>
<body>
    <div class="container">
        <header class="page-header">
            <h1><?php echo htmlspecialchars(SCHOOL_NAME); ?> - 調課查詢系統</h1>
            <div class="header-decoration"></div>
            <p class="subtitle">請選擇要查詢的班級</p>
        </header>

        <div class="main-content">
            <div class="selection-card">
                <form method="get" action="showSchedule.php" id="classForm">
                    <div class="form-group">
                        <label for="grade">選擇年級</label>
                        <select name="grade" id="grade" class="form-select" required>
                            <option value="">-- 請選擇年級 --</option>
                            <?php foreach( array_keys($classes_by_grade) as $grade ): ?>
                                <option value="<?php echo htmlspecialchars($grade); ?>"><?php echo htmlspecialchars(grade_label($grade)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="classGroup" style="display: none;">
                        <label for="class_id">選擇班級</label>
                        <select name="class_id" id="class_id" class="form-select" required>
                            <option value="">-- 請先選擇年級 --</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-button" id="submitBtn" disabled>
                        查詢課表
                    </button>
                </form>
            </div>
            <div class="info-section">
                <a href="information.php" id="information" class="info-card">
                    <div class="info-icon">📢</div>
                    <h3>網站資訊（功能開發中）</h3>
                    <p>查看網站更新與資訊</p>
                </a>
                <a href="feedback.php" id="feedback" class="info-card">
                    <div class="info-icon">📩</div>
                    <h3>意見回饋</h3>
                    <p>系統功能建議或錯誤回報</p>
                </a>
            </div>
        </div>

        <footer class="page-footer">
        <p>© 2025 Steven Chin | 調課查詢系統 | Licensed under MIT License</p>
        </footer>
    </div>
</body>
</html>