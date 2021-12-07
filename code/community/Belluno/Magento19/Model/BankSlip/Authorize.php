<?php

class Belluno_Magento19_Model_BankSlip_Authorize
{
  /**
   * Function authorize of method payment
   * @param Varien_Object $payment
   * @param $amount
   * @param $info
   */
  public function authorize(Varien_Object $payment, $amount, $info)
  {
    $createRequest = $this->getCreateRequest();
    $data = json_decode($payment->getAdditionalInformation("data"), true);
    $request = $createRequest->createRequest($data, $info);

    $connector = $this->getConnector();
    
    $response = $connector->doRequest($request, "POST", "/bankslip");
    $response = json_decode($response, true);

    $bankslip = [
      'id' => $response['bankslip']['id'],
      'due' => $response['bankslip']['due'],
      'quote_id' => $response['bankslip']['document_code'],
      'url' => $response['bankslip']['url'],
      'digitable_line' => $response['bankslip']['digitable_line']
    ];
    $info->setAdditionalInformation("status", $response['bankslip']['status']);
    $info->setAdditionalInformation("bankslip", json_encode($bankslip));
  }

  /**Function to return class connector for requests */
  public function getConnector()
  {
    return new Belluno_Magento19_Service_Connector();
  }

  /**Function to return class create request */
  public function getCreateRequest()
  {
    return new Belluno_Magento19_Model_BankSlip_CreateRequest();
  }
}
