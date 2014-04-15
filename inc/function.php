<?php
//Check access
function check_accses($rules, $action) {
  switch ($action) {
    case 'main':
      return in_array($rules, array('baned'));
      break;
    case 'add':
      return in_array($rules, array('admin', 'editor'));
      break;
    case 'add_news':
      return in_array($rules, array('admin', 'editor'));
      break;
  }
}

// Redirect Pages and run function
function route($action) {
  global $err;
  switch ($action) {
    case '':
      main();
      break;
    case 'mode_god':
      mode_god();
      break;
    case 'user_info':
      user_info();
      break;
    case 'show_news':
      show_news();
      break;
    case 'logout':
      logout();
      break;
    case 'add_news':
      add_news();
      break;
    case 'registration':
      registration();
      break;
    case 'delete_news':
      delete_news();
      break;
    case 'edit_news':
      edit_news();
      break;
    case 'pages':
      main();
      break;
    case 'profileview':
      profileview();
      break;
    case 'delete_user':
      delete_user();
      break;
    case 'mode_god_delete':
      mode_god_delete();
      break;
    case 'mode_god_edit':
      mode_god_edit();
      break;
    default:
      $err .= 'Page not found';
      break;
  }
}

//Show main page
function main() {
  global $DBH, $html_main_content, $on_page;
//Check access
  if (check_accses($_SESSION['rules'], 'main')) {
    $html_main_content .= 'You baned on this site pls contact admin@mail.ua Sorry but u can`t login<br> ';
    session_unset();
    session_destroy();
  }
//Pages
  $STH = $DBH->query('SELECT * from news');
  $count_records = $STH->rowCount();
  $num_pages = ceil($count_records / $on_page);
  $current_page = isset($_GET['id']) ? (int) $_GET['id'] : 1;
  if ($current_page < 1) {
    $current_page = 1;
  }
  elseif ($current_page > $num_pages) {
    $current_page = $num_pages;
  }
  $start_from = ($current_page - 1) * $on_page;
  $STH = $DBH->prepare("SELECT * from news LIMIT :start_from,:on_page");
  $STH->bindParam(':start_from', $start_from, PDO::PARAM_INT);
  $STH->bindParam(':on_page', $on_page, PDO::PARAM_INT);
  $STH->execute();
  while ($row = $STH->fetch(PDO::FETCH_ASSOC)) {
    $html_main_content .= '
    <b>Title: </b><a href="/news/show_news/' . $row['id'] . '">' . $row['title'] . '</a>
    <b>Rating: </b>' . $row['rating'] . '<br>';
    $encod = mb_detect_encoding($row['text']);
    if (mb_strlen($row['text'], $encod) >= 150) {
      $html_main_content .= '<b>Text: </b>' . trimming_line($row['text'], 150) .
        '<br><a href="/news/show_news/' . $row['id'] . '">Read more</a><br>';
    }
    else {
      $html_main_content .= '<b>Text: </b>' . $row['text'] . '<br>';
    }
    $html_main_content .= '<b>Author: </b>
    <a href="/news/profileview/' . $row['author'] . '">' . $row['author'] . '</a><br>
    <b>Date: </b>' . $row['date'] . '<br><hr>';
  }
  if ($num_pages != 1) {
    $html_main_content .= '<p>';
    for ($page = 1; $page <= $num_pages; $page++) {
      if ($page == $current_page) {
        $html_main_content .= '<strong>' . $page . '</strong> &nbsp;';
      }
      else {
        $html_main_content .= '<a href="/news/pages/' . $page . '">' . $page . '</a> &nbsp;';
      }
    }
    $html_main_content .= '</p>';
  }
}

//Image upload
function image_upload() {
  global $er;
  if (isset($_FILES['file']) && $_FILES['file']['error'] != 4) {
    $filename = $filepath = $filetype = '';
    if ($_FILES['file']['error'] != 1 && $_FILES['file']['error'] != 0) {
      $error = $_FILES['file']['error'];
      $er .= 'Error: file not loaded. Error code: ' . $error . '<br>';

    }
    else {
      $filesize = $_FILES['file']['size'];
      if ($_FILES['file']['error'] == 1 || $filesize > 3145728) {
        $filesize = ($filesize != 0) ?
          sprintf('(%.2f Мб)', $filesize / 1024) : '';
        die($er .= 'Error: File size image' . $filesize . 'more acceptable (3 MB).<br>');
      }
      else {
        $filename = $_FILES['file']['name'];
        $filepath = $_FILES['file']['tmp_name'];
        $filetype = $_FILES['file']['type'];
      }
    }
    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
      $uploaddir = $_SERVER['DOCUMENT_ROOT'] . '/news/images/';
      $uploadfile = $uploaddir . basename($_FILES['file']['name']);
      $imageinfo = getimagesize($_FILES['file']['tmp_name']);
      if ($imageinfo['mime'] != 'image/gif' && $imageinfo['mime'] != 'image/jpeg' && $imageinfo['mime'] != 'image/jpg' && $imageinfo['mime'] != 'image/png') {
        $er .= 'Sorry, you can only upload GIF, JPEG, PNG image <br>';
      }
      else {
        move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
        return $img = basename($_FILES['file']['name']);
      }
    }
  }
}

//Show User Info
function user_info() {
  global $DBH, $html_main_content, $er;

  if (isset($_POST['submit'])) {
    if (($_POST['password'] != '') && ($_POST['password'] != $_POST['rpassword'])) {
      $er .= 'Passwords no match<br>';
    }
    $STH = $DBH->prepare("SELECT * FROM user WHERE email=:email ");
    $data = array('email' => $_POST['email']);
    $STH->execute($data);
    if ($STH->rowCount() >= 1) {
      $er .= "This email is already taken<br>";
    }
    $pos = mb_strrpos($_POST['email'], '@');
    if ($pos == 0 && $_POST['email'] != '') {
      $er .= 'incorrect email addresses Example: mail@example.com<br>';
    }
    $img = image_upload();
    if ($er == '') {
      if ($img == '') {
        $img = $_POST['avatar'];
      }
      $login = $_POST['login'];
      $_SESSION['rules'] != 'admin' ? $_SESSION['login'] = $login : '';
      $lastname = $_POST['lastname'];
      $name = $_POST['name'];
      $surname = $_POST['surname'];
      $sql = "UPDATE user SET";
      if ($_POST['password'] !== '') {
        $password = md5(trim($_POST['password']));
        $sql .= " password=:password,";
      }
      if ($_POST['email'] !== '') {
        $sql .= " email=:email,";
      }
      $sql .= " lastname=:lastname, name=:name, surname=:surname, avatar=:avatar WHERE login=:login";
      $STH = $DBH->prepare($sql);
      if ($_POST['password'] !== '') {
        $STH->bindParam(':password', $password);
      }
      if ($_POST['email'] !== '') {
        $STH->bindParam(':email', $_POST['email']);
      }
      $STH->bindParam(':lastname', $lastname);
      $STH->bindParam(':name', $name);
      $STH->bindParam(':surname', $surname);
      $STH->bindParam(':avatar', $img);
      $STH->bindParam(':login', $login);
      $STH->execute();
      $html_main_content .= 'You information upadte sucsesful<br>';
    }
    $html_main_content .= $er;
    $_FILES['file']['error'] = '';
  }
  $STH = $DBH->prepare("SELECT * FROM user WHERE login=:login");
  $data = array('login' => $_GET['id']);
  $STH->execute($data);
  $row = $STH->fetch(PDO::FETCH_ASSOC);
  $html_main_content .= '<form method="post" enctype="multipart/form-data">
<table><tr><td><b>Avatar</b></td><td><img src="/news/images/';
  if ($row['avatar'] == '') {
    $html_main_content .= 'noimage.jpeg';
  }
  else {
    $html_main_content .= $row['avatar'];
  }
  $html_main_content .= '"width="150px" height="150px"></td></tr>
<tr><td><b>Email</b></td><td><input type=text name="email" value=""></td></tr>
<tr><td><b>Surname</b></td><td><input type=text name="surname" value="' . $row['surname'] . '"></td></tr>
<tr><td><b>Name</b></td><td><input type=text name="name" value="' . $row['name'] . '"></td></tr>
<tr><td><b>Lastname</b></td><td><input type=text name="lastname"value="' . $row['lastname'] . '"></td></tr>
<tr><td><b>Password</b></td><td><input type="Password" name="password"></td></tr>
<tr><td><b>RetryPassword</b></td><td><input type="Password" name="rpassword"></td></tr>
<tr><td><b>EditAvatar</b></td><td><input type="file" name="file" size="30" /></td></tr>
<input type="hidden" name="login" value="' . $row['login'] . '">
<input type="hidden" name="avatar" value="' . $row['avatar'] . '"></td></tr>
<tr><td colspan="2"><b><input type="submit" value="ok" name="submit"></td></tr>
</table>
</form>';
}

//More information news
function show_news() {
  global $DBH, $html_main_content, $title;
  if ($_POST['submit_show']) {
    $STH = $DBH->prepare("INSERT INTO comments SET news_id=:id,title=:title,text=:text,author=:author,date=:date  ");
    $data = array(
      'title' => $_POST['title'],
      'text' => $_POST['text'],
      'author' => $_SESSION['login'],
      'id' => $_GET['id'],
      'date' => DATE('Y-m-d')
    );
    $STH->execute($data);
  }
  $STH = $DBH->prepare("SELECT * FROM news WHERE id=:id ");
  $data = array('id' => $_GET['id']);
  $STH->execute($data);
  $row = $STH->fetch(PDO::FETCH_ASSOC);
  $author = $row['author'];
  $title = $row['title'];
  $html_main_content .= '<h2 class="title">' . $row['title'] . '</h2>
  <b>rating: </b>' . $row['rating'] . '<br><b>Text: </b>' . $row['text'] . '
  <br><b>Author: </b><a href="/news/profileview/' . $row['author'] . '">' . $row['author'] . '</a><br>
  <b>Date: </b>' . $row['date'] . '<br><hr>';
  $STH = $DBH->prepare("SELECT * FROM  comments WHERE  news_id =  :id ORDER BY  id ASC ");
  $data = array('id' => $_GET['id']);
  $STH->execute($data);
  if ($STH->rowCount() != 0) {
    $html_main_content .= '<br><b>Comments</b><hr>';
  }
  while ($row = $STH->fetch(PDO::FETCH_ASSOC)) {
    $html_main_content .= '<b>Title: </b>' . $row['title'] . '<br>
    <b>Text: </b>' . $row['text'] . '<br>
    <b>Author: </b><a href="/news/profileview/' . $row['author'] . '">' . $row['author'] . '</a><br>
    <b>Date: </b>' . $row['date'] . '<br><hr>';
  }
  // If u author u have more privilege
  if ($_SESSION['login'] == $author || $_SESSION['rules'] == 'admin') {
    $html_main_content .= '<br><a href="/news/edit_news/' . $_GET['id'] . '">Edit news</a><br>
    <a href="/news/delete_news/' . $_GET['id'] . '">Delete news</a><hr><br>';
  }
  if ($_SESSION['login']) {
    $html_main_content .= '<b>Add Coment</b><form  method="post">
<label for="title"><b>Title: </b></label>
<input  name="title" value="" type="text" size="32"/><br>
<b>Text: </b><br><textarea cols="50" rows="10" name="text"></textarea>
<br><input name="submit_show" type="submit"  value="ок"></form>';
  }
}

//Logout
function logout() {
  session_unset();
  session_destroy();
  header("Location: /news/");
  exit;
}

//Add news
function add_news() {
  global $DBH, $html_main_content;
  if ((check_accses($_SESSION['rules'], 'add')) && isset($_SESSION['login'])) {
    if (isset($_POST['submit_add'])) {
      if (($_POST['title'] !== '') && (($_POST['text'] !== ''))) {
        $STH = $DBH->prepare("INSERT INTO news SET title=:title, text=:text, author=:author, rating='0',date=:date");
        $data = array(
          'title' => $_POST['title'],
          'text' => $_POST['text'],
          'author' => $_SESSION['login'],
          'date' => DATE('Y-m-d')
        );
        $STH->execute($data);
        header("Location: /news/show/" . $DBH->lastInsertId() . '');
        exit;
      }
      else {
        $html_main_content .= 'Write title & text <br>';
      }
    }
    $html_main_content .= '<form method="post" name="add">
      <b>Title: </b><input type="text" name="title"><br>
      <b>Text: </b><br><textarea name="text" cols="40" rows="5"> </textarea><br>
      <input type="submit" value="ok" name="submit_add"></form>';
  }
  else {
    $html_main_content .= 'Failed u don`t have rules <br>';
  }
}

//Delete news and all comments
function delete_news() {
  global $DBH, $html_main_content;
  $STH = $DBH->prepare("SELECT * FROM news WHERE id=:id ");
  $data = array('id' => $_GET['id']);
  $STH->execute($data);
  $row = $STH->fetch(PDO::FETCH_ASSOC);
  if ($_SESSION['login'] == $row['author'] || $_SESSION['rules'] == 'admin') {
    $STH = $DBH->prepare("Delete FROM news WHERE id=:id;Delete FROM comments WHERE news_id=:id ");
    $STH->execute($data);
    header("Location: /news/");
    exit;
  }
  else {
    $html_main_content .= "<b>You have not enough rights</b>";
  }
}

//Edit news
function edit_news() {
  global $DBH, $html_main_content;
  if (isset($_POST['submit_edit'])) {
    if ($_POST['title'] != '' && $_POST['text'] != '') {
      $STH = $DBH->prepare("UPDATE news Set  title=:title, text=:text,date=:date where id=:id");
      $data = array('title' => $_POST['title'], 'text' => $_POST['text'], 'id' => $_GET['id'], 'date' => DATE('Y-m-d'));
      $STH->execute($data);
      header("Location: /news/show/" . $_GET['id'] . '');
      exit;
    }
  }
  else {
    $STH = $DBH->prepare("SELECT * 	FROM news WHERE id=:id ");
    $data = array('id' => $_GET['id']);
    $STH->execute($data);
    $row = $STH->fetch(PDO::FETCH_ASSOC);
    if ($_SESSION['login'] == $row['author'] || $_SESSION['rules'] == 'admin') {
      $html_main_content .= '<form method="post" name="news_edit">
  <b>Title: </b><input type="text" name="title"  value="' . $row['title'] . '"><br>
  <b>Text: </b><br><textarea cols="40" rows="5" name="text">' . $row['text'] . '</textarea><br>
  <input type="submit" name="submit_edit" value="ok"></form>';
    }
    else {
      $html_main_content .= "<b>You have not enough rights</b>";
    }
  }
}

//Regitration user
function registration() {
  global $DBH, $html_main_content, $er;
  if ($_POST['submit_registration']) {
    $STH = $DBH->prepare("SELECT * 	FROM user WHERE login=:login or email=:email ");
    $data = array('login' => $_POST['login'], 'email' => $_POST['email']);
    $STH->execute($data);
    if ($STH->rowCount() >= 1) {
      $er .= "This login or email is already taken<br>";
    }
    if ((empty($_POST['login'])) && (empty($_POST['password'])) && (empty($_POST['email']))) {
      $er .= "You need write required fields <br>";
    }
    if ($_POST['password'] != $_POST['rpassword']) {
      $er .= 'Passwords no match<br>';
    }
    $pos = mb_strrpos($_POST['email'], '@');
    if ($pos == 0) {
      $er .= 'Incorrect email addresses Example: mail@example.com<br>';
    }
    $img = image_upload();
    $sucs = 0;
    if ($er == '') {
      if (empty($img)) {
        $img = '';
      }
      $password = md5(trim($_POST['password']));
      $STH = $DBH->prepare("INSERT INTO user ( login, name, surname, lastname, rules, password, avatar,email,date_reg,date_login)
     VALUES ( :login, :name, :surname, :lastname, 'user', :password, :avatar,:email,:date_reg,:date_login)");
      $data = array(
        'login' => $_POST['login'],
        'name' => $_POST['name'],
        'surname' => $_POST['surname'],
        'lastname' => $_POST['lastname'],
        'password' => $password,
        'avatar' => $img,
        'email' => $_POST['email'],
        'date_reg' => DATE('Y-m-d'),
        'date_login' => DATE('Y-m-d')
      );
      $STH->execute($data);
      $html_main_content .= "You register successful<br>";
      $sucs = 1;
    }
    $html_main_content .= $er;
    $_FILES['file']['error'] = '';
  }
  if ($sucs == 0) {
    $html_main_content .= 'Required Field *<br>
<form method="post" enctype="multipart/form-data"><table>
<tr><td><b>Login *</b></td><td><input type=text name="login"></td></tr>
<tr><td><b>Email *</b></td><td><input type=text name="email"></td></tr>
<tr><td><b>Password *</b></td><td><input type="Password" name="password"></td></tr>
<tr><td><b>Retry password *</b></td><td><input type="Password" name="rpassword"></td></tr>
<tr><td><b>Surname</b></td><td><input type=text name="surname"></td></tr>
<tr><td><b>Name</b></td><td><input type=text name="name" ></td></tr>
<tr><td><b>LastName</b></td><td><input type=text name="lastname"></td></tr>
<tr><td><b>Avatar</b></td><td><input type="file" name="file" size="30" /></td></tr>
<tr><td><input type="submit" value="ok" name="submit_registration"></td></tr></table></form>';
  }
}

function login() {
  global $DBH, $html_login_form, $err;
  if ((!empty($_POST['login'])) && (!empty($_POST['password']))) {
    $login = $_POST['login'];
    $password = md5($_POST['password']);
    $STH = $DBH->prepare("SELECT * 	FROM user WHERE login=:login AND password=:password LIMIT 1");
    $data = array('login' => $login, 'password' => $password);
    $STH->execute($data);
    if ($STH->rowCount() == 1) {
      $row = $STH->fetch(PDO::FETCH_ASSOC);
      $_SESSION = array_merge($_SESSION, $row);
      $STH = $DBH->prepare("UPDATE user Set date_login=:date_login where login=:login");
      $data = array('date_login' => DATE('Y-m-d'), 'login' => $login);
      $STH->execute($data);
      header("Location: /news/");
    }
    else {
      if ($_POST['submit_login']) {
        $err .= 'Incorect login or pass</br>';
      }
    }
  }
  else {
    if ($_POST['submit_login']) {
      $err .= 'Write login and pass</br>';
    }
  }
  if (isset($_SESSION['login'])) {
    $html_login_form .= 'You enter as <b>' . $_SESSION['login'] . '</b><br>
   <a href="/news/logout/">Logout</a><br>
   <a href="/news/profileview/' . $_SESSION['login'] . '">Your Profile</a><br>';
    if (check_accses($_SESSION['rules'], 'add_news')) {
      $html_login_form .= '<a href="/news/add_news/">Add news</a></br>
      <a href="/news/mode_god/">Mode god</a>';
    }
  }
  else {
    $html_login_form .= $err;
    $html_login_form .= '<form method="post" name="login">
   <b>Name:</b><input name="login" size="20" type="text">
   <b>Password:</b><input name="password" type="password">
   <input name="submit_login" type="submit" value="ok"></form><br>
   <a href="/news/registration/">Registration</a>';
  }
}

//Str triming
function trimming_line($string, $length = 150) {
  ++$length;
  $encod = mb_detect_encoding($string);
  if ($length && mb_strlen($string) > $length) {
    $str = mb_substr($string, 0, $length - 1);
    $pos = mb_strrpos($string, ' ');
    return mb_substr($str, 0, $pos - 1) . ' ... ';
  }
  return $string;
}

//Profile detail
function profileview() {
  global $html_main_content, $DBH;
  $STH = $DBH->prepare("SELECT * FROM user WHERE login=:login");
  $data = array('login' => $_GET['id']);
  $STH->execute($data);
  $row = $STH->fetch(PDO::FETCH_ASSOC);
  $html_main_content .= '<table><tr><td><b>Avatar</b></td><td><img src="/news/images/';
  if ($row['avatar'] == '') {
    $html_main_content .= 'noimage.jpeg';
  }
  else {
    $html_main_content .= $row['avatar'];
  }
  $html_main_content .= '"width="150px" height="150px"></td></tr>';
  if (isset($_SESSION['login'])) {
    $html_main_content .= '<tr><td><b>Email</b></td><td>' . $row['email'] . '</td></tr>';
  }
  $html_main_content .= '<tr><td><b>Login</b></td><td>' . $row['login'] . '</td></tr>
<tr><td><b>Surname</b></td><td>' . $row['surname'] . '</td></tr>
<tr><td><b>Name</b></td><td>' . $row['name'] . '</td></tr>
<tr><td><b>Lastname</b></td><td>' . $row['lastname'] . '</td></tr>
<tr><td><b>Rules</b></td><td>' . $row['rules'] . '</td></tr>
<tr><td><b>Registration date:</b></td><td>' . $row['date_reg'] . '</td></tr>
<tr><td><b>Last login:</b></td><td>' . $row['date_login'] . '</td></tr></table>';
  if ($_SESSION['login'] == $_GET['id'] || $_SESSION['rules'] == 'admin') {
    $html_main_content .= '<a href = "/news/user_info/' . $_GET['id'] . '" > Edit information </a ><br >
    <a href="/news/delete_user/' . $_GET['id'] . '">Delete user</a>';
  }
}

//Delete user
function delete_user() {
  global $html_main_content, $DBH;
  if ($_SESSION['login'] == $_GET['id'] || $_SESSION['rules'] == 'admin') {
    $STH = $DBH->prepare("Delete FROM user WHERE login=:login;
  Delete FROM comments WHERE author=:login;
  Delete FROM news WHERE author=:login;");
    $data = array('login' => $_GET['id']);
    $STH->execute($data);
    if ($_SESSION['rules'] == 'admin') {
      $html_main_content .= 'Profile & all comments ' . $_GET['id'] . 'will be delete ';
    }
    else {
      session_unset();
      session_destroy();
      $html_main_content .= 'You profile will be delete & all comments';
    }
  }
}

function mode_god() {
  global $html_main_content, $DBH;
  $STH = $DBH->query("SELECT * FROM user ");
  $html_main_content .= '<table>';
  while ($row = $STH->fetch(PDO::FETCH_ASSOC)) {
    $html_main_content .= '<tr>
    <td><b>Login:</b></td><td>' . $row['login'] . '</td>
    <td><b>Email:</b></td><td>' . $row['email'] . '</td>
    <td><b>Surname:</b></td><td>' . $row['surname'] . '</td>
    <td><b>Name:</b></td><td>' . $row['name'] . '</td>
    <td><b>Lastname:</b></td><td>' . $row['lastname'] . '</td>
    <td><b>Rules:</b></td><td>' . $row['rules'] . '</td>
   <td>
   <a href="/news/mode_god_edit/' . $row['login'] . '"><img src=/news/images/edit.png></a>
   <a href="/news/mode_god_delete/' . $row['login'] . '"><img src=/news/images/delete.gif></a>
   </td></tr>';
  }
  $html_main_content .= '</table>';

}

function mode_god_edit() {
  global $html_main_content, $DBH;
  if ($_SESSION['rules'] == 'admin') {
    $STH = $DBH->prepare("Select * FROM user WHERE login=:login");
    $data = array('login' => $_GET['id']);
    $STH->execute($data);
    $row = $STH->fetch(PDO::FETCH_ASSOC);
    $html_main_content .= '<form method="post" enctype="multipart/form-data">
<table><tr><td><b>Avatar</b></td><td>
<img src="/news/images/';
    if ($row['avatar'] == '') {
      $html_main_content .= 'noimage.jpeg';
    }
    else {
      $html_main_content .= $row['avatar'];
    }
    $html_main_content .= '"width="150px" height="150px"></td></tr>
<tr><td><b>Login</b></td><td><input type="text" name="login" value="' . $row['login'] . '"></td></tr>
<tr><td><b>Email</b></td><td><input type=text name="email" value="' . $row['email'] . '"></td></tr>
<tr><td><b>Surname</b></td><td><input type=text name="surname" value="' . $row['surname'] . '"></td></tr>
<tr><td><b>Name</b></td><td><input type=text name="name" value="' . $row['name'] . '"></td></tr>
<tr><td><b>Lastname</b></td><td><input type=text name="lastname"value="' . $row['lastname'] . '"></td></tr>
<tr><td><b>Date reg</b></td><td><input type=text name="lastname"value="' . $row['date_reg'] . '"></td></tr>
<tr><td><b>Last login</b></td><td><input type=text name="lastname"value="' . $row['date_login'] . '"></td></tr>
<tr><td><b>Rules</b></td><td>
<select >
<option ';
    if ($row['rules'] == 'user') {
      $html_main_content .= 'selected ';
    }
    $html_main_content .= 'value="user">user</option><option ';
    if ($row['rules'] == 'editor') {
      $html_main_content .= 'selected ';
    }
    $html_main_content .= 'value="editor">editor</option><option ';
    if ($row['rules'] == 'baned') {
      $html_main_content .= 'selected ';
    }
    $html_main_content .= 'value="baned">baned</option><option ';
    if ($row['rules'] == 'admin') {
      $html_main_content .= 'selected ';
    }
    $html_main_content .= 'value="admin">admin</option>';
    $html_main_content .= '  </select>
 </td></tr>
<tr><td><b>Password</b></td><td><input type="Password" name="password"></td></tr>
<tr><td><b>RetryPassword</b></td><td><input type="Password" name="rpassword"></td></tr>
<tr><td><b>EditAvatar</b></td><td><input type="file" name="file" size="30" /></td></tr>
<input type="hidden" name="avatar" value="' . $row['avatar'] . '"></td></tr>
<tr><td colspan="2"><b><input type="submit" value="ok" name="submit"></td></tr>
</table>
</form>';
  }
}

function mode_god_delete() {
  global $html_main_content, $DBH;
  if ($_SESSION['rules'] == 'admin') {
    $STH = $DBH->prepare("Delete FROM user WHERE login=:login;
  Delete FROM comments WHERE author=:login;
  Delete FROM news WHERE author=:login;");
    $data = array('login' => $_GET['id']);
    $STH->execute($data);
    $html_main_content .= 'Profile & all comments ' . $_GET['id'] . 'will be delete <br>';
    $html_main_content .= '<a href="/news/mode_god/">Back</a>';
  }
}