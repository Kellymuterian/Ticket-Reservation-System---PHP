<?php
require_once 'session.php';
require_once '../constants.php';
if (!isset($_SESSION['amount'], $_SESSION['email'])) {
  @session_destroy();
  header("Location: ../");
  exit;
}

// Simulate a successful payment
$email = $_SESSION['email'];
$amount = $_SESSION['amount'] . "00";
$tranx = new stdClass();
$tranx->data = new stdClass();
$tranx->data->authorization_url = "verify.php?reference=test_reference"; // Redirect to verification page with a test reference
// Redirect to verification page
header('Location: ' . $tranx->data->authorization_url);


  