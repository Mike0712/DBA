<?php
$i = 0;

while ($i <= 500) {

    $data[] = [
        'good_id' => rand(1,250),
        'category_id' =>rand(32, 46),
    ];
    $i++;
}
return $data;
