<?php
/**
 * Ansible Job Poller
 *
 * This class provides functionality to poll an Ansible job for completion and retrieve JSON data.
 *

 * Oct 2023
 */
class ADeskJobPoller {

   /**
    * Polls an Ansible job for completion and retrieves JSON data.
    *
    * This method continuously polls an Ansible job until it is completed and retrieves JSON data from the job logs upon 
    * successful completion. If the job fails to complete within the specified timeout or exceeds the maximum number of retries, 
    * an error message will be displayed.
    *
    * @param string $sURL The base URL for the Ansible API.
    * @param string $jobId The unique identifier of the Ansible job.
    * @param array $headers An array of HTTP headers, including authorization information.
    *
    * @return array|null An associative array containing JSON data from the job logs upon successful completion, 
    * or null if the job fails or the maximum number of retries is exceeded.
    *
    * @throws Exception if the timeout is reached or the job launch fails after maximum retries.
    */
    public static function pollForJobCompletion($sURL, $jobId, $headers) {
        $jsonObj = null; // Initialize jsonObj
        $timeout = time() + 180; // 3 minutes timeout
        $maxRetries = 10;
        $retryCount = 0;
        $retryDelay = 2; // Initial retry delay in seconds

        while (true) {
            if (time() >= $timeout) {
                die("Timeout reached. Job did not complete within the expected time.");
            }

            $jobStatusResponse = self::fetchJobStatus($sURL, $jobId, $headers);
            $jobStatus = json_decode($jobStatusResponse, true);

            if (isset($jobStatus['status'])) {
                if ($jobStatus['status'] === 'successful') {
                    // Retrieve and display job logs
                    $jobLogContent = self::fetchJobLog($sURL, $jobId, $headers);
                    $jsonObj = self::extractJsonData($jobLogContent);
                    break; // Job is completed, exit the loop
                } elseif ($jobStatus['status'] === 'failed') {
                    die("Job launch failed after $maxRetries retries.");
                }
            }

            if ($retryCount >= $maxRetries) {
                die("Maximum retries exceeded.");
            }

            $retryCount++;
            $retryDelay *= 2; // Exponential backoff, doubling the delay
            sleep(min($retryDelay, 60)); // Cap the maximum delay at 60 seconds
        }

        return $jsonObj; // Return the JSON object
    }

    /**
     * Fetches the job status from the Ansible API.
     *
     * This method sends a cURL request to the Ansible API to retrieve the job status.
     *
     * @param string $sURL The base URL for the Ansible API.
     * @param string $jobId The unique identifier of the Ansible job.
     * @param array $headers An array of HTTP headers, including authorization information.
     *
     * @return string The JSON response containing job status.
     */
    private static function fetchJobStatus($sURL, $jobId, $headers) {
        $jobStatusUrl = $sURL . "/api/v2/jobs/" . $jobId . "/";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $jobStatusUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $jobStatusResponse = curl_exec($ch);
        curl_close($ch);

        return $jobStatusResponse;
    }

    /**
     * Fetches the job log content from the Ansible API.
     *
     * This method sends a cURL request to the Ansible API to retrieve the job log content.
     *
     * @param string $sURL The base URL for the Ansible API.
     * @param string $jobId The unique identifier of the Ansible job.
     * @param array $headers An array of HTTP headers, including authorization information.
     *
     * @return string The JSON response containing job log content.
     */
    private static function fetchJobLog($sURL, $jobId, $headers) {
        $jobLogUrl = $sURL . "/api/v2/jobs/" . $jobId . "/stdout/?format=json";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $jobLogUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $jobLogResponse = curl_exec($ch);
        curl_close($ch);

        return json_decode($jobLogResponse)->content;
    }

    /**
     * Extracts JSON data from a job log content.
     *
     * This method extracts and processes JSON data from the job log content.
     *
     * @param string $jobLogContent The content of the job log.
     *
     * @return array|null An associative array containing the extracted JSON data, or null if extraction fails.
     */
    private static function extractJsonData($jobLogContent) {
        $pattern = '/response_notes\.json\.entries(.*?})/s';

        if (preg_match($pattern, $jobLogContent, $matches)) {
            $input_string = $matches[0];

            // Define a regular expression pattern to match text between specific curly braces
            $pattern = '/adsk[^}]+\}/';

            // Use preg_match_all to find all matches
            if (preg_match_all($pattern, $input_string, $matches)) {
                // $matches[0] contains all the full matches

                // Replace escaped double quotes and add quotes around the "adsk" key
                $validJsonString = substr_replace($matches[0], '{"adsk":"', 0, 8);
                $validJsonString = substr_replace($validJsonString, '"}', -2);
                $jsonStruct = str_replace('\\', '', $validJsonString[0]);
                $jsonStruct = str_replace('""', '"', $jsonStruct);

                // Parse the JSON string into an associative array
                $jsonData = json_decode($jsonStruct, true);
                if (isset($jsonData)) {
                    return $jsonData;
                } else {
                    echo 'Failed to decode JSON data.';
                }
            } else {
                echo "Failed to match JSON pattern.";
            }
        }

        return null; // Return null if JSON data extraction fails
    }
}
?>
