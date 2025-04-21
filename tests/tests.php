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
