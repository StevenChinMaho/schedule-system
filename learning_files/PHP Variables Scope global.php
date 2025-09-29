<!DOCTYPE html>
<html>
    <?php
    $x = 5; // global scope

    function Test() {
    // 函式內存取全域變數會出錯
    //echo "<p>Variable x inside function is: $x</p>";
    }
    Test();

    echo "<p>Variable x outside function is: $x</p>";

    ?>
</html>