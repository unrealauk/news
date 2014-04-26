<script type="text/javascript">
  //Confirm delete
  function confirmDelete() {
    var msg = '<?php echo print_lg('You confirm delete?', $_SESSION['lang'])?>';
    if (confirm(msg)) {
      return true;
    } else {
      return false;
    }
  }
  function validateFormLogin() {    // Validate login
    var loginValue = document.getElementById("form_login").value;
    var passwordValue = document.getElementById("form_password").value;
    var errMsg = '<?php echo  print_lg('Incorect login or pass', $_SESSION['lang'])?>';
    var ErrElement = document.getElementById('form_err');
    if (loginValue == '' || loginValue == null || passwordValue == '' || passwordValue == null) {
      ErrElement.innerHTML = errMsg;
      return false;
    }
  }
  function validateFormReg() {    // Validate Form Reg
    //Validation login.
    var loginValue = document.getElementById("login").value;
    var err_loginElement = document.getElementById("login_error");
    if (loginValue == '' || loginValue == null) {
      err_loginElement.innerHTML = '<?php echo  print_lg('Please write login', $_SESSION['lang'])?>';
    }
    else {
      $.post('../inc/validate.php', {login: loginValue},
        function (data) {
          // err_login.innerHTML = data;
          err_loginElement.innerHTML = data;
        }
      );
    }
    //Check email
    var emailElement = document.getElementById("email");
    var email_errorElement = document.getElementById("email_error");
    var emailValue = emailElement.value;
    var atPos = emailValue.indexOf("@");
    if (atPos <= 0) {
      email_errorElement.innerHTML = '<?php echo print_lg('Incorrect email addresses Example: mail@example.com', $_SESSION['lang'])?>';
    }
    else {
      $.post('../inc/validate.php', {email: emailValue},
        function (data) {
          email_errorElement.innerHTML = data;
        }
      );
    }
    //Check password
    var passValue = document.getElementById("password").value;
    var rpassValue = document.getElementById("rpassword").value;
    var pass_errElement = document.getElementById("password_error");
    var rpass_errElement = document.getElementById("rpassword_error");
    if (passValue.length < 5) {
      pass_errElement.innerHTML = '<?php echo  print_lg('Enter a valid password! min 5 chars', $_SESSION['lang'])?>';
    } else {
      pass_errElement.innerHTML = '';
    }
    if (passValue != rpassValue) {
      rpass_errElement.innerHTML = '<?php echo  print_lg('Password: no match', $_SESSION['lang'])?>';
    } else {
      rpass_errElement.innerHTML = '';
    }
    // All validate
    if (err_loginElement.innerHTML != ''
      || email_errorElement.innerHTML != ''
      || pass_errElement.innerHTML != ''
      || rpass_errElement.innerHTML != '') return false;
  }
  function validateAddNews() {    // Validate Add News
    var title_enValue = document.getElementById("title_en").value;
    var title_uaValue = document.getElementById("title_ua").value;
    var text_enValue = document.getElementById("text_en").value;
    var text_uaValue = document.getElementById("text_ua").value;
    var errMsg = '<?php echo print_lg('Write title and text', $_SESSION['lang'])?>';
    var ErrElement = document.getElementById('news_error');
    if (title_enValue == '' || title_uaValue == ''
      || text_enValue == '' || text_uaValue == '') {
      ErrElement.innerHTML = errMsg;
      return false;
    }
  }
  function validateAddComment() {    // Validate Add Comment
    var text_commentValue = document.getElementById("text_comment").value;
    var errMsg = '<?php echo  print_lg('Write text for comments', $_SESSION['lang'])?>';
    var ErrElement = document.getElementById('comment_error');
    if (text_commentValue == '' || text_commentValue == null) {
      ErrElement.innerHTML = errMsg;
      return false;
    }
  }
</script>