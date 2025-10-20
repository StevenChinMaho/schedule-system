<?php
    require_once("includes/config.php");


?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>湖內國中調課查詢系統 - 意見回饋</title>
    <link rel="stylesheet" href="css/frame.css">
    <link rel="stylesheet" href="css/feedback-style.css">
    <script src="js/feedback.js"></script>
</head>
<body>
    <a href="index.html" class="back-button">← 返回首頁</a>
    
    <div class="container">
        <header class="page-header">
            <h1>意見回饋</h1>
            <div class="header-decoration"></div>
            <p class="subtitle">您的意見是我們改進的動力</p>
        </header>

        <div class="main-content">
            <div class="feedback-card">
                <form method="POST" action="" id="feedbackForm">
                    <div class="form-group">
                        <label for="feedback_type">
                            回饋類型<span class="required">*</span>
                        </label>
                        <select name="feedback_type" id="feedback_type" class="form-select" required>
                            <option value="">-- 選擇類型 --</option>
                            <option value="建議">功能建議</option>
                            <option value="錯誤回報">錯誤回報</option>
                            <option value="其他">其他</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            回饋內容<span class="required">*</span>
                        </label>
                        <textarea
                            name="feedback_content"
                            id="feedback_content"
                            class="form-textarea"
                            rows="8"
                            placeholder="請詳述您的建議或遇到的問題..."
                            required
                            minlength="10"
                            maxlength="1000"
                        ><?php echo htmlspecialchars($_POST["feedback_content"] ?? ""); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="contact_info">
                            聯絡方式<span class="optional">（選填）</span>
                        </label>
                        <input 
                            type="text" 
                            name="contact_info" 
                            id="contact_id" 
                            class="form-input"
                            placeholder="如需回覆請留下 Email 或其他聯絡方式"
                        >
                    </div>

                    <button type="submit" class="submit-button">送出</button>
                </form>
            </div>
        </div>
        <footer class="page-footer">
        <p>© 2025 Steven Chin | 調課查詢系統 | Licensed under MIT License</p>
        </footer>
    </div>
</body>
</html>