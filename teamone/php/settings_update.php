<?php

/**
 * Changes user settings depending on the contents of POST request
 * Echoes "success", if successful. Nothing, if not.
 * @author Joe Kanagawa
 * (c) 2018
 */

session_name("sid");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["updateType"]) && isset($_POST["newData"])) {
        $updateType = $_POST["updateType"];
        $pdo = new PDO(apache_getenv("PGSQL_DB_DSN")); // establish connection anyway first
        if ($updateType === "pwd" && isset($_POST["oldData"])) {
            $stmt = $pdo->prepare("SELECT password FROM Userbase WHERE id = ?");
            $stmt->bindValue(1, $_SESSION["uid"]);
            $stmt->execute();
            if ($row = $stmt->fetch()) {
                if (password_verify($_POST["oldData"], $row[0])) {
                    $stmt = $pdo->prepare("UPDATE Userbase SET password = ? WHERE id = ?");
                    $stmt->bindValue(1, password_hash($_POST["newData"], PASSWORD_DEFAULT));
                    $stmt->bindValue(2, $_SESSION["uid"], PDO::PARAM_INT);
                    if ($stmt->execute())
                        echo "success";
                } else
                    echo "pwd_not_match"; // old pwd sent as POST not match with current pwd
            }
        } else {
            $columns = array(
                "first" => "firstname",
                "last" => "lastname",
                "eml" => "email"
            );
            if (isset($columns[$_POST["updateType"]])) { // if false, not valid updateType
                $stmt = $pdo->prepare("UPDATE Userbase SET " .
                    $columns[$_POST["updateType"]] . " = ? WHERE id = ?");
                $stmt->bindValue(1, $_POST["newData"]);
                $stmt->bindValue(2, $_SESSION["uid"], PDO::PARAM_INT);
                if ($stmt->execute()) {
                    if ($_POST["updateType"] === "first")
                        $_SESSION["firstname"] = $_POST["newData"];
                    echo "success";
                }
            }
        }
    }
}
