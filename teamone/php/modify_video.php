<?php

/**
 * Script that, depending on the POST input, deletes or renames the corresponding video.
 * @author Joe Kanagawa
 * (c) 2018
 */

session_name("sid");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["modifyType"]) && isset($_POST["path"])) {
        $pdo = new PDO(apache_getenv("PGSQL_DB_DSN"));
        if ($_POST["modifyType"] === "delete") {
            $stmt = $pdo->prepare("DELETE FROM Video WHERE uploaderid = ? AND path = ?");
            $stmt->bindValue(1, $_SESSION["uid"], PDO::PARAM_INT);
            $stmt->bindValue(2, $_POST["path"]);
            if ($stmt->execute()) {
                unlink($_SERVER["DOCUMENT_ROOT"] . "/res/video/" . $_POST["path"]); // rm
                // unlink($_SERVER["DOCUMENT_ROOT"] . "/res/thumbnail/" . $_POST["path"]);
                echo "success";
            }
        } else if ($_POST["modifyType"] === "rename" && isset($_POST["title"])) {
            $stmt = $pdo->prepare("UPDATE Video SET title = ? WHERE uploaderid = ? AND path = ?");
            $stmt->bindValue(1, $_POST["title"]);
            $stmt->bindValue(2, $_SESSION["uid"], PDO::PARAM_INT);
            $stmt->bindValue(3, $_POST["path"]);
            if ($stmt->execute())
                echo "success";
        } else
            exit;
    }
}