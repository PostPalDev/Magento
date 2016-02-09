<?php
/**
 * @author Jana Vassiljeva <jana@artmarka.com>
 */

class PostPal_Shipping_Model_Shipping
{
    /**
     * Completes the Shipment, followed by completing the Order life-cycle
     * It is assumed that the Invoice has already been generated
     * and the amount has been captured.
     */
    public function completeShipment($order, $shipmentResult)
    {
        if (!$order->getId()) {
            Mage::throwException("Order does not exist, for the Shipment process to complete");
        }

        if ($order->canShip()) {
            try {

                $shipment = Mage::getModel('sales/service_order', $order)
                    ->prepareShipment($this->_getItemQtys($order));

                /**
                 * Carrier Codes can be like "ups" / "fedex" / "custom",
                 * but they need to be active from the System Configuration area.
                 * These variables can be provided custom-value, but it is always
                 * suggested to use Order values
                 */

                $arrTracking = array(
                    'carrier_code' => 'postpal_shipping_fixed',
                    'title' => 'PostPal',
                    'description' =>$shipmentResult->packageLabelPDF,
                    'number' => $shipmentResult->trackingCode,
                );

                $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
                $shipment->addTrack($track);

                // Register Shipment
                $shipment->register();

                // Save the Shipment
                $this->_saveShipment($shipment, $order);

                // Finally, Save the Order
                $this->_saveOrder($order);
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * Get the Quantities shipped for the Order, based on an item-level
     * This method can also be modified, to have the Partial Shipment functionality in place
     *
     * @param $order Mage_Sales_Model_Order
     * @return array
     */
    protected function _getItemQtys(Mage_Sales_Model_Order $order)
    {
        $qty = array();
        foreach ($order->getAllItems() as $_eachItem) {
            if ($_eachItem->getParentItemId()) {
                $qty[$_eachItem->getParentItemId()] = $_eachItem->getQtyOrdered();
            } else {
                $qty[$_eachItem->getId()] = $_eachItem->getQtyOrdered();
            }
        }

        return $qty;
    }

    /**
     * Saves the Shipment changes in the Order
     *
     * @param $shipment Mage_Sales_Model_Order_Shipment
     * @param $order Mage_Sales_Model_Order
     * @param $customerEmailComments string
     */
    protected function _saveShipment(Mage_Sales_Model_Order_Shipment $shipment, Mage_Sales_Model_Order $order, $customerEmailComments = '')
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($order)
            ->save();

        $shippingAddress = $order->getShippingAddress()->getData();
        $customerEmail = $shippingAddress['email'];
        $emailSentStatus = $shipment->getData('email_sent');

        if (!is_null($customerEmail) && !$emailSentStatus) {
            $shipment->sendEmail(true, $customerEmailComments);
            $shipment->setEmailSent(true);
        }

        return $this;
    }

    /**
     * Saves the Order, to complete the full life-cycle of the Order
     * Order status will now show as Complete
     *
     * @param $order Mage_Sales_Model_Order
     */
    protected function _saveOrder(Mage_Sales_Model_Order $order)
    {
        $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
        $order->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);

        $order->save();

        return $this;
    }
}