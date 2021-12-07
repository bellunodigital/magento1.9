<?php

class Belluno_Magento19_Model_Creditcard extends Mage_Payment_Model_Method_Abstract
{

  protected $_canAuthorize = true;
  protected $_canCapture = true;
  protected $_canRefund = true;
  protected $_canRefundInvoicePartial = true;
  protected $_canUseCheckout = true;
  protected $_canUseForMultishipping = true;
  protected $_canUseInternal = true;
  protected $_canVoid = true;
  protected $_canReviewPayment = false;
  protected $_isGateway = true;
  protected $_canCancelInvoice = true;
  protected $_code = 'belluno_creditcardpayment';
  protected $_formBlockType = 'magento19/form_creditcard';

  /**
   * Function to get data form frontend
   */
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
      'client_document' => $data['client_document'],
      'visitor_id' => $data['visitor_id'],
      'card_holder_document' => $data['card_holder_document'],
      'card_holder_cellphone' => $data['card_holder_cellphone'],
      'card_holder_birth' => $data['card_holder_birth'],
      'card_number' => $data['card_number'],
      'name_on_card' => $data['name_on_card'],
      'card_month_exp' => $data['card_month_exp'],
      'card_year_exp' => $data['card_year_exp'],
      'cc_cvv' => $data['cc_cvv'],
      'card_installment' => $data['card_installment']
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
    $authorize = new Belluno_Magento19_Model_CreditCard_Authorize();
    $authorize->authorize($payment, $amount, $info);
  }

  /**
   * Function capture of method payment
   */
  public function capture(Varien_Object $payment, $amount)
  {
    $info = $this->getInfoInstance();
    $capture = new Belluno_Magento19_Model_CreditCard_Capture();
    $capture->capture($payment, $amount, $info);
  }

  /**
   * Function credit memo of method payment
   */
  public function processCreditmemo($creditmemo, $payment)
  {
    $refund = new Belluno_Magento19_Model_CreditCard_Refund();
    $refund->refund($payment);
  }

  /**
   * Function to validate assign data
   */
  public function validateAssignData($data)
  {
    $quote = $this->getQuote();
    $shippingAddress = $quote->getShippingAddress();
    $billingAddress = $quote->getBillingAddress();

    $cardNumber = preg_replace('/[^0-9]/is', '', $data['card_number']);

    //body request
    $clientName = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
    $clientEmail = $quote->getCustomerEmail();
    $clientPhone = $data['card_holder_cellphone'];
    $brand = $this->getCreditCardValidator()->getCardTypeBelluno($cardNumber);
    //shipping
    $postalCode = $shippingAddress->getPostcode();
    $postalCode = substr_replace($postalCode, '-', 5, 0);
    $street = $shippingAddress->getStreet(1);
    $number = $shippingAddress->getStreet(2);
    $city = $shippingAddress->getCity();
    $state = $this->getRegionCodeAPI()->getRegionCode($shippingAddress->getRegion());
    //billing
    $bpostalCode = $billingAddress->getPostcode();
    $bpostalCode = substr_replace($bpostalCode, '-', 5, 0);
    $bstreet = $billingAddress->getStreet(1);
    $bnumber = $billingAddress->getStreet(2);
    $bcity = $billingAddress->getCity();
    $bstate = $this->getRegionCodeAPI()->getRegionCode($billingAddress->getRegion());

    //validations
    $this->validationRequest(
      $data,
      $postalCode,
      $street,
      $number,
      $city,
      $state,
      $clientName,
      $clientEmail,
      $clientPhone,
      $bpostalCode,
      $bstreet,
      $bnumber,
      $bcity,
      $bstate,
      $district = '0',
      $brand
    );
  }

  /**
   * Function to validate request credit card
   * @param array $data
   * @param string $postalCode
   * @param string $street
   * @param string $number
   * @param string $city
   * @param string $state
   * @param string $clientName
   * @param string $clientEmail
   * @param string $clientPhone
   * @param string $bpostalCode
   * @param string $bstreet
   * @param string $bnumber
   * @param string $bcity
   * @param string $bstate
   * @param string $district
   * @param string $brand
   */
  public function validationRequest(
    $data,
    $postalCode,
    $street,
    $number,
    $city,
    $state,
    $clientName,
    $clientEmail,
    $clientPhone,
    $bpostalCode,
    $bstreet,
    $bnumber,
    $bcity,
    $bstate,
    $district = '0',
    $brand
  ) {
    $documentValidator = $this->getDocumentsValidator();
    $credentialsValidator = $this->getCredentialsValidator();
    $creditCardValidator = $this->getCreditCardValidator();
    $quote = $this->getQuote();
    $customerId = $quote->getCustomerId();

    $taxDocument = $this->getUseTaxDocumentCapture();
    if ($taxDocument == true) {
      $clientDocument = $data['client_document'];
      $cardHolderDocument = $data['card_holder_document'];
    } else {
      $clientDocument = $this->getTaxVat($customerId);
      $clientDocument = $this->formatCpfCnpj($clientDocument);
      $cardHolderDocument = $clientDocument;
    }
    if (empty($clientDocument)) {
      $clientDocument = $quote->getCustomerTaxvat();
      $cardHolderDocument = $clientDocument;
    }

    $isValid = $documentValidator->validateDocument($clientDocument);
    if ($isValid == false) {
      Mage::throwException(__('Documento do cliente inválido. Verifique por favor.'));
    }
    $isValid = $documentValidator->validateDocument($cardHolderDocument);
    if ($isValid == false) {
      Mage::throwException(__('Documento do titular do cartão inválido. Verifique por favor.'));
    }
    $isValid = $credentialsValidator->validateCellphone($data['card_holder_cellphone']);
    if ($isValid == false) {
      Mage::throwException(__('Celular do titular do cartão inválido. Verifique por favor.'));
    }
    $isValid = $credentialsValidator->validateDateBirth($data['card_holder_birth']);
    if ($isValid == false) {
      Mage::throwException(__('Data de nascimento inválida. Verifique por favor.'));
    }
    $cardNumber = preg_replace('/[^0-9]/is', '', $data['card_number']);
    $isValid = $creditCardValidator->validateCreditCard($cardNumber);
    if ($isValid['valid'] == false) {
      Mage::throwException(__('Cartão de crédito inválido. Verifique por favor.'));
    }
    if ((strlen($data['cc_cvv'])) < 3 || (strlen($data['cc_cvv'])) > 3) {
      Mage::throwException(__('CVV inválido. Verifique por favor.'));
    }
    if (!is_numeric($brand)) {
      Mage::throwException(__('CVV inválido. Verifique por favor.'));
    }
    $credentialsValidator->validateShipping($postalCode, $street, $number, $city, $state);
    $credentialsValidator->validateBilling($bpostalCode, $bstreet, $bnumber, $bcity, $bstate, $district);
    $credentialsValidator->validateClientData($clientName, $clientEmail, $clientPhone);
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

  /**
   * Function to format cpf and cnpj
   */
  public function formatCpfCnpj($doc)
  {
    $doc = preg_replace("/[^0-9]/", "", $doc);
    $qtd = strlen($doc);

    if ($qtd >= 11) {
      if ($qtd === 11) {

        $docFormatado = substr($doc, 0, 3) . '.' .
          substr($doc, 3, 3) . '.' .
          substr($doc, 6, 3) . '-' .
          substr($doc, 9, 2);
      } else {
        $docFormatado = substr($doc, 0, 2) . '.' .
          substr($doc, 2, 3) . '.' .
          substr($doc, 5, 3) . '/' .
          substr($doc, 8, 4) . '-' .
          substr($doc, -2);
      }

      return $docFormatado;
    } else {
      return false;
    }
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
   * Function to get tax document
   */
  public function getUseTaxDocumentCapture()
  {
    return Mage::getStoreConfig('payment/belluno_creditcardpayment/capture_tax');
  }

  /**Function to return class region code API */
  public function getRegionCodeAPI()
  {
    return new Belluno_Magento19_Validations_RegionCodeAPI();
  }

  /**Function to return class credit card validator */
  public function getCreditCardValidator()
  {
    return new Belluno_Magento19_Validations_CreditCardValidator();
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
