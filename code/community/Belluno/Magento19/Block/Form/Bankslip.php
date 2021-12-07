<?php

class Belluno_Magento19_Block_Form_Bankslip extends Mage_Payment_Block_Form {

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('magento19/form/bankslip.phtml');
  }

  public function getFieldCaptureTax() {
    $captureTax = Mage::getStoreConfig('payment/belluno_bankslippayment/capture_tax');
    if ($captureTax == true) {
      return true;
    } else {
      return false;
    }
  }
}
