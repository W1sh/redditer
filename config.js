window.onload = boot;

var ID_FORM = "idWordpressForm",
    ID_WORDPRESS_URL = "idWordpressUrl",
    ID_WORDPRESS_USER = "idWordpressUser",
    ID_WORDPRESS_PASSWORD ="idWordpressPassword";

var eleForm, eleWordpressUrl, eleWordpressUser, eleWordpressPassword;

function boot() {
    eleForm = $(ID_FORM);
    eleWordpressUrl=$(ID_WORDPRESS_URL);
    eleWordpressUser = $(ID_WORDPRESS_USER);
    eleWordpressPassword = $(ID_WORDPRESS_PASSWORD);

    var objects = [eleForm, eleWordpressUrl, eleWordpressUser, eleWordpressPassword];
    var bAllOk = allOk(objects);
    if (!bAllOk) {
        alert("There is 1+ object(s) with a problem.");
        return;
    }// if
}// boot