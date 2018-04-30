<?php

/**
 * Script that removes specified account permanently from the database.
 * Once deletion process terminates successfully, the corresponding ajax call should not receive back any text data.
 * @author Joe Kanagawa
 * (c) 2018
 */

session_name("sid");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["pwd"])) {
        $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
        $stmt = $pdo->prepare("SELECT password FROM Userbase WHERE id = ?");
        $stmt->bindValue(1, $_SESSION["uid"], PDO::PARAM_INT);
        $stmt->execute();
        if ($row = $stmt->fetch()) {
            if (password_verify($_POST["pwd"], $row[0])) {
                $stmt = $pdo->prepare("DELETE FROM Userbase WHERE id = ?");
                $stmt->bindValue(1, $_SESSION["uid"], PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $_SESSION["letDelete"] = password_hash($_SESSION["uid"] .
                        $_SESSION["firstname"], PASSWORD_DEFAULT);
                    foreach (glob("$_SERVER[DOCUMENT_ROOT]/res/video/$_SESSION[uid]_[0-9][0-9][0-9][0-9]-" .
                        "[0-9][0-9]-[0-9][0-9]_[0-9][0-9]-[0-9][0-9]-[0-9][0-9].*") as $file)
                        unlink($file);
                    /* foreach (glob("$_SERVER[DOCUMENT_ROOT]/res/thumbnail/$_SESSION[uid]_[0-9][0-9][0-9][0-9]-" .
                        "[0-9][0-9]-[0-9][0-9]_[0-9][0-9]-[0-9][0-9]-[0-9][0-9].*") as $file)
                        unlink($file);*/
                    echo "success";
                    exit;
                }
            }
        }
    }
}
echo "fail";
