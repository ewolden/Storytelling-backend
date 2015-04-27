<?php
$output = shell_exec("cd /app && git pull 2>&1");
echo $output;
?>