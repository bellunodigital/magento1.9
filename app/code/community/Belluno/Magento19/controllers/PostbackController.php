<?php

class Belluno_Magento19_PostbackController extends Mage_Core_Controller_Front_Action {

   /**
   * Function to get postback from belluno @author Vitor <web@tryideas.com.br>
   */
  public function indexAction() {
      
    
    $post = new Zend_Controller_Request_Http();
    $data = $post->getRawBody();
    $data = json_decode($data, true);
    $date_file = Mage::getModel('core/date')->gmtDate('Y-m-d');
    Mage::log( var_export( $data ,true) , Zend_Log::DEBUG , $date_file . '-bulluno-payment.log', true);
    
    $orderId = null;
    $status = null;
    
    if(isset($data['transaction']) && count($data['transaction']) > 0){
        $orderId = $data['transaction']['details'];
        $status = $data['transaction']['status'];
    
    }else if(isset($data['bankslip']) && count($data['bankslip']) > 0){
        $orderId = $data['bankslip']['document_code'];
        $status = $data['bankslip']['status'];
    }
    
    
    if (empty($orderId) || empty($status)) {
      return false;
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

    
    if (isset($status) && strlen($status) > 1 && $status == 'Refused') {
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        
      $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
      $order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED, true);
      $order->save();
    }
  
  }
  
}
