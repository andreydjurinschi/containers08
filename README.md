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

<header><h1 style="color: green">ITS LAYOUT WORK</h1></header>

<main>
    <div class="container">
        <?php /** @var string $content */?>
        <?= $content ?>
    </div>
</main>
<footer>
        <p>CONTAINERS08 &copy; 2025</p>
        <p>asd</p>
</footer>
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

![](https://i.imgur.com/VT1kUlx.png)

Шаблонизатор работает на ура, передавая все необходимые данные, поэтому идем далее

4. Создаем каталог `sql` и файл в нем `schema.sql` с соответствующим содержимым

```sql
CREATE TABLE page (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    content TEXT
);
INSERT INTO page (title, content) VALUES ('Page 1', 'Content 1');
INSERT INTO page (title, content) VALUES ('Page 2', 'Content 2');
INSERT INTO page (title, content) VALUES ('Page 3', 'Content 3');
```

5. Создаем каталог `tests` и файла в нем `testframework.php`
6. Создаем файл `tests.php` где есть все тесты для методов в классе Database и Page

```php
<?php
require_once __DIR__ . '/testframework.php';
require_once __DIR__ . '/../site/config.php';
require_once __DIR__ . '/../site/modules/database.php';
require_once __DIR__ . '/../site/modules/page.php';

use site\modules\database\Database;
use site\modules\page\Page;

$tests = new TestFramework();

function testDatabaseConnection() {
    global $config;
    return new Database($config['db_path']);
}
function testDbExecute() {
    global $config;
    $db = new Database($config['db_path']);
    $sql = "CREATE TABLE IF NOT EXISTS test (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)";
    return $db->Execute($sql) !== false;
}

function testDbCount() {
    global $config;
    $db = new Database($config['db_path']);
    $before = $db->Count("test");
    $db->Create("test", ['name' => 'test count']);
    $after = $db->Count("test");
    return $after === $before + 1;
}

function testDbCreate() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['title' => 'test create']);
    return is_numeric($id);
}

function testDbRead() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['title' => 'test read']);
    $page = $db->Read("pages", $id);
    return $page['title'] === 'test read';
}

function testDbUpdate() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['title' => 'test update']);
    $db->Update("pages", ['title' => 'updated title'], $id);
    $page = $db->Read("pages", $id);
    return $page['title'] === 'updated title';
}

function testDbDelete() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['title' => 'test delete']);
    $db->Delete("pages", $id);
    $page = $db->Read("pages", $id);
    return $page === false;
}

function testDbFetchAll() {
    global $config;
    $db = new Database($config['db_path']);
    $db->Create("test page", ['name' => 'page fetch all']);
    $rows = $db->FetchAll("SELECT * FROM pages");
    return is_array($rows) && count($rows) > 0;
}

function testRenderPage() {
    $temp = new Page('layout', __DIR__ . "/../site/templates");
    $temp->render("index.tpl", ['title'=>"TEST_PAGE", 'message' => 'TEST PAGE FOR TESTING']);
}

$tests->add('Database Connection', 'testDatabaseConnection');
$tests->add('Database Execute', 'testDatabaseExecute');
$tests->add('Database Count', 'testDbCount');
$tests->add('Database Create', 'testDbCreate');
$tests->add('Database Read', 'testDbRead');
$tests->add('Database Update', 'testDbUpdate');
$tests->add('Database Delete', 'testDbDelete');
$tests->add('Database Fetch All', 'testDbFetchAll');
$tests->add('Render Page', 'testRenderPage');
$tests->run();

echo $tests->getResult();

```