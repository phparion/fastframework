<?php
session_start();

include "/home3/p33hp33/public_html/newFC/core/config/settings.info.php";

include "/home3/p33hp33/public_html/newFC/core/code/php/classes/shell.class.php";

$boot = new shell();
$boot->appShell();
$boot->showOutput();

?>