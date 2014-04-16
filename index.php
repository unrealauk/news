<?php
require_once "inc/config.php";
require_once "inc/lang.php";
require_once "inc/function.php";
route($_GET['action']);
login();
include "tmpl/carcas.php";