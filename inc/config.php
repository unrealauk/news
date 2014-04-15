<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
session_start();
date_default_timezone_set('Europe/Moscow');
$host = 'localhost';
$dbname = 'news';
$user = 'root';
$pass = 'root';
$err= '';
// Количество новостей на странице
$on_page = 2;
$html_login_form = '';
$html_main_content = '';
$title ='My site';
$er = '';
try {
  # MySQL через PDO_MYSQL
  $DBH = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
  echo $e->getMessage();
}
$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);