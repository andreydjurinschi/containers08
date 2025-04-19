<?php

namespace site\modules\database;
use PDO;

class Database {
    private $conn;

    public function __construct($path) {
        $this->conn = new PDO("sqlite:" . $path);
    }
    public function Execute($sql){
        return $this->conn->exec($sql);
    }

    public function Fetch($sql){
        $stmt = $this->conn->prepare($sql);
        $this->Execute($stmt);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Create($table, $data){
        $properties = implode(", ", array_keys($data));
        $values = ":" . implode(", :", array_keys($data));
        $statement = $this->conn->prepare("insert into $table ($properties) values ($values)");
        $statement->execute($data);
        return $this->conn->lastInsertId();
    }

    public function Reade($table, $id){
     $statement = $this->conn->prepare("select * from $table where id = :id");
     $statement->bindParam(':id', $id);
     return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function Update($table, $data, $id){
    $fields = [];
    foreach ($data as $key => $value){
        $fields[] = "$key = :$key";
    }
    $proprieties = implode(", ", $fields);
    $statement = $this->conn->prepare("update $table set $proprieties where id = :id");
    foreach ($data as $key => $value){
        $statement->bindValue(":$key", $data[$key]);
    }
    $statement->bindValue(":id", $id, PDO::PARAM_INT);
    return $statement->execute();
    }

    public function Delete($table, $id){
        $statement = $this->conn->prepare("delete from $table where id = :id");
        $statement->bindParam(':id', $id);
        return $statement->execute();
    }

    public function Count($table){
        $statement = $this->conn->prepare("select count(*) from $table");
        $statement->execute();
        return $statement->fetchColumn();
    }
}
