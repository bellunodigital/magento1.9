<?php

class Belluno_Magento19_Block_Adminhtml_Order_View_Tab_Contents
extends Mage_Adminhtml_Block_Template
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
  /**
   * Simple construct
   */
  public function _construct()
  {
    parent::_construct();
    $this->setTemplate('magento19/order/view/tab/contents.phtml');
  }

  public function canShowTab()
  {
    return true;
  }

  public function isHidden()
  {
    return false;
  }

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
  public function getViewData()
  {
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
   * Function to get tab label
   */
  public function getTabLabel()
  {
    return $this->__('Payment Details');
  }

  /**
   * Function to get tab title
   */
  public function getTabTitle()
  {
    return $this->__('Payment Details');
  }

  /**
   * Function to get Order
   */
  public function getOrder()
  {
    return Mage::registry('current_order');
  }
}
