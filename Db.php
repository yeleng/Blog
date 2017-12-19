<?php
header("Content-Type:text/html;charset=UTF-8");
//连接数据库返回句柄
$pdo = new PDO('mysql:host=localhost;dbname=数据库名字','root','密码'); 
$pdo->query("SET NAMES utf8");     
//需要在句柄加这么一句话
return $pdo;
?>