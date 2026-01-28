<?php
require '../includes/auth.php';

session_destroy();
redirect('/public/login.php');