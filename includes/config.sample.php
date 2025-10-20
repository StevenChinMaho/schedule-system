<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'the_DB');

$asset_versions = [
    "feedback-style.css" => filemtime( "css/feedback-style.css" ),
    "frame.css" => filemtime( "css/frame.css" ),
    "index-style.css" => filemtime( "css/index-style.css" ),
    "ShowSchedule-style.css" => filemtime( "css/ShowSchedule-style.css" ),
    "feedback.js" => filemtime( "js/feedback.js" ),
    "index.js" => filemtime( "js/index.js" ),
    "main.js" => filemtime( "js/main.js" )
];

try {
    $pdo = new PDO( 
        "mysql:host=" . DB_HOST . "; dbname=" . DB_NAME . ";charset=utf8mb4", 
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch ( PDOException $e ) {
    error_log("資料庫連線失敗: " . $e->getMessage());
    http_response_code(503);
    die('
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
            h1 {
                color: #e74c3c;
                margin-bottom: 20px;
            }
            p {
                color: #555;
                line-height: 1.6;
            }
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
            .back-link:hover {
                background: #5568d3;
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>⚠️ 系統暫時無法使用</h1>
            <p>很抱歉，資料庫連線發生問題。請稍後再試，或聯繫系統管理員。</p>
            <a href="index.html" class="back-link">返回首頁</a>
        </div>
    </body>
    </html>
    ');
}
?>