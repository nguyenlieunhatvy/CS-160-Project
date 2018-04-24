<?php

/**
 * Script used for signing up process
 * @author Joe Kanagawa
 * (c) 2018
 */

session_name("sid");
session_start();

if (isset($_POST["vcode"])) {
    if (!isset($_COOKIE["vcode_sid"])) { // expired cookie
        unset($_SESSION["vcode"]);
        echo "expired";
    } else {
        if ($_POST["vcode"] == $_SESSION["vcode"]) {
            // verification success.
            // connect to postgres db to add this user.
            $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
            $stmt = $pdo->prepare("INSERT INTO Userbase(username,password," .
                "firstname,lastname,email,since) VALUES(?,?,?,?,?,current_timestamp(0))");
            $stmt->bindValue(1, $_POST["user"]);
            $stmt->bindValue(2, password_hash($_POST["pwd"], PASSWORD_DEFAULT));
            $stmt->bindValue(3, $_POST["first"]);
            $stmt->bindValue(4, $_POST["last"]);
            $stmt->bindValue(5, $_POST["email"]);
            if ($stmt->execute()) {
                echo "success";
                setcookie("vcode_sid", session_id(), 1, "/");
                unset($_SESSION["vcode"]); // verification complete, no longer need this
                exit;
            }
        }
        echo "fail";
    }
} else { // else, make script send out email
    if (!isset($_COOKIE["vcode_sid"])) { // if vcode not set in session file, issue a verification code
        if (isset($_POST["user"]) && isset($_POST["email"])) {
            $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Userbase WHERE username = ?");
            $stmt->bindValue(1, $_POST["user"]);
            if ($stmt->execute()) {
                if(!$stmt->fetch()[0]) { // no user with given username exists
                    $vcode = rand(1000, 10000);
                    $_SESSION["vcode"] = $vcode;
                    setcookie("vcode_sid", session_id(), time() + 60, "/");
                    /*mail($_POST["email"], "[TEAM ONE - Computer Vision System] Verification Code",
                        "Hi, $_POST[user].\n\nThank you very much for your business. " .
                        "The following is a verification code we would need to continue with your " .
                        "registration to our website.\n\nEnter this code in the prompt: $vcode\n\n" .
                        "We once again thank you for your interest and I hope you enjoy!\n\n" .
                        "If you do not recognize this action, please ignore this email.\n\n" .
                        "SJSU CS160 - TEAM ONE", "From: bruno@website.domain");*/
                    echo $vcode; // d
                } else {
                    echo "user_exists";
                }
            }
        }
    }
}
