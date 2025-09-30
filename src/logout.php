<?php
setcookie("user_data", "", time() - 3600, "/");
header("Location: home.php");
exit();
?>