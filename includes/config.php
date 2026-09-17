<?php
/**
 * 資料庫連線與共用設定。
 *
 * 單一部署可服務多間學校：依照請求的網域自 includes/schools.php 找出對應的
 * 學校，並連往該校專屬的資料庫。找不到對應網域時一律中止，不會退回任何預設
 * 學校，以免將某校的資料顯示在未註冊的網域上。
 *
 * 資料庫帳號密碼由環境變數提供（見 docker-compose.yaml），此檔不含機密。
 */

require_once __DIR__ . '/error_page.php';

/**
 * 讀取必要的環境變數，缺少時中止執行。
 */
function require_env(string $name): string
{
    $value = getenv($name);

    if ($value === false || $value === '') {
        error_log("設定錯誤: 環境變數 {$name} 未設定");
        render_error_page('系統暫時無法使用', '系統設定不完整，請聯繫系統管理員。', 503);
    }

    return $value;
}

/**
 * 依請求的網域找出對應的學校設定。
 */
function resolve_school(array $schools, string $host): ?array
{
    // 去除連接埠並正規化，Host 標頭為用戶端提供，僅用於查表
    $host = strtolower(explode(':', $host)[0]);

    foreach ($schools as $key => $school) {
        if (in_array($host, $school['hosts'], true)) {
            return $school + ['key' => $key];
        }
    }

    return null;
}

$school = resolve_school(
    require __DIR__ . '/schools.php',
    $_SERVER['HTTP_HOST'] ?? ''
);

if ($school === null) {
    error_log('未註冊的網域: ' . ($_SERVER['HTTP_HOST'] ?? '(空)'));
    render_error_page(
        '網域尚未設定',
        '此網域尚未對應到任何學校。若您認為這是錯誤，請聯繫系統管理員。',
        404
    );
}

define('SCHOOL_KEY', $school['key']);
define('SCHOOL_NAME', $school['name']);
define('SCHOOL_ACTIVITY_SLOTS', $school['activity_slots']);

define('DB_HOST', require_env('DB_HOST'));
define('DB_NAME', $school['database']);
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
    error_log("資料庫連線失敗 (" . SCHOOL_KEY . "): " . $e->getMessage());
    render_error_page(
        '系統暫時無法使用',
        '很抱歉，資料庫連線發生問題。請稍後再試，或聯繫系統管理員。',
        503
    );
}
