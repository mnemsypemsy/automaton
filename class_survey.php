<?php
/*
This code is part of the Automation Desk application.


*/
class ADeskSurveryToFormGenerator{

    /**
    * Generates HTML option elements based on provided choices, with optional custom formatting.
    *
    * This function generates HTML <option> elements based on the provided choices array.
    * It allows setting a default selected value or an array of default selected values.
    * Additionally, it supports custom formatting of choice values using the $formattedChoices parameter.
    *
    * @param array $choices An array of choice values.
    * @param mixed $default The default selected value or an array of default selected values.
    * @param array $formattedChoices An optional associative array containing custom formatting for choice values.
    *
    * @return string The HTML code for the generated <option> elements.
    */
    public static function generateOptions($choices, $default, $formattedChoices = []) {
        // This function generates HTML option elements based on the provided choices.
        $optionsHtml = '';
        foreach ($choices as $index => $choice) {
            // Check if the choice is selected based on default values.
            $isSelected = (is_array($default) && in_array($choice, $default)) || $choice === $default;
            $optionValue = isset($formattedChoices[$index]['choice']) ? $formattedChoices[$index]['choice'] : $choice;
            // Construct the HTML for the option element.
            $optionsHtml .= '<option value="' . $choice . '" ' . ($isSelected ? 'selected' : '') . '>' . $optionValue . '</option>';
        }

        return $optionsHtml;
    }

    /**
    * Generates an HTML input element based on the provided parameters.
    *
    * This function generates an HTML input element with the specified type, name, min, max, value, and required attributes.
    * If the provided $variable matches one of the excluded cases, it returns the $value directly.
    *
    * @param string $type The type of the input element (e.g., text, number, etc.).
    * @param string $variable The name of the input element.
    * @param string $min The min attribute value (optional).
    * @param string $max The max attribute value (optional).
    * @param string $value The value attribute value (optional).
    * @param string $required The required attribute (optional).
    *
    * @return string The HTML input element or the value directly if it matches one of the excluded variables.
    */
    public static function generateInput($type, $variable, $min = '', $max = '', $value = '', $required) {
        // This function generates an HTML input element based on the provided parameters.
        if ($variable !== "sel_host" && $variable !== "sel_ip" && $variable !== "userid" && $variable !== "sel_email") {
            // Generate an input element if the variable is not one of the excluded cases.
            return '<input type="' . $type . '" name="' . $variable . '" ' . $min . ' ' . $max . ' value="' . $value . '" ' . $required . ' ' .  str_replace('max=', 'maxlength=', $max) . '>';
        } else {
            // Echo the value directly if it matches one of the excluded variables.
            return $value;
        }
    }

    /**
    * Generates an HTML form for running an Ansible automation template.
    *
    * This function constructs an HTML form for executing an Ansible automation template.
    * It populates the form with hidden input fields for essential data, such as the host, IP, email, and ID.
    * It also generates form elements based on the provided specifications, including labels, input fields,
    * and select elements for multiple choice questions.
    * Required fields are indicated with a red asterisk.
    *
    * @param string $id The ID associated with the automation.
    * @param array $spec An array of specifications for generating form elements.
    * @param string $host The hostname to pre-fill in the form.
    * @param string $ip The IP address to pre-fill in the form.
    * @param string $email The email address to pre-fill in the form.
    *
    * @return string The generated HTML form.
    */
    public static function generateForm($id, $spec, $email) {
        // We use the sel_ prefix to send data directly to Ansible, without user interaction
        // The corresponding fields with the _sel-prefix must exist in the Ansible-template..
        // We use this to run Automations against CheckMK alerts.
        $formHtml = '
        <form method="POST" action="run_ansible_template.php">
            <input readonly type="hidden" name="id" value="' .  $id . '"><br>
            <input readonly type="hidden" name="sel_email" value="' .  $email . '">
	    <input type="hidden" name="csrf_token" value="' . $_SESSION['cstoken'] . '">
         ';
        // Extract specifications for each question.
        foreach ($spec as $question) {
            $questionName = $question['question_name'];
            $questionDescription = $question['question_description'];
            $required = $question['required'];
            $type = $question['type'];
            $variable = $question['variable'];
            $choices = $question['choices'];
            $default = $question['default'];
            $inputRequired = "";
            $reqHTML = "";
            // Add a red asterisk to indicate required fields.
            if ($required) {
                $reqHTML = '<span style="color: red; font-size: 12px;"> *</span>';
                $inputRequired = "required";
            }
            if ($variable !== "userid" && $variable !== "sel_email") {
                // Generate a label for input elements that are not excluded.
                $formHtml .= '<label for="' . $variable . '">' . $questionName .  $reqHTML . '</label>';
            }
            // Generate input elements based on the question type.
            if ($type === 'multiselect' || $type === 'multiplechoice') {
                // Generate a select element for multiple choices.
                // Options are generated using the generateOptions function.
                if (!empty($choices)) {
                    if (!is_array($choices)) {
                        $choices = explode("\n", $choices);
                    }

                    $formattedChoices = isset($question['formattedChoices']) ? $question['formattedChoices'] : [];

                    $formHtml .= ($type === 'multiselect' ? '<select name="' . $variable . '[]" multiple>' : '<select name="' . $variable . '" ' . $inputRequired . '>') .
                        self::generateOptions($choices, $default, $formattedChoices) .
                        '</select>';
                }
            } elseif (in_array($type, ['text', 'integer', 'float', 'password', 'textarea'])) {
                  // Generate different types of input elements based on the question type.
                  $min = isset($question['min']) ? 'min="' . $question['min'] . '"' : '';
                  $max = isset($question['max']) ? 'max="' . $question['max'] . '"' : '';
                  $inputHtml = @self::generateInput($type === 'textarea' ? 'textarea' : 'number', $variable, $min, $max, $default, $inputRequired);

                if ($type !== 'textarea') {
                    $inputHtml = @self::generateInput($type === 'password' ? 'password' : 'text', $variable, $min, $max, $default, $inputRequired);
                }

                $formHtml .= $inputHtml;
            }
        }

        $formHtml .= '<br><br><input type="submit" value="Run Ansible Automation"></form>';

        return $formHtml;
    }
}
