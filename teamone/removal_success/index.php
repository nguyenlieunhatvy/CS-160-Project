<?php
/**
 * Removal success page (c) 2018
 * Redirects to 404 when not appropriate to show.
 * @author Joe Kanagawa
 */
session_name("sid");
session_start();
if (isset($_SESSION["letDelete"]) && isset($_SESSION["uid"]) && isset($_SESSION["firstname"])) {
    if (password_verify($_SESSION["uid"] . $_SESSION["firstname"], $_SESSION["letDelete"])) {
        unset($_SESSION["letDelete"]);
        unset($_SESSION["uid"]);
        unset($_SESSION["firstname"]);
        session_destroy();
    }
} else {
    header("HTTP/1.0 404 Not Found"); // redirect to 404 and exit
    require $_SERVER["DOCUMENT_ROOT"] . "/err/404.php";
    exit;
}
require "StandardPage.php";
?>

<!DOCTYPE HTML>

<html>
<head>
    <?php $page = new StandardPage("Account Successfully Deleted"); ?>
</head>

<body>
<?php $page->printHeader(); ?>
<div id="main_container">
    <div class="text_box" style="margin:30px auto;width:400px">
        Your account has been successfully deleted.<br />
        Thank you for using our website!<br /><br />
        Redirecting to the main page in 5 seconds..
    </div>
</div>
<?php $page->printFooter(); ?>
<script>setTimeout(function () {window.location.replace("/");}, 5000);</script>
</body>
</html>