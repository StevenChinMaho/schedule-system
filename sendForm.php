<?php
    require_once("config.php");

    if ( empty($_POST['name']) || empty($_POST['age']) )
    {
        echo "你少填了啦 阿齁<br>";
        echo "<a href=\"index.php\">點我回去</a>";
        exit();
    }
    else
    {
        echo "你好啊 " . $_POST['name'] . "，聽說你 " . $_POST['age'] . " 歲！<br>";

        print_r($_POST);

        $name = $_POST['name'];
        $age = $_POST['age'];

        $sql = sprintf( "INSERT INTO test( name, age ) VALUES ( '%s', %s )", $name, $age );

        $result = $conn -> query( $sql );

        if( !$result ) {
            die( $conn -> error );
        }

        
    }
?>