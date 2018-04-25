<!DOCTYPE HTML>

<?php
/**
 * Settings page (c) 2018
 * @author Joe Kanagawa
 */
session_name("sid");
session_start();
require "StandardPage.php";
?>

<html>
<head>
    <?php $page = new StandardPage("Account Settings", "/js/teamone.js"); ?>
</head>

<body>
<?php $page->printHeader(); ?>
<div id="main_container">
    <?php
    if (!($username = $page->authenticate()))
        header("Location: /"); // only logged in users can view
    ?>
    <div id="control_container">
        <?php $page->printUserMenu($username); ?>
        <div id="cp_wrapper">
            <div class="text_box" id="control_panel">
                <table>
                    <tr>
                        <td>Email Address</td>
                        <td>*********cs</td>
                        <td>[Update]</td>
                    </tr>
                    <tr></tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $page->printFooter(); ?>
</body>
</html>