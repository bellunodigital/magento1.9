<?php

class Belluno_Magento19_Block_Order_Info extends Mage_Core_Block_Template
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
   * Function to get additionalInformation
   */
  public function getViewData() {
    $order = $this->getOrder();
    $payment = $order->getPayment();
    $viewData = json_decode($payment->getAdditionalInformation('view'), true);
    return $viewData;
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
   * Function to get additionalInformation from order
   */
  public function getAdditionalInformation()
  {
    $order = $this->getOrder();
    $payment = $order->getPayment();
    return $payment->getAdditionalInformation("bankslip");
  }

  /**
   * Function to get order
   */
  public function getOrder()
  {
    $order_id = $this->getRequest()->getParam('order_id');
    $order = Mage::getModel('sales/order')->load($order_id);
    return $order;
  }
}
