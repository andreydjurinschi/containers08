<?php

namespace site\modules\database;
use PDO;
use PDOException;

class Database {

    private $connection;

    public function __construct($path) {
        try {
            $this->connection = new PDO("sqlite:$path");
        }catch(PDOException $exception){
            echo $exception->getMessage();
        }
    }

    public function Execute($sql){
        return $this->connection->exec($sql);
    }

    public function FetchAll($sql){
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Create($table, $data){
        $columns = implode(", ", array_keys($data));
        $properties = ":" . implode(", :", array_keys($data));
        $query = "insert into $table ($columns) values ($properties)";
        $statement = $this->connection->prepare($query);
        $statement->execute($data);
        return $this->connection->lastInsertId();
    }

    public function Read($table, $id){
        $query = "select * from $table where id = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute(['id' => $id]);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function Update($table, $data, $id){
        $fields = [];
        foreach($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $fields = implode(", ", $fields);
        $query = "update $table set $fields where id = :id";
        $result = $this->connection->prepare($query)->execute(['id' => $id]);
        if($result){
            return $this->Read($table, $id);
        }
        return false;
    }

    public function Delete($table, $id){
        $query = "delete from $table WHERE id = :id";
        return $this->connection->prepare($query)->execute(['id' => $id]);
    }

    public function Count($table){
        $allData = $this->FetchAll("select * from $table");
        return count($allData);
    }

}
