<?php

class Belluno_Magento19_Validations_CreditCardValidator
{

  protected $cards = [
    'visa' => [
      'type' => 'visa',
      'pattern' => '/^4[0-9]{12}([0-9]{3})?$/',
      'length' => array(13, 16),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'mastercard' => [
      'type' => 'mastercard',
      'pattern' => '/^(5[1-5]\d{4}|677189)\d{10}$/',
      'length' => array(16),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'elo' => [
      'type' => 'elo',
      'pattern' => '/^4011(78|79)|^43(1274|8935)|^45(1416|7393|763(1|2))|^50(4175|6699|67[0-6][0-9]|677[0-8]|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])|^627780|^63(6297|6368|6369)|^65(0(0(3([1-3]|[5-9])|4([0-9])|5[0-1])|4(0[5-9]|[1-3][0-9]|8[5-9]|9[0-9])|5([0-2][0-9]|3[0-8]|4[1-9]|[5-8][0-9]|9[0-8])|7(0[0-9]|1[0-8]|2[0-7])|9(0[1-9]|[1-6][0-9]|7[0-8]))|16(5[2-9]|[6-7][0-9])|50(0[0-9]|1[0-9]|2[1-9]|[3-4][0-9]|5[0-8]))/',
      'length' => array(16),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'hipercard' => [
      'type' => 'hipercard',
      'pattern' => '/^(606282\d{10}(\d{3})?)|(3841\d{15})$/',
      'length' => array(13, 16, 19),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'hiper' => [
      'type' => 'hiper',
      'pattern' => '/^(637095|637612|637599|637609|637568)/',
      'length' => array(12, 13, 14, 15, 16, 17, 18, 19),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'cabal' => [
      'type' => 'cabal',
      'pattern' => '/(60420[1-9]|6042[1-9][0-9]|6043[0-9]{2}|604400)/',
      'length' => array(16),
      'cvcLength' => array(3),
      'luhn' => true
    ]
  ];

  /**
   * Function to validate credit card
   * @param string $number
   * @param string $type
   * @return array
   */
  public function validateCreditCard($number, $type = null): array
  {
    $returnDefault = [
      'valid' => false,
      'number' => '',
      'type' => ''
    ];

    if (empty($type)) {
      $type = $this->creditCardType($number);
    }

    $this->validCard($number, $type);

    if (array_key_exists($type, $this->cards) && $this->validCard($number, $type)) {
      if ($this->luhnCheck($number)) {
        return [
          'valid' => true,
          'number' => $number,
          'type' => $type
        ];
      }
    }

    return $returnDefault;
  }

  /**
   * Function to get card type
   * @param string $number
   * @return string $type
   */
  public function getCardTypeBelluno($number): string
  {
    $arrayTypes = [
      '1' => 'mastercard',
      '2' => 'visa',
      '3' => 'elo',
      '4' => 'hipercard',
      '5' => 'cabal',
      '6' => 'hiper'
    ];

    $res = $this->validateCreditCard($number);
    $type = $res['type'];
    $resultTypeCard = '';

    foreach ($arrayTypes as $key => $value) {
      if ($type == $value) {
        $resultTypeCard = $key;
      }
    }

    return $resultTypeCard;
  }

  //Internal functions

  /**
   * Function to validate credit card (luhncheck)
   * @param $cardNumber
   * @return bool
   */
  protected function luhnCheck($cardNumber)
  {
    $cardNumber = preg_replace('/\D/', '', $cardNumber);
    $numberLenght = strlen($cardNumber);
    $parity = $numberLenght % 2;

    $total = 0;
    for ($i = 0; $i < $numberLenght; $i++) {
      $digit = $cardNumber[$i];
      if ($i % 2 == $parity) {
        $digit *= 2;

        if ($digit > 9) {
          $digit -= 9;
        }
      }
      $total += $digit;
    }

    return ($total % 10 == 0) ? true : false;
  }

  /**
   * Function to get credit card type
   * @param string $number
   * @return string
   */
  protected function creditCardType($number): string
  {
    foreach ($this->cards as $type => $card) {
      if (preg_match($card['pattern'], $number)) {
        return $type;
      }
    }
    return '';
  }

  /**
   * Function to validate credit card
   * @param string $number
   * @param string $type
   * @return bool
   */
  protected function validCard($number, $type): bool
  {
    return ($this->validPattern($number, $type) && $this->validLength($number, $type));
  }

  /**
   * Function to validate brand pattern
   * @param string $number
   * @param string $type
   * @return bool
   */
  protected function validPattern($number, $type): bool
  {
    return preg_match($this->cards[$type]['pattern'], $number);
  }

  /**
   * Function to validate length number of card
   * @param string $number
   * @param string $type
   * @return bool
   */
  protected function validLength($number, $type): bool
  {
    foreach ($this->cards[$type]['length'] as $length) {
      if (strlen($number) == $length) {
        return true;
      }
    }
    return false;
  }
}
