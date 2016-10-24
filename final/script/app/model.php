<?php

namespace App;

class Model
{
    protected $driver;
//    public $id;
    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function insert()
    {
        $props = [];
        $binds = [];
        $params = [];
        foreach ($this as $k => $v) {
            /*if ('id' == $k) {
                continue;
            }
            if('driver' == $k){
                continue;
            }*/

            $props[] = $k;
            $binds[] = ':' . $k;
            $params[':' . $k] = $v;
        }

        $sql = '
        INSERT INTO ' . static::$table . '
        (' . implode(', ', $props) . ')
        VALUES
        (' . implode(', ', $binds) . ')
        ';

        $db = new Db($this->driver);
        $db->execute($sql, $params);
        $this->id = $db->insertId();
    }
    public function fill($arr)
    {
        foreach ($arr as $k => $item) {
            $this->$k = trim(strip_tags($item));
            if (empty($arr[$k])) {
                $this->$k = null;
            }
        }
    }
}