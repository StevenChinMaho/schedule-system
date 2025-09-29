<?php
    if ( empty($_GET['name']) || empty($_GET['age']) )
    {
        echo "你少填了啦 阿齁<br>";
        echo "<a href=\"index.php\">點我回去</a>";
        exit();
    }
    else
    {
        echo "你好啊 " . $_GET['name'] . "，聽說你 " . $_GET['age'] . " 歲！<br>";

        print_r($_GET);
    }
?>