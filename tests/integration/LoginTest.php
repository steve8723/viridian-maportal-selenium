<?php
/**
 * Integration tests for login functionality
 */
class LoginTest extends PHPUnit_Extensions_Selenium2TestCase
{

    /**
     * Defines which browsers are going to be tested
     * @var array
     */
    public static $browsers = array(
        array(
            "name" => "Chrome",
            "browserName" => "chrome",
        ),
        // array(
        //     "name" => "Firefox",
        //     "browserName" => "firefox",
        // ),
        // array(
        //     "name" => "Internet Exprlorer",
        //     "browserName" => "iexplore",
        // ),
    );

    /**
     * setup will be run for all our tests
     */
    protected function setUp()  {
        $this->setBrowserUrl("http://geekpad.ca/blog/demos/phpunit-selenium-demo");
        $this->setHost("127.0.0.1");

        $myClassReflection = new \ReflectionClass( get_class( $this->prepareSession() ) );
        $secret            = $myClassReflection->getProperty( 'stopped' );
        $secret->setAccessible( true );
        $secret->setValue( $this->prepareSession(), true );
    } // setUp()

    /**
     * Test that logins work
     *
     */
    public function testLoginSuccessful()  {
        include_once('config.php');
        $this->timeouts()->implicitWait(2000);

        $data_string = "username=".$_SESSION['vg_username']."&password=".$_SESSION['vg_pass'];       
        $api_url =  $config['old_viridian_api_url'];
        
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
            $page = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($page, 0, $header_size);
            $body = substr($page, $header_size);
    
            return $body;
        }
    
        $customers = curl_request($ch, $api_url.'/customers', null, $_SESSION['viridian_token']);
    
        $customersInfo =json_decode($customers, true);
        $totalOrders = [];
        if ($customersInfo["data"]) {
            foreach ($customersInfo["data"] as $eachCustomer) {
                if ($eachCustomer["ispatient"]) {
                    $orders = curl_request($ch, $api_url.'/order'.'?customerID='.$eachCustomer["id"], null, $_SESSION['viridian_token']);
                    $ordersInfo =json_decode($orders, true);
                    foreach ($ordersInfo["data"] as $eachOrder) {
                        $orderToSave = $eachOrder;
                        $orderToSave["medicalcardnumber"] =  $eachCustomer["medicalcardnumber"];
                        $orderToSave["lastname"] =  $eachCustomer["lastname"];
                        array_push($totalOrders, $orderToSave);
                    }
                }
            } 
        }

        // DB operation to insert new orders.
        $servername = $config['servername'] ;
        $username = $config['db_username'];
        $password = $config['db_password'];
        $dbname = $config['dbname'];

        // Create DB connection
        $conn = mysqli_connect($servername, $username, $password, $dbname);

        // Check DB connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
        foreach ($totalOrders as $single_record) {
            $insert_sql = "INSERT IGNORE INTO order_status (order_id, order_status) VALUES (".$single_record['id'].", 'waiting')";
            if ($conn->query($insert_sql) === TRUE) {
                print "New record created successfully";
            } else {
                print "Error: " . $insert_sql . "<br>" . $conn->error;
            }
        }

        $sql = "SELECT * FROM order_status WHERE order_status!='success'";
        $result = $conn->query($sql);
        $array_on_db = [];
        $array_to_update = [];

        if ($result) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                array_push($array_on_db, $row["order_id"]);
            }
        } else {
            print "No orders to be updated";
        }

        foreach ($totalOrders as $single_record) {
            if (!array_search($single_record['id'], $array_on_db)) array_push($array_to_update, $single_record);
        }
  
        try {
            // Check MA portal's session is still alive.
            $this->url($config['ma_portal_hhs_url']);
            $isLogged = $this->byLinkText("Logout");
            if ($isLogged) throw new Exception;

        } catch (Exception $e) {

            $username = $_SESSION['username'];
            $password = $_SESSION['pass'];

            // Login process
            $this->url($config['ma_portal_login_page_url']);

            $usernameInput = $this->byName("username");
            $usernameInput->clear();
            $this->keys($username);

            $usernameInput = $this->byName("password");
            $usernameInput->clear();
            $this->keys($password);

            $form = current($this->elements($this->using('css selector')->value('form#loginData')));
            $form->submit();

            // -----Invalid error message !-----
            $pErrorMessage = current($this->elements($this->using('css selector')->value('p#errorTxtAlignment')));

            if ($pErrorMessage) {
                if ($pErrorMessage->text() == $MAXIMUM_SEESION_CONTEXT) {
                    $this->assertEquals($MAXIMUM_SEESION_CONTEXT, $pErrorMessage->text());
                    print $MAXIMUM_SEESION_CONTEXT;
                } else if ($pErrorMessage->text() == $INVALID_CREDENTIALS) {
                    $this->assertEquals($INVALID_CREDENTIALS, $pErrorMessage->text());
                    print $INVALID_CREDENTIALS;
                }
                header('Location: login/vg_login.php?msg='.$pErrorMessage->text());
            } else {
                $_SESSION['vg_loggedin'] = true;
            }
            

            if (count($array_to_update) > 0) {
                foreach ($array_to_update as $eachOrder) {
                    $this->url($DISPENSE_PAGE_URL."id=".$eachOrder["medicalcardnumber"]."&lastname=".$eachOrder["lastname"]);
                    $gramsRemaining = current($this->elements($this->using('css selector')->value('span#grams-remaining')));
                    if ($gramsRemaining) {
                        print "Remaining weight: ".$gramsRemaining->text();
                    }
    
                    // Get batch info.
                    $count = 0;
                    $barch_info = array();
                    if ($eachOrder["LineItems"]) {
                        foreach ($eachOrder["LineItems"] as $line_item) {
                            if ($line_item["BatchOrder"]) {
                                $batch_orders = $line_item["BatchOrder"];
                                $count += count($batch_orders);
                                foreach ($batch_orders as $batch_order) {
                                    $batch_order["type"] = $line_item["type"];
                                    $batch_order["packageweight"] = $line_item["packageweight"];
                                    array_push($barch_info, $batch_order);
                                }
                            }
                        }
                    }

                    if (count($barch_info) > 0) {
                        foreach ($barch_info as $index=>$single_info) {

                            // Input product type.
                            $typeString = $single_info['type'];
                            $typeIndex = 2;
                            if ((int)$typeString > 14 && (int)$typeString < 18 ) $typeIndex = 5;
                            if ((int)$typeString == 18 && (int)$typeString == 19 && (int)$typeString == 24 ) $typeIndex = 6;
                            if ((int)$typeString == 22 && (int)$typeString == 23 ) $typeIndex = 3;
                            if ((int)$typeString == 20 && (int)$typeString == 21 && (int)$typeString == 25 ) $typeIndex = 4;
        
                            $this->select($this->byId('productType.id'.($index+1)))->selectOptionByValue($typeIndex);
                            
                            // Input DWE value.
                            $weightInput = $this->byId("grams".($index+1));
                            $weightInput->clear();
                            $this->keys($single_info['packageweight']);
        
                             // Input Batch Number.
                            $batchInput = $this->byId("batchNumber".($index+1));
                            $batchInput->clear();
                            $this->keys($single_info['batchnumber']);
        
                             // Show next input group.                            
                            if ($index < $count - 1) {
                                $moreButton = $this->byClassName("urlAsButton");
                                $moreButton->click();
                            }
                        }
                    }
                    
                    // Compare remaining weight and total weight.                            
                    $totalGrams = $this->byId("totalGrams");
                    $gramsRemaining = $this->byId("grams-remaining");
                    $isOverWeight = false;
                    if ((float)$totalGrams->text() > (float)$gramsRemaining->text() ) $isOverWeight = true;
                    
                    //Submit the dispense form and update order status in DB.
                    if (!$isOverWeight) {
                        $proceedButton = $this->byClassName("update");
                        $update_sql = "INSERT INTO order_status (order_id, order_status) VALUES ('".$eachOrder['id']."', 'success') ON DUPLICATE KEY UPDATE order_status='success'";
                        // $proceedButton->click();
                    } else {
                        $update_sql = "INSERT INTO order_status (order_id, order_status) VALUES ('".$eachOrder['id']."', 'failed') ON DUPLICATE KEY UPDATE order_status='failed'";
                        print 'Overweight!';
                    }
    
                    if ($conn->query($update_sql) === TRUE) {
                        print "The record updated successfully";
                    } else {
                        print "Error updating record: " . $conn->error;
                    }
                }
            }
        }

    } // testLoginSuccessful()

} //LoginTest class
