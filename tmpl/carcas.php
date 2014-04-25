<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta name="keywords" content=""/>
  <meta name="description" content=""/>
  <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
  <title><?php echo print_lg('News', $_SESSION['lang']); ?></title>
  <link href="/news/tmpl/style.css" rel="stylesheet" type="text/css"
        media="screen"/>
  <link href="http://fonts.googleapis.com/css?family=Arvo" rel="stylesheet"
        type="text/css"/>
  <link href='http://fonts.googleapis.com/css?family=Cookie' rel='stylesheet'
        type='text/css'>
  <script type="text/javascript" src="http://code.jquery.com/jquery-1.6.min.js"></script>
  <?php  include "{$_SERVER['DOCUMENT_ROOT']}/news/tmpl/myScript.php";?>
  </head>
<body>
<div id="header" class="container">
  <div id="logo">
    <h1>
      <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/news/">
        <?php echo print_lg('News site', $_SESSION['lang']); ?></a></h1>
  </div>
  <div id="menu">

    <ul>
      <li>
    <a href="/news/en/"><img src="/news/images/ru.png"></a></li>
      <li><a href="/news/ua/"><img src="/news/images/ua.png"></a></li></ul>
<!--    <ul>-->
<!--      <li class="current_page_item"><a href="#">Homepage</a></li>-->
<!--      <li><a href="#">Blog</a></li>-->
<!--      <li><a href="#">Photos</a></li>-->
<!--      <li><a href="#">About</a></li>-->
<!--      <li><a href="#">Contact</a></li>-->
<!--    </ul>-->
  </div>
</div>
<!--<div id="splash-wrapper">-->
<!--  <div id="splash">-->
<!-- <h2>--><?php //echo print_lg('Slogon', $_SESSION['lang']); ?><!--</h2>-->
<!-- <p>--><?php //echo print_lg('Nice words', $_SESSION['lang']); ?><!--</p>-->
<!--  </div>-->
<!--</div>-->
<!-- end #header -->
<div id="wrapper">
  <div id="wrapper2">
    <div id="wrapper-bgtop">
      <div id="page">
        <div id="content">
          <div class="err" id="err"></div>
          <?php echo $html_main_content; ?>

          <!--  <div class="post">
              <h2 class="title"><a href="#">Welcome to Rock Castle</a></h2>

              <p class="meta"><span class="date">December 12, 2011</span><span
                  class="posted">Posted by <a href="#">Someone</a></span></p>

              <div style="clear: both;">&nbsp;</div>
              <div class="entry">
                <p>This is <strong>Rock Castle</strong>, a free, fully
                  standards-compliant CSS template designed by <a
                    href="http://www.freecsstemplates.org/" rel="nofollow">FreeCSSTemplates.org</a>.
                  This free template is released under a <a
                    href="http://creativecommons.org/licenses/by/3.0/">Creative
                    Commons Attribution 3.0</a> license, so you’re pretty much
                  free to do whatever you want with it (even use it commercially)
                  provided you keep the links in the footer intact. Aside from
                  that, have fun with it :)</p>

                <p>Sed lacus. Donec lectus. Nullam pretium nibh ut turpis. Nam
                  bibendum. In nulla tortor, elementum ipsum. Proin imperdiet est.
                  Phasellus dapibus semper urna. Pellentesque ornare, orci in
                  felis. Donec ut . In id eros. Suspendisse turpis, cursus egestas
                  at sem.</p>

                <p class="links"><a href="#" class="more">Read More</a>
                  <a href="#" title="b0x" class="comments">Comments</a>
                </p>
              </div>-->

          <div style="clear: both;">&nbsp;</div>
        </div>
        <!-- end #content -->
        <div id="sidebar">
          <div id="sidebar-bgtop">
            <div id="sidebar-bgbtm">
              <ul>
                <li><h2><?php echo print_lg('Login Form', $_SESSION['lang']); ?></h2>
                  <ul>
                    <li><?php echo $html_login_form; ?></li>
                  </ul>
                </li>
                <!--  <li><h2>Categories</h2>
                  <ul>
                    <li><a href="#">Aliquam libero</a></li>
                    <li><a href="#">Consectetuer adipiscing elit</a></li>
                    <li><a href="#">Metus aliquam pellentesque</a></li>
                    <li><a href="#">Suspendisse iaculis mauris</a></li>
                    <li><a href="#">Urnanet non molestie semper</a></li>
                    <li><a href="#">Proin gravida orci porttitor</a></li>
                  </ul>
                </li>-->
              </ul>
            </div>
          </div>
        </div>
        <!-- end #sidebar -->
        <div style="clear: both;">&nbsp;</div>
      </div>
      <!-- end #page -->
    </div>
  </div>
</div>
<div id="footer">
  <div class="content">
    <p>
      <?php echo print_lg('Copyrighting by Auk 2014', $_SESSION['lang']); ?>
    </p>
  </div>
</div>
<!-- end #footer -->
</body>
</html>
