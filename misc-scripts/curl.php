<?php
function do_http_request($hostname, $type='get', $post_data=array()) {
    // Get cURL resource
    $curl = curl_init();
    // Set some options
    if($type == 'post') {
        $settings = array(
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_URL => $hostname,
                        CURLOPT_POST => 1,
                        CURLOPT_POSTFIELDS => $post_data
        );
    }
    else {
        $settings = array(
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_URL => $hostname
        );
    }

    curl_setopt_array($curl, $settings);
    // Send the request & save response to $resp
    $resp['body'] = curl_exec($curl);
    $resp['responseCode'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    // Close request to clear up some resources
    curl_close($curl);

    return $resp;
}
?>