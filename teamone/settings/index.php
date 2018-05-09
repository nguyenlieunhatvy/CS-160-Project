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
    <style type="text/less">
        #user_settings_table {
            margin-top: 20px;
            text-align: center;
            &, tr, td {
                border: solid 2px black;
                border-collapse: collapse;
                padding: 6px;
            }
            a {
                cursor: pointer;
            }
        }
    </style>
    <?php $page = new StandardPage("Account Settings"); ?>
</head>

<body>
<?php $page->printHeader(); ?>
<div id="main_container">
    <?php if (!($username = $page->authenticate())) : ?>
        <?php
        header("HTTP/1.0 404 Not Found");
        $page->print404ErrorMessage(); // only logged in users can view
        ?>
        <script>$("title").html("Page Not Found - TEAM ONE");</script>
    <?php else : ?>
        <div id="control_container">
            <?php
            $page->printUserMenu($username);
            $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
            $stmt = $pdo->prepare("SELECT firstname,lastname,email,extract(EPOCH FROM since) FROM Userbase WHERE id = ?");
            $stmt->bindValue(1, $_SESSION["uid"], PDO::PARAM_INT);
            $stmt->execute();
            ?>
            <div id="cp_wrapper">
                <div class="text_box" id="control_panel">
                    <?php if ($row = $stmt->fetch()) : ?>
                    You are a user since: <?=date("F j, Y" , $row[3])?><br /><br />
                    Keep in mind that usernames cannot be updated.
                    <table id="user_settings_table">
                        <tr>
                            <td>Edit First Name</td>
                            <td><?=$row[0]?></td>
                            <td><a onclick="teamone.showUpdate('settings_popup/update_first.html');">Update</a></td>
                        </tr>
                        <tr>
                            <td>Edit Last Name</td>
                            <td><?=$row[1]?></td>
                            <td><a onclick="teamone.showUpdate('settings_popup/update_last.html');">Update</a></td>
                        </tr>
                        <tr>
                            <td>Change Email Address</td>
                            <td><?=$row[2]?></td>
                            <td><a onclick="teamone.showUpdate('settings_popup/update_email.html');">Update</a></td>
                        </tr>
                        <tr>
                            <td colspan="2">Update Password</td>
                            <td><a onclick="teamone.showUpdate('settings_popup/update_password.html');">Update</a></td>
                        </tr>
                        <tr>
                            <td colspan="2">Delete Account</td>
                            <td><a onclick="teamone.confirmDelete();">Delete</a></td>
                        </tr>
                    </table>
                    <?php else : ?>
                    Unable to retrieve user data. Please contact system administrator.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $page->printFooter(); ?>
</body>
</html>