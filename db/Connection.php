<?php


namespace common\db;

use PDO;

class Connection
{
    public $dsn;

    public $username;

    public $password;

    public $attributes;

    public function getDb()
    {
        return new PDO($this->dsn, $this->username, $this->password, $this->attributes);
    }
}