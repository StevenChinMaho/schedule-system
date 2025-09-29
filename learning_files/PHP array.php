<!DOCTYPE html>
<html>
<body>
    <?php
    $arr1 = array( 0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5 ); //鍵可以是整數或字串
    $arr2 = array(1, 2, 3, 4, 5);
    $arr3 = [1, 2, 3, 4, 5];

    var_dump($arr1);
    echo "<br>";
    
    var_dump($arr2);
    echo "<br>";
    
    var_dump($arr3);
    echo "<br>"; //以上三種寫法都相等

    $zipcode1 = array ('100'=>'台北市中正區','952'=>'蘭嶼' , '200'=>'基隆市仁愛區' ,'640' =>'斗六' ); 
    $zipcode2 = ["100"=>"台北市中正區","952"=>"蘭嶼" , "200"=>"基隆市仁愛區" ,"640" =>"斗六" ];  //鍵也可以是字串
    //如果不是整數或字串的話會被強制轉換

    var_dump($zipcode1);
    echo "<br>";

    var_dump($zipcode2);
    echo "<br>";
    ?>
</body>
</html>