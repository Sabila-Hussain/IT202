<?php


$arr = [ 1, 2, 3, 3 , 4, 5, 6, 7, 7, 8, 15, 20 ];

$count = count($arr);


echo "<br>\n";
echo "Evens in the array: <br>\n";
for($i = 0; $i < $count; $i++){
    if($arr[$i] % 2 == 0 ){
        echo "$arr[$i] is even <br>\n";
    }
}

?>