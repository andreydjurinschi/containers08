<?php

use site\modules\page\Page;
require_once __DIR__ . "/modules/page.php";
$temp = new Page('layout', __DIR__ . "/templates");
$temp->render("index.tpl", ['title'=>"TEST_PAGE", 'message' => 'HELLO BRATISHKA ITS INDEX PAGE']);