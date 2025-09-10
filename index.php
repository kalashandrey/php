<?php
$college="ГБПОУ МКАГ";
$group="ИСИП";
$lesson="УП.14.01";
$cabinet="402";
$teacher="Карташова Юлия Сергеевна";
$numbergroup="4-23";
$student="Калашников Андрей Артемович";


echo (
    "Название образовательного учреждения: ".$college."<br>".
    "Отделение: " .$group. "<br>".
    "Предмет: " .$lesson. "<br>".
    "Кабинет: " .$cabinet. "<br>".
    "Преподаватель: " .$teacher."<br>".
    "Номер группы: " .$numbergroup. "<br>".
    "ФИО студента: " .$student."<br>"
);

echo "<h1>Тема 1 </h1>";
echo "<h1>Задание 1 </h1>";

$x=10;
$y=5;
$sum=$x+$y;
$raz=$x-$y;
$pro=$x*$y;
$cha=$x/$y;
echo "сумма - ".$sum." ";
echo "разность - ".$raz." ";
echo "произведение - ".$pro." ";
echo "частность - ".$cha."<br><br>";

echo "<h1>Задание 2 </h1>";

$x=(3+8+2);
$y=(4+6+2);
$z=pow($x,$y);
echo "$z <br><br>";

echo "<h1>Задание 3 </h1>";

$a=5;
$b=$a*2;
echo "число a: $a<br>";
echo "число b: $b<br>";
echo "<br>";
echo "<br>";

$a=333/3;
$b=$a+1;
echo "число a: $a<br>";
echo "число b: $b<br>";
echo "<br>";
echo "<br>";

$s=(1/4) * pow($a,2)*sqrt(3);
$a=6;
echo "площадь - ".$s." ";
echo "<br>";
echo "<br>";

$p=11;
$q=15;
$c=22;
$min=min($p,$q,$c);
$max=max($p,$q,$c);
echo "минимальное - "," " .$min. "<br>";
echo "максимальное - "," " .$max. "<br>";
echo "<br>";
echo "<br>";

echo "<h1>Тема 2 </h1>";
echo "<h1>Задание 1 </h1>";

for ($x=4; $x<=9; $x++){
    $x2=$x*$x;
    echo "<table border = 1><tr><td>$x^2</td><td>$x2</td></tr></table>";
};

echo "<h1>Задание 2</h1>";
echo "<table border='1'; color=red>";
echo "<tr><th></th>";
for ($i = 1; $i <= 10; $i++) {
    echo "<th>$i</th>";
}
echo "</tr>";
for ($x = 4; $x <= 9; $x++) {
    echo "<tr><td>$x</td>";
    for ($y = 1; $y <= 10; $y++) {
        $result = $x * $y;
        $style = ($result % 2 == 0) ? "background-color: gray;" : "";
        if ($result < 40) {
            $style .= "color: green;";
        } elseif ($result > 75) {
            $style .= "color: red; font-weight: bold;";
        }
        echo "<td style='$style'>$result</td>";
    }
    echo "</tr>";
}
echo "</table>";

5!
