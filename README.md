# Лабораторная работа 8
# Тема: "Непрерывная интеграция с помощью Github Actions
# Цель: "Научиться использовать Github Actions для автоматизации процессов CI/CD"
# Задание: "Создать Web приложение, написать тесты для него и настроить непрерывную интеграцию с помощью Github Actions на базе контейнеров"

# ВЫПОЛНЕНИЕ:

1. задаем структуру web приложения
```
site
├── modules/
│   ├── database.php
│   └── page.php
├── templates/
│   └── index.tpl
├── styles/
│   └── style.css
├── config.php
└── index.php
```

2. Блок с щаданием
> Файл modules/database.php содержит класс Database для работы с базой данных. Для работы с базой данных используйте SQLite. Класс должен содержать методы:
__construct($path) - конструктор класса, принимает путь к файлу базы данных SQLite;
Execute($sql) - выполняет SQL запрос;
Fetch($sql) - выполняет SQL запрос и возвращает результат в виде ассоциативного массива.
Create($table, $data) - создает запись в таблице $table с данными из ассоциативного массива $data и возвращает идентификатор созданной записи;
Read($table, $id) - возвращает запись из таблицы $table по идентификатору $id;
Update($table, $id, $data) - обновляет запись в таблице $table по идентификатору $id данными из ассоциативного массива $data;
Delete($table, $id) - удаляет запись из таблицы $table по идентификатору $id.
Count($table) - возвращает количество записей в таблице $table.

### Выолнение:
```php
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

```

CRUD + count функции.

3. Задание:
> Файл modules/page.php содержит класс Page для работы с страницами. Класс должен содержать методы:
__construct($template) - конструктор класса, принимает путь к шаблону страницы;
Render($data) - отображает страницу, подставляя в шаблон данные из ассоциативного массива $data.
### Выполнение:
```php
<?php

namespace site\modules\page;
    class Page{
        public $layout = 'layout';
        public $viewDirectory;

        public function __construct($layout, $viewDirectory) {
            $this->$layout = $viewDirectory . '/' . $layout . '.php';
            $this->viewDirectory = $viewDirectory;
        }

        public function Render($templateName, $data = []) {
            extract($data);
            ob_start();
            include $this->viewDirectory . '/' . $templateName . '.php';
            $content = ob_get_clean();
            include $this->layout;
    }
```
Я буду испольщовать свой шаблонизатор, выполенный в рамках курса по php.