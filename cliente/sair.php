
<?php

session_start();

unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nome']);

header('Location: login.php');
exit;
