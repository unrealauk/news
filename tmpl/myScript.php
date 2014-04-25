<script type="text/javascript">
  function confirmDelete() {
    var msg = '<?php echo print_lg('You confirm delete?', $_SESSION['lang'])?>';
    if (confirm(msg)) {
      return true;
    } else {
      return false;
    }
  }

  function validateFormLogin() {    // Validate login
    var login = document.getElementById("form_login").value;
    var password = document.getElementById("form_password").value;
    var err = '<?php echo  print_lg('Incorect login or pass', $_SESSION['lang'])?>';
    var FormErr = document.getElementById('form_err');
    if (login == '' || login == null || password == '' || password == null) {
      FormErr.innerHTML = err;
      return false;
    }
  }

  function validateFormReg() {    // Validate login
    //Validation login.
    var login = document.getElementById("login").value;
    var err_login = document.getElementById("login_error");
    if (login == '' || login == null) {
      err_login.innerHTML = '<?php echo  print_lg('Please write login', $_SESSION['lang'])?>';
      document.getElementById("login").focus();
    }
    else {
      $.post('../inc/checkreg.php', {login: login},
        function (data) {
         // err_login.innerHTML = data;
          err_login.innerHTML=data;
        }
      );
    }
    //Check email
    var emailElement = document.getElementById("email");
    var errorElement = document.getElementById("email_error");
    var emailValue = document.getElementById("email").value;
    var atPos = emailValue.indexOf("@");
    if (atPos <= 0) {
      errorElement.innerHTML = '<?php echo print_lg('Incorrect email addresses Example: mail@example.com', $_SESSION['lang'])?>';
      document.getElementById("email").focus();
     }
    else {
      document.getElementById("email").focus();
      $.post('../inc/checkreg.php', {email: emailValue},
        function (data) {
          errorElement.innerHTML = data;
        }
      );
    }
    if (err_login.innerHTML!=''||errorElement.innerHTML!='') return false;
  }
</script>