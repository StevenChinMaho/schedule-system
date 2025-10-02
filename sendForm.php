<?php
    require_once("includes/config.php");

    if ( empty($_POST['name']) || empty($_POST['age']) )
    {
        echo "你少填了啦 阿齁<br>";
        echo "<a href=\"index.html\">點我回去</a>";
        exit();
    }
    else
    {
        echo "你好啊 " . $_POST['name'] . "，聽說你 " . $_POST['age'] . " 歲！<br>";

        $name = $_POST['name'];
        $age = (int)$_POST['age'];

        // $sql = sprintf( "INSERT INTO test( name, age ) VALUES ( '%s', %s )", $name, $age );

        $stmt = $pdo->prepare("INSERT INTO test( name, age ) VALUES ( ?, ? )");
        // $stmt->bindParam();
        $stmt->execute([$name, $age]);

        if (!$stmt) {
            die($conn->error);
        }

        $stmt = NULL;
    }
?>