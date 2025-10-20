<?php
    require_once("includes/config.php");
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>湖內國中調課查詢系統 - 首頁</title>
    <link rel="stylesheet" href="css/frame.css?v=<?php echo $asset_versions["frame.css"];?>">
    <link rel="stylesheet" href="css/index-style.css?v=<?php echo $asset_versions["index-style.css"];?>">
    <script src="js/index.js?v=<?php echo $asset_versions["index.js"];?>" defer></script>
</head>
<body>
    <div class="container">
        <header class="page-header">
            <h1>湖內國中 - 調課查詢系統</h1>
            <div class="header-decoration"></div>
            <p class="subtitle">請選擇要查詢的班級</p>
        </header>

        <div class="main-content">
            <div class="selection-card">
                <form method="get" action="ShowSchedule.php" id="classForm">
                    <div class="form-group">
                        <label for="grade">選擇年級</label>
                        <select name="grade" id="grade" class="form-select" required>
                            <option value="">-- 請選擇年級 --</option>
                            <option value="7">七年級</option>
                            <option value="8">八年級</option>
                            <option value="9">九年級</option>
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
                    <h3>網站資訊</h3>
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