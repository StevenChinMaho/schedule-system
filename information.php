<?php
    require_once("includes/config.php");
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(SCHOOL_NAME); ?>調課查詢系統 - 網站資訊</title>
    <link rel="stylesheet" href="css/frame.css?v=<?php echo $asset_versions["frame.css"];?>">
</head>
<body>
    <a href="index.php" class="back-button">← 返回首頁</a>

    <div class="container">
        <header class="page-header">
            <h1><?php echo htmlspecialchars(SCHOOL_NAME); ?> - 調課查詢系統</h1>
            <div class="header-decoration"></div>
        </header>

        <div class="main-content">
            <h3>功能開發中...</h3>
        </div>

        <footer class="page-footer">
        <p>© 2025 Steven Chin | 調課查詢系統 | Licensed under MIT License</p>
        </footer>
    </div>
</body>
</html>