<?php

/**
 * Script used to change the logout flag.
 * Triggered ONLY upon logging out, and DOES NOT represent any state.
 * @author Joe Kanagawa
 * (c) 2018
 */

session_name("sid");
session_start();

if (isset($_SESSION["uid"])) {
    $_SESSION["uid"] = "terminate";
}
