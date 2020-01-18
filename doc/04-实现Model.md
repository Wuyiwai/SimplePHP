### 本次任务: 实现Model

#### ModelInterface `/db/ModelInterface.php`
1. 一个合理的Model类应该由接口去规范应该实现什么.所以有了这个接口:
```
<?php


namespace common\db;


interface ModelInterface
{
    public static function tableName();

    public static function primaryKey();

    public static function findOne($condition);

    public static function findAll($condition);

    public static function updateAll($condition, $attributes);

    public static function deleteAll($condition);

    public function insert();

    public function update();

    public function delete();
}
```

#### Connection类 `/db/Connection.php`
如果不对Model类进行封装.每次查询sql时都要走一次PDO操作.所以我们对其进行封装,达到`(new Connection())->getDb()`的目的;
```
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
```
#### db.config `/config/db.config`
封装好db的配置
```
<?php
return [
    'dsn' => 'mysql:host=localhost;dbname=finance',
    'username' => 'root',
    'password' => 'wuyiwai#root',
    'attributes' => [
        \PDO::ATTR_STRINGIFY_FETCHES => false,
    ],
];
```

#### Model类 `/db/Model.php`
```
<?php


namespace common\db;
use PDO;


class Model implements ModelInterface
{
    /**
     * @var PDO $pdo
     */
    public static $pdo;

    public static function getDb()
    {
        if (empty(static::$pdo)) {
            try {
                $config = require("../config/db.php");
                $instance = new Connection();
                foreach ($config as $key=>$value) {
                    $instance->$key = $value;
                }
                static::$pdo = ($instance)->getDb();
            } catch (\PDOException $e) {
                echo $e->getMessage();
            }
            static::$pdo->exec("set names 'utf8'");
        }
        return static::$pdo;
    }

    public static function tableName()
    {
        return get_called_class();
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    public static function findOne($condition)
    {
        list($where, $params) = static::buildWhere($condition);
        $sql = 'select * from ' . static::tableName() . $where;

        $stmt = static::getDb()->prepare($sql);
        $rs = $stmt->execute($params);

        if ($rs) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($row)) {
                return static::getModel($row);
            }
        }

        return null;
    }

    public static function findAll($condition)
    {
        list($where, $params) = static::buildWhere($condition);
        $sql = 'select * from ' . static::tableName() . $where;

        $stmt = static::getDb()->prepare($sql);
        $rs = $stmt->execute($params);
        $models = [];

        if ($rs) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                if (!empty($row)) {
                    $model = static::getModel($row);
                    array_push($models, $model);
                }
            }
        }

        return $models;
    }

    public static function updateAll($condition, $attributes)
    {
        $sql = 'update ' . static::tableName();
        $params = [];

        if (!empty($attributes)) {
            $sql .= ' set ';
            $params = array_values($attributes);
            $keys = [];
            foreach ($attributes as $key => $value) {
                array_push($keys, "$key = ?");
            }
            $sql .= implode(' , ', $keys);
        }

        list($where, $params) = static::buildWhere($condition, $params);
        $sql .= $where;

        $stmt = static::getDb()->prepare($sql);
        $execResult = $stmt->execute($params);
        if ($execResult) {
            $execResult = $stmt->rowCount();
        }
        return $execResult;
    }

    public static function deleteAll($condition)
    {
        list($where, $params) = static::buildWhere($condition);
        $sql = 'delete from ' . static::tableName() . $where;

        $stmt = static::getDb()->prepare($sql);
        $execResult = $stmt->execute($params);
        if ($execResult) {
            $execResult = $stmt->rowCount();
        }
        return $execResult;
    }

    public function insert()
    {
        $sql = 'insert into ' . static::tableName();
        $params = [];
        $keys = [];
        foreach ($this as $key => $value) {
            array_push($keys, $key);
            array_push($params, $value);
        }

        $holders = array_fill(0, count($keys), '?');
        $sql .= ' (' . implode(' , ', $keys) . ') values ( ' . implode(' , ', $holders) . ')';

        $stmt = static::getDb()->prepare($sql);
        $execResult = $stmt->execute($params);

        $primaryKeys = static::primaryKey();
        foreach ($primaryKeys as $name) {
            $lastId = static::getDb()->lastInsertId($name);
            $this->$name = (int) $lastId;
        }
        return $execResult;
    }

    public function update()
    {
        $primaryKeys = static::primaryKey();
        $condition = [];
        foreach ($primaryKeys as $name) {
            $condition[$name] = isset($this->$name) ? $this->$name : null;
        }

        $attributes = [];
        foreach ($this as $key => $value) {
            if (!in_array($key, $primaryKeys, true)) {
                $attributes[$key] = $value;
            }
        }

        return static::updateAll($condition, $attributes) !== false;
    }

    public function delete()
    {
        $primaryKeys = static::primaryKey();
        $condition = [];
        foreach ($primaryKeys as $name) {
            $condition[$name] = isset($this->$name) ? $this->$name : null;
        }

        return static::deleteAll($condition) !== false;
    }

    public static function buildWhere($condition, $params = null)
    {
        if (is_null($params)) {
            $params = [];
        }

        $where = '';
        if (!empty($condition))
        {
            $where .= ' where ';
            $keys = [];
            foreach ($condition as $key => $value) {
                array_push($keys, "$key = ?");
                array_push($params, $value);
            }
            $where .= implode(' and ', $keys);
        }
        return [$where, $params];
    }

    public static function getModel($row)
    {
        $model = new static();
        foreach ($row as $rowKey => $rowValue)
        {
            $model->$rowKey = $rowValue;
        }
        return $model;
    }
}
```

#### 实例CashDetail `/models/CashDetail.php`
```
<?php


namespace common\models;


use common\db\Model;

class CashDetail extends Model
{
    public static function tableName()
    {
        return 'cash_detail';
    }
}
```
```
#ddl
CREATE TABLE `cash_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account` int(10) unsigned NOT NULL DEFAULT '0'
  `amount` float NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` int(11) NOT NULL DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `cash_type` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
```

#### 测试
在`SiteController中添加方法`:
```
public function actionGetCashInfo()
{
    $data = CashDetail::findAll([]);
    echo $this->toJson($data);
}
```

访问: localhost/site/getCashInfo
