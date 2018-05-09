<?php

/**
 * Script that processes the uploaded video!
 * @author Joe Kanagawa
 * (c) 2018
 */

session_name("sid");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["vid"])) {
        $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
        if (isset($_POST["check"])) {
            $stmt = $pdo->prepare("SELECT path FROM Video WHERE id = ? AND uploaderid = ?");
            $stmt->bindValue(1, $_POST["vid"], PDO::PARAM_INT);
            $stmt->bindValue(2, $_SESSION["uid"], PDO::PARAM_INT);
            $stmt->execute();
            if ($row = $stmt->fetch()) { // if corresponding thumbnail exists, then the video is processed.
                $thumbnailFile = preg_replace("/.\w+$/", ".png", $row[0]);
                if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/res/thumbnail/$thumbnailFile"))
                    echo "complete";
            }
        } else {
            //exec("start exectest 10", $printed);
            $stmt = $pdo->prepare("SELECT path FROM video WHERE id = ? AND uploaderid = ?");
            $stmt->bindValue(1, $_POST["vid"], PDO::PARAM_INT);
            $stmt->bindValue(2, $_SESSION["uid"], PDO::PARAM_INT);
            $stmt->execute();
            if ($row = $stmt->fetch()) {
                exec("cd /home/j_kang/PhpstormProjects/CS-160-Project/teamone/res/cpp && " .
                    "./pp ../video/$row[0] main > /dev/null &", $o);
                echo "success";
            }
        }
    }
}
