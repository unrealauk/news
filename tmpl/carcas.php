<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <title><?php echo $title; ?></title>
  <meta name="keywords" content=""/>
  <meta name="description" content=""/>
  <link href="/news/css/style.css" rel="stylesheet">
</head>

<body>

<div class="wrapper">
  <header class="header">
    <br>

    <h1><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/news/">My site News</a></h1>
  </header>
  <div class="middle">
    <div class="container">
      <main class="content">
        <?php echo $html_main_content; ?>
      </main>
    </div>
    <aside class="left-sidebar">
      <br>

      <h1> Login Form </h1><br>
      <?php echo $html_login_form; ?>
    </aside>
  </div>
  <footer class="footer">
    <h3>Copyrighting by Auk 2014</h3>
  </footer>

</div>

</body>
</html>