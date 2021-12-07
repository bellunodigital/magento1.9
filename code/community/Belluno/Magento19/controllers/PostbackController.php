<?php

class Belluno_Magento19_PostbackController extends Mage_Core_Controller_Front_Action {

  /**
   * Function to get postback from belluno
   */
  public function indexAction() {
    $post = new Zend_Controller_Request_Http();
    $data = $post->getRawBody();
    $data = json_decode($data, true);

    $orderId = $data['bankslip']['document_code'];
    $status = $data['bankslip']['status'];

    if (empty($orderId) || empty($status)) {
      $orderId = $data['transaction']['details'];
      $status = $data['transaction']['status'];
    }

    if ($status == 'Paid') {
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
      $status = $order->getStatus();
      if ($status != Mage_Sales_Model_Order::STATE_PROCESSING) {
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
          ->addObject($invoice)
          ->addObject($invoice->getOrder());
        $transactionSave->save();
      }
      $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
      $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING, true);
      $order->save();
    }

    if (isset($data['transaction']['refunds']['status'])) {
      if (!empty($data['transaction']['refunds']['amount'])) {
        $amount = $data['transaction']['refunds']['amount'];
        if ($amount != ' ' && $amount != '0' && $amount != 0) {
          $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
          $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
          $order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED, true);
          $order->save();
        }
      }
    }
  }
}
