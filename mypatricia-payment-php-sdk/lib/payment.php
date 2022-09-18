<?php 

namespace Patricia;

// Prevent direct access to this class
defined('BASEPATH') OR exit('No direct script access allowed');

require __DIR__.'/../vendor/autoload.php';

use Unirest\Request;
use Unirest\Request\Body;



class Payment {
    protected $publicKey;
    protected $secretKey;
    protected $amount;
    protected $paymentOptions = Null;
    protected $customDescription;
    protected $customLogo;
    protected $customTitle;
    protected $country;
    protected $currency;
    protected $customerEmail;
    protected $customerFirstname;
    protected $customerLastname;
    protected $customerPhone;
    protected $txref;
    protected $integrityHash;
    protected $payButtonText = 'Make Payment';
    protected $redirectUrl;
    protected $meta = array();
    protected $transactionPrefix;
    public $logger;
    protected $handler;
    protected $liveUrl = 'https://checkout.business.mypatricia.co';
    protected $baseUrl;
    protected $transactionData;
    protected $overrideTransactionReference;
    protected $requeryCount = 0;
    protected $disableBarter;
	protected $context = array( 'source' => 'payment_for_woocommerce' );
    protected $business_reference;
    

    function __construct($business_reference, $publicKey, $secretKey, $prefix, $overrideRefWithPrefix,$disable_logging_option){
        $this->publicKey = $publicKey;
        $this->secretKey = $secretKey;
        // $this->env = $env;
        $this->transactionPrefix = $overrideRefWithPrefix ? $prefix : $prefix.'_';
        $this->overrideTransactionReference = $overrideRefWithPrefix;

        // create a log channel
		$this->logger = wc_get_logger();

        
        $this->createReferenceNumber();

        // set baseurl
        $this->baseUrl = $this->liveUrl;
        
        // logs
		$this->logger->notice('Payment Class Initializes....', $this->context);
        
        return $this;
    }
    
    /**
     * Generates a checksum value for the information to be sent to the payment gateway
     * @return object
     * */
    function createCheckSum(){
		$this->logger->notice('Generating Checksum....', $this->context);
            //set the currency to route to their countries
            switch ($this->currency) {
                case 'KES':
                    $this->country = 'KES';
                    break;
                case 'GHC':
                    $this->country = 'GHC';
                    break;
    
                default:
                    $this->country = 'NGN';
                    break;
            }

        $options = array( 
            "public_key" => $this->publicKey, 
            "amount" => (float) $this->amount, 
            "email" => $this->customerEmail, 
            "first_name" => $this->customerFirstname, 
            "last_name" => $this->customerLastname, 
            "txref" => $this->txref, 
            "payment_method" => $this->paymentOptions, 
            "currency" => $this->currency
        );
		
		$options['disable_pwb'] = 0;

        // check if the user disabled barter
        if ($this->getDisableBarter() == 'yes'){
            $options['disable_pwb'] = true;
        }
        
        ksort($options);
        
        $this->transactionData = $options;
        
        $hashedPayload = '';
        
        foreach($options as $key => $value){
            $hashedPayload .= $value;
        }

        $completeHash = $hashedPayload.$this->secretKey;
        $hash = hash('sha256', $completeHash);
        
        $this->integrityHash = $hash;
        return $this;
    }
    
    /**
     * Generates a transaction reference number for the transactions
     * @return object
     * */
    function createReferenceNumber(){
		$this->logger->notice('Generating Reference Number....', $this->context);
        if($this->overrideTransactionReference){
            $this->txref = $this->transactionPrefix;
        }else{
            $this->txref = uniqid($this->transactionPrefix);
        }
		$this->logger->notice('Generated Reference Number....'.$this->txref, $this->context);
        return $this;
    }
    
    /**
     * gets the current transaction reference number for the transaction
     * @return string
     * */
    function getReferenceNumber(){
        return $this->txref;
    }

    /**
     * Disable barter from the form
     * @param string yes/no
     * @return object
     */
    function setDisableBarter($barter){
        $this->disableBarter = $barter;
        return $this;
    }

    /**
     * gets the disable barter decision
     * @return string
     */
    function getDisableBarter(){
        return $this->disableBarter;
    }
    
    /**
     * Sets the transaction amount
     * @param integer $amount Transaction amount
     * @return object
     * */
    function setAmount($amount){
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * gets the transaction amount
     * @return string
     * */
    function getAmount(){
        return $this;
    }
    
    /**
     * Sets the allowed payment methods
     * @param string $paymentOptions The allowed payment methods. Can be card, account or both 
     * @return object
     * */
    function setPaymentOptions($paymentOptions){
        $this->paymentOptions = $paymentOptions;
        return $this;
    }
    
    /**
     * gets the allowed payment methods
     * @return string
     * */
    function getPaymentOptions(){
        return $this;
    }
    
    /**
     * Sets the transaction description
     * @param string $customDescription The description of the transaction
     * @return object
     * */
    function setDescription($customDescription){
        $this->customDescription = $customDescription;
        return $this;
    }
    
    /**
     * gets the transaction description
     * @return string
     * */
    function getDescription(){
        return $this->customDescription;
    }
    
    /**
     * Sets the payment page logo
     * @param string $customLogo Your Logo
     * @return object
     * */
    function setLogo($customLogo){
        $this->customLogo = $customLogo;
        return $this;
    }
    
    /**
     * gets the payment page logo
     * @return string
     * */
    function getLogo(){
        return $this->customLogo;
    }
    
    /**
     * Sets the payment page title
     * @param string $customTitle A title for the payment. It can be the product name, your business name or anything short and descriptive 
     * @return object
     * */
    function setTitle($customTitle){
        $this->customTitle = $customTitle;
        return $this;
    }
    
    /**
     * gets the payment page title
     * @return string
     * */
    function getTitle(){
        return $this->customTitle;
    }
    
    /**
     * Sets transaction country
     * @param string $country The transaction country. Can be NG, US, KE, GH and ZA
     * @return object
     * */
    function setCountry($country){
        $this->country = $country;
        return $this;
    }
    
    /**
     * gets the transaction country
     * @return string
     * */
    function getCountry(){
        return $this->country;
    }
    
    /**
     * Sets the transaction currency
     * @param string $currency The transaction currency. Can be NGN, GHS, KES, ZAR, USD, EUR and GBP
     * @return object
     * */
    function setCurrency($currency){
        $this->currency = $currency;
        return $this;
    }
    
    /**
     * gets the transaction currency
     * @return string
     * */
    function getCurrency(){
        return $this->currency;
    }
    
    /**
     * Sets the customer email
     * @param string $customerEmail This is the paying customer's email
     * @return object
     * */
    function setEmail($customerEmail){
        $this->customerEmail = $customerEmail;
        return $this;
    }
    
    /**
     * gets the customer email
     * @return string
     * */
    function getEmail(){
        return $this->customerEmail;
    }
    
    /**
     * Sets the customer firstname
     * @param string $customerFirstname This is the paying customer's firstname
     * @return object
     * */
    function setFirstname($customerFirstname){
        $this->customerFirstname = $customerFirstname;
        return $this;
    }
    
    /**
     * gets the customer firstname
     * @return string
     * */
    function getFirstname(){
        return $this->customerFirstname;
    }
    
    /**
     * Sets the customer lastname
     * @param string $customerLastname This is the paying customer's lastname
     * @return object
     * */
    function setLastname($customerLastname){
        $this->customerLastname = $customerLastname;
        return $this;
    }
    
    /**
     * gets the customer lastname
     * @return string
     * */
    function getLastname(){
        return $this->customerLastname;
    }
    
    /**
     * Sets the customer phonenumber
     * @param string $customerPhone This is the paying customer's phonenumber
     * @return object
     * */
    function setPhoneNumber($customerPhone){
        $this->customerPhone = $customerPhone;
        return $this;
    }
    
    /**
     * gets the customer phonenumber
     * @return string
     * */
    function getPhoneNumber(){
        return $this->customerPhone;
    }
    
    /**
     * Sets the payment page button text
     * @param string $payButtonText This is the text that should appear on the payment button on the Payment payment gateway.
     * @return object
     * */
    function setPayButtonText($payButtonText){
        $this->payButtonText = $payButtonText;
        return $this;
    }
    
    /**
     * gets payment page button text
     * @return string
     * */
    function getPayButtonText(){
        return $this->payButtonText;
    }
    
    /**
     * Sets the transaction redirect url
     * @param string $redirectUrl This is where the Payment payment gateway will redirect to after completing a payment
     * @return object
     * */
    function setRedirectUrl($redirectUrl){
        $this->redirectUrl = $redirectUrl;
        return $this;
    }
    
    /**
     * gets the transaction redirect url
     * @return string
     * */
    function getRedirectUrl(){
        return $this->redirectUrl;
    }
    
    /**
     * Sets the transaction meta data. Can be called multiple time to set multiple meta data
     * @param array $meta This are the other information you will like to store with the transaction. It is a key => value array. eg. PNR for airlines, product colour or attributes. Example. array('name' => 'femi')
     * @return object
     * */
    function setMetaData($meta){
        array_push($this->meta, $meta);
        return $this;
    }
    
    /**
     * gets the transaction meta data
     * @return string
     * */
    function getMetaData(){
        return $this->meta;
    }
    
    /**
     * Sets the event hooks for all available triggers
     * @param object $handler This is a class that implements the Event Handler Interface
     * @return object
     * */
    function eventHandler($handler){
        $this->handler = $handler;
        return $this;
    }
    
    /**
     * Requerys a previous transaction from the Payment payment gateway
     * @param string $referenceNumber This should be the reference number of the transaction you want to requery
     * @return object
     * */
    function requeryTransaction($referenceNumber){
        $this->txref = $referenceNumber;
        $this->requeryCount++;
		$this->logger->notice('Requerying Transaction....'.$this->txref, $this->context);
        if(isset($this->handler)){
            $this->handler->onRequery($this->txref);
        }

        $data = array(
            'txref' => $this->txref,
            'SECKEY' => $this->secretKey,
        );

        // make request to endpoint using unirest.
        $headers = array('Content-Type' => 'application/json');
        $body = Body::json($data);
        $url = $this->baseUrl.'/v1/payment/verify/'. $this->$business_reference;

        // try and catch error if any
        try {

            // Make `POST` request and handle response with unirest
            $response = Request::post($url, $headers, $body);

        } catch (Exception $e) {

            //log error
            $err = $e->getMessage();
            file_put_contents('Payment_Err_'.time(), $err);
            echo $err;
        }
  
        //check the status is success
        if ($response->body && $response->body->status === "success") {
            if($response->body && $response->body->data && $response->body->data->status === "successful"){
				$this->logger->notice('Requeryed a successful transaction....'.json_encode($response->body->data), $this->context);
                // Handle successful
                if(isset($this->handler)){
                    $this->handler->onSuccessful($response->body->data);
                }
            }elseif($response->body && $response->body->data && $response->body->data->status === "failed"){
                // Handle Failure
				$this->logger->warning('Requeryed a failed transaction....'.json_encode($response->body->data), $this->context);
                if(isset($this->handler)){
                    $this->handler->onFailure($response->body->data);
                }
            }else{
                // Handled an undecisive transaction. Probably timed out.
				$this->logger->warning('Requeryed an undecisive transaction....'.json_encode($response->body->data), $this->context);
                // I will requery again here. Just incase we have some devs that cannot setup a queue for requery. I don't like this.
                if($this->requeryCount > 4){
                    // Now you have to setup a queue by force. We couldn't get a status in 5 requeries.
                    if(isset($this->handler)){
                        $this->handler->onTimeout($this->txref, $response->body);
                    }
                }else{
					$this->logger->notice('delaying next requery for 3 seconds', $this->context);
                    sleep(3);
					$this->logger->notice('Now retrying requery...', $this->context);
                    $this->requeryTransaction($this->txref);
                }
            }
        }else{
			$this->logger->warning('Requery call returned error for transaction reference.....'.json_encode($response->body).'Transaction Reference: '. $this->txref, $this->context);
            // Handle Requery Error
            if(isset($this->handler)){
                $this->handler->onRequeryError($response->body);
            }
        }
        return $this;
    }
    
    /**
     * Generates the final json to be used in configuring the payment call to the payment payment gateway
     * @return string
     * */
    function initialize(){
        $this->createCheckSum();
        $this->transactionData = array_merge($this->transactionData, array('integrity_hash' => $this->integrityHash), array('meta' => $this->meta));
        
        if(isset($this->handler)){
            $this->handler->onInit($this->transactionData);
        }
        
        $json = json_encode($this->transactionData);
        echo '<html>';
        echo '<body>';
        echo '<center>Proccessing...<br /><img src="'.plugins_url('mypatricia-payment-php-sdk/ajax-loader.gif', MPT_WC_PLUGIN_FILE).'" /></center>';
        echo '<script type="text/javascript" src="'.$this->baseUrl.'/build/v1/index.min.js"></script>';
        echo '<script>';
        echo 'var isCompleted = false;';
        echo 'var paymentForm = document.getElementById("paymentForm");';
	    echo 'document.addEventListener("DOMContentLoaded", function(event) {';
        echo 'initialize({
                public_key: "'. $this->transactionData['public_key'] .'",
                amount: '. $this->transactionData['amount'] .',
                payment_method: "'. $this->transactionData['payment_options'] .'",
                currency: "'. $this->transactionData['currency'] .'",
                email: "'. $this->transactionData['customer_email'] .'",
                first_name: "'. $this->transactionData['customer_firstname'] .'",
                last_name: "'. $this->transactionData['customer_lastname'] .'",
                customer_phone: "'. $this->transactionData['customer_phone'] .'",
                txref: "' . $this->transactionData['txref'] . '",
                onclose: function() {
                    if(window.isCompleted){
                        window.location = "'. $this->transactionData['redirect_url'].'&txref='. $this->transactionData['txref'] .'"
                    }
                    window.location = "'. $_SERVER['HTTP_REFERER'] .'"
                },
                onSuccess: function() {
                    var txref = response.data.txRef;
                    console.log("This is the response returned after a charge", response);
                    if (
                        response.tx.chargeResponseCode == "00" ||
                        response.tx.chargeResponseCode == "0"
                    ) {
                        window.isCompleted = true;
                        // redirect to a success page
                        window.location = "'. $this->transactionData['redirect_url'].'&txref='. $this->transactionData['txref'] .'"
                    } else {
                        // redirect to a failure page.
                        window.location = "'. $_SERVER['HTTP_REFERER'] .'"
                    }
  
                    x.close(); // use this to close the modal immediately after payment.
                },
                onError: function() {
                    if(window.isCompleted){
                        window.location = "'. $this->transactionData['redirect_url'].'&txref='. $this->transactionData['txref'] .'"
                    }
                    window.location = "'. $_SERVER['HTTP_REFERER'] .'"
                }
        });';
        echo '});';
        echo '</script>';
        echo '</body>';
        echo '</html>';

        return $json;
    }
    
    /**
     * Handle canceled payments with this method
     * @param string $referenceNumber This should be the reference number of the transaction that was canceled
     * @return object
     * */
    function paymentCanceled($referenceNumber){
        $this->txref = $referenceNumber;
		$this->logger->notice('Payment was canceled by user..'.$this->txref, $this->context);
        if(isset($this->handler)){
            $this->handler->onCancel($this->txref);
        }
        return $this;
    }
    
}

// silencio es dorado

?>