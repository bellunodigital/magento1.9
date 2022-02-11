<?php

class Belluno_Magento19_Service_Connector {
  
  /**
   * Function to get url by environment
   * @return string
   */
  private function getUrlEnvironment(): string {
    $environment = Mage::getStoreConfig('payment/belluno_custompayment/environment');
    if ($environment == 'sandbox') {
      return 'https://ws-sandbox.bellunopag.com.br';
    } else {
      return 'https://api.belluno.digital';
    }
  }

  /**
   * Function to get token
   * @return string
   */
  private function getToken(): string {
    $token = "";
    $token = Mage::getStoreConfig('payment/belluno_custompayment/auth_token');
    return $token;
  }

  /**
   * Function to do request
   * @param string $dataRequest
   * @param string $method
   * @param string $uri
   * @return string
   */
  public function doRequest($dataRequest, $method, $uri): string {
    $token = $this->getToken();
    $url = $this->getUrlEnvironment() . $uri;    

    Mage::log("[-- REQUEST BELLUNO --] $dataRequest", null, 'belluno.log', true);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    if ($method == "POST") {
      curl_setopt($ch, CURLOPT_POST, TRUE);  
      curl_setopt($ch, CURLOPT_POSTFIELDS, "$dataRequest");
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Content-Type: application/json",
      "Authorization: Bearer $token"
    ));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );

    Mage::log("[-- RESPONSE BELLUNO --] $response", null, 'belluno.log', true);

    if ($httpCode > 200 || $httpCode < 200) {
      Mage::throwException("Algo não ocorreu bem. Por favor verifique suas informações.");
    }

    curl_close($ch);
    
    json_encode($response);
    return $response;
  }
}
