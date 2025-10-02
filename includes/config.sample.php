<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'the_DB');

try {
    $pdo = new PDO( "mysql:host=" . DB_HOST . "; dbname=" . DB_NAME, DB_USER, DB_PASS );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "資料庫連線成功<br>";

} catch ( PDOException $e ) {
    echo "資料庫連線失敗 " . $e->getMessage() . "<br>";
}


?>