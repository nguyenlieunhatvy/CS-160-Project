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
        if (isset($_POST["check"]) && ((int)$_POST["check"])) {
            /*$pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
            $stmt = $pdo->prepare("SELECT title FROM Video WHERE id = ? AND uploaderid = ?");
            $stmt->bindValue(1, $_POST["vid"], PDO::PARAM_INT);
            $stmt->bindValue(2, $_SESSION["uid"], PDO::PARAM_INT);
            $stmt->execute();
            if ($row = $stmt->fetch()) {
                preg_replace($row[0])
            }*/
        } else {
            //exec("start exectest 10", $printed);
            $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
            $stmt = $pdo->prepare("SELECT path FROM video WHERE id = ? AND uploaderid = ?");
            $stmt->bindValue(1, $_POST["vid"], PDO::PARAM_INT);
            $stmt->bindValue(2, $_SESSION["uid"], PDO::PARAM_INT);
            $stmt->execute();
            if ($row = $stmt->fetch()) {
                // echo $row[0] . "\n"; # d
                exec("~/PhpstormProjects/CS-160-Project/CS-160-Project/pp ~/PhpstormProjects/CS-160-Project/teamone/res/video/$row[0] ~/PhpstormProjects/CS-160-Project/CS-160-Project/build/main", $o);
                // foreach ($o as $item) echo ($item . "\n"); # d
                /* foreach ($printed as $item)
                    echo $item . "\n";
                $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
                $stmt = $pdo->prepare("UPDATE Video SET isprocessed = TRUE WHERE id = ? AND uploaderid = ?");
                $stmt->bindValue(1, $_POST["vid"], PDO::PARAM_INT);
                $stmt->bindValue(2, $_SESSION["uid"], PDO::PARAM_INT);
                if ($stmt->execute())*/
                echo "success";
            }
        }
    }
}
