<?php

namespace App;

class Db
{
    protected $config;
    protected $dbh;

    public function __construct($driver)
    {
        $this->config = include __DIR__ . '/../config.php';
        switch ($driver) {
            case 'mysql':
                $config = $this->config['mysql'];
                break;
            case 'pgsql':
                $config = $this->config['pgsql'];
                break;
            default:
                $config = [];
        }
        $connection = $config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'];

        $this->dbh = new \PDO($connection, $config['user'], $config['password']);
    }

    public function execute($query, $params = [])
    {
        $sth = $this->dbh->prepare($query);
        $res = $sth->execute($params);
        return $res;
    }

    public function insertId()
    {
        return $this->dbh->lastInsertId();
    }
}