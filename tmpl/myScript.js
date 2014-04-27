function confirmDelete() {
    var msg = 'You confirm delete?';
    if (confirm(msg)) {
        return true;
    } else {
        return false;
    }
}

function validateFormLogin() {    // Validate login
    var loginValue = document.getElementById("form_login").value;
    var passwordValue = document.getElementById("form_password").value;
    var errMsg = 'Incorect login or pass';
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
        err_loginElement.innerHTML = 'Please write login';
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
        email_errorElement.innerHTML = 'Incorrect email addresses Example: mail@example.com';
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
        pass_errElement.innerHTML = 'Enter a valid password! min 5 chars';
    } else {
        pass_errElement.innerHTML = '';
    }
    if (passValue != rpassValue) {
        rpass_errElement.innerHTML = 'Password: no match';
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
    var errMsg = 'Write title and text';
    var ErrElement = document.getElementById('news_error');
    if (title_enValue == '' || title_uaValue == ''
        || text_enValue == '' || text_uaValue == '') {
        ErrElement.innerHTML = errMsg;
        return false;
    }
}
function validateAddComment() {    // Validate Add Comment
    var text_commentValue = document.getElementById("text_comment").value;
    var errMsg = 'Write text for comments';
    var ErrElement = document.getElementById('comment_error');
    if (text_commentValue == '' || text_commentValue == null) {
        ErrElement.innerHTML = errMsg;
        return false;
    }
}