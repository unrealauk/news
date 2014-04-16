<?php
/**
 * Created by PhpStorm.
 * User: auk
 * Date: 4/16/14
 * Time: 6:00 PM
 */
//Set language
if ($_GET['action'] == 'ru') {
  $_SESSION['lang'] = 'ru';
  header("Location: /news/");
  exit;
}

if ($_GET['action'] == 'en') {
  $_SESSION['lang'] = 'en';
  header("Location: /news/");
  exit;
}
if (isset($_SESSION['lang'])) {
  $DefaultLang = $_SESSION['lang'];
}
else {
  $DefaultLang = "en";
}
//Greate array: $language
if ($DefaultLang == 'en') {
  $STH = $DBH->query('SELECT * from lang_eng');
}else
{$STH = $DBH->query('SELECT * from lang_ru');}
$language = array();
while ($row = $STH->fetch(PDO::FETCH_ASSOC)) {
  $language[$row['alias']] = $row['text'];
}