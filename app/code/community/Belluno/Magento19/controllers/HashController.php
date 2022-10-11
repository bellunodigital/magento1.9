<?php

class Belluno_Magento19_HashController extends Mage_Core_Controller_Front_Action
{
  const URI = '/transaction/card_hash_key';

  /**
   * Function to get html of terms and conditions
   */
  public function indexAction() {
    $responseback = $this->doRequest();
    echo json_encode($responseback);
  }

  public function doRequest(){
    $environmentState = Mage::getStoreConfig('payment/belluno_custompayment/environment');
    if ($environmentState == 'sandbox') {
        $environmentUrl = 'https://ws-sandbox.bellunopag.com.br';
      } else {
        $environmentUrl = 'https://api.belluno.digital';
    }
    $environment = $environmentUrl . self::URI;

    
    $token = Mage::getStoreConfig('payment/belluno_custompayment/auth_token');
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => ($environment),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => [
        "Content-Type:application/json",
        "Accept:application/json",
        "Authorization: Bearer $token",
      ],
    ));

    $response = curl_exec($curl); 

    curl_close($curl);

    return ($response);
  }
}