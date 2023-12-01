<?php
/*
This code is part of the Automation Desk application.
It handles the login process, token generation, and template rendering.

*/

echo file_get_contents('html/template_survey_header.html');

include '../../includes/settings.php';
include('../../includes/security_headers.php');

require_once '../../classes/adesk_survey_form_gen.php';
require_once '../../classes/adesk_auth.php';
require_once 'classes/adesk_plugin.php';


ADeskAuthenticationUtility::viewErrors(true);

// URLs and paths..
$loginUrl = $sURL . $apiPathLogin;
$usersUrl = $sURL . $apiPathUsers;
$userTokenPath = $apiPathPesonalTokens;
$templatePath = $apiPathJobTemplates;
$surveyPath = $apiPathSurvey;
$userId = null;
$token = null;
$id = null;
$email=null;
$responseDataRoot = null;

session_start();

echo "<!-- Plugin Loaded: " . AdeskPlugin::version() . " -->"; 

//Validate the POST, check if we should continue..
$id=ADeskAuthenticationUtility ::validatePost();
$jobTemplatesUrl = $sURL . $templatePath . $id . $surveyPath;

if(!isset($_SESSION['token'])){

    // Because session data is stored on the server side and not exposed to the client..
    $_SESSION['username'] = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $_SESSION['password'] = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Login Request..
    $loginData = [
        'username' => $_SESSION['username'],
        'password' => $_SESSION['password']
    ];

    // Perform initial login..
    $loginResponse = ADeskAuthenticationUtility::performLogin($loginUrl, $loginData);

    // Get the User ID (slightly different call)..
    $response = ADeskAuthenticationUtility::getUserID($usersUrl, $_SESSION['username'], $_SESSION['password']);
    $responseArray = json_decode($response, true);
    $userId = $responseArray['userid'];
    $email = $responseArray['email'];
    $_SESSION['email'] = $email; 
    $_SESSION['plugin'] = $_POST['plugin'];

    // Check if valid user id..
    ADeskAuthenticationUtility::checkUserIdAndLogout($userId);

    // Token Request..
    $response = ADeskAuthenticationUtility::requestUserToken($usersUrl, $userId, $userTokenPath, $_SESSION['username'], $_SESSION['password']);

    // Extract the user ID from the first result..
    $_SESSION['token'] = $response['token'];
}


//Continue if this is a brand new INC..
if(!AdeskPlugin::incExists($_POST['inc'])){
	// Ge the template json..
	$response = ADeskAuthenticationUtility ::getJobTemplate($jobTemplatesUrl, $_SESSION['token']);

	// Extract the job template specifications..
	$spec = $response['spec'];

	if (empty($spec)) {
		header("Location: logout.php?id=" . $id . "&plugin=" . $_POST['plugin'] . "&inc=" . $_POST['inc']);
		exit();
	}

	// Render the form..
	echo ADeskSurveryToFormGenerator::generateForm($id, $spec, $_SESSION['email']);

	echo AdeskPlugin::generateWelcome();

	echo file_get_contents('html/template_survey_footer.html');
}
?>
