<?php
    session_start();
    include_once('../tests/integration/config.php');
    
    $api_url =  $config['old_viridian_api_url'] ;
    
    $ch  = curl_init();

    function curl_request($ch, $url, $post_data, $token) {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);                
        curl_setopt($ch, CURLOPT_USERAGENT, ' Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if ($post_data != null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        } else {
            curl_setopt($ch, CURLOPT_POST, 0);
        }
        if ($token != null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer ".$token,
                'Content-Type: application/json'
            ));
        }
        // Get content
        $page = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($page, 0, $header_size);
        $body = substr($page, $header_size);

        return $body;
    }

    $orders = curl_request($ch, $api_url.'/order'.'?customerID='.$_REQUEST['customerID'], null, $_SESSION['viridian_token']);
    echo json_encode($orders);
?>