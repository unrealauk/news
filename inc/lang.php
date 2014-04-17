<?php
/**
 * Created by PhpStorm.
 * User: auk
 * Date: 4/16/14
 * Time: 6:00 PM
 */
//Set language
if ($_GET['action'] == 'ua') {
  $_SESSION['lang'] = 'ua';
  header("Location: /news/");
  exit;
}

if ($_GET['action'] == 'en') {
  $_SESSION['lang'] = 'en';
  header("Location: /news/");
  exit;
}
$language = array();
function print_lg($text_en, $lg = 'en') {
  global $DBH;
  $STH = $DBH->prepare("SELECT * FROM lang WHERE text_en=:text_en");
  $data = array('text_en' => $text_en);
  $STH->execute($data);
  if ($STH->rowCount() == 1) {
    $row = $STH->fetch(PDO::FETCH_ASSOC);
    if ($lg == 'en') {
      return $row['text_en'];
    }
    else {
      return $row['text_ua'];
    }
  }
  else {
    $STH = $DBH->prepare("INSERT INTO lang SET text_en=:text_en");
    $data = array('text_en' => $text_en);
    $STH->execute($data);
    return $text_en;
  }
}
