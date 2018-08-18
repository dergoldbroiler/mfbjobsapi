<?php
header("Content-Disposition: attachment; filename=".urldecode($_GET['filename']));
readfile("wp-content/plugins/".urldecode($_GET['filename']));
?>