<?php

class Belluno_Magento19_Block_Adminhtml_Form_Field_Rulecapture {

  /*
  * Function to get capture options
  * @return array
  */
  public function toOptionArray() {
    $array = [
      [
        'value' => 'authorize',
        'label' => __('Authorize')
      ],
      [
        'value' => 'authorize_capture',
        'label' => __('Authorize and Capture')
      ]  
    ];

    return $array;
  }
}
