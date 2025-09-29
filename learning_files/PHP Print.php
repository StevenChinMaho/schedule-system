<!DOCTYPE html>
<html>
<body>
    <?php
    //括弧可用可不用，兩者相同
    print "Hello";
    print("Hello");
    

    print "<h2>PHP is Fun!</h2>";
    print "Hello world!<br>";
    print "I'm about to learn PHP!";

    $txt1 = "Learn PHP";
    $txt2 = "W3Schools.com";

    print "<h2>$txt1</h2>";
    print "<p>Study PHP at $txt2</p>";

    $txt1 = "Learn PHP";
    $txt2 = "W3Schools.com";

    print '<h2>' . $txt1 . '</h2>';
    print '<p>Study PHP at ' . $txt2 . '</p>';
    //其實跟echo差不多，差別在於print有回傳值，echo沒有，並且echo快一點點
    ?>
</body>
</html>