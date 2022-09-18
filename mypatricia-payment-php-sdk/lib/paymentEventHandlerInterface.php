<?php


namespace Patricia\Payment;

// Prevent direct access to this class
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Implement this interface to set triggers for transaction event on Payment.
 * An event can be triggered when a Payment initializes a transaction, When a 
 * transaction is successful, failed, requeried and when a requery fails.
 * @author Olufemi Olanipekun <iolufemi@ymail.com>
 * @version 1.0
 **/

interface EventHandlerInterface
{
    /**
     * This is called when the a transaction is initialized
     * @param object $initializationData This is the initial transaction data as passed
     * */
    function onInit($initializationData);

    /**
     * This is called only when a transaction is successful
     * @param object $transactionData This is the transaction data as returned from the Payment payment gateway
     * */
    function onSuccessful($transactionData);

    /**
     * This is called only when a transaction failed
     * @param object $transactionData This is the transaction data as returned from the Payment payment gateway
     * */
    function onFailure($transactionData);

    /**
     * This is called when a transaction is requeryed from the payment gateway
     * @param string $transactionReference This is the transaction reference as returned from the Payment payment gateway
     * */
    function onRequery($transactionReference);

    /**
     * This is called a transaction requery returns with an error
     * @param string $requeryResponse This is the error response gotten from the Payment payment gateway requery call
     * */
    function onRequeryError($requeryResponse);

    /**
     * This is called when a transaction is canceled by the user
     * @param string $transactionReference This is the transaction reference as returned from the Payment payment gateway
     * */
    function onCancel($transactionReference);

    /**
     * This is called when a transaction doesn't return with a success or a failure response.
     * @param string $transactionReference This is the transaction reference as returned from the Payment payment gateway
     * @data object $data This is the data returned from the requery call.
     * */
    function onTimeout($transactionReference, $data);
}
?>