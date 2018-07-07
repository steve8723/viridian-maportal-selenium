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
        $this->timeouts()->implicitWait(2000);

            $path = 'results.json';
            $fp = fopen($path, 'r');
            $fileData = fread($fp, filesize($path));
            $json = json_decode($fileData, true);
            fclose($fp);  
  
    try {

            $this->url("https://hhsvgapps01.hhs.state.ma.us");
            $isLogged = $this->byLinkText("Logout");
            if ($isLogged) throw new Exception;

        } catch (Exception $e) {

            $username = 'SCARLSON39';
            $password = 'LicorneTech1!';

            $this->url("https://sso.hhs.state.ma.us/oam/server/obrareq.cgi?encquery%3DHbFmFH0s53KuRvxkoCE%2F9OAqyADAQz7Ih8%2BnXfhsc0GOuWKKEdwIScTnF%2FfahjhjYVCRwn0MPE6ssHYWXkecWrhNa%2B%2BOhhD%2BTLWxR2ySV0gdGxfvLe%2FpRS6%2BfizTSn%2B6d8rtyb5rcknC%2BKX6PjJl%2F%2FoJzTu7%2FZ2aLjDZa0BjNR2toXDkmIu5sEeN4z16pglgZY4CnWP2AalQY1%2FezKijNB5Lw2VoV5Swr0zEHocq%2Fw5SIdbN8ohFWveq5UBFNKWPZ13J3S7JMt5WzzmGYmoII6vcJhtcxOOUVaVPBzr23xNpUaEJcaTPVeWV%2F%2Fvfbzu4DbYwO54MzMDrOi3plqhccxDfcE6vJ%2FP2K9MWkVE45e8%3D%20agentid%3Dwebgate1%20ver%3D1%20crmethod%3D2&ECID-Context=1.16736091039091636%3BkXhglfC");

            $usernameInput = $this->byName("username");
            $usernameInput->clear();
            $this->keys($username);

            $usernameInput = $this->byName("password");
            $usernameInput->clear();
            $this->keys($password);

            // $this->url("https://sso.hhs.state.ma.us/oam/server/auth_cred_submit");
            $form = current($this->elements($this->using('css selector')->value('form#loginData')));
            $form->submit();

            // -----Maximum allowed session reached!-----
            $MAXIMUM_SEESION_CONTEXT = 'The user has already reached the maximum allowed number of sessions. Please close one of the existing sessions before trying to login again.';
            $pMax = current($this->elements($this->using('css selector')->value('p#errorTxtAlignment')));

            if ($pMax)
            if ($pMax->text() == $MAXIMUM_SEESION_CONTEXT) {
                $this->assertEquals($MAXIMUM_SEESION_CONTEXT, $pMax->text());
                print '-----Maximum allowed session reached!-----';
            }
            $this->url("https://hhsvgapps01.hhs.state.ma.us/mmj-rmd/dispense/dispensing?id=11038157&lastName=Veeder");
            $gramsRemaining = current($this->elements($this->using('css selector')->value('span#grams-remaining')));
            if ($gramsRemaining) {
                print $gramsRemaining->text();
            }


            $mock = '{"customer":"1876","customerdetails":{"id":"1876","name":"Al Bundy"},"hasbeenpaid":"true","id":"70459","cashpaid":"15","creditpaid":"0","totaltax":"0","regulartax":"0","ordertotal":"15","discountotal":"0","cannatax":"0","note":"","DateCreated":"2018-05-09T11:15:55.18","Payment_Date":"2018-05-09T11:15:57.51","Products":"","transaction":"","email":"","CaregiverID":"","debitpaid":"0","LineItems":[{"id":"110693","OrderID":"0","ProductID":"22256","CannabisProductID":"0","TaxRate":"0","Quantity":"1","AmountPaid":"15","LineTax":"0","LineTotal":"0","CannaTaxRate":"0","CannaLineTax":"0","DiscountAmount":"0","Notes":"","Name":"BULLFIGHTER-PACKAGED FLOWER- 1G","price":"15","Products":"","thc":"0","cbd":"0","cbn":"0","wastate_id":"","packageweight":"0","type":"0","Batches":"PG-FL-1G-H-3:PG-FL-1G-H-3-PK-1:1","batches":[{"batchnumber":"PG-FL-1G-H-3-PK-1","quantity":"123","soldquantity":"37","leftquantity":"86","ismedical":"true"},{"batchnumber":"PG-FL-1G-H-3-PK-2","quantity":"34","soldquantity":"0","leftquantity":"34","ismedical":"true"}],"BatchOrder":[{"batchnumber":"PG-FL-1G-H-3-PK-1","orderquantity":"1","ismedical":"true"}],"category":"1"}],"caregiverdetails":""}';
            $mock_json = json_decode($mock, true);
            $count = 0;
            $barch_info = array();
            foreach ($json["LineItems"] as $line_item) {
                $batch_orders = $line_item["BatchOrder"];
                $count += count($batch_orders);
                foreach ($batch_orders as $batch_order) {
                    array_push($barch_info, $batch_order);
                }
            }
            var_dump($barch_info);
            foreach ($barch_info as $index=>$single_info) {
                $this->select($this->byId('productType.id'.($index+1)))->selectOptionByValue(2);

                $batchInput = $this->byId("batchNumber".($index+1));
                $batchInput->clear();
                $this->keys($single_info['batchnumber']);

                if ($index < $count - 1) {
                    $moreButton = $this->byClassName("urlAsButton");
                    $moreButton->click();
                }
            }
            
        }

        // -------------------------------------------
       

       

    } // testLoginSuccessful()

} //LoginTest class
