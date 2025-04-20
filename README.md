# Лабораторная работа 8
### Тема: "Непрерывная интеграция с помощью Github Actions
### Цель: "Научиться использовать Github Actions для автоматизации процессов CI/CD"
### Задание: "Создать Web приложение, написать тесты для него и настроить непрерывную интеграцию с помощью Github Actions на базе контейнеров"

## ВЫПОЛНЕНИЕ:

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
```

Пока класс оставлю в таком виде, при возникновении ошибок буду что-то менять...

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
Я буду использовать свой шаблонизатор, выполненный в рамках курса по php
> ПРОВЕРКА работы шаблонищатора:

<li>layout.php</li>

```php
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/site/styles/style.css">
    <?php /** @var string $title */?>
    <title>CONTAINERS08 - <?= $title ?></title>
</head>
<body>

<h1 style="color: green">ITS LAYOUT WORK</h1>
<div class="container">
    <?php /** @var string $content */?>
    <?= $content ?>
</div>
</body>
</html>

```
<li>index.tpl.php</li>

```php
<?php /** @var string $message */?>
<h1><?= $message ?></h1>
```

<li>index.php</li>

```php
<?php
use site\modules\page\Page;
require_once __DIR__ . "/modules/page.php";
$temp = new Page('layout', __DIR__ . "/templates");
$temp->render("index.tpl", ['title'=>"TEST_PAGE", 'message' => 'HELLO BRATISHKA ITS INDEX PAGE']);
```

![](https://i.imgur.com/v7r076j.png)

Шаблонизатор работает на ура, передавая все необходимые данные, поэтому идем далее

4. Создаем каталог `sql` и файл в нем `schema.sql` с соответствующим содержимым
5. Создаем каталог `tests` и файла в нем `testframework.php`
6. Создаем файл `tests.php` где есть все тесты для методов в классе Database и Page

```php

```