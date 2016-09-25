<?php
$i = 1;

while ($i <= 1000) {

    $data[] = [
        'title' => 'Товар' . $i,
        'vendor_code' => str_pad($i * 13, 5, '0', STR_PAD_LEFT),
        'image_url' => '/images/good' . $i,
        'price' => rand(350000, 100000000),
        'old_price' => rand(350000, 100000000),
        'warehouse_date' => date('Y-m-d h:i:s', time()-rand(0, 8467200)),
        'quantity' => rand(1, 50),
    ];

    if($i%15 === 0){
        $data[$i-1]['vendor_code'] = 'test'.$data[$i-1]['vendor_code'];
    }
    $i++;
    if ($data[$i-1]['price'] > $data[$i-1]['old_price']){
        $data[$i-1]['old_price'] = null;
    }
}
return $data;