<?php

class Belluno_Magento19_Block_Adminhtml_Form_Field_Environment {

  /*
  * Function to get environment options
  * @return array
  */
  public function toOptionArray() {
    $array = [
      [
        'value' => 'sandbox',
        'label' => __('Sandbox - Environment for tests')
      ],
      [
        'value' => 'production',
        'label' => __('Production')
      ]  
    ];

    return $array;
  }
}
