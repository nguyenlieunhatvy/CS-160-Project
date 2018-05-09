<?php
/**
 * Joe Kanagawa (c) 2018
 */

class StandardPage {

    /**
     * StandardPage constructor.
     * @param string $title String literal to be inserted between the title tag.
     */
    public function __construct($title) {
        $echoStr = "<meta charset='UTF-8' />" .
            "<link rel='stylesheet/less' type='text/less' href='/css/teamone.less' />" .
            "<script src='https://cdnjs.cloudflare.com/ajax/libs/less.js/3.0.0/less.min.js' ></script>" .
            "<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>" .
            "<script src='/js/teamone.js'></script>";
        echo $echoStr . "<title>$title - TEAM ONE</title>";
    }

    /**
     * A simple authentication method for logging in.
     * @return bool|string false, if first time access. Empty string, if login failed.
     */
    public function authenticate() {
        if (isset($_SESSION["uid"])) {
            if ($_SESSION["uid"] !== "undefined") // if $_SESSION["uid"] contains undefined, incorrect login attempt.
                return $_SESSION["firstname"]; // revisit without having logged out previously
            else {
                unset($_SESSION["uid"]);
                return ""; // incorrect login credentials
            }
        }
        return false; // first time access
    }

    /**
     * A simple function to test whether or not the user has just logged out.
     * @return bool true, if just logged out, and will output "Logout Successful" message.
     */
    public function justLoggedOut() {
        if (isset($_SESSION["uid"])) {
            if ($_SESSION["uid"] === "terminate") {
                unset($_SESSION["uid"]);
                unset($_SESSION["firstname"]);
                session_destroy();
                return true;
            }
        }
        return false;
    }

    /**
     * Shows all videos the corresponding user uploaded.
     */
    public function showUploadedVideos() {
        $echoStr = "";
        $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
        $stmt = $pdo->prepare("SELECT title,uploaddate,filesize,path,id FROM " .
            "Video WHERE uploaderid = ? ORDER BY uploaddate DESC");
        $stmt->bindValue(1, $_SESSION["uid"], PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $thumbnailFile = preg_replace("/.\w+$/", ".png", $row[3]);
            $thumbnail = file_exists($_SERVER["DOCUMENT_ROOT"] . "/res/thumbnail/$thumbnailFile") ?
                "style='background: url(/res/thumbnail/$thumbnailFile)'" : "";
            $echoStr .= "<div class='video_entry' onclick='teamone.selectVideo(\"$row[0]\", \"$row[3]\", \"$row[4]\")'>" .
                "<div><div class='thumbnail' $thumbnail></div><div><div><span title='$row[0]'>$row[0]" .
                "</span></div><div>Size: $row[2]B</div></div></div><div>Upload: $row[1]</div></div>";
        }
        echo $echoStr;
    }

    /**
     * Prints the error message that shows upon a 404 error.
     */
    public function print404ErrorMessage() {
        echo "<div class='text_box' style='margin:70px auto 0;width:400px'>" .
            "<span class='sub_header'>Page Not Found</span><p>The page you have requested was not found. " .
            "It may have been permanently removed or could be under maintenance.<br />" .
            "Please make sure that the spelling in the URL is correct, or try again later.<br /></p>" .
            "<p>We apologize for any inconvenience.</p></div>";
    }

    /**
     * Prints HTML code of footer shown at the bottom of the page.
     */
    public function printFooter() {
        echo "<div id='footer'><div class='text_box'>" .
            "&copy;2018 SJSU CS160 SP18 - <a href='/about'>TEAM ONE</a></div></div>";
    }

    /**
     * Prints HTML code of header shown at the top of the page.
     */
    public function printHeader() {
        echo "<div class='text_box' id='header'><a href='/'>" .
            "TEAM ONE</a></div>";
    }

    /**
     * Prints HTML code responsible for the top menu in the user control panel.
     * @param string $username Username to be displayed while logged in.
     */
    public function printUserMenu($username) {
        echo "<div class='text_box' id='user_menu'>Welcome, $username | <a href='/'>Home</a>" .
                " | <a style='cursor:pointer' onclick='teamone.logOut();'>Log Out</a> | " .
                "<a href='/settings'>Account Settings</a></div>";
    }
}
