<?php

class Belluno_Magento19_Model_CreditCard_Refund
{
  /**
   * Function refund (credit memo online) of method payment
   * @param Varien_Object $payment
   */
  public function refund(Varien_Object $payment)
  {
    $method = $payment->getMethod();
    $transactionId = $payment->getAdditionalInformation('transaction_id');
    $value = $payment->getAdditionalInformation('value');

    if ($method == 'belluno_creditcardpayment') {
      $request = [
        'amount' => $value,
        'reason' => '2'
      ];

      $request = json_encode($request);
      $connector = $this->getConnector();
      $connector->doRequest($request, "POST", "/transaction/$transactionId/refund");
    }
  }

  /**Function to return class connector for requests */
  public function getConnector()
  {
    return new Belluno_Magento19_Service_Connector();
  }
}
