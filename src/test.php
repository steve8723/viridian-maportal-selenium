<?php

// $path = 'results.json';
//             $fp = fopen($path, 'r');
//             $fileData = fread($fp, filesize($path));
//             $json = json_decode($fileData, true);
//             fclose($fp);  
            
//             $count = 0;
//             $barch_info = array();
//             foreach ($json["LineItems"] as $line_item) {
//                 $batch_orders = $line_item["BatchOrder"];
//                 $count += count($batch_orders);
//                 foreach ($batch_orders as $batch_order) {
//                     $batch_order["type"] = $line_item["type"];
//                     $batch_order["packageweight"] = $line_item["packageweight"];
//                     var_dump($batch_order);
//                     array_push($barch_info, $batch_order);
//                 }
//             }


// $data_string = "username=michaellaw&password=Mayflower1";
// $old_url = 'https://mayflowersandbox.navigatorpos.com/api/v3';
// $new_url = 'https://nbs-dev.azurewebsites.net/api/v3';

// $api_url =  $old_url;

// $ch  = curl_init();

// function curl_request($ch, $url, $post_data, $token) {
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);                
//     curl_setopt($ch, CURLOPT_USERAGENT, ' Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36');
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_HEADER, 1);
//     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//     if ($post_data != null) {
//         curl_setopt($ch, CURLOPT_POST, 1);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//     } else {
//         curl_setopt($ch, CURLOPT_POST, 0);
//     }
//     if ($token != null) {
//         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//             "Authorization: Bearer ".$token,
//             'Content-Type: application/json'
//         ));
//     }
//     // Get content
//     $page = curl_exec($ch);
//     $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
//     $header = substr($page, 0, $header_size);
//     $body = substr($page, $header_size);

//     return $body;
// }

// $auth = curl_request($ch, $api_url.'/token', $data_string, null);
// $auth_obj = json_decode($auth);
// $token = $auth_obj->token;
// $orders = curl_request($ch, $api_url.'/order'.'?customerID=1890', null, $token);
// $order_arr = json_decode($orders, true);



// $order_info = $order_arr['data'];

// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "orders";

// // Create connection
// $conn = mysqli_connect($servername, $username, $password, $dbname);

// // Check connection
// if (!$conn) {
//     die("Connection failed: " . mysqli_connect_error());
// }
// echo "Connected successfully";

// $sql = "SELECT * FROM order_status";
// $result = $conn->query($sql);
// $array_on_db = [];
// $array_to_update = [];

// if ($result->num_rows > 0) {
//     // output data of each row
//     while($row = $result->fetch_assoc()) {
//         array_push($array_on_db, $row["order_id"]);
//     }
// } else {
//     echo "0 results";
// }

// foreach ($order_info as $single_record) {
//     if (!array_search($single_record['id'], $array_on_db)) array_push($array_to_update, $single_record['id']);
// }

// var_dump($array_to_update);


// $insert_sql = "INSERT IGNORE INTO order_status (order_id, order_status) VALUES (".$single_record['id'].", 'failed')";
// echo $insert_sql;
// if ($conn->query($insert_sql) === TRUE) {
//     echo "New record created successfully";
// } else {
//     echo "Error: " . $insert_sql . "<br>" . $conn->error;
// }

// foreach ($order_info as $single_record) {
//     $insert_sql = "INSERT IGNORE INTO order_status (order_id, order_status) VALUES (".$single_record['id'].", 'failed')";
//     echo $insert_sql;
//     if ($conn->query($insert_sql) === TRUE) {
//         echo "New record created successfully";
//     } else {
//         echo "Error: " . $insert_sql . "<br>" . $conn->error;
//     }
// }

// $conn->close();

$data_string = "username=smartdev2017&password=smartdev2017";
        $old_url = 'https://mayflowersandbox.navigatorpos.com/api/v3';
        $new_url = 'https://nbs-dev.azurewebsites.net/api/v3';
        
        $api_url =  $old_url;
        
        $ch  = curl_init();
    
        function curl_request($ch, $url, $post_data, $token) {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
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
    
        $auth = curl_request($ch, $api_url.'/token', $data_string, null);
        $auth_obj = json_decode($auth);
        $token = $auth_obj->token;
        $customers = curl_request($ch, $api_url.'/customers', null, $token);
    
        $customersInfo =json_decode($customers, true);
        $totalOrders = [];
        foreach ($customersInfo["data"] as $eachCustomer) {
            if ($eachCustomer["ispatient"]) {
                $orders = curl_request($ch, $api_url.'/order'.'?customerID='.$eachCustomer["id"], null, $token);
                $ordersInfo =json_decode($orders, true);
                $totalOrders = array_merge($totalOrders, $ordersInfo["data"]);
            }
        } 

        var_dump($totalOrders);
?>