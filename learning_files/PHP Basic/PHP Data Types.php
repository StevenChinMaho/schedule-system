<!DOCTYPE html>
<html>
<body>
    <?php
    $x = 5;
    var_dump($x);
    echo "<br>";

    $x = "Hello world!"; //單引號或雙引號都行
    $y = 'Hello world!';

    var_dump($x);
    echo "<br>";
    var_dump($y);

    $x = 5985; // -2,147,483,648 ~ 2,147,483,647
    var_dump($x);      
    echo "<br>";

    $x = 10.365; // Float
    var_dump($x);
    echo "<br>";

    $x = true; // Boolean
    var_dump($x);
    echo "<br>";
    $cars = array("Volvo","BMW","Toyota"); //array
    var_dump($cars);
    echo "<br>";

    class Car {
    public $color;
    public $model;
    public function __construct($color, $model) {
        $this->color = $color;
        $this->model = $model;
    }
    public function message() {
        return "My car is a " . $this->color . " " . $this->model . "!";
    }
    }

    $myCar = new Car("red", "Volvo"); //object
    var_dump($myCar);
    echo "<br>";

    $x = "Hello world!"; // NULL型別只有一種值: NULL
    $x = null;           // 沒有賦值得變數預設也是NULL，也可以將NULL賦值給變數來清空變數。
    var_dump($x);
    echo "<br>";

    $x = 5; //直接覆蓋可以改變資料型別
    var_dump($x);

    $x = "Hello";
    var_dump($x);
    echo "<br>";

    $x = 5; //如果要不改變值直接轉換型態，可以用強制型別轉換(Casting)
    $x = (string) $x;
    var_dump($x);
    ?>
</body>
</html>