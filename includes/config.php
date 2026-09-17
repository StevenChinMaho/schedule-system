<?php
/**
 * 資料庫連線與共用設定。
 *
 * 連線資訊一律由環境變數提供（見 docker-compose.yaml 的 environment 區段），
 * 此檔案不含任何帳號密碼，可安全納入版本控制。
 */

/**
 * 輸出 503 維護頁面並中止執行。
 */
function render_maintenance_page(): never
{
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo <<<'HTML'
    <!DOCTYPE html>
    <html lang="zh-TW">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>系統維護中</title>
        <style>
            body {
                font-family: "Microsoft JhengHei", sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .error-container {
                background: white;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                text-align: center;
                max-width: 500px;
            }
            h1 { color: #e74c3c; margin-bottom: 20px; }
            p  { color: #555; line-height: 1.6; }
            .back-link {
                display: inline-block;
                margin-top: 20px;
                padding: 12px 24px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                transition: all 0.3s;
            }
            .back-link:hover { background: #5568d3; transform: translateY(-2px); }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>⚠️ 系統暫時無法使用</h1>
            <p>很抱歉，資料庫連線發生問題。請稍後再試，或聯繫系統管理員。</p>
            <a href="index.php" class="back-link">返回首頁</a>
        </div>
    </body>
    </html>
    HTML;
    exit;
}

/**
 * 讀取必要的環境變數，缺少時中止執行。
 */
function require_env(string $name): string
{
    $value = getenv($name);

    if ($value === false || $value === '') {
        error_log("設定錯誤: 環境變數 {$name} 未設定");
        render_maintenance_page();
    }

    return $value;
}

/**
 * 學校名稱，顯示於各頁標題與頁首。
 * 非機密且不影響系統運作，故未設定時使用通用預設值而不中止。
 */
define('SCHOOL_NAME', getenv('SCHOOL_NAME') ?: '國中');

define('DB_HOST', require_env('DB_HOST'));
define('DB_NAME', require_env('DB_NAME'));
define('DB_USER', require_env('DB_USER'));
define('DB_PASS', require_env('DB_PASS'));

$asset_versions = [
    "feedback-style.css" => filemtime( __DIR__ . "/../css/feedback-style.css" ),
    "frame.css" => filemtime( __DIR__ . "/../css/frame.css" ),
    "index-style.css" => filemtime( __DIR__ . "/../css/index-style.css" ),
    "showSchedule-style.css" => filemtime( __DIR__ . "/../css/showSchedule-style.css" ),
    "feedback.js" => filemtime( __DIR__ . "/../js/feedback.js" ),
    "index.js" => filemtime( __DIR__ . "/../js/index.js" ),
    "main.js" => filemtime( __DIR__ . "/../js/main.js" )
];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch ( PDOException $e ) {
    error_log("資料庫連線失敗: " . $e->getMessage());
    render_maintenance_page();
}
