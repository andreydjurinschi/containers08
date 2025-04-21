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
    $sql = "CREATE TABLE IF NOT EXISTS pages (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)";
    return $db->Execute($sql) !== false;
}

function testDbCount() {
    global $config;
    $db = new Database($config['db_path']);
    $before = $db->Count("test");
    $db->Create("pages", ['name' => 'pages count']);
    $after = $db->Count("pages");
    return $after === $before + 1;
}

function testDbCreate() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['name' => 'test create']);
    return is_numeric($id);
}

function testDbRead() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['name' => 'test read']);
    $page = $db->Read("pages", $id);
    return $page['title'] === 'test read';
}

function testDbUpdate() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['title' => 'test update']);
    $db->Update("pages", ['name' => 'updated title'], $id);
    $page = $db->Read("pages", $id);
    return $page['title'] === 'updated title';
}

function testDbDelete() {
    global $config;
    $db = new Database($config['db_path']);
    $id = $db->Create("pages", ['name' => 'test delete']);
    $db->Delete("pages", $id);
    $page = $db->Read("pages", $id);
    return $page === false;
}

function testDbFetchAll() {
    global $config;
    $db = new Database($config['db_path']);
    $db->Create("page", ['name' => 'page fetch all']);
    $rows = $db->FetchAll("SELECT * FROM pages");
    return is_array($rows) && count($rows) > 0;
}

function testRenderPage() {
    $temp = new Page('layout', __DIR__ . "/../site/templates");
    $temp->render("index.tpl", ['title'=>"TEST_PAGE", 'message' => 'TEST PAGE FOR TESTING']);
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
