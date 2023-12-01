<?php

include '../../includes/settings.php';
include('../../includes/security_headers.php');


require_once '../../classes/adesk_common.php';
require_once '../../classes/adesk_auth.php';
require_once '../../classes/adesk_survey_form_gen.php';
require_once '../../classes/adesk_monitor.php';


require_once 'classes/adesk_plugin.php';

ADeskAuthenticationUtility::viewErrors(false);
session_start();

// Display a loading message
$surveyData = $_POST;
$modifiedData = ADeskPlugin::castFieldsToIntegers($surveyData);

$requestData = array(
    "extra_vars" => $modifiedData
);

$jobTemplateId = $_POST["id"];
$apiToken = $_SESSION['token'];

// URLs and paths..
$loginUrl = $sURL . $apiPathLogin;
$usersUrl = $sURL . $apiPathUsers;
$userTokenPath = $apiPathPesonalTokens;
$templatePath = $apiPathJobTemplates;
$surveyPath = $apiPathSurvey;

$launchUrl = $sURL . $apiPathJobTemplates . $jobTemplateId . $apiPathLaunch;

//Validate the POST, check if we should continue..
$id=ADeskAuthenticationUtility::validatePost();
$jobTemplatesUrl = $sURL . $templatePath . $id . $surveyPath;

$headers = array(
    "Content-Type: application/json",
    "Authorization: Bearer " . $apiToken
);

// This will be used by the initial template and/or if the plugin will run a template
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $launchUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

// Outside the function
if (ADeskCommon::displayErrorMessageInBox($response)) {
    // There are errors..
    die();
}

curl_close($ch);

$response = json_decode($response, true);

//We are running the first automation and we wait for its return value..
if (isset($response['job']) && $jobTemplateId==$_SESSION['inboundID']) {

    $jobId = $response['job'];

    echo file_get_contents('html/template_survey_header.html');

    // Wait for return from Ansible Playbook..
    $jsonObj=ADeskJobPoller::pollForJobCompletion($sURL, $jobId, $headers);

    // Update the Template Path
    $jobTemplatesUrl = $sURL . $templatePath . ADeskPlugin::generateTemplateID($jsonObj) . $surveyPath;

    // Ge the JSON from the Template return..
    $response = ADeskAuthenticationUtility::getJobTemplate($jobTemplatesUrl, $_SESSION['token']);

    // Extract the job template specifications..
    $spec = $response['spec'];
    
    // Let the plugin control the rest
    // If the plugin draws a form, it can be launched against a template, but there will be no check of its return.. 
   /* ################# Plugin Start #################  */
	echo ADeskPlugin::insertImage($jsonObj);
        echo ADeskSurveryToFormGenerator::generateForm(ADeskPlugin::generateTemplateID($jsonObj), $spec, $_SESSION['email']);
        echo ADeskPlugin::generateScript($jsonObj);
    /* #################  End Plugin  ################# */

    echo file_get_contents('html/logo.html');
}

else{

    AdeskPlugin::redirectFinished();
}
?>
