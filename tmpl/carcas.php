<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <title><?php echo print_lg('News', $_SESSION['lang']); ?></title>
  <meta name="keywords" content=""/>
  <meta name="description" content=""/>
  <link href="/news/css/style.css" rel="stylesheet">
</head>
<body>
<div class="wrapper">
  <header class="header">
    <br>

    <h1><a
        href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/news/"><?php echo print_lg('News site', $_SESSION['lang']); ?></a>
    </h1>
    <br>

    <div class="language">
      <a href='/news/ua/'><img src='http://localhost/news/images/ua.png'></a>
      <a href='/news/en/'><img src='http://localhost/news/images/eng.png'></a>
    </div>
  </header>
  <div class="middle">
    <div class="container">
      <main class="content">
        <?php echo $html_main_content; ?>
      </main>
    </div>
    <aside class="left-sidebar">
      <br>

      <h1><?php
        echo print_lg('Login Form', $_SESSION['lang']);
        ?></h1><br>
      <?php echo $html_login_form; ?>
    </aside>
  </div>
  <footer class="footer">
    <h3><?php echo print_lg('Copyrighting by Auk 2014', $_SESSION['lang']); ?></h3>
  </footer>
</div>
</body>
</html>