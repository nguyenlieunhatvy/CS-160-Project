<?php

/**
 * Script used for authentication process.
 * Writing in a separate file so the header function is guaranteed to work.
 * @author Joe Kanagawa
 * (c) 2018
 */

session_name("sid");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["usr"]) && isset($_POST["pwd"])) {
        $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
        $stmt = $pdo->prepare("SELECT id, password, firstname FROM Userbase WHERE username = ?");
        $stmt->bindValue(1, $_POST["usr"]);
        $stmt->execute();
        if ($row = $stmt->fetch()) {
            if (password_verify($_POST["pwd"], $row[1])) { // login success inside this if block
                $_SESSION["uid"] = $row[0];
                $_SESSION["firstname"] = $row[2];
                $stmt = $pdo->prepare("UPDATE Userbase SET lastlogin = localtimestamp(0), ipaddr = ? WHERE id = ?");
                $stmt->bindValue(1, $_SERVER["REMOTE_ADDR"]);
                $stmt->bindValue(2, $_SESSION["uid"], PDO::PARAM_INT);
                $stmt->execute();
                session_regenerate_id(true);
                header("Location: /");
                exit;
            }
        }
    }
    $_SESSION["uid"] = "undefined";
    header("Location: /");
}
