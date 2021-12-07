<?php

class Belluno_Magento19_Model_Creditcard_Capture
{

  /**
   * Function capture of method payment
   * @param Varien_Object $payment
   * @param $amount
   * @param $info
   */
  public function capture(Varien_Object $payment, $amount, $info)
  {
    $connector = $this->getConnector();
    $method = $payment->getMethod();
    $quote = $this->getQuote();

    if ($method == 'belluno_creditcardpayment') {
      $transactionId = $info->getAdditionalInformation("transaction_id");
      $status = $info->getAdditionalInformation("status");

      if ($status == "Client Manual Analysis") {
        $request = [
          'status' => 'approve',
          'reason' => 'Nothing to declare'
        ];
        $request = json_encode($request);
        $response = $connector->doRequest($request, "POST", "/transaction/$transactionId/result");
        $response = json_decode($response, true);
        $info->setAdditionalInformation("status", $response['transaction']['status']);

        $orderId = Mage::app()->getRequest()->getParam('order_id');

        $payment->setTransactionId($orderId);
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
      } else {
        $createRequest = $this->getCreateRequest();
        $data = json_decode($payment->getAdditionalInformation("data"), true);
        $request = $createRequest->createRequest($data, $info);

        $response = $connector->doRequest($request, "POST", "/transaction");
        $response = json_decode($response, true);
        $info->setAdditionalInformation("transaction_id", $response['transaction']['transaction_id']);
        $info->setAdditionalInformation("value", $response['transaction']['value']);
        $info->setAdditionalInformation("status", $response['transaction']['status']);
        $data = json_decode($info->getAdditionalInformation('view'), true);
        $data[] = "NSU do pagamento:";
        $data[] = $response['transaction']['nsu_payment'];
        $info->setAdditionalInformation("view", json_encode($data));

        $payment->setTransactionId($quote->getReservedOrderId());
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
      }
    }
  }

  /** 
   * Get checkout session 
   * @return Mage_Checkout_Model_Session
   */
  public function getCheckout()
  {
    return Mage::getSingleton('checkout/session');
  }

  /**
   * Get current quote
   * @return Mage_Sales_Model_Quote
   */
  public function getQuote()
  {
    return $this->getCheckout()->getQuote();
  }

  /**Function to return class connector for requests */
  public function getConnector()
  {
    return new Belluno_Magento19_Service_Connector();
  }

  /**Function to return class create request */
  public function getCreateRequest()
  {
    return new Belluno_Magento19_Model_CreditCard_CreateRequest();
  }
}
