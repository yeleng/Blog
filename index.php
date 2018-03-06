<?php
header("Content-Type:text/html;charset=UTF-8");
require 'User.php';
require 'Article.php';
$pdo = require 'Db.php';

$user = new User($pdo);
$article =new Article($pdo); //构造函数传入的为数据库的句柄

print_r('55');
?>