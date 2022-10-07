<?php

class Belluno_Magento19_Model_Bankslip extends Mage_Payment_Model_Method_Abstract
{

  protected $_canAuthorize = true;
  protected $_canCapture = true;
  protected $_canUseCheckout = true;
  protected $_canUseInternal = true;
  protected $_canCapturePartial = true;
  protected $_canCaptureOnce = true;
  protected $_canRefund = false;
  protected $_canVoid = false;
  protected $_canRefundInvoicePartial = false;
  protected $_canCancelInvoice = false;
  protected $_isGateway = true;
  protected $_code = 'belluno_bankslippayment';
  protected $_formBlockType = 'magento19/form_bankslip';

  public function assignData($data)
  {
    if (!($data instanceof Varien_Object)) {
      $data = new Varien_Object($data);
    }
    $info = $this->getInfoInstance();
    $info->setCheckNo($data->getCheckNo())->setCheckDate($data->getCheckDate());

    $this->validateAssignData($data);
    $dataAssign = $this->saveAssignData($data);
    $info->setAdditionalInformation("data", $dataAssign);

    return $this;
  }

  /**
   * Function to save assign data
   */
  public function saveAssignData($data)
  {
    $array = [
      'method' => $data['method'],
      'client_document' => $data['client_document']
    ];
    return json_encode($array);
  }

  /**
   * Validate the payment before request
   * @return $this
   */
  public function validate()
  {
    parent::validate();
    $info = $this->getInfoInstance();

    return $this;
  }

  /**
   * Function authorize of method payment
   */
  public function authorize(Varien_Object $payment, $amount)
  {
    $info = $this->getInfoInstance();
    $authorize = new Belluno_Magento19_Model_BankSlip_Authorize();
    $authorize->authorize($payment, $amount, $info);
  }

  /**
   * Function to validate assign data
   */
  public function validateAssignData($data)
  {
    $quote = $this->getQuote();
    $customerId = $quote->getCustomerId();
    $billingAddress = $quote->getBillingAddress();

    //client
    $clientName = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();

    $taxDocument = $this->getUseTaxDocumentCapture();
    if ($taxDocument == true) {
      $clientDocument = $data['client_document'];
    } else {
      $clientDocument = $this->getTaxVat($customerId);
    }
    if (empty($clientDocument)) {
      $clientDocument = $quote->getCustomerTaxvat();
    }

    $clientEmail = $quote->getCustomerEmail();
    $clientPhone = $billingAddress->getTelephone();
    //billing
    $postalCode = $billingAddress->getPostcode();
    $postalCode = preg_replace('/[^0-9]/is', '', $postalCode);
    $postalCode = substr_replace($postalCode, '-', 5, 0);
    $district = $billingAddress->getRegion();
    $address = $billingAddress->getStreet(1);
    $number = $billingAddress->getStreet(2);
    $city = $billingAddress->getCity();
    $state = $this->getRegionCodeAPI()->getRegionCode($billingAddress->getRegion());

    $this->validationRequest(
      $postalCode,
      $district,
      $address,
      $number,
      $city,
      $state,
      $clientName,
      $clientDocument,
      $clientEmail,
      $clientPhone
    );
  }

  /**
   * Function to validate request
   * @param string $postalCode
   * @param string $district
   * @param string $address
   * @param string $number
   * @param string $city
   * @param string $state
   * @param string $clientName
   * @param string $clientDocument
   * @param string $clientEmail
   * @param string $clientPhone
   */
  public function validationRequest(
    $postalCode,
    $district,
    $address,
    $number,
    $city,
    $state,
    $clientName,
    $clientDocument,
    $clientEmail,
    $clientPhone
  ) {
    $documentValidator = $this->getDocumentsValidator();
    $credentialsValidator = $this->getCredentialsValidator();

    $isValid = $documentValidator->validateDocument($clientDocument);
    if ($isValid == false) {
      Mage::throwException(__('Documento do cliente inválido. Verifique por favor.'));
    }
    $isValid = $credentialsValidator->validateCellphone($clientPhone);
    if ($isValid == false) {
      Mage::throwException(__('Celular do cliente inválido. Verifique por favor.'));
    }
    $credentialsValidator->validateBilling($postalCode, $address, $number, $city, $state, $district);
    $credentialsValidator->validateClientData($clientName, $clientEmail, $clientPhone);
  }

  /**
   * Function to get tax document
   */
  public function getUseTaxDocumentCapture()
  {
    return Mage::getStoreConfig('payment/belluno_bankslippayment/capture_tax');
  }

  /**
   * Function to get TaxVat
   * @param $customerId
   */
  public function getTaxVat($customerId)
  {
    $customer = Mage::getModel('customer/customer')->load($customerId);
    $vatNumber = $customer->getData('taxvat');
    return $vatNumber;
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

  /**Function to return class region code API */
  public function getRegionCodeAPI()
  {
    return new Belluno_Magento19_Validations_RegionCodeAPI();
  }

  /**Function to return class credentials validator */
  public function getCredentialsValidator()
  {
    return new Belluno_Magento19_Validations_CredentialsValidator();
  }

  /**Function to return class documents validator*/
  public function getDocumentsValidator()
  {
    return new Belluno_Magento19_Validations_DocumentsValidator();
  }
}
