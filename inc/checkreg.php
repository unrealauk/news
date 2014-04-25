<?php
/**
 * Created by PhpStorm.
 * User: auk
 * Date: 4/25/14
 * Time: 6:07 PM
 */
require_once "config.php";
require_once "lang.php";
  $STH = $DBH->prepare("SELECT * 	FROM user WHERE login=:login ");
  $data = array('login' => $_POST['login']);
  $STH->execute($data);
  if ($STH->rowCount() >= 1) {
    echo print_lg('This login is already taken', $_SESSION['lang']);
  }
  else {
    echo '';
  }

$STH = $DBH->prepare("SELECT * 	FROM user WHERE email=:email ");
$data = array('email' => $_POST['email']);
$STH->execute($data);
  if ($STH->rowCount() >= 1) {
    echo print_lg('This email is already taken', $_SESSION['lang']);
  }
  else {
    echo '';
  }
