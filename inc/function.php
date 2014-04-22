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
  global $html_main_content;
  show_err();
  switch ($action) {
    case '':
      main();
      break;
    case 'user_show':
      user_show();
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
    case 'user_delete':
      user_delete();
      break;
    case 'user_edit':
      user_edit();
      break;
    case 'edit_language':
      edit_language();
      break;
    case 'delete_comments':
      delete_comments();
      break;
    case 'delete_vote':
      delete_vote();
      break;
    default:
      $html_main_content .= print_lg('Page not found', $_SESSION['lang']);
      break;
  }
}


//Show main page
function main() {
  global $DBH, $html_main_content, $on_page;
  //Check access
  if (check_accses($_SESSION['rules'], 'main')) {
    $html_main_content .= print_lg('You baned on this site pls contact
    admin@mail.ua Sorry but u can`t login', $_SESSION['lang']) . '<br>';
    session_unset();
    session_destroy();
  }
  //Pages
  if ($_SESSION['lang'] == "ua") {
    $table = 'news';
  }
  else {
    $table = 'news_en';
  }
  $STH = $DBH->query("SELECT * from $table");
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
  $STH = $DBH->prepare("SELECT * from $table LIMIT :start_from,:on_page");
  $STH->bindParam(':start_from', $start_from, PDO::PARAM_INT);
  $STH->bindParam(':on_page', $on_page, PDO::PARAM_INT);
  $STH->execute();
  while ($row = $STH->fetch(PDO::FETCH_ASSOC)) {
    //Rating
    $STT = $DBH->prepare("Select avg(value)
    from rating where news_id=:news_id");
    $STT->execute(array('news_id' => $row['id']));
    $rows = $STT->fetch(PDO::FETCH_NUM);
    $votes = (int) $rows['0'];
    $html_main_content .= '<b>' .
      print_lg('Title', $_SESSION['lang']) .
      ': </b><a href="/news/show_news/' . $row['id'] . '">' .
      $row['title'] . '</a>  ';
    if ($votes == 0) {
      $html_main_content .= '<b>' .
        print_lg('Rating', $_SESSION['lang']) . ': </b>' .
        print_lg('Any don`t vote', $_SESSION['lang']) . '<br>';
    }
    else {
      $html_main_content .= '<b>' . print_lg('Rating', $_SESSION['lang']) .
        ': </b>' . $votes . '<br>';
    }
    //Text trim
    $encod = mb_detect_encoding($row['text']);
    if (mb_strlen($row['text'], $encod) >= 150) {
      $html_main_content .= '<b>' . print_lg('Text', $_SESSION['lang']) .
        ': </b>' . trimming_line($row['text'], 150) .
        '<br><a href="/news/show_news/' . $row['id'] . '">' .
        print_lg('Read more', $_SESSION['lang']) . '... </a><br>';
    }
    else {
      $html_main_content .= '<b>' .
        print_lg('Text', $_SESSION['lang']) . ': </b>' . $row['text'] . '<br>';
    }
    $html_main_content .= '<b>' .
      print_lg('Author', $_SESSION['lang']) .
      ': </b><a href="/news/profileview/' . $row['author'] . '">' .
      $row['author'] . '</a><br><b>' .
      print_lg('Date', $_SESSION['lang']) . ': </b>' .
      $row['date'] . '<br><hr>';
  }
  //Pages
  if ($num_pages != 1) {
    $html_main_content .= '<p>';
    for ($page = 1; $page <= $num_pages; $page++) {
      if ($page == $current_page) {
        $html_main_content .= '<strong>' . $page . '</strong> &nbsp;';
      }
      else {
        $html_main_content .= '<a href="/news/pages/' . $page . '">' . $page .
          '</a> &nbsp;';
      }
    }
    $html_main_content .= '</p>';
  }
}

//Image upload
function image_upload() {
  global $html_main_content;
  if (isset($_FILES['file']) && $_FILES['file']['error'] != 4) {
    $filename = $filepath = $filetype = '';
    //Image don`t upload
    if ($_FILES['file']['error'] != 1 && $_FILES['file']['error'] != 0) {
      $error = $_FILES['file']['error'];
      $_SESSION['err'] .= print_lg('Error: file not loaded. Error code', $_SESSION['lang'])
        . ':' . $error . '<br>';
    }
    else {
      //Check imagesize
      $filesize = $_FILES['file']['size'];
      if ($_FILES['file']['error'] == 1 || $filesize > 3145728) {
        $filesize = ($filesize != 0) ?
          sprintf('(%.2f Мб)', $filesize / 1024) : '';
        $_SESSION['err'] .= print_lg('Error: File size image more acceptable (3 MB)',
            $_SESSION['lang']) . '<br>';
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
      if ($imageinfo['mime'] != 'image/gif' &&
        $imageinfo['mime'] != 'image/jpeg' &&
        $imageinfo['mime'] != 'image/jpg' &&
        $imageinfo['mime'] != 'image/png'
      ) {
        $_SESSION['err'] .= print_lg('Sorry, you can only upload GIF, JPEG, PNG image',
            $_SESSION['lang']) . '<br>';
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
  global $DBH, $html_main_content;
  //Submit form
  if (isset($_POST['submit'])) {
    //Check pass
    if (($_POST['password'] != '') &&
      ($_POST['password'] != $_POST['rpassword'])
    ) {
      $_SESSION['err'] .= print_lg('Password: no match', $_SESSION['lang']) . '<br>';
    }
    //Check email
    $STH = $DBH->prepare("SELECT * FROM user WHERE email=:email ");
    $data = array('email' => $_POST['email']);
    $STH->execute($data);
    if ($STH->rowCount() >= 1) {
      $_SESSION['err'] .= print_lg('This email is already taken', $_SESSION['lang']) .
        "<br>";
    }
    //Check email
    $pos = mb_strrpos($_POST['email'], '@');
    if ($pos == 0 && $_POST['email'] != '') {
      $_SESSION['err'] .= print_lg('Incorrect email addresses Example: mail@example.com',
          $_SESSION['lang']) . '<br>';
    }
    //Image upload
    $img = image_upload();
    if ($_SESSION['err'] == '') {
      if ($img == '') {
        $img = $_POST['avatar'];
      }
      $login = $_POST['login'];
      $_SESSION['rules'] != 'admin' ? $_SESSION['login'] = $login : '';
      $lastname = $_POST['lastname'];
      $name = $_POST['name'];
      $surname = $_POST['surname'];
      $sql = "UPDATE user SET";
      //Check update pass or no
      if ($_POST['password'] !== '') {
        $password = md5(trim($_POST['password']));
        $sql .= " password=:password,";
      }
      if ($_POST['email'] !== '') {
        $sql .= " email=:email,";
      }
      $sql .= " lastname=:lastname,
      name=:name,
      surname=:surname,
      avatar=:avatar
      WHERE login=:login";
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
      $html_main_content .= print_lg('Your information update sucsesful',
          $_SESSION['lang']) . '<br>';
    }
    show_err();
    $_FILES['file']['error'] = '';
  }
  //Show user info
  $STH = $DBH->prepare("SELECT * FROM user WHERE login=:login");
  $data = array('login' => $_GET['id']);
  $STH->execute($data);
  $row = $STH->fetch(PDO::FETCH_ASSOC);
  $html_main_content .= '<form method="post" enctype="multipart/form-data">
<table><tr><td><b>' . print_lg('Avatar', $_SESSION['lang']) .
    ': </b></td><td><img src="/news/images/';
  if ($row['avatar'] == '') {
    $html_main_content .= 'noimage.jpeg';
  }
  else {
    $html_main_content .= $row['avatar'];
  }
  $html_main_content .= '"width="150px" height="150px"></td></tr><tr><td><b>' .
    print_lg('Email', $_SESSION['lang']) . ': </b></td><td>
    <input type=text name="email" value=""></td></tr><tr><td><b>' .
    print_lg('Surname', $_SESSION['lang']) . ': </b></td><td>
    <input type=text name="surname" value="' . $row['surname'] . '">
    </td></tr><tr><td><b>' . print_lg('Name', $_SESSION['lang']) .
    ': </b></td><td><input type=text name="name" value="' . $row['name'] . '">
    </td></tr><tr><td><b>' . print_lg('Lastname', $_SESSION['lang']) . ': </b>
    </td><td><input type=text name="lastname" value="' . $row['lastname'] .
    '"></td></tr><tr><td><b>' . print_lg('Password', $_SESSION['lang']) .
    ': </b></td><td> <input type="Password" name="password"></td></tr><tr>
    <td><b>' . print_lg('Retry password', $_SESSION['lang']) . ': </b>
    </td><td><input type="Password" name="rpassword"></td></tr><tr><td><b>' .
    print_lg('Email', $_SESSION['lang']) . ': </b></td><td>
    <input type="file" name="file" size="30" /></td></tr>
    <input type="hidden" name="login" value="' .
    $row['login'] . '"><input type="hidden" name="avatar" value="' .
    $row['avatar'] . '"></td></tr><tr><td colspan="2"><b>
    <input type="submit" value="ok" name="submit"></td></tr></table></form>';
}

//More information news
function show_news() {
  global $DBH, $html_main_content, $title;
  //Vote
  if ($_POST['add_vote'] && isset($_SESSION['login'])) {
    $STH = $DBH->prepare("INSERT INTO rating SET
    news_id=:news_id,
    author=:author,
    lang=:lang,
    value=:value  ");
    $data = array(
      'news_id' => $_GET['id'],
      'author' => $_SESSION['login'],
      'lang' => $_SESSION['lang'],
      'value' => $_POST['vote']
    );
    $STH->execute($data);
    $_SESSION['err'] .= print_lg('Your vote added', $_SESSION['lang']);
  }
  if ($_POST['delete_vote'] && isset($_SESSION['login'])) {
    $STH = $DBH->prepare("Delete  from rating where
    news_id=:news_id and
    author=:author and
    lang=:lang  ");
    $data = array(
      'news_id' => $_GET['id'],
      'author' => $_SESSION['login'],
      'lang' => $_SESSION['lang']
    );
    $STH->execute($data);
    $_SESSION['err'] .= print_lg('Your vote delete', $_SESSION['lang']);
  }
  //Add comment
  if ($_POST['submit_show']) {
    if ($_POST['title'] == '') {
      $com_title = trimming_line($_POST['text'], 15);
    }
    else {
      $com_title = $_POST['title'];
    }
    if ($_SESSION['lang'] == "ua") {
      $table = 'comments';
    }
    else {
      $table = 'comments_en';
    }
    $STH = $DBH->prepare("INSERT INTO $table SET
    news_id=:id,
    title=:title,
    text=:text,
    author=:author,
    date=:date  ");
    $data = array(
      'title' => $com_title,
      'text' => $_POST['text'],
      'author' => $_SESSION['login'],
      'id' => $_GET['id'],
      'date' => DATE('Y-m-d')
    );
    $STH->execute($data);
    $_SESSION['err'] .= print_lg('Your comment added', $_SESSION['lang']);
  }
  show_err();
  //Rating
  $STH = $DBH->prepare("Select avg(value) from rating where news_id=:news_id");
  $STH->execute(array('news_id' => $_GET['id']));
  $row = $STH->fetch(PDO::FETCH_NUM);
  $votes = (int) $row['0'];
  if ($_SESSION['lang'] == "ua") {
    $table = 'news';
  }
  else {
    $table = 'news_en';
  }
  $STH = $DBH->prepare("SELECT * FROM $table WHERE id=:id ");
  $data = array('id' => $_GET['id']);
  $STH->execute($data);
  $row = $STH->fetch(PDO::FETCH_ASSOC);
  $author = $row['author'];
  $title = $row['title'];
  $html_main_content .= '<h2 class="title">' . $row['title'] . '</h2>';
  if ($votes == 0) {
    $html_main_content .= '<b>' . print_lg('Rating', $_SESSION['lang']) .
      ': </b>' . print_lg('Any don`t vote', $_SESSION['lang']) . '<br>';
  }
  else {
    $html_main_content .= '<b>' .
      print_lg('Rating', $_SESSION['lang']) . ': </b>' . $votes . '<br>';
  }
  $STT = $DBH->prepare("SELECT * FROM rating
  WHERE author=:author
  and news_id=:news_id
  and lang=:lang");
  $STT->execute(array(
    'news_id' => $_GET['id'],
    'author' => $_SESSION['login'],
    'lang' => $_SESSION['lang']
  ));
  $rat = $STT->fetch(PDO::FETCH_ASSOC);
  if ($STT->rowCount() == 1) {
    $html_main_content .= '<b>' . print_lg('Your vote', $_SESSION['lang']) .
      ': </b> ' . $rat['value'] . ' <form name="delete_vote" method="post">
      <input type="submit" name="delete_vote" value=' .
      print_lg('Delete', $_SESSION['lang']) . '></form></br>';
  }
  else {
    if (isset($_SESSION['login'])) {
      $html_main_content .= "<form name='vote' method='post'>
  <input type='radio' name='vote' value='1' >1&nbsp;
  <input type='radio' name='vote' value='2'>2&nbsp;
  <input type='radio' name='vote' value='3' checked>3&nbsp;
  <input type='radio' name='vote' value='4'>4&nbsp;
  <input type='radio' name='vote' value='5'>5&nbsp;
  <input type='submit' name='add_vote' value='ok'>
  </form> ";
    }
  }
  //Delete all votes for admin
  if ($_SESSION['rules'] == 'admin') {
    $html_main_content .= '<a href="/news/delete_vote/' . $_GET['id'] . '">' .
      print_lg('Delete all votes', $_SESSION['lang']) . '</a><br>';
  }
  $html_main_content .= '<b>' . print_lg('Text', $_SESSION['lang']) . ': </b>' .
    $row['text'] . '<br><b>' . print_lg('Author', $_SESSION['lang']) . ': </b>
    <a href="/news/profileview/' . $row['author'] . '">' . $row['author'] .
    '</a><br><b>' . print_lg('Date', $_SESSION['lang']) . ': </b>' .
    $row['date'] . '<br><hr>';
  //Pages
  if ($_SESSION['lang'] == "ua") {
    $table = 'comments';
  }
  else {
    $table = 'comments_en';
  }
  $STH = $DBH->prepare("SELECT * FROM  $table WHERE  news_id =  :id
  ORDER BY  id desc ");
  $data = array('id' => $_GET['id']);
  $STH->execute($data);
  $count_records = $STH->rowCount();
  $on_page = 10;
  $num_pages = ceil($count_records / $on_page);
  $current_page = isset($_GET['pages']) ? (int) $_GET['pages'] : 1;
  if ($current_page < 1) {
    $current_page = 1;
  }
  elseif ($current_page > $num_pages) {
    $current_page = $num_pages;
  }
  $start_from = ($current_page - 1) * $on_page;
  if ($start_from < 0) {
    $start_from = 0;
  }
  //Main
  if ($_SESSION['lang'] == "ua") {
    $table = 'comments';
  }
  else {
    $table = 'comments_en';
  }
  // If u author u have more privilege
  if ($_SESSION['login'] == $author || $_SESSION['rules'] == 'admin') {
    $html_main_content .= '<br><a href="/news/edit_news/' . $_GET['id'] . '">' .
      print_lg('Edit news', $_SESSION['lang']) . ': </a><br>
    <a href="/news/delete_news/' . $_GET['id'] . '">' .
      print_lg('Delete news', $_SESSION['lang']) . ' </a><hr><br>';
  }
  //if u login u can add comment
  if ($_SESSION['login']) {
    $html_main_content .= '<b>' . print_lg('Add Comment', $_SESSION['lang']) .
      ': </b><form  method="post"><label for="title"><b>' .
      print_lg('Title', $_SESSION['lang']) . ': </b></label>
      <input  name="title" value="" type="text" size="32"/><br><b>' .
      print_lg('Text', $_SESSION['lang']) . ': </b><br>
      <textarea cols="50" rows="10" name="text"></textarea>
      <br><input name="submit_show" type="submit"  value="ок"></form>';
  }
  $STH = $DBH->prepare("SELECT * FROM  $table WHERE news_id =  :id
  ORDER BY  id desc LIMIT :start_from,:on_page");
  $STH->bindParam(':id', $_GET['id']);
  $STH->bindParam(':start_from', $start_from, PDO::PARAM_INT);
  $STH->bindParam(':on_page', $on_page, PDO::PARAM_INT);
  $STH->execute();
  if ($STH->rowCount() != 0) {
    $html_main_content .= '<br><b>' . print_lg('Comments', $_SESSION['lang']) .
      ' </b><hr>';
  }
  while ($row = $STH->fetch(PDO::FETCH_ASSOC)) {
    $html_main_content .= '<b>' . print_lg('Title', $_SESSION['lang']) .
      ': </b>' . $row['title'];
    if ($_SESSION['rules'] == 'admin') {
      $html_main_content .= '<a href="/news/delete_comments/' . $row['id'] . '">
      <img src=/news/images/delete.gif></a>';
    }
    $html_main_content .= '<br><b>' .
      print_lg('Text', $_SESSION['lang']) . ': </b>' . $row['text'] .
      '<br> <b>' . print_lg('Author', $_SESSION['lang']) . ': </b>
      <a href="/news/profileview/' . $row['author'] . '">' . $row['author'] .
      '</a><br><b>' . print_lg('Date', $_SESSION['lang']) . ': </b>' .
      $row['date'] . '<br><hr>';
  }
  //Pages
  if ($num_pages != 1) {
    $html_main_content .= '<p>';
    for ($page = 1; $page <= $num_pages; $page++) {
      if ($page == $current_page) {
        $html_main_content .= '<strong>' . $page . '</strong> &nbsp;';
      }
      else {
        $html_main_content .= '<a href="/news/show_news/' . $_GET['id'] .
          '&pages=' . $page . '">' . $page . '</a> &nbsp;';
      }
    }
    $html_main_content .= '</p>';
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
  //Check access
  if ((check_accses($_SESSION['rules'], 'add')) && isset($_SESSION['login'])) {
    if (isset($_POST['submit_add'])) {
      if ($_POST['title_eu'] !== '' && $_POST['text_eu'] !== ''
        && $_POST['title_ua'] !== '' && $_POST['text_ua'] !== ''
      ) {
        $STH = $DBH->prepare("INSERT INTO news_en SET
        title=:title,
        text=:text,
        author=:author,
        rating='0',date=:date");
      }
      $data = array(
        'title' => $_POST['title_en'],
        'text' => $_POST['text_en'],
        'author' => $_SESSION['login'],
        'date' => DATE('Y-m-d')
      );
      $STH->execute($data);
      $id_en = $DBH->lastInsertId();
      $STH = $DBH->prepare("INSERT INTO news SET title=:title,
      text=:text,
      author=:author,
      rating='0',
      date=:date");
      $data = array(
        'title' => $_POST['title_ua'],
        'text' => $_POST['text_ua'],
        'author' => $_SESSION['login'],
        'date' => DATE('Y-m-d')
      );
      $STH->execute($data);
      $id_ua = $DBH->lastInsertId();
      //Redirect
      if ($_SESSION['lang'] == 'ua') {
        header("Location: /news/show_news/" . $id_ua . '');
      }
      else {
        header("Location: /news/show_news/" . $id_en . '');
      }
    }
    else {
      $html_main_content .= print_lg('Write title and text', $_SESSION['lang'])
        . '<br>';
    }
    $html_main_content .= print_lg('Required field *', $_SESSION['lang']) .
      '<br><br>      <form method="post" name="add">
      ' . print_lg('English version', $_SESSION['lang']) . ': <br>
      <b>' . print_lg('Title', $_SESSION['lang']) . ': *</b>
      <input type="text" name="title_en"><br><b>' .
      print_lg('Text', $_SESSION['lang']) . ': *</b><br>
      <textarea name="text_en" cols="40" rows="5"> </textarea><br>' .
      print_lg('Ukraine version ', $_SESSION['lang']) . ': <br><b>' .
      print_lg('Title', $_SESSION['lang']) . ': *</b>
      <input type="text" name="title_ua"><br><b>' .
      print_lg('Text', $_SESSION['lang']) . ': *</b><br>
      <textarea name="text_ua" cols="40" rows="5"> </textarea><br>
      <input type="submit" value="ok" name="submit_add"></form>';
  }
  else {
    $html_main_content .= print_lg('Failed u don`t have rules',
        $_SESSION['lang']) . '<br>';
  }
}

//Delete news and all comments
function delete_news() {
  global $DBH, $html_main_content;
  if ($_SESSION['lang'] == "ua") {
    $table = 'news';
  }
  else {
    $table = 'news_en';
  }
  $STH = $DBH->prepare("SELECT * FROM $table WHERE id=:id ");
  $data = array('id' => $_GET['id']);
  $STH->execute($data);
  $row = $STH->fetch(PDO::FETCH_ASSOC);
  if ($_SESSION['login'] == $row['author'] || $_SESSION['rules'] == 'admin') {
    if ($_SESSION['lang'] == "ua") {
      $table = 'news';
    }
    else {
      $table = 'news_en';
    }
    $STH = $DBH->prepare("Delete FROM $table WHERE id=:id;Delete FROM $table
    WHERE news_id=:id ");
    $STH->execute($data);
  //  $_SESSION['err'] .= print_lg('News delete',$_SESSION['lang']);
    header("Location: /news/");
    exit;
  }
  else {
    $html_main_content .= print_lg('Failed u don`t have rules',
        $_SESSION['lang']) . "<br/>";
  }
}

//Edit news
function edit_news() {
  global $DBH, $html_main_content;
  //Submit add
  if (isset($_POST['submit_edit'])) {
    if ($_POST['title'] != '' && $_POST['text'] != '') {
      if ($_SESSION['lang'] == "ua") {
        $table = 'news';
      }
      else {
        $table = 'news_en';
      }
      $STH = $DBH->prepare("UPDATE $table Set  title=:title, text=:text,
      date=:date where id=:id");
      $data = array(
        'title' => $_POST['title'],
        'text' => $_POST['text'],
        'id' => $_GET['id'],
        'date' => DATE('Y-m-d')
      );
      $STH->execute($data);
      header("Location: /news/show_news/" . $_GET['id'] . '');
      exit;
    }
    else {
      $html_main_content .= print_lg('Write title and text', $_SESSION['lang'])
        . '<br>';
    }
  }
  else {
    //Show form and info
    if ($_SESSION['lang'] == "ua") {
      $STH = $DBH->prepare("SELECT * 	FROM news WHERE id=:id ");
    }
    else {
      $STH = $DBH->prepare("SELECT * 	FROM news_en WHERE id=:id ");
    }
    $data = array('id' => $_GET['id']);
    $STH->execute($data);
    $row = $STH->fetch(PDO::FETCH_ASSOC);
    if ($_SESSION['login'] == $row['author'] || $_SESSION['rules'] == 'admin') {
      $html_main_content .= print_lg('Required field *', $_SESSION['lang']) .
        ' <br><form method="post" name="news_edit">
  <b>' . print_lg('Title', $_SESSION['lang']) . ': *</b>
  <input type="text" name="title"  value="' . $row['title'] . '"><br><b>' .
        print_lg('Text', $_SESSION['lang']) . ': *</b><br>
        <textarea cols="40" rows="5" name="text">' . $row['text'] . '</textarea>
        <br><input type="submit" name="submit_edit" value="ok"></form>';
    }
    else {
      $html_main_content .= print_lg('Rules', $_SESSION['lang']) . ": <br/>";
    }
  }
}

//Regitration user
function registration() {
  global $DBH, $html_main_content, $er;
  //Check submit registratiron
  if ($_POST['submit_registration']) {
    //Check log and email
    $STH = $DBH->prepare("SELECT * 	FROM user
    WHERE login=:login
    or email=:email ");
    $data = array('login' => $_POST['login'], 'email' => $_POST['email']);
    $STH->execute($data);
    if ($STH->rowCount() >= 1) {
      $er .= print_lg('This login or email is already taken',
          $_SESSION['lang']) . "<br>";
    }
    if ((empty($_POST['login'])) && (empty($_POST['password'])) &&
      (empty($_POST['email']))
    ) {
      $er .= print_lg('This login or email is already taken_required',
          $_SESSION['lang']) . "<br>";
    }
    //Check pass
    if ($_POST['password'] != $_POST['rpassword']) {
      $er .= print_lg('Password: no match', $_SESSION['lang']) . '<br>';
    }
    //Check email
    $pos = mb_strrpos($_POST['email'], '@');
    if ($pos == 0) {
      $er .= print_lg('Incorrect email addresses Example: mail@example.com',
          $_SESSION['lang']) . '<br>';
    }
    //Upload image
    $img = image_upload();
    $sucs = 0;
    if ($er == '') {
      if (empty($img)) {
        $img = '';
      }
      $password = md5(trim($_POST['password']));
      $STH = $DBH->prepare("INSERT INTO user
( login, name, surname, lastname, rules, password,
avatar,email,date_reg,date_login)VALUES ( :login, :name, :surname, :lastname,
 'user', :password, :avatar,:email,:date_reg,:date_login)");
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
  //Show form
  if ($sucs == 0) {
    $html_main_content .= print_lg('Required field *', $_SESSION['lang']) .
      '<br><form method="post" enctype="multipart/form-data"><table><tr><td><b>'
      . print_lg('Login', $_SESSION['lang']) . ': *</b></td><td>
      <input type=text name="login"></td></tr><tr><td><b>' .
      print_lg('Email', $_SESSION['lang']) . ': *</b></td><td>
      <input type=text name="email"></td></tr><tr><td><b>' .
      print_lg('Password', $_SESSION['lang']) . ': *</b></td><td>
      <input type="Password" name="password"></td></tr><tr><td><b>' .
      print_lg('Retry password', $_SESSION['lang']) . ': *</b></td><td>
      <input type="Password" name="rpassword"></td></tr><tr><td><b>' .
      print_lg('Surname', $_SESSION['lang']) . ': </b></td><td>
      <input type=text name="surname"></td></tr><tr><td><b>' .
      print_lg('Name', $_SESSION['lang']) . ': </b></td><td>
      <input type=text name="name" ></td></tr><tr><td><b>' .
      print_lg('Lastname', $_SESSION['lang']) . ': </b></td><td>
      <input type=text name="lastname"></td></tr><tr><td><b>' .
      print_lg('Avatar', $_SESSION['lang']) . ': </b></td><td>
      <input type="file" name="file" size="30" /></td></tr><tr><td>
      <input type="submit" value="ok" name="submit_registration"></td>
      </tr></table></form>';
  }
}

function login() {
  global $DBH, $html_login_form, $err;
  //Check log and pass
  if ((!empty($_POST['login'])) && (!empty($_POST['password']))) {
    $login = $_POST['login'];
    $password = md5($_POST['password']);
    $STH = $DBH->prepare("SELECT * 	FROM user
    WHERE login=:login
    AND password=:password LIMIT 1");
    $data = array('login' => $login, 'password' => $password);
    $STH->execute($data);
    if ($STH->rowCount() == 1) {
      $row = $STH->fetch(PDO::FETCH_ASSOC);
      $_SESSION = array_merge($_SESSION, $row);
      $STH = $DBH->prepare("UPDATE user Set date_login=:date_login where
      login=:login");
      $data = array('date_login' => DATE('Y-m-d'), 'login' => $login);
      $STH->execute($data);
      //Redirect
      header("Location: /news/");
    }
    else {
      if ($_POST['submit_login']) {
        $err .= print_lg('Incorect login or pass', $_SESSION['lang']) . '</br>';
      }
    }
  }
  else {
    if ($_POST['submit_login']) {
      $err .= print_lg('Incorect login or pass', $_SESSION['lang']) . '</br>';
    }
  }
  //Print html form log in
  if (isset($_SESSION['login'])) {
    $html_login_form .= print_lg('You enter as', $_SESSION['lang']) . ' <b>' .
      $_SESSION['login'] . '</b><br>   <a href="/news/logout/">' .
      print_lg('Logout', $_SESSION['lang']) . ' </a><br>
      <a href="/news/profileview/' . $_SESSION['login'] . '">' .
      print_lg('Your profile', $_SESSION['lang']) . ' </a><br>';
    if (check_accses($_SESSION['rules'], 'add_news')) {
      $html_login_form .= '<a href="/news/add_news/">' . print_lg('Add news',
          $_SESSION['lang']) . ' </a></br>
      <a href="/news/user_show/">' . print_lg('Edit User', $_SESSION['lang']) .
        ' </a></br>
      <a href="/news/edit_language/">' . print_lg('Edit translate',
          $_SESSION['lang']) . ' </a>';
    }
  }
  else {
    //Print html form log out
    $html_login_form .= $err;
    $html_login_form .= '<form method="post" name="login">
   <b>' . print_lg('Name', $_SESSION['lang']) . ': </b>
   <input name="login" size="20" type="text"><b>' .
      print_lg('Password', $_SESSION['lang']) . ': </b>
      <input name="password" type="password">
      <input name="submit_login" type="submit" value="ok"></form><br>
   <a href="/news/registration/">' . print_lg('Registration',
        $_SESSION['lang']) . ' </a>';
  }
}

//Str triming
function trimming_line($string, $length = 150) {
  ++$length;
  $encod = mb_detect_encoding($string);
  if ($length && mb_strlen($string) > $length) {
    $str = mb_substr($string, 0, $length - 1);
    $pos = mb_strrpos($string, ' ');
    return mb_substr($str, 0, $pos - 1);
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
  $html_main_content .= '<table><tr><td><b>' . print_lg('Avatar',
      $_SESSION['lang']) . ': </b></td><td><img src="/news/images/';
  if ($row['avatar'] == '') {
    $html_main_content .= 'noimage.jpeg';
  }
  else {
    $html_main_content .= $row['avatar'];
  }
  $html_main_content .= '"width="150px" height="150px"></td></tr>';
  if (isset($_SESSION['login'])) {
    $html_main_content .= '<tr><td><b>' . print_lg('Email',
        $_SESSION['lang']) . ': </b></td><td>' . $row['email'] . '</td></tr>';
  }
  $html_main_content .= '<tr><td><b>' . print_lg('Login', $_SESSION['lang']) .
    ' </b></td><td>' . $row['login'] . '</td></tr><tr><td><b>' .
    print_lg('Surname', $_SESSION['lang']) . ': </b></td><td>' .
    $row['surname'] . '</td></tr><tr><td><b>' .
    print_lg('Name', $_SESSION['lang']) . ': </b></td><td>' .
    $row['name'] . '</td></tr><tr><td><b>' . print_lg('Lastname',
      $_SESSION['lang']) . ': </b></td><td>' . $row['lastname'] . '</td></tr>
    <tr><td><b>' . print_lg('Rule', $_SESSION['lang']) . ': </b></td><td>' .
    $row['rules'] . '</td></tr><tr><td><b>' . print_lg('Registration date',
      $_SESSION['lang']) . ': </b></td><td>' . $row['date_reg'] . '</td></tr><tr>
      <td><b>' . print_lg('Last login', $_SESSION['lang']) . ': </b></td><td>' .
    $row['date_login'] . '</td></tr></table>';
  if ($_SESSION['login'] == $_GET['id'] || $_SESSION['rules'] == 'admin') {
    $html_main_content .= '<a href = "/news/user_info/' . $_GET['id'] . '" >' .
      print_lg('Edit inforamtion', $_SESSION['lang']) . ' </a ><br >
      <a href="/news/delete_user/' . $_GET['id'] . '">' .
      print_lg('Delete user', $_SESSION['lang']) . ' </a>';
  }
}

//Delete user
function delete_user() {
  global $html_main_content, $DBH;
  //Check access
  if ($_SESSION['login'] == $_GET['id'] || $_SESSION['rules'] == 'admin') {
    $STH = $DBH->prepare("Delete FROM user WHERE login=:login;
  Delete FROM comments WHERE author=:login;
  Delete FROM news WHERE author=:login;");
    $data = array('login' => $_GET['id']);
    $STH->execute($data);
    if ($_SESSION['rules'] == 'admin') {
      $html_main_content .= print_lg('Delete user_sucs', $_SESSION['lang']) .
        '<br>';
    }
    else {
      session_unset();
      session_destroy();
      $html_main_content .= print_lg('Profile & all comments will be delete',
          $_SESSION['lang']) . '<br>';
    }
  }
}


//Delete comment
function delete_comments() {
  global $html_main_content, $DBH;
  if ($_SESSION['rules'] == 'admin') {
    if ($_SESSION['lang'] == "ua") {
      $table = 'comments';
    }
    else {
      $table = 'comments_en';
    }
    $STH = $DBH->prepare("delete  FROM  $table WHERE  id =  :id  ");
    $data = array('id' => $_GET['id']);
    $STH->execute($data);
    $html_main_content .= print_lg('Comment delete', $_SESSION['lang']) .
      '<br>';
  }
}

//Show all users
function user_show() {
  global $html_main_content, $DBH;
  $STH = $DBH->query("SELECT * FROM user ");
  $html_main_content .= '<table>';
  while ($row = $STH->fetch(PDO::FETCH_ASSOC)) {
    $html_main_content .= '<tr>
    <td><b>' . print_lg('Login', $_SESSION['lang']) . ': </b></td><td>' .
      $row['login'] . '</td> <td><b>' . print_lg('Email', $_SESSION['lang']) .
      ': </b></td><td>' . $row['email'] . '</td><td><b>' .
      print_lg('Surname', $_SESSION['lang']) . ': </b></td><td>' .
      $row['surname'] . '</td><td><b>' . print_lg('Name', $_SESSION['lang']) .
      ': </b></td><td>' . $row['name'] . '</td><td><b>' .
      print_lg('Lastname', $_SESSION['lang']) . ': </b></td><td>' .
      $row['lastname'] . '</td><td><b>' . print_lg('Rules', $_SESSION['lang']) .
      ': </b></td><td>' . $row['rules'] . '</td><td>
      <a href="/news/user_edit/' . $row['login'] . '">
      <img src=/news/images/edit.png></a><a href="/news/user_delete/' .
      $row['login'] . '"><img src=/news/images/delete.gif></a>
   </td></tr>';
  }
  $html_main_content .= '</table>';
}

//User edit
function user_edit() {
  global $html_main_content, $DBH, $er;
  //check access
  if ($_SESSION['rules'] == 'admin') {
    //Update info
    if (isset($_POST['submit'])) {
      if (($_POST['password'] != '') &&
        ($_POST['password'] != $_POST['rpassword'])
      ) {
        $er .= print_lg('Password: no match', $_SESSION['lang']) . '<br>';
      }
      $pos = mb_strrpos($_POST['email'], '@');
      if ($pos == 0 && $_POST['email'] != '') {
        $er .= print_lg('Incorrect email addresses Example: mail@example.com',
            $_SESSION['lang']) . '<br>';
      }
      $img = image_upload();
      if ($er == '') {
        if ($img == '') {
          $img = $_POST['avatar'];
        }
        $login = $_POST['login'];
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
        $sql .= " lastname=:lastname, name=:name, surname=:surname,
        avatar=:avatar, date_reg=:date_reg, date_login=:date_login,
        rules=:rules WHERE login=:login";
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
        $STH->bindParam(':date_reg', $_POST['date_reg']);
        $STH->bindParam(':date_login', $_POST['date_login']);
        $STH->bindParam(':rules', $_POST['rules']);
        $STH->bindParam(':login', $login);
        $STH->execute();
        $html_main_content .= print_lg('Your information update sucsesful',
            $_SESSION['lang']) . '<br>';
      }
      $html_main_content .= $er;
      $_FILES['file']['error'] = '';
    }
    // Show html.
    $STH = $DBH->prepare("Select * FROM user WHERE login = :login");
    $data = array('login' => $_GET['id']);
    $STH->execute($data);
    $row = $STH->fetch(PDO::FETCH_ASSOC);
    $html_main_content .= '<form method="post" enctype="multipart/form-data">
    <table><tr><td><b>' . print_lg('Avatar', $_SESSION['lang']) . '
    : </b></td><td><img src="/news/images/';
    if ($row['avatar'] == '') {
      $html_main_content .= 'noimage.jpeg';
    }
    else {
      $html_main_content .= $row['avatar'];
    }
    $html_main_content .= '"width="150px" height="150px"></td></tr><tr><td><b>'
      . print_lg('Login', $_SESSION['lang']) . ': </b></td><td>
      <input type="text" name="login" readonly value="' . $row['login'] . '">
      </td></tr><tr><td><b>' . print_lg('Email', $_SESSION['lang']) . ': </b>
      </td><td><input type=text name="email" value="' . $row['email'] . '"></td>
      </tr><tr><td><b>' . print_lg('Surname', $_SESSION['lang']) . ': </b></td>
      <td><input type=text name="surname" value="' . $row['surname'] . '"></td>
      </tr><tr><td><b>' . print_lg('Name', $_SESSION['lang']) . ': </b></td><td>
      <input type=text name="name" value="' . $row['name'] . '"></td></tr><tr>
      <td><b>' . print_lg('Lastname', $_SESSION['lang']) . ': </b></td><td>
      <input type=text name="lastname"value="' . $row['lastname'] . '"></td>
      </tr><tr><td><b>' . print_lg('Registration date', $_SESSION['lang']) .
      ': </b></td><td><input type=text name="date_reg"value="' .
      $row['date_reg'] . '"></td></tr><tr><td><b>' . print_lg('Last login',
        $_SESSION['lang']) . ': </b></td><td>
        <input type=text name="date_login" value="' .
      $row['date_login'] . '"></td></tr><tr><td><b>' .
      print_lg('Rules', $_SESSION['lang']) . ': </b></td><td>
      <select name="rules"><option ';
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
    $html_main_content .= '  </select></td></tr><tr><td><b>' .
      print_lg('Password', $_SESSION['lang']) . ': </b></td>
      <td><input type="Password" name="password"></td></tr><tr><td><b>' .
      print_lg('Retry password', $_SESSION['lang']) . ': </b></td><td>
      <input type="Password" name="rpassword"></td></tr><tr><td><b>' .
      print_lg('Edit avatar', $_SESSION['lang']) . ': </b></td><td>
      <input type="file" name="file" size="30" /></td></tr>
      <input type="hidden" name="avatar" value="' . $row['avatar'] . '"></td>
      </tr><tr><td colspan="2"><b><input type="submit" value="ok" name="submit">
      </td></tr></table></form>';
  }
}

//Delete user
function user_delete() {
  global $html_main_content, $DBH;
  if ($_SESSION['rules'] == 'admin') {
    $STH = $DBH->prepare("Delete FROM user WHERE login=:login;
  Delete FROM comments WHERE author=:login;
  Delete FROM news WHERE author=:login;");
    $data = array('login' => $_GET['id']);
    $STH->execute($data);
    $html_main_content .= print_lg('Delete user_sucs', $_SESSION['lang']) .
      '<br>';
    $html_main_content .= '<a href="/news/user_show/">' . print_lg('Back',
        $_SESSION['lang']) . ' </a>';
  }
}

//Edit lang
function edit_language() {
  global $html_main_content, $DBH, $on_page;
  if ($_GET['id'] == 'clear') {
    unset($_SESSION['search']);
    header("Location: /news/edit_language/");
  }
  //Search html
  $html_main_content .= '<b>' . print_lg('Search', $_SESSION['lang']) .
    '</b><br/>
  <form method="post"><input type="text" name="search" value="';
  $_POST['search'] ? $html_main_content .= $_POST['search'] :
    $html_main_content .= $_SESSION['search'];
  $html_main_content .= '"><input type="submit" value="ok" name="submit_search">
  <a href="/news/edit_language/clear">' . print_lg('Clear', $_SESSION['lang']) .
    '</a></form><br>';
  //Delete lang
  if ($_GET['id'] == 'delete') {
    $STH = $DBH->prepare("Delete FROM lang WHERE id=:lg_id");
    $data = array('lg_id' => $_GET['lg_id']);
    $STH->execute($data);
    $html_main_content .= print_lg('Your text delete sucsesful',
        $_SESSION['lang']) . '</br>';
    header('Refresh:3 ; URL=/news/edit_language/');
  }
  //Change lang
  if (isset($_POST['change'])) {
    $STH = $DBH->prepare('UPDATE lang SET
    text_en=:text_en,
    text_ua=:text_ua where id=:id');
    $STH->execute(array(
      'text_en' => $_POST['text_en'],
      'text_ua' => $_POST['text_ua'],
      'id' => $_POST['id']
    ));
    $html_main_content .= print_lg('Your information update sucsesful',
        $_SESSION['lang']) . '</br>';
  }
  //Search lang
  if (!empty($_POST['search']) || isset($_SESSION['search'])) {
    if (!empty($_POST['search'])) {
      $_SESSION['search'] = $_POST['search'];
    }
    $STH = $DBH->prepare('Select * from lang where text_en like ? or
    text_ua like ? ');
    $STH->bindValue(1, "%{$_SESSION['search']}%", PDO::PARAM_STR);
    $STH->bindValue(2, "%{$_SESSION['search']}%", PDO::PARAM_STR);
    $STH->execute();
    //Pages
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
    $STH = $DBH->prepare('Select * from lang where text_en like ? or text_ua like ? LIMIT ?,?');
    $STH->bindValue(1, "%{$_SESSION['search']}%", PDO::PARAM_STR);
    $STH->bindValue(2, "%{$_SESSION['search']}%", PDO::PARAM_STR);
    $STH->bindParam(3, $start_from, PDO::PARAM_INT);
    $STH->bindParam(4, $on_page, PDO::PARAM_INT);
    $STH->execute();
    while ($row = $STH->fetch(PDO::FETCH_ASSOC)) {
      $html_main_content .= '<form  method="post">
         <textarea rows="3" cols="35" name="text_en" readonly>' . $row['text_en'] .
        '</textarea><textarea rows="3" cols="35" name="text_ua">' .
        $row['text_ua'] . '</textarea><input type="hidden" name="id" value="' .
        $row['id'] . '"><input type="submit" name="change" value="ok">
        <a href="/news/edit_language/delete&lg_id=' . $row['id'] . '">
        <img src=/news/images/delete.gif></a></form>';
    }
    //Pages
    if ($num_pages != 1) {
      $html_main_content .= '<p>';
      for ($page = 1; $page <= $num_pages; $page++) {
        if ($page == $current_page) {
          $html_main_content .= '<strong>' . $page . '</strong> &nbsp;';
        }
        else {
          $html_main_content .= '<a href="/news/edit_language/' . $page . '">' .
            $page . '</a> &nbsp;';
        }
      }
      $html_main_content .= '</p>';
    }
  }
}

//Delete all votes
function delete_vote() {
  global $html_main_content, $DBH;
  $STT = $DBH->prepare("DELETE  from rating where news_id=:news_id");
  $STT->execute(array('news_id' => $_GET['id']));
  $html_main_content .= print_lg('All votes deleted', $_SESSION['lang']) .
    '</br>';
}