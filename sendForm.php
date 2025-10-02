<!DOCTYPE html>
<html>
<head lang="zh-TW">
    <meta charset="UTF-8">
    <title>SendForm</title>
</head>
<body>
    
    <?php
        require_once("includes/config.php");

        if ( empty($_POST['name']) || empty($_POST['age']) )
        {
            echo "你少填了啦 阿齁<br>";
            echo "<a href='index.php'>點我回去</a>";
            exit();
        }
        else
        {
            echo "你好啊 " . $_POST['name'] . "，聽說你 " . $_POST['age'] . " 歲！<br>";

            $name = $_POST['name'];
            $age = (int)$_POST['age'];

            $stmt = $pdo->prepare("INSERT INTO test( name, age ) VALUES ( ?, ? )");
            $stmt->execute([$name, $age]);
            echo "資料插入完成<br>";
            echo "<a href='index.php'>回主頁</a><br>";
    ?>
    <a href="tamamitsune.html">間隔用</a><br>
    <hr>
    <table>
        <thead>
                <tr>
                    <th>ID</th>
                    <th>名字</th>
                    <th>歲數</th>
                </tr>
        </thead>
        <tbody>
    <?php
        $sql = "SELECT * FROM test";
        $stmt = $pdo->query($sql);
        $tests = $stmt->fetchAll();

        foreach( $tests as $t ) {
            echo "<tr>";
            echo "<td>" . $t['tid'] . "</td>";
            echo "<td>" . $t['name'] . "</td>";
            echo "<td>" . $t['age'] . "</td>";
            echo "</tr>";
        }
    }
    ?>
        </tbody>
    </table>
</body>
</html>