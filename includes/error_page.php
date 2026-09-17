<?php
/**
 * 輸出錯誤頁面並中止執行。
 * 供設定錯誤、資料庫連線失敗、網域未註冊等情況共用。
 */
function render_error_page(string $heading, string $message, int $status): never
{
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');

    $heading = htmlspecialchars($heading);
    $message = htmlspecialchars($message);

    echo <<<HTML
    <!DOCTYPE html>
    <html lang="zh-TW">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$heading}</title>
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
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>⚠️ {$heading}</h1>
            <p>{$message}</p>
        </div>
    </body>
    </html>
    HTML;
    exit;
}
