<?php
session_start();
$toastrScript = '';
if (isset($_SESSION['toastr_message'])) {
    $type = $_SESSION['toastr_type'] ?? 'info';
    $message = $_SESSION['toastr_message'];
    unset($_SESSION['toastr_message']);
    unset($_SESSION['toastr_type']);
    $toastrScript = "toastr.$type('$message');";
    error_log("Toastr script prepared: " . $toastrScript);
}
?>