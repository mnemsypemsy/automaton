User
<?php
/*
This code is part of the Automation Desk application.


This plugin will..
Run the Automation for getting the data from INC in Remedy
Fill the form from survey in Ansible (NSE Create Server)
Run the Ansible Template

*/
class ADeskPlugin {

    public static function generateWelcome() {
        $html = <<<HTML
        <link rel="stylesheet" type="text/css" href="css/welcome_box.css">
        <center>
        <div class="container-box">
            <p><b><font size="6">Welcome to Automation Desk</font></b>
            <br><br><br>Remedy Incident Nr: <b>{$_POST['inc']}</b>
            <br><br> You have a pending server setup:
        </div>
        </center>
        <link rel="stylesheet" type="text/css" href="css/spinner_initiate.css">
        <div class="spinner" id="loading-spinner"></div>
        <script>
            function setInputValue(inputElement, value) {
                inputElement.value = value;
            }
            const inc = document.getElementsByName('remedy_incident_id')[0];
            setInputValue(inc, '{$_POST['inc']}');
        </script>
        <link rel="stylesheet" type="text/css" href="css/spinner.css">
        <style>
            #showFormButton:disabled {
                background-color: #ccc; /* Gray background */
                color: #666; /* Gray text color */
                cursor: not-allowed; /* Change cursor to not-allowed */
            }
        </style>
        <div id="button-container">
            <button id="showFormButton" onclick="disableButton(this)">Continue with the Server Setup</button>
            <div id="spinner"></div>
        </div>
        <script src="js/form_submit.js"></script>
        <script>
            function disableButton(button) {
                button.disabled = true;
            }
        </script>
        HTML;

        return $html;
    }

    public static function generateScript($jsonObj) {
        $script = '
        <script>
            var values = {
                sla_number: "' . $jsonObj['0'] . '",
                availability: "' . $jsonObj['1'] . '",
                location: "' . $jsonObj['2'] . '",
                description: "' . $jsonObj['3'] . '",
                operating_system: "' . $jsonObj['4'] . '",
                network_zone: "' . $jsonObj['5'] . '",
                tier_number: "' . $jsonObj['6'] . '",
                vm_number_vcpu: "' . $jsonObj['7'] . '",
                vm_memory_size: "' . $jsonObj['8'] . '",
                inc: "' . $_POST['remedy_incident_id'] . '",
                email: "' . $jsonObj['email'] . '"
            };
        </script>
        <script src="js/form.js"></script>';

        return $script;
    }


    public static function generateTemplateID($jsonObj) {
        $templateID = ($jsonObj['4'] >= 1 && $jsonObj['4'] <= 8) ? "10" : "9";
        return $templateID;
    }

    public static function insertImage($jsonObj){
	$templateID = ($jsonObj['4'] >= 1 && $jsonObj['4'] <= 8) ? "10" : "9";
      	if($templateID==9){
		echo '<center><img src="images/linux.png" height="50" width="40" valign="bottom">&nbsp;&nbsp;<font face="verdana" size="6">New Virtual Linux Server</font></center><br>';
	}
	else{
		echo '<center><img src="images/win.png" height="50" width="40" valign="bottom">&nbsp;&nbsp;<font face="verdana" size="6">New Virtual Windows Server</font></center><br>';

	}
    }

    public static function castFieldsToIntegers($surveyData) {
        $modifiedData = array();

        // Loop through the data and cast fields with "_int_" prefix as integers
        foreach ($surveyData as $key => $value) {

        if (strpos($key, '_int_') === 0 || strpos($key, 'vm_number_vcpu') === 0 || strpos($key, 'vm_memory_size') === 0) {
                // Remove the "_int_" prefix and cast to integer
                $keyWithoutPrefix = ltrim($key, '_int_'); // Remove the '_int_' prefix
               // $keyWithoutPrefix = substr($key, 5); // Remove the first 6 characters (length of "_int_")
                $modifiedData[$keyWithoutPrefix] = (int)$value;
            } else {
                // If no "_int_" prefix, keep the data unchanged
                $modifiedData[$key] = $value;
            }
        }

        return $modifiedData;
    }


    public static function redirectFinished(){
    	header("Location: finished.php");
	exit;
    }

    public static function version(){
        return "servergen 1.0";

    }


public static function incExists($param) {
    $filePath="/var/www/html/backend/plugins/_logs/inc.log";
return false;
if (!file_exists($filePath)) {
    	touch($filePath);
}
    $file = fopen($filePath, 'r');
    $exists = false;

    if ($file) {
        while (!feof($file)) {
            $line = fgets($file);

            if (stripos($line, $param) !== false) {
                $exists = true;
                break;
            }
        }

        fclose($file);
    }

    if (!$exists) {
        $file = fopen($filePath, 'a');
        fwrite($file, $param . PHP_EOL);
        fclose($file);
        return true;
    }

    return false;
}

}

?>
