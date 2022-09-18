<?php
// Prevent direct access to this class
define("BASEPATH", 1);
include('lib/payment.php');
include('lib/paymentEventHandlerInterface.php');

use Flutterwave\Payment;
use Flutterwave\Payment\EventHandlerInterface;

$URL = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
$getData = $_GET;
$postData = $_POST;
$publicKey = '****YOUR**PUBLIC**KEY****'; // Remember to change this to your live public keys when going live
$secretKey = '****YOUR**SECRET**KEY****'; // Remember to change this to your live secret keys when going live
$env = 'staging'; // Remember to change this to 'live' when you are going live
$prefix = 'MY_APP_NAME'; // Change this to the name of your business or app
$overrideRef = false;
$business_reference = '****YOUR**BUSINESS**REFERENCE****'


$payment = new Payment($business_reference, $publicKey, $secretKey, $prefix, $env, $overrideRef);


// This is where you set how you want to handle the transaction at different stages
class myEventHandler implements EventHandlerInterface{
    /**
     * This is called when the Payment class is initialized
     * */
    function onInit($initializationData){
        // Save the transaction to your DB.
        echo 'Payment started......'.json_encode($initializationData).'<br />'; //Remember to delete this line
    }
    
    /**
     * This is called only when a transaction is successful
     * */
    function onSuccessful($transactionData){
        echo 'Payment Successful!'.json_encode($transactionData).'<br />'; //Remember to delete this line
    }
    
    /**
     * This is called only when a transaction failed
     * */
    function onFailure($transactionData){
        echo 'Payment Failed!'.json_encode($transactionData).'<br />'; //Remember to delete this line
    }
    
    /**
     * This is called when a transaction is requeryed from the payment gateway
     * */
    function onRequery($transactionReference){
        // Do something, anything!
        echo 'Payment requeried......'.$transactionReference.'<br />'; //Remember to delete this line
    }
    
    /**
     * This is called a transaction requery returns with an error
     * */
    function onRequeryError($requeryResponse){
        // Do something, anything!
        echo 'An error occured while requeying the transaction...'.json_encode($requeryResponse).'<br />'; //Remember to delete this line
    }
    
    /**
     * This is called when a transaction is canceled by the user
     * */
    function onCancel($transactionReference){
        // Do something, anything!
        // Note: Somethings a payment can be successful, before a user clicks the cancel button so proceed with caution
        echo 'Payment canceled by user......'.$transactionReference.'<br />'; //Remember to delete this line
    }
    
    /**
     * This is called when a transaction doesn't return with a success or a failure response. This can be a timedout transaction on the Payment server or an abandoned transaction by the customer.
     * */
    function onTimeout($transactionReference, $data){
        // Get the transaction from your DB using the transaction reference (txref)
        // Queue it for requery. Preferably using a queue system. The requery should be about 15 minutes after.
        // Ask the customer to contact your support and you should escalate this issue to the flutterwave support team. Send this as an email and as a notification on the page. just incase the page timesout or disconnects
        echo 'Payment timeout......'.$transactionReference.' - '.json_encode($data).'<br />'; //Remember to delete this line
    }
}

if($postData['amount']){
    // Make payment
    $payment
    ->eventHandler(new myEventHandler)
    ->setAmount($postData['amount'])
    ->setPaymentMethod($postData['payment_method']) // value can be card, account or both
    ->setDescription($postData['description'])
    ->setLogo($postData['logo'])
    ->setTitle($postData['title'])
    ->setCountry($postData['country'])
    ->setCurrency($postData['currency'])
    ->setEmail($postData['email'])
    ->setFirstname($postData['firstname'])
    ->setLastname($postData['lastname'])
    ->setPhoneNumber($postData['phonenumber'])
    ->setPayButtonText($postData['pay_button_text'])
    ->setRedirectUrl($URL)
    // ->setMetaData(array('metaname' => 'SomeDataName', 'metavalue' => 'SomeValue')) // can be called multiple times. Uncomment this to add meta datas
    // ->setMetaData(array('metaname' => 'SomeOtherDataName', 'metavalue' => 'SomeOtherValue')) // can be called multiple times. Uncomment this to add meta datas
    ->initialize();
}else{
    if($getData['cancelled'] && $getData['txref']){
        // Handle canceled payments
        $payment
        ->eventHandler(new myEventHandler)
        ->requeryTransaction($getData['txref'])
        ->paymentCanceled($getData['txref']);
    }elseif($getData['txref']){
        // Handle completed payments
        $payment->logger->notice('Payment completed. Now requerying payment.');
        
        $payment
        ->eventHandler(new myEventHandler)
        ->requeryTransaction($getData['txref']);
    }else{
        $payment->logger->warn('Stop!!! Please pass the txref parameter!');
        echo 'Stop!!! Please pass the txref parameter!';
    }
}

?>