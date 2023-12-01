<?php
/*
This code is part of the Automation Desk application.


*/

class ADeskCommon {
    /**
    * Casts specific fields in the survey data array to integers based on a "_int_" prefix.
    *
    * This function takes an associative array $surveyData and creates a new array with modified values.
    * It loops through the data and checks if each field's name begins with "_int_" prefix.
    * If a field has the prefix, it removes the prefix and casts the corresponding value to an integer.
    * Fields without the "_int_" prefix are included in the new array as-is.
    *
    * @param array $surveyData The survey data as an associative array.
    *
    * @return array The modified survey data with specific fields cast to integers.
    */
    public static function castFieldsToIntegers($surveyData) {
        $modifiedData = array();

        // Loop through the data and cast fields with "_int_" prefix as integers
        foreach ($surveyData as $key => $value) {
 
	if (strpos($key, '_int_') === 0) {
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



	public static function displayErrorMessageInBox($jsonResult) {
		// Decode the JSON data
		$errorData = json_decode($jsonResult, true);

		// Check if the decoded data matches the expected structure
		if (is_array($errorData) && isset($errorData['variables_needed_to_start'])) {
			// Extract the error messages
			$errorMessages = $errorData['variables_needed_to_start'];

			// Check if there are error messages
			if (!empty($errorMessages)) {
				// Create an error message
				$errorMessage = "<font face='verdana' size='2'><b>Ansible Form Validation Error:</b><br><br>" . implode("<br>", $errorMessages);

				// Display the error message in a styled box
				echo '<br><br><center></font><font size="3"><div style="width:900px; border: 2px solid red; background-color: #ffeeee; padding: 10px;">' . $errorMessage . '<br><br>Please correct the error(s) and try again.</div></font></center>';
			}
		} else {
			// If the JSON data structure doesn't match what's expected
//			echo "Invalid data format in the JSON result.";
		}
	}



}
?>
