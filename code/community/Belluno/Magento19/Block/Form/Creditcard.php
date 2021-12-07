<?php

class Belluno_Magento19_Block_Form_Creditcard extends Mage_Payment_Block_Form
{

  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('magento19/form/creditcard.phtml');
  }

  protected function _getConfig()
  {
    return Mage::getSingleton('payment/config');
  }

  /**
   * Function to get cc valid months
   */
  public function getCcMonths()
  {
    $months = $this->getData('cc_months');
    if (is_null($months)) {
      $months[0] = "Month";
      $months = array_merge($months, $this->_getConfig()->getMonths());
      $this->setData('cc_months', $months);
    }
    return $months;
  }

  /**
   * Function to get cc valid years
   */
  public function getCcYears()
  {
    $years = $this->getData('cc_years');
    if (is_null($years)) {
      $years[0] = "Year";
      $years = array_merge($years, $this->_getConfig()->getYears());
      $this->setData('cc_years', $years);
    }
    return $years;
  }

  /**
   * Function to get installments
   * @param mixed $total
   */
  public function getInstallments($total)
  {
    $maxInstallments = Mage::getStoreConfig('payment/belluno_creditcardpayment/installments');
    $minValueInstalment = Mage::getStoreConfig('payment/belluno_creditcardpayment/min_installment');
    $dataInterest = Mage::getStoreConfig('payment/belluno_creditcardpayment/installment_interest');
    $dataInterest = unserialize($dataInterest);
    foreach ($dataInterest as $key => $value) {
      $installmentInterest[] = $value['from_qty'];
    }
    $arrayInstallments[0] = "Select Installment";
    if ($maxInstallments == 0) {
      $arrayInstallments[] = "1x de R$$total sem juros";
    }

    for ($i = 0; $i < $maxInstallments; $i++) {
      $valuePortion = ($total / ($i + 1));
      $valuePortion = number_format($valuePortion, 2);
      $times = $i + 1;
      if (($i + 1) == 1) {
        if ($installmentInterest[0] == 0 || $installmentInterest[0] == null) {
          $arrayInstallments[] = "1x de R$$valuePortion sem juros";
        } else {
          $interest = $valuePortion * ($installmentInterest[0] / 100);
          $interest = number_format($interest, 2);
          $valuePortion = $valuePortion + $interest;
          $valuePortion = number_format($valuePortion, 2);
          $totalInterest = $interest * ($i + 1);
          $totalInterest = number_format($totalInterest, 2);
          $arrayInstallments[] = "1x de R$$valuePortion com juros total de R$$totalInterest";
        }
      } else if ($installmentInterest[$i] != 0 && $installmentInterest[$i] != null) {
        $valuePortion = ($total / ($i + 1));
        if ($valuePortion >= $minValueInstalment) {
          $interest = $valuePortion * ($installmentInterest[$i] / 100);
          $interest = number_format($interest, 2);
          $valuePortion = $valuePortion + $interest;
          $valuePortion = number_format($valuePortion, 2);
          $totalInterest = $interest * ($i + 1);
          $totalInterest = number_format($totalInterest, 2);
          $arrayInstallments[] = "$times" . "x de R$$valuePortion com juros total de R$$totalInterest";
        }
      } else {
        if ($valuePortion >= $minValueInstalment) {
          $arrayInstallments[] = "$times" . "x de R$$valuePortion sem juros";
        }
      }
    }
    return $arrayInstallments;
  }

  /**
   * Function to get image type brands
   */
  public function getBrandImages()
  {
    $array = [
      'mastercard' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'belluno/magento19/images/mastercard.png',
      'visa' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'belluno/magento19/images/visa.png',
      'elo' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'belluno/magento19/images/elo.png',
      'hipercard' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'belluno/magento19/images/hipercard.png',
      'cab' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'belluno/magento19/images/cab.png',
      'hiper' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'belluno/magento19/images/hiper.png'
    ];
    return $array;
  }

  /**
   * Function to get pub key konduto
   */
  public function getPubKeyKonduto()
  {
    $pubKey = Mage::getStoreConfig('payment/belluno_custompayment/public_key');
    return $pubKey;
  }

  public function getFieldCaptureTax()
  {
    $captureTax = Mage::getStoreConfig('payment/belluno_creditcardpayment/capture_tax');
    if ($captureTax == true) {
      return true;
    } else {
      return false;
    }
  }
}