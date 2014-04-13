<?php
//My change from home
// Redirect Pages and run function
function route($action) {
  global $err;
  switch ($action) {
    case '':
      main();
      break;
    case 'info':
      info();
      break;
    case 'show':
      show();
      break;
    case 'logout':
      logout();
      break;
    case 'add':
      add();
      break;
    case 'registration':
      registration();
      break;
    case 'delete':
      delete();
      break;
    case 'edit':
      edit();
      break;
    case 'pages':
      main();
      break;
    case 'profileview':
      profileview();
      break;
    default:
      $err .= 'Page not found';
      break;
  }
}

//Show main page
function main() {
  global $DBH, $html_main_content, $on_page;


// Получаем количество записей таблицы news
  $STH = $DBH->query('SELECT * from news');
  $count_records = $STH->rowCount();

// Получаем количество страниц
// Делим количество записей на количество новостей на странице
// и округляем в большую сторону
  $num_pages = ceil($count_records / $on_page);

// Текущая страница из GET-параметра page
// Если параметр не определен, то текущая страница равна 1
  $current_page = isset($_GET['id']) ? (int) $_GET['id'] : 1;

// Если текущая страница меньше единицы, то страница равна 1
  if ($current_page < 1) {
    $current_page = 1;
  }
// Если текущая страница больше общего количества страница, то
// текущая страница равна количеству страниц
  elseif ($current_page > $num_pages) {
    $current_page = $num_pages;
  }

// Начать получение данных от числа (текущая страница - 1) * количество записей на странице
  $start_from = ($current_page - 1) * $on_page;

// Формат оператора LIMIT <ЗАПИСЬ ОТ>, <КОЛИЧЕСТВО ЗАПИСЕЙ>
  $STH = $DBH->prepare("SELECT * from news LIMIT :start_from,:on_page");
  $STH->bindParam(':start_from', $start_from, PDO::PARAM_INT);
  $STH->bindParam(':on_page', $on_page, PDO::PARAM_INT);
  $STH->execute();

# выбираем режим выборки
  $STH->setFetchMode(PDO::FETCH_ASSOC);

# выводим результат
  while ($row = $STH->fetch()) {
    $html_main_content .= '
    <b>Title: </b><a href="/news/show/' . $row['id'] . '">' . $row['title'] . '</a>
    <b>rating: </b>' . $row['rating'] . '<br>';
    $encod = mb_detect_encoding($row['text']);
    if (mb_strlen($row['text'], $encod) >= 150) {

      $html_main_content .= '<b>Text: </b>' . trimming_line($row['text'], 150) .
        '<br><a href="/news/show/' . $row['id'] . '">Read more</a><br>';
    }
    else {
      $html_main_content .= '<b>Text: </b>' . $row['text'] . '<br>';
    }
    $html_main_content .= '<b>Author: </b>' . $row['author'] . '<br>
    <b>Date: </b>' . $row['date'] . '<br><hr>';
  }

// Вывод списка страниц
// Без вывода если у нас только 1 страница
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

//Show User Info
function info() {
  global $DBH, $html_main_content;
  $er = '';
//  $STH = $DBH->prepare("SELECT * FROM user WHERE login=:login");
//  $data = array('login' => $_SESSION['login']);
//  $STH->execute($data);
//  $STH->setFetchMode(PDO::FETCH_ASSOC);
//  $row = $STH->fetch();


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
          $img = basename($_FILES['file']['name']);
        }
      }
    }
    if ($er == '') {
      if ($img == '') {
        $img = $_POST['avatar'];
      }
      $login = $_POST['login'];
      $_SESSION['login'] = $login;
      $lastname = $_POST['lastname'];
      $name = $_POST['name'];
      $surname = $_POST['surname'];
      $rules = $_POST['rules'];
      $sql = "UPDATE user SET";
      if ($_POST['password'] !== '') {
        $password = md5(trim($_POST['password']));
        $sql .= " password=:password,";
      }
      if ($_POST['email'] !== '') {
        $sql .= " email=:email,";
      }
      $sql .= " lastname=:lastname, name=:name, surname=:surname, rules=:rules, avatar=:avatar WHERE login=:login";
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
      $STH->bindParam(':rules', $rules);
      $STH->bindParam(':avatar', $img);
      $STH->bindParam(':login', $login);

      $STH->execute();
      $html_main_content .= 'You information upadte sucsesful<br>';
    }
    $html_main_content .= $er;
    $_FILES['file']['error'] = '';
  }
  $STH = $DBH->prepare("SELECT * FROM user WHERE login=:login");
  $data = array('login' => $_SESSION['login']);
  $STH->execute($data);
  $STH->setFetchMode(PDO::FETCH_ASSOC);
  $row = $STH->fetch();
  $html_main_content .= '<form method="post" enctype="multipart/form-data">
<table>
<tr><td><b>Avatar</b></td><td><img src="/news/images/';
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
<input type="hidden" name="rules" value="' . $row['rules'] . '">
<input type="hidden" name="login" value="' . $row['login'] . '">
<input type="hidden" name="avatar" value="' . $row['avatar'] . '"></td></tr>
<tr><td colspan="2"><b><input type="submit" value="ok" name="submit"></td></tr>
</table>
</form>';
}

//More information news
function show() {
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
  $STH->setFetchMode(PDO::FETCH_ASSOC);
  $row = $STH->fetch();
  $author = $row['author'];
  $title = $row['title'];
  $html_main_content .= '<h2 class="title">' . $row['title'] . '</h2>';
  $html_main_content .= '<b>rating: </b>' . $row['rating'] . '<br><b>Text: </b>' . $row['text'] . '
  <br><b>Author: </b>' . $row['author'] . '<br>
  <b>Date: </b>' . $row['date'] . '<br><hr>';
  $STH = $DBH->prepare("SELECT * FROM  comments WHERE  news_id =  :id ORDER BY  id ASC ");
  $data = array('id' => $_GET['id']);
  $STH->execute($data);
  if ($STH->rowCount() != 0) {
    $html_main_content .= '<br><b>Comments</b><hr>';
  }
  $STH->setFetchMode(PDO::FETCH_ASSOC);
  while ($row = $STH->fetch()) {
    $html_main_content .= '<b>Title: </b>' . $row['title'] . '<br><b>Text: </b>' . $row['text'] . '<br>
    <b>Author: </b>' . $row['author'] . '<br>
    <b>Date: </b>' . $row['date'] . '<br><hr>';
  }
  // If u avtor u have more privilege
  if ($_SESSION['login'] == $author) {
    $html_main_content .= '<br><a href="/news/edit/' . $_GET['id'] . '">Edit news</a><br>
    <a href="/news/delete/' . $_GET['id'] . '">Delete news</a><hr><br>';
  }
  if ($_SESSION['login']) {
    $html_main_content .= '<b>Add Coment</b>
<form  method="post">
<label for="title"><b>Title: </b></label>
<input  name="title" value="" type="text" size="32"/><br>
<b>Text: </b><br><textarea cols="50" rows="10" name="text"></textarea>
<br><input name="submit_show" type="submit"  value="ок">
</form>';
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
function add() {
  global $DBH, $html_main_content;
  if (isset($_POST['submit_add'])) {
    if (($_POST['title'] !== '') && (($_POST['text'] !== ''))) {
      $STH = $DBH->prepare("INSERT INTO news (id, title, text, author, rating,date)
                          VALUES (NULL, :title, :text, :author, '0',:date)");
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
      <input type="submit" value="ok" name="submit_add">
    </form>';

}

//Delete news and all comments
function delete() {
  global $DBH, $html_main_content;
  $STH = $DBH->prepare("SELECT * FROM news WHERE id=:id ");
  $data = array('id' => $_GET['id']);
  $STH->execute($data);
  $STH->setFetchMode(PDO::FETCH_ASSOC);
  $row = $STH->fetch();
  if ($_SESSION['login'] == $row['author']) {
    $STH = $DBH->prepare("Delete FROM news WHERE id=:id ");
    $STH->execute($data);
    $STH = $DBH->prepare("Delete FROM comments WHERE news_id=:id ");
    $STH->execute($data);
    header("Location: /news/");
    exit;
  }
  else {
    $html_main_content .= "<b>You have not enough rights</b>";
  }
}

//Edit news
function edit() {
  global $DBH, $html_main_content;
  if (isset($_POST['submit_edit'])) {
    if ($_POST['title'] !== '' && $_POST['text'] !== '') {
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
    $STH->setFetchMode(PDO::FETCH_ASSOC);
    $row = $STH->fetch();
    if ($_SESSION['login'] == $row['author']) {
      $html_main_content .= '<form method="post" name="news_edit">
  <b>Title: </b><input type="text" name="title"  value="' . $row['title'] . '"><br>
  <b>Text: </b><br><textarea cols="40" rows="5" name="text">' . $row['text'] . '</textarea><br>
  <input type="submit" name="submit_edit" value="ok">
</form>';
    }
    else {
      $html_main_content .= "<b>You have not enough rights</b>";
    }
  }
}

//Regitration user
function registration() {
  global $DBH, $html_main_content;
  $er = '';
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
      $er .= 'incorrect email addresses Example: mail@example.com<br>';
    }
    if (isset($_FILES['file']) && $_FILES['file']['error'] != 4) {
      $filename = $filepath = $filetype = '';
      if ($_FILES['file']['error'] != 1 && $_FILES['file']['error'] != 0) {
        $error = $_FILES['file']['error'];
        $er .= 'Error: file not loaded. Error code: ' . $error;

      }
      else {
        $filesize = $_FILES['file']['size'];
        if ($_FILES['file']['error'] == 1 || $filesize > 3145728) {
          $filesize = ($filesize != 0) ?
            sprintf('(%.2f Мб)', $filesize / 1024) : '';
          die($er .= 'Error: File size image' . $filesize . 'more acceptable (3 MB).');
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
          $er .= 'Sorry, you can only upload GIF, JPEG, PNG image ';
        }
        else {
          move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
          $img = basename($_FILES['file']['name']);
        }
      }
    }
    $sucs = 0;
    if ($er == '') {
      if (empty($img)) {
        $img = '';
      }
      $password = md5(trim($_POST['password']));
      $STH = $DBH->prepare("INSERT INTO user ( login, name, surname, lastname, rules, password, avatar,email)
     VALUES ( :login, :name, :surname, :lastname, 'user', :password, :avatar,:email)");
      $data = array(
        'login' => $_POST['login'],
        'name' => $_POST['name'],
        'surname' => $_POST['surname'],
        'lastname' => $_POST['lastname'],
        'password' => $password,
        'avatar' => $img,
        'email' => $_POST['email']
      );
      $STH->execute($data);

      $html_main_content .= "You register successful<br>";
      $sucs = 1;
    }
    $html_main_content .= $er;
    $_FILES['file']['error'] = '';
  }

  if ($sucs == 0) {
    $html_main_content .= '<form method="post" enctype="multipart/form-data"><table>
<tr><td><b>Login *</b></td><td><input type=text name="login"></td></tr>
<tr><td><b>Email *</b></td><td><input type=text name="email"></td></tr>
<tr><td><b>Password *</b></td><td><input type="Password" name="password"></td></tr>
<tr><td><b>Retry password *</b></td><td><input type="Password" name="rpassword"></td></tr>
<tr><td><b>Surname</b></td><td><input type=text name="surname"></td></tr>
<tr><td><b>Name</b></td><td><input type=text name="name" ></td></tr>
<tr><td><b>LastName</b></td><td><input type=text name="lastname"></td></tr>
<tr><td><b>Avatar</b></td><td><input type="file" name="file" size="30" /></td></tr>
<tr><td><input type="submit" value="ok" name="submit_registration"></td></tr>
</table></form><br>
Required Field *';
  }

}

function login() {
  global $DBH, $html_login_form;
  $err = '';
  if ((!empty($_POST['login'])) && (!empty($_POST['password']))) {
    $login = $_POST['login'];
    $password = md5($_POST['password']);
    $STH = $DBH->prepare("SELECT * 	FROM user WHERE login=:login AND password=:password LIMIT 1");
    $data = array('login' => $login, 'password' => $password);
    $STH->execute($data);
    if ($STH->rowCount() == 1) {
      $STH->setFetchMode(PDO::FETCH_ASSOC);
      $row = $STH->fetch();
      $_SESSION = array_merge($_SESSION, $row);
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
   <a href="/news/profileview/">Your Profile</a><br>
   <a href="/news/add/">Add news</a>';
  }
  else {
    $html_login_form .= $err;
    $html_login_form .= '<form method="post" name="login">
   <b>Name:</b><input name="login" size="20" type="text">
   <b>Password:</b><input name="password" type="password">
    <input name="submit_login" type="submit" value="ok">
  </form><br>
  <a href="/news/registration/">Registration</a>';
  }
}

/*
* Функция обрезает строку на заданное число символов до слова
* @param $string - обрезаисая строка
* @param $length - до скольки символов обрезать строку
* @return srting - обрезаная строка
*/
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

function profileview() {
  global $html_main_content, $DBH;
  $STH = $DBH->prepare("SELECT * FROM user WHERE login=:login");
  $data = array('login' => $_SESSION['login']);
  $STH->execute($data);
  $STH->setFetchMode(PDO::FETCH_ASSOC);
  $row = $STH->fetch();
  $html_main_content .= '<table>
<tr><td><b>Avatar</b></td><td><img src="/news/images/';
  if ($row['avatar'] == '') {
    $html_main_content .= 'noimage.jpeg';
  }
  else {
    $html_main_content .= $row['avatar'];
  }

  $html_main_content .= '"width="150px" height="150px"></td></tr>
<tr><td><b>Email</b></td><td>' . $row['email'] . '</td></tr>
<tr><td><b>Login</b></td><td>' . $row['login'] . '</td></tr>
<tr><td><b>Surname</b></td><td>' . $row['surname'] . '</td></tr>
<tr><td><b>Name</b></td><td>' . $row['name'] . '</td></tr>
<tr><td><b>Lastname</b></td><td>' . $row['lastname'] . '</td></tr>
<tr><td><b>Rules</b></td><td>' . $row['rules'] . '</td></tr>
</table>';
  $html_main_content .= '<a href = "/news/info/" > Edit information </a ><br > ';

}