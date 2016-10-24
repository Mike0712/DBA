<?php
$i = 251;

while ($i <= 500) {

    $data[] = [
        'title' => 'Товар' . $i,
        'vendor_code' => str_pad($i * 7, 5, '0', STR_PAD_LEFT),
        'image_url' => '/images/good' . $i,
        'price' => rand(350000, 100000000),
        'old_price' => rand(350000, 100000000),
        'warehouse_date' => date('Y-m-d h:i:s', time()-rand(0, 8467200)),
        'quantity' => rand(0, 50),
        'brand_id' => rand(9, 16),
        'presence' => 't',
    ];

    if($i%13 === 0){
        $data[$i-1]['vendor_code'] = 'test'.$data[$i-1]['vendor_code'];
    }
    
    if ($data[$i-1]['price'] > $data[$i-1]['old_price']){
        $data[$i-1]['old_price'] = null;
    }
    if ($data[$i]['quantity'] == 0){
        $data[$i]['presence'] = 'f';
    }
    $i++;
}
return $data;
