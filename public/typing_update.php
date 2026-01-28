<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

$_SESSION['typing_' . $_POST['community_id']] = time();