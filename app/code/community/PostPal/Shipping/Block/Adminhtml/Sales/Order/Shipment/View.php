<?php
/**
 * @author Jana Vassiljeva <jana@artmarka.com>
 */

class PostPal_Shipping_Block_Adminhtml_Sales_Order_Shipment_View extends Mage_Adminhtml_Block_Sales_Order_Shipment_View
{


    public function  __construct()
    {
        parent::__construct();

        $trackPdfUrl = '';
        $trackUrl = Mage::getStoreConfig('carriers/postpal_shipping/track_url');

        $orderId = $this->getShipment()->getOrderId();
        $order = Mage::getModel("sales/order")->load($orderId);

        $shippingMethod = $order->getShippingMethod();

        if ($shippingMethod != 'postpal_shipping_fixed')
            return $this;

        $tracks = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($order)
            ->load();

        foreach ($tracks as $shipment){
            foreach($shipment->getAllTracks() as $tracknum)
            {
                $trackPdfUrl = $tracknum->getDescription();
                $trackNumber = $tracknum->getTrackNumber();
            }
        }

        if (empty($trackPdfUrl))
            return $this;

        $this->_addButton('print_label', array(
                'label'     => Mage::helper('postpal')->__('Print package label'),
                'class'     => 'save',
                'target' => '_blank',
                'onclick'   => 'window.open(\''.$trackPdfUrl.'\', \'_blank\')'
            )
        );

        $this->_addButton('track_label', array(
                'label'     => Mage::helper('postpal')->__('Track this shipment'),
                'class'     => 'save',
                'target' => '_blank',
                'onclick'   => 'window.open(\''.$trackUrl.'/'.$trackNumber.'\', \'_blank\')'
            )
        );

    }

}
