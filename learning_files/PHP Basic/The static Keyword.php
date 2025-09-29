<!DOCTYPE html>
<html>
    <body>
        <?php
        function Test() {
            static $a = 0; //static 關鍵字可以讓變數在函式結束時不被刪除值，注意這仍然是區域變數
            echo $a;
            $a++;
        }
       
        Test();
        Test();
        Test();

        ?>
    </body>
</html>