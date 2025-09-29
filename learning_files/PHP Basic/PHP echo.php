<!DOCTYPE html>
<html>
<body>
    <?php
    //括弧可用可不用，兩者相同
    echo "Hello";
    echo("Hello");

    echo "<h2>PHP is Fun!</h2>"; //可以使用html標籤
    echo "Hello world!<br>";
    echo "I'm about to learn PHP!<br>";
    echo "This ", "string ", "was ", "made ", "with multiple parameters."; //用逗號分隔多個參數

    $txt1 = "Learn PHP";
    $txt2 = "W3Schools.com";

    echo "<h2>$txt1</h2>"; //雙引號可以嵌入變數
    echo "<p>Study PHP at $txt2</p>"; 

    $txt1 = "Learn PHP";
    $txt2 = "W3Schools.com";

    echo '<h2>' . $txt1 . '</h2>'; //單引號有些不同，必須用 . 運算符插入變數
    echo '<p>Study PHP at ' . $txt2 . '</p>';
    ?>
</body>
</html>