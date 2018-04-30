<!DOCTYPE HTML>

<?php
/**
 * Top page (c) 2018
 * @author Joe Kanagawa
 */
session_name("sid");
session_start();
require "StandardPage.php"
?>

<html>
<head>
    <?php
    $page = new StandardPage("Welcome!", "/js/teamone.js");
    ?>
</head>

<body>
<?php
$page->printHeader();
$lgOutAcc = $page->justLoggedOut();
?>
<div id="main_container">
    <?php if (!($username = $page->authenticate())) : ?>
        <div class="text_box" id="main_window">
            <span class="sub_header">Greetings</span><br />
            Please enter your login credentials below to proceed.
            <?php
            if ($username === "") // login fail?
                echo "<br /><span class='error_message'>Login failed. "
                    . "Unable to find a user with the given credentials.</span>";
            else if (isset($lgOutAcc) && $lgOutAcc) // ..or logout?
                echo "<br /><span class='error_message'>Logout Successful</span>";
            ?>
            <form action="/php/authenticate.php" method="post">
                <div class="input_table">
                    <div>
                        <span><label for="username">Username:</label></span>
                        <span><input id="username" name="usr" /></span>
                    </div>
                    <div>
                        <span><label for="password">Password:</label></span>
                        <span><input id="password" type="password" name="pwd" /></span>
                    </div>
                    <div>
                        <span></span>
                        <span>
                            <input type="submit" value="Login" />
                        </span>
                    </div>
                    <div>
                        <span>No Account?</span>
                        <span><a style="cursor: pointer" onclick="teamone.showSignUp();">Sign Up</a></span>
                    </div>
                </div>
            </form>
        </div>
    <?php else : ?>
        <div id="control_container">
            <?php $page->printUserMenu($username); ?>
            <div id="cp_wrapper">
                <div class="text_box" id="control_panel">
                    <div id="video_settings">
                        <span>Playing: None</span>
                        <div id="delete_video" title="Click here to delete the selected video" ></div>
                        <div id="rename_video" title="Click here to rename the selected video"></div>
                    </div>
                    <div id="video_box">
                        <div>
                            Video not selected yet.<br />
                            Please select or upload one using the side menu to the right.
                        </div>
                        <video width="384" height="288">
                        </video>
                    </div>
                </div>
                <div class="text_box" id="video_menu">
                    <div>
                        <div>
                            <div>
                                <div id="add_img" onclick="teamone.showUpload();"></div>
                                <div>Upload Video</div>
                            </div>
                        </div>
                    </div>
                    <?php $page->showUploadedVideos(); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $page->printFooter(); ?>
</body>
</html>
