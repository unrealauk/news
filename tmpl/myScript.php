<script>
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
    var login = document.getElementById("login").value;
    var err_login = document.getElementById("login_error");
    if (login == '' || login == null) {
      err_login.innerHTML = 'Please write login';
      return false;
    }
    else {
      err_login.innerHTML = '';
      return false;//delete
    }
  }
</script>