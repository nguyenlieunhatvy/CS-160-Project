<!DOCTYPE HTML>

<?php
/**
 * About page (c) 2018
 * @author Joe Kanagawa
 */
require "StandardPage.php";
?>

<html>
<head>
    <style type="text/less">
        #main_window {
            margin: 60px auto 30px !important;
            width: 550px !important;
            > div:not(:first-of-type):not(:last-of-type) {
                margin: 20px auto !important;
            }
        }
    </style>
    <?php $page = new StandardPage("About Us"); ?>
</head>

<body>
<?php $page->printHeader(); ?>
<div id="main_container">
    <div class="text_box" id="main_window">
        <div>
            <span class="sub_header">What Even Is This Website?</span><br />
            This is a website developed as a group project for the required course <!--
            -->"CS160 - Software Engineering" offered at San Jose State University. All this website <!--
            -->does is it sends videos with recognizable human faces to the backend system, <!--
            -->and processes it so all detected faces have delaunay triangles drawn over them. <!--
            -->The backend server, once all processing is complete, returns the processed video <!--
            -->back to the frontend system, making it viewable directly on the browser.
        </div>
        <div>
            <span class="sub_header">Who Are You Guys?</span><br />
            San Jose State University Computer Science students! (as of May 2018)
            <ul>
                <li>Bruno Esquivel - Database Manager</li>
                <li>Joe Kanagawa -  UI/UX Designer, Frontend System Developer</li>
                <li>Cyrus Javan - Backend System Developer (Drawing Delaunay Triangles)</li>
                <li>Vy Nguyen Lieu - Backend System Developer (Eye pupil tracking)</li>
            </ul>
        </div>
        <div>
            <span class="sub_header">I Wish To Be a One-time User</span><br />
            You would still need to create an account. Once you are done, you can delete your account <!--
            -->through the control panel. Deleting an account will automatically delete all of the user's <!--
            -->uploaded videos.
        </div>
    </div>
</div>
<?php $page->printFooter(); ?>
</body>
</html>
