<?php

/**
 * Uploads POST-sent video data to the database.
 * @author Joe Kanagawa
 * (c) 2018
 */

session_name("sid");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (is_uploaded_file($tmpFile = $_FILES["video"]["tmp_name"])) {
        // echo "\$tmpFile: $tmpFile\n"; # d
        preg_match("/\.\w+$/", $_FILES["video"]["name"],$extension);
        $destFullPath = "$_SERVER[DOCUMENT_ROOT]/res/video/" .
            date("$_SESSION[uid]_Y-m-d_H-i-s") . $extension[0];
        // echo "\$destFullPath: $destFullPath\n"; # d
        // echo "\$_POST[\"title\"]: $_POST[title]\n"; # d
        if (move_uploaded_file($tmpFile, $destFullPath)) {
            $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
            $stmt = $pdo->prepare("INSERT INTO Video(uploaderid,title,uploaddate," .
                "filesize,path,isprocessed) VALUES(?,?,localtimestamp(0),?,?,FALSE)");
            $stmt->bindValue(1, $_SESSION["uid"], PDO::PARAM_INT);
            $stmt->bindValue(2, $_POST["title"] ? $_POST["title"] : $_FILES["video"]["name"]);
            $stmt->bindValue(3, $_FILES["video"]["size"], PDO::PARAM_INT);
            $stmt->bindValue(4, basename($destFullPath));
            if ($stmt->execute()) {
                $stmt = $pdo->prepare("SELECT MAX(id) FROM Video WHERE uploaderid = ?");
                $stmt->bindValue(1, $_SESSION["uid"], PDO::PARAM_INT);
                $stmt->execute();
                if ($row = $stmt->fetch()) {
                    echo $row[0];
                    exit;
                }
            }
        }
    }
}
echo "fail"; // All instances where the program doesn't reach the "exit" command above means upload failure.
