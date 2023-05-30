<?php


$a = 53270; //16
$b = 828; //32

$target = 54317078;

$result = $a + ($b << 16);

// $result int(54317078)

var_dump($result);




// .42 run speed = 207 
// line 2
var_dump(54330247 - 54330040);

// .48 run speed = 70
// line 1 70 p/m very roughly
var_dump(56482372 - 56482302);
