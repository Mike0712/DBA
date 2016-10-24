<?php

namespace App\Models;


use App\Model;

class GoodsCategory extends Model
{
    protected static $table = 'qoods_category';

    public $good_id;
    public $category_id;
}