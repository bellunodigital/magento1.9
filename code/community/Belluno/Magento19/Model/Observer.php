<?php

class Belluno_Magento19_Model_Observer
{
    /**
     * Function to cancel order
     */
    public function cancelOrder(Varien_Event_Observer $observer)
    {
        if ($observer->getOrder()->isCanceled()) {
            $order = $observer->getOrder();
            $payment = $order->getPayment();
            $method = $payment->getMethod();

            if ($method == 'belluno_creditcardpayment') {
                $connector = $this->getConnector();
                $value = $payment->getAdditionalInformation('value');
                $transactionId = $payment->getAdditionalInformation('transaction_id');

                $request = [
                    'amount' => $value,
                    'reason' => '2'
                ];
                $request = json_encode($request);
                $connector->doRequest($request, "POST", "/transaction/$transactionId/refund");
            }
        }
    }

    public function updateStatusOrder(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethod();
        $status = "";
        $status = $payment->getAdditionalInformation("status");

        $order->setCanViewOrder(true);

        if ($method == 'belluno_creditcardpayment' || $method == 'belluno_bankslippayment') {
            if ($status != 'Paid') {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                $order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true);
                $order->save();
            } else {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                $order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true);
                $order->save();
            }
        }
    }

    /**Function to return class connector for requests */
    public function getConnector()
    {
        return new Belluno_Magento19_Service_Connector();
    }
}
