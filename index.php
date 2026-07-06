<?php
require_once __DIR__ . "/auth.php";

header("Location: " . (is_logged_in() ? "main.php" : "login.php"));
exit;
?>
