<?php
/*
This code is part of the Automation Desk application.


*/

require_once '../../classes/adesk_auth.php';
include('../../includes/security_headers.php');

ADeskAuthenticationUtility::viewErrors(true);

session_start();

$automationID = null;
$loginTxt = null;

// Automation ID (Template ID or Name)..
$id = 0;

// Could be anything, id, name etc needed for you automation to run..
$inc = 0;

// Initialize or refresh the CSRF token if it doesn't exist
if (!isset($_SESSION['cstoken'])) {
    $_SESSION['cstoken'] = bin2hex(random_bytes(32));
}

// Check CSRF token on POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['cstoken']) {
        die("CSRF token validation failed.");
    }
}

// Get values for 'id', 'project' and 'inc' from GET, if provided
$id = $_GET['id'] ?? null;
$inc = $_GET['inc'] ?? null;
$plugin = $_GET['plugin'] ?? null;
$_SESSION['inboundID'] = $id;

//Generate the login form..
if($id !=0){
	echo file_get_contents('html/indexform_top.html');

	$utility = new ADeskAuthenticationUtility();
	$utility->printInput('hidden', 'csrf_token', $_SESSION['cstoken']);
	$utility->printInput('text', 'username',"");
	$utility->printInput('password', 'password',"");
	$utility->printInput('text', 'inc', $inc);
	$utility->printInput('hidden', 'id', $id);
	$utility->printInput('hidden', 'plugin', $plugin);
	$utility->printInput('submit', '', 'Login');

	echo '<br>' . $loginTxt . '<br>';
	echo file_get_contents('html/indexform_footer.html');
}
else{

	$templateContent = file_get_contents('html/template_login_error.html');
	echo $templateContent;
}
?>
