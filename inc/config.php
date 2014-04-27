<?php
session_start();
try {
  # MySQL через PDO_MYSQL
  $DBH = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
  echo $e->getMessage();
}
$lg=$_SESSION['lang'];
$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$STH = $DBH->prepare("SELECT * FROM lang WHERE text_en=:text_en");
$data = array('text_en' => $_POST['lang']);
$STH->execute($data);
$z=$STH->rowCount();
if ($STH->rowCount() >= 1) {
  $row = $STH->fetch(PDO::FETCH_ASSOC);
  if ($lg == 'ua') {
    echo $row['text_ua'];
  }
  else {
    echo $row['text_en'];
  }
}
else {
  $STH = $DBH->prepare("INSERT INTO lang SET text_en=:text_en");
  $data = array('text_en' =>  $_POST['lang']);
  $STH->execute($data);
  echo $_POST['lang'];
}

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
session_start();
date_default_timezone_set('Europe/Moscow');
$host = 'localhost';
$dbname = 'news';
$user = 'root';
$pass = 'root';
$err = '';
$title='';
// Количество новостей на странице
$on_page = 3;
$html_login_form = '';
$html_main_content = '';
$er = '';
try {
  # MySQL через PDO_MYSQL
  $DBH = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
  echo $e->getMessage();
}
$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

function show_err(){
  global $html_main_content;
  $html_main_content.='<div class=err>'.$_SESSION['err'].'</div>';
  $_SESSION['err']='';
}