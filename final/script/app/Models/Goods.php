<?php

namespace App\Models;


use App\Model;

class Goods extends Model
{
    protected static $table = 'goods';

    public $title;
    public $vendor_code;
    public $image_url;
    public $price;
    public $old_price;
    public $warehouse_date;
    public $quantity;
    public $brand_id;
    public $presence;
}