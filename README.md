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
    $db = new Database($config['db_path']);
    return $db instanceof Database;
}

function testDbExecute() {
    global $config;
    $db = new Database($config['db_path']);
    $sql = "CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        content TEXT
    )";
    return $db->Execute($sql) !== false;
}

function testDbCount() {
    global $config;
    $db = new Database($config['db_path']);
    $before = $db->Count("pages");
    $db->Create("pages", ['title' => 'count test', 'content' => '...']);
    $after = $db->Count("pages");
    return $after === $before + 1;
}

function testDbCreate() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['title' => 'create test', 'content' => '...']);
    return is_numeric($id);
}

function testDbRead() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['title' => 'read test', 'content' => 'read']);
    $row = $db->Read("pages", $id);
    return $row['title'] === 'read test';
}

function testDbUpdate() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['title' => 'old title', 'content' => '...']);
    $db->Update("pages", ['title' => 'new title', 'content' => '...'], $id);
    $row = $db->Read("pages", $id);
    return $row['title'] === 'new title';
}

function testDbDelete() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['title' => 'to delete', 'content' => '...']);
    $db->Delete("pages", $id);
    $row = $db->Read("pages", $id);
    return $row === false || $row === null;
}

function testDbFetchAll() {
    global $config;
    $db = new Database($config['db_path']);
    $db->Create("pages", ['title' => 'fetch test', 'content' => '...']);
    $rows = $db->FetchAll("SELECT * FROM pages");
    return is_array($rows) && count($rows) > 0;
}

function testRenderPage() {
    $page = new Page('layout', __DIR__ . '/../site/templates');
    ob_start();
    $page->Render('index.tpl', ['title' => 'TEST_PAGE', 'message' => 'TEST PAGE FOR TESTING']);
    ob_end_clean();
    return true;
}

$tests->add('Database Connection', 'testDatabaseConnection');
$tests->add('Database Execute', 'testDbExecute');
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
7. Создаем `Dockerfile`

```dockerfile
FROM php:7.4-fpm as base
RUN apt-get update && \
    apt-get install -y sqlite3 libsqlite3-dev && \
    docker-php-ext-install pdo_sqlite
VOLUME ["/var/www/db"]
COPY sql/schema.sql /var/www/db/schema.sql
RUN echo "prepare database" && \
    cat /var/www/db/schema.sql | sqlite3 /var/www/db/db.sqlite && \
    chmod 777 /var/www/db/db.sqlite && \
    rm -rf /var/www/db/schema.sql && \
    echo "database is ready"
COPY site /var/www/html
```
8. .github/workflows/main.yml

```yaml
name: CI
on:
  push:
    branches:
      - main
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4
      - name: Build the Docker image
        run: docker build -t containers08 .
      - name: Create `container`
        run: docker create --name container --volume database:/var/www/db containers08
      - name: Copy tests and site to the container
        run: |
          docker cp ./tests container:/var/www/html
          docker cp ./site container:/var/www/html
      - name: Up the container
        run: docker start container
      - name: Run tests
        run: docker exec container php /var/www/html/tests/tests.php
      - name: Stop the container
        run: docker stop container
      - name: Remove the container
        run: docker rm container
```

Данный файл запускает контейнер, копирует в него файлы с тестами и запускает их. После завершения работы контейнер останавливается и удаляется.

![](https://i.imgur.com/hSzw1GL.png)

> Что такое непрерывная интеграция?

Непрерывная интеграция - это практика разработки программного обеспечения, при которой разработчики регулярно интегрируют свои изменения в общий код. Каждый раз, когда разработчик вносит изменения в код, они автоматически тестируются и собираются с помощью инструментов CI/CD. Это позволяет быстро выявлять и исправлять ошибки, а также поддерживать высокое качество кода

> Для чего нужны юнит-тесты? Как часто их нужно запускать?

Юнит-тесты - это автоматизированные тесты, которые проверяют отдельные части кода (юниты) на корректность работы. Они помогают выявлять ошибки на ранних стадиях разработки и обеспечивают уверенность в том, что изменения в коде не нарушают его функциональность. Юнит-тесты следует запускать как можно чаще, особенно перед каждым коммитом или при внесении изменений в код

> Что нужно изменить в файле .github/workflows/main.yml для того, чтобы тесты запускались при каждом создании запроса на слияние (Pull Request)?

```yaml
name: CI
on:
  push:
    branches:
      - main
  pull_request:
    branches:
      - main
```

> Что нужно добавить в файл .github/workflows/main.yml для того, чтобы удалять созданные образы после выполнения тестов?

```yml
      - name: Remove the container
        run: docker rm container  
      - name: Remove the image
        run: docker rmi containers08
```
Если образ сто процентов существует, то его можно удалить, но если он не существует, то будет ошибка. `|| true`,  игнорирует ошибку и продолжает выполнение скрипта (даже елис образа нет).
```yml
      - name: Remove the image
        run: docker rmi containers08 || true
```