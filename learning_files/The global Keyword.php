<!DOCTYPE html>
<html>
<body>
    <?php
    $x = 5;
    $y = 10;

    function myTest() {
    global $x, $y;
    $y = $x + $y;
    }

    myTest();
    echo $y; // outputs 15
    echo "<br>";
    function Test2() {
        $GLOBALS['y'] = $GLOBALS['x'] + $GLOBALS['y'];
    }

    Test2();
    echo $y;
    ?>
</body>
</html>