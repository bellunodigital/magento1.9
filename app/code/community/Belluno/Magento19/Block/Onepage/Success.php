<?php

class Belluno_Magento19_Block_Onepage_Success extends Mage_Core_Block_Template
{

  /**
   * Function to get bankslip url
   */
  public function getUrlBankSlip()
  {
    $data = json_decode($this->getAdditionalInformation(), true);
    return $data['url'];
  }

  /**
   * Function to get bankslip digitable line
   */
  public function getDigitableLineBankSlip()
  {
    $data = json_decode($this->getAdditionalInformation(), true);
    return $data['digitable_line'];
  }

  /**
   * Function to get last real order
   */
  public function getOrder()
  {
    $checkoutSession = $this->getCheckout();
    return $checkoutSession->getLastRealOrder();
  }

  /**
   * Function to get additionalInformation from order
   */
  public function getAdditionalInformation()
  {
    $order = $this->getOrder();
    $payment = $order->getPayment();
    return $payment->getAdditionalInformation("bankslip");
  }

  /** 
   * Get checkout session 
   * @return Mage_Checkout_Model_Session
   */
  public function getCheckout()
  {
    return Mage::getSingleton('checkout/session');
  }
}
