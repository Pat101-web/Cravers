<?php
// public/vendor_logout.php
session_start();
session_unset();
session_destroy();
header("Location: vendor_login.php");
exit;
