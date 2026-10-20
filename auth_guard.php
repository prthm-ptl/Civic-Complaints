<?php
function require_login() {
    if (!isset($_SESSION['userid'])) {
        header('location: login.php');
        exit;
    }
}

function require_role($role) {
    if (!isset($_SESSION['role'])) {
        header('location: login.php');
        exit;
    }
    if ($_SESSION['role'] !== $role) {
        if ($_SESSION['role'] === 'officer') {
            header('location: officer_dashboard.php');
        } else {
            header('location: feed.php');
        }
        exit;
    }
}
?>