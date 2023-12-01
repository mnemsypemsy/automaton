<?php
/*

This code is part of the Automation Desk application.


AuthenticationUtility
*/
class ADeskAuthenticationUtility {

    /**
     * Display an error message.
     *
     * This function is responsible for displaying an error message on the web page. It generates
     * a structured HTML element that includes a header and text, allowing you to easily inform
     * users about errors or important information.
     *
     * @param string $header The header or title of the error message.
     * @param string $text The text or description of the error message.
     *
     * @return void
     */
    public static function displayError($header, $text) {

        echo '<link rel="stylesheet" type="text/css" href="/frontend/css/error.css">'; // Include your CSS file reference here
        echo '<div class="error-box">';
        echo '<div class="error-prompt">';
        echo '<h2>' . htmlspecialchars($header) . '</h2>';
        echo '<p>' . htmlspecialchars($text) . '</p>';
        echo '</div>';
        echo '</div>';
    }

    /**
    * Get the path to the requested plugin's include file.
    *
    * This function dynamically determines the allowed subfolders within the "plugins" directory
    * and checks whether the requested subfolder exists within the list of valid plugin directories.
    * If the requested subfolder is allowed and the required conditions are met (e.g., "inc" parameter),
    * it returns the path to the requested plugin's include file. Otherwise, it returns null.
    *
    * @param string $requestedPlugin The name of the subfolder within the "plugins" directory to load.
    *
    * @return string|null The path to the include file if the requested plugin is allowed, or null if not allowed or conditions are not met.
    */
    public static function getPluginIncludePath($requestedPlugin) {
        $pluginPath = 'plugins/' . $requestedPlugin . '/adesk_plugin.php';

        // Get a list of subfolders in the "plugins" directory
        $pluginDirectories = glob('plugins/*', GLOB_ONLYDIR);

        if (in_array(dirname($pluginPath), $pluginDirectories) && file_exists($pluginPath) && isset($_POST['inc']) && !empty($_POST['inc'])) {
            return $pluginPath;
        } else {
            return null;
        }
    }

    /**
    * Validates a POST request, checking for CSRF token and required parameters.
    *
    * This function checks if the incoming request method is POST and verifies the CSRF token.
    * If the CSRF token is not present or doesn't match the expected value, it terminates the script.
    * If the 'token' session variable is not set, it checks for the presence of 'username' and 'password' POST parameters.
    * If any required parameter is missing, it generates a new CSRF token and terminates the script.
    * Finally, it filters and sanitizes the 'id' parameter and returns it if valid; otherwise, it terminates the script.
    *
    * @return string|null The sanitized 'id' parameter if valid, or null if the request is not a POST or validation fails.
    */
    public static function validatePost() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token..
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['cstoken']) {
                die("CSRF token validation failed.");
            }
            if (!isset($_SESSION['token'])) {
                if (!isset($_POST['username']) || !isset($_POST['password'])) {
                    $_SESSION['cstoken'] = bin2hex(random_bytes(32));
                    exit("Invalid 'login' parameter.");
                }
            }

            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
            if ($id === false || strlen($id) === 0) {
                die("Invalid 'id' parameter.");
            }

            return $id;
        }
        return null;
    }
    /**
    * Checks the cURL response for errors and displays an error message if necessary.
    *
    * This private utility function is used to check the cURL response for errors.
    * If the response is false, indicating an error, it retrieves the cURL error message
    * and displays it as an error message before exiting the script.
    *
    * @param mixed $response The cURL response to check for errors.
    * @param resource $curlHandle The cURL handle associated with the request.
    */
    private static function checkCurlResponse($response, $curlHandle) {
        if ($response === false) {
            $error = curl_error($curlHandle);
            echo "Error: " . $error . PHP_EOL;
            exit();
        }
    }
    /**
    * Checks if a user ID is empty and performs a session logout if necessary.
    *
    * This function checks the length of the provided user ID. If it is empty (zero length),
    * it performs a session logout by unsetting and destroying the session data.
    * After logout, it redirects the user to the index.php page and terminates the script.
    *
    * @param string $usrid The user ID to check.
    */
    public static function checkUserIdAndLogout($usrid) {
        if(strlen($usrid)==0){
            // Unset and destroy the session
            session_unset();
            session_destroy();

            //Redirect to index.php
            header("Location: index.php");
            die();
        }
    }
    /**
    * Performs a login request to a specified URL using provided login data.
    *
    * This function initializes a cURL request to the given login URL and performs a GET request.
    * It sets the necessary headers, including the 'Content-Type' header, and sends the login data as JSON.
    * The function retrieves the response and checks if the cURL request was successful.
    * Any errors in the response are handled using the 'checkCurlResponse' method.
    * Finally, it closes the cURL connection and returns the login response.
    *
    * @param string $loginUrl The URL for performing the login request.
    * @param array $loginData An associative array containing login data to be sent as JSON in the request body.
    *
    * @return string|null The login response if the request is successful, or null on failure.
    */
    public static function performLogin($loginUrl, $loginData) {
        $loginCurl = curl_init();
        curl_setopt($loginCurl, CURLOPT_URL, $loginUrl);
        curl_setopt($loginCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($loginCurl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($loginCurl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($loginCurl, CURLOPT_POSTFIELDS, json_encode($loginData));
	curl_setopt($loginCurl, CURLOPT_SSL_VERIFYPEER, true);
        $loginResponse = curl_exec($loginCurl);

        self::checkCurlResponse($loginResponse, $loginCurl);
        curl_close($loginCurl);

        return $loginResponse;
    }
    /**
    * Performs a login request to a specified URL using provided login data.
    *
    * This function initializes a cURL request to the given login URL and performs a GET request.
    * It sets the necessary headers, including the 'Content-Type' header, and sends the login data as JSON.
    * The function retrieves the response and checks if the cURL request was successful.
    * Any errors in the response are handled using the 'checkCurlResponse' method.
    * Finally, it closes the cURL connection and returns the login response.
    *
    * @param string $loginUrl The URL for performing the login request.
    * @param array $loginData An associative array containing login data to be sent as JSON in the request body.
    *
    * @return string|null The login response if the request is successful, or null on failure.
    */
    public static function getUserID($usersUrl, $username, $password) {

        $userIDCurl = curl_init();
        curl_setopt($userIDCurl, CURLOPT_URL, $usersUrl);
        curl_setopt($userIDCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($userIDCurl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($userIDCurl, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($userIDCurl, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($userIDCurl);

        self::checkCurlResponse($response, $userIDCurl);
        curl_close($userIDCurl);

        $userId = null;
        $email = null;

        $responseData = json_decode($response, true);

        // Find the user ID by filtering the response data.
        foreach ($responseData['results'] as $user) {
            if ($user['username'] === $username) {
                $userId = $user['id'];
                $email = $user['email'];
                break;
            }
        }

        // Check if the user was found
        if ($userId !== null) {
            $responseArray = array(
                "userid" => $userId,
                "email" => $email
            );
        } else {
            $responseArray = array(
                "error" => "User not found"
            );
        }

        $jsonResponse = json_encode($responseArray);
        return $jsonResponse;
    }
    /**
    * Requests a user token for a specific user from a user-related URL.
    *
    * This function initializes a cURL request to the specified user URL with the user's ID appended.
    * It sets the necessary cURL options, including HTTP basic authentication using session credentials.
    * The function performs a POST request and retrieves the response.
    * It checks if the cURL request was successful and handles any errors using 'checkCurlResponse'.
    * Finally, it closes the cURL connection, decodes the JSON response, and returns the result as an associative array.
    *
    * @param string $usersUrl The base URL for user-related requests.
    * @param string $userId The ID of the user for whom the token is requested.
    * @param string $userTokenPath The path to request the user token.
    * @param string $sessionUsername The session username for HTTP basic authentication.
    * @param string $sessionPassword The session password for HTTP basic authentication.
    *
    * @return array|null An associative array representing the user token if the request is successful, or null on failure.
    */
    public static function requestUserToken($usersUrl, $userId, $userTokenPath, $sessionUsername, $sessionPassword) {
        $userTokenCurl = curl_init();
        curl_setopt($userTokenCurl, CURLOPT_URL, $usersUrl . $userId . $userTokenPath);
        curl_setopt($userTokenCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($userTokenCurl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($userTokenCurl, CURLOPT_USERPWD, $sessionUsername . ":" . $sessionPassword);
        curl_setopt($userTokenCurl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($userTokenCurl, CURLOPT_POST, true);
        $response = curl_exec($userTokenCurl);

        // Check if cURL request was successful
        self::checkCurlResponse($response, $userTokenCurl);
        curl_close($userTokenCurl);
	return json_decode($response, true);

    }
    /**
    * Retrieves a job template from a specified URL using a provided token.
    *
    * This function initializes a cURL request to the given job template URL with the provided token for authorization.
    * It sets the necessary headers, including the authorization header, for the request.
    * The function performs a GET request and retrieves the response.
    * It checks if the cURL request was successful and calls the 'checkCurlResponse' method to handle any errors.
    * Finally, it closes the cURL connection, decodes the JSON response, and returns the result as an associative array.
    *
    * @param string $jobTemplatesUrl The URL to retrieve the job template from.
    * @param string $token The authorization token to use for the request.
    *
    * @return array|null An associative array representing the job template if the request is successful, or null on failure.
    */
    public static function getJobTemplate($jobTemplatesUrl, $token) {
        $templateCurl = curl_init($jobTemplatesUrl); 
        $authorization = "Authorization: Bearer " . $token; 
        curl_setopt($templateCurl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization)); 
        curl_setopt($templateCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($templateCurl, CURLOPT_POST, 0); 
        curl_setopt($templateCurl, CURLOPT_FOLLOWLOCATION, 1); 
	curl_setopt($templateCurl, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($templateCurl); 

        // Check if cURL request was successful
        self::checkCurlResponse($response, $templateCurl);
        curl_close($templateCurl);

	return json_decode($response, true);
    }
    /**
    * Checks if a template is currently running on a specified host.
    *
    * This function constructs a URL to query running jobs for the given template ID and host.
    * It adds the authorization token to the headers and performs a GET request using cURL.
    * The function checks if the cURL request was successful and handles any errors using 'checkCurlResponse'.
    * It then decodes the JSON response and examines the 'results' field to determine if there are running jobs.
    * If running jobs are found, it returns 1; otherwise, it returns 0 to indicate that the template is not running.
    *
    * @param string $serverURL The base URL of the server.
    * @param string $templateId The ID of the template to check.
    * @param string $token The authorization token to use for the request.
    * @param string $hostName The name of the host to check.
    *
    * @return int 1 if the template is running on the host, 0 if it is not running.
    */
    public static function isTemplateRunningOnHost($serverURL, $templateId, $token, $hostName) {
    // Construct the URL to query running jobs
    $templateCurl = curl_init($serverURL .  $templateId . '/jobs/?status=running');
    $authorization = "Authorization: Bearer " . $token; // Prepare the authorization token
    curl_setopt($templateCurl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization)); // Inject the token into the header
    curl_setopt($templateCurl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($templateCurl, CURLOPT_POST, 0); // Specify the request method as POST
    curl_setopt($templateCurl, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
    curl_setopt($templateCurl, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($templateCurl); // Execute the cURL statement

    // Check if cURL request was successful
    self::checkCurlResponse($response, $templateCurl);
    curl_close($templateCurl);

    // Decode the JSON response
    $data = json_decode($response);

    // Check if there are running jobs
    if (!empty($data->results)) {
        // Loop through the results and check if any of them match the host name in extra_vars
            foreach ($data->results as $job) {
                $extraVars = json_decode($job->extra_vars);
                    if ($extraVars && isset($extraVars->sel_host) && $extraVars->sel_host === $hostName) {
                            return 1; // Job with the specified host name is running
                    }
            }
        }
        return 0; // No running job with the specified host name
    }
    /**
    * Retrieves an automation ID from a JSON file based on the specified service.
    *
    * This function reads JSON data from a file specified by $jsonFilePath.
    * It decodes the JSON data into an associative array.
    * If the JSON data cannot be read or decoded, it returns null.
    * It then checks if the requested service exists in the JSON data.
    * If the service exists and is marked as active, it returns the associated template ID.
    * If the service exists but is not active, it returns null.
    * If the service does not exist in the JSON data, it returns null as well.
    *
    * @param string $jsonFilePath The path to the JSON file containing automation data.
    * @param string $service The name of the service for which to retrieve the automation ID.
    *
    * @return string|null The automation ID if found and active, or null if not found or not active.
    */
    public static function getAutomationIDFromJson($jsonFilePath, $service) {
        // Read JSON data from file
        $jsonData = file_get_contents($jsonFilePath);

        if ($jsonData === false) {
            return null; 
        }

        // Decode the JSON data into an associative array
        $data = json_decode($jsonData, true);

        if ($data === null) {
            return null; 
        }

        // Check if the requested service exists in the JSON data
        if (isset($data[$service])) {
            $item = $data[$service];
            $isActive = isset($item['active']) ? $item['active'] : false;

            if ($isActive) {
                return $item['template']; 
            } else {
                return null; 
            }
        } else {
            return null; 
        }
    }
    /**
    * Generates a login form for Automation Desk.
    *
    * This function generates an HTML login form for Automation Desk, which can include hidden fields and login input fields.
    * The form action is set to "backend/get_ansible_survey.php," and it uses POST method for submission.
    * If $hasToken is true, it includes hidden fields for host, service, IP, user ID, and CSRF token, and a "Continue to the Automation" button.
    * If $hasToken is false, it includes hidden fields for host, IP, and CSRF token, as well as input fields for username, password, and an ID field, and a "Login" button.
    * It also displays any provided $loginTxt.
    *
    * @param bool $hasToken Indicates whether the form should include hidden token fields or not.
    * @param string $host The host information.
    * @param string $service The service information.
    * @param string $ip The IP address.
    * @param string $userid The user ID.
    * @param string $automationID The automation ID.
    * @param string $loginTxt Additional text to display on the form.
    */

    function printInput($type, $name, $value) {

	if ($type === 'hidden') {
        	echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
	}
	elseif ($type === 'text' || $type === 'password') {
        	echo '<input type="' . $type . '" name="' . $name . '" placeholder="' . $name . '" required style="width:380px" value="' . $value . '">';
    	}
	elseif ($type === 'submit') {
        	echo '<input type="submit" value="' . $value . '">';
    	}
	else {
        	echo 'Unsupported input type: ' . $type;
	}
    }

   /**
    * Configures the display of PHP errors based on the provided flag.
    *
    * This function allows you to control the display of PHP errors by modifying PHP's configuration settings.
    * If the $showErrors parameter is true, it enables the display of errors, startup errors, and sets error reporting to show all errors.
    * If $showErrors is false, it disables the display of errors, startup errors, and sets error reporting to suppress all errors.
    *
    * @param bool $showErrors Whether to display PHP errors (true) or suppress them (false).
    */
    public static function viewErrors($showErrors) {
        if ($showErrors) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }
    }






    public static function getAutomationFQDNFromJson($jsonFilePath, $service) {

        // Read JSON data from file

        $jsonData = file_get_contents($jsonFilePath);

        if ($jsonData === false) {
            return null;
        }

        // Decode the JSON data into an associative array
        $data = json_decode($jsonData, true);

        if ($data === null) {
            return null;
        }

        // Check if the requested service exists in the JSON data
        if (isset($data[$service])) {
            $item = $data[$service];
            $isActive = isset($item['active']) ? $item['active'] : false;

            if ($isActive) {
                return $item['fqdn'];
            } else {
                return null;
            }
        } else {
            return null;
        }
  

 }
  
}

?>
