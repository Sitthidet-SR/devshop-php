<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/auth_functions.php';

// ออกจากระบบ
logout_user();

require_once 'includes/redirect_helper.php';
redirect_with_message('index.php', 'logout');
?>
