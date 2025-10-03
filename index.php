<?php
    require_once("includes/config.php");
?>

<!DOCTYPE html>
<html lang="zh-TW">
    <head>
        <meta charset="UTF-8">
        <title>咕嚕靈波測試網站</title>
    </head>
    <body>
        <h1>I Feel Good</h1>
        <p>
            <a href="tamamitsune.html">Click Me!</a>
            <br>
            <a href="learning_files">練習用</a>
            <br>
        </p>
        <hr>
        <div>
            <form method="post" action="sendForm.php">
                name: <input type="text" name="name"/><br>
                age: <input type="text" name="age"/><br>
                <input type="submit">
            </form>
            <hr>
            <p>選擇要查詢的班級：</p>
            <form method="get" action="ShowSchedule.php">
                <select name="class_id">
                    <option value="1">七年一班</option>
                    <option value="2">七年二班</option>
                    <option value="3">七年三班</option>
                    <option value="4">七年四班</option>
                    <option value="5">八年一班</option>
                    <option value="6">八年二班</option>
                    <option value="7">八年三班</option>
                    <option value="8">八年四班</option>
                    <option value="9">九年一班</option>
                    <option value="10">九年二班</option>
                    <option value="11">九年三班</option>
                    <option value="12">九年四班</option>
                </select>
                <input type="submit">
            </form>
        </div>
        <hr>
    </body>
</html>