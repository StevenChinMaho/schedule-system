<?php
    require_once("includes/config.php");

    $success_message = "";
    $error_message = "";

    if ( $_SERVER["REQUEST_METHOD"] === "POST")
    {
        $feedback_type = $_POST["feedback_type"] ?? "其他";
        $feedback_content = trim($_POST["feedback_content"] ?? "");
        $contact_info = trim($_POST["contact_info"] ?? "");
        $ip_address = $_SERVER["REMOTE_ADDR"] ?? "unknown";

        $valid_types = ["建議", "錯誤回報", "其他" ];
        if (!in_array($feedback_type, $valid_types)) $feedback_type = "其他";

        if ( mb_strlen( $feedback_content, "UTF-8" ) < 10 ) $error_message = "字數不可小於10個字";
        elseif ( mb_strlen( $feedback_content, "UTF-8") > 1000 ) $error_message = "字數不可超過1000個字";
        else 
        {
            try {
                $stmt = $pdo->prepare( "
                    INSERT INTO `feedback` ( `feedback_type`, `feedback_content`, `contact_info`, `ip_address` ) 
                    VALUE ( ?, ?, ?, ? );
                ");
                $stmt->execute([
                    $feedback_type,
                    $feedback_content,
                    empty($contact_info) ? null : $contact_info,
                    $ip_address
                ]);

                $success_message = "感謝您的回饋！我們已收到您的意見。";

                $_POST = [];

            } catch (PDOException $e) {
                error_log("意見回饋提交失敗: " . $e->getMessage() );
                $error_message = "提交失敗，請稍後再試";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>湖內國中調課查詢系統 - 意見回饋</title>
    <link rel="stylesheet" href="css/frame.css?v=<?php echo $asset_versions["frame.css"];?>">
    <link rel="stylesheet" href="css/feedback-style.css?v=<?php echo $asset_versions["feedback-style.css"];?>">
    <script src="js/feedback.js?v=<?php echo $asset_versions["feedback.js"];?>" defer></script>
</head>
<body>
    <a href="index.php" class="back-button">← 返回首頁</a>
    
    <div class="container">
        <header class="page-header">
            <h1>意見回饋</h1>
            <div class="header-decoration"></div>
            <p class="subtitle">您的意見是我們改進的動力</p>
        </header>

        <div class="main-content">
            <?php if ( $error_message ): ?>
                <div class="message error-message">
                    <span class="message-icon">✕</span>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <?php if ( $success_message ): ?>
                <div class="message success-message">
                    <span class="message-icon">✓</span>
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>

            <div class="feedback-card">
                <form method="POST" action="" id="feedbackForm">
                    <div class="form-group">
                        <label for="feedback_type">
                            回饋類型<span class="required">*</span>
                        </label>
                        <select name="feedback_type" id="feedback_type" class="form-select" required onchange="changed()">
                            <option value="" <?php echo ($_POST["feedback_type"] ?? "") === "" ? "selected" : "" ; ?>>-- 選擇類型 --</option> 
                            <option value="建議" <?php echo ($_POST["feedback_type"] ?? "") === "建議" ? "selected" : "" ; ?>>功能建議</option>
                            <option value="錯誤回報" <?php echo ($_POST["feedback_type"] ?? "") === "錯誤回報" ? "selected" : "" ; ?>>錯誤回報</option>
                            <option value="其他" <?php echo ($_POST["feedback_type"] ?? "") === "其他" ? "selected" : "" ; ?>>其他</option>
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
                            onchange="changed()"
                        ><?php echo htmlspecialchars($_POST["feedback_content"] ?? ""); ?></textarea>
                        <div class="char-counter">
                            <span id="char_count">0</span> / 1000 字
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact_info">
                            聯絡方式<span class="optional">（選填）</span>
                        </label>
                        <input 
                            type="text" 
                            name="contact_info" 
                            id="contact_info" 
                            class="form-input"
                            placeholder="如需回覆請留下 Email 或其他聯絡方式"
                            onchange="changed()"
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