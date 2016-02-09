<?php
/**
 * @author Jana Vassiljeva <jana@artmarka.com>
 */

class PostPal_Shipping_Model_Observer extends Varien_Object
{

    public function getOrderValidation($observer)
    {
        $passed = false;
        $errors = '';
        $order = $observer->getEvent()->getOrder();

        $shippingMethod = $order->getShippingMethod();

        if ($shippingMethod != 'postpal_shipping_fixed')
            return $this;

        $data = array(
            'shippingAddress' => $order->getShippingAddress()->getData(),
            'packageSize' => 'size20x36x60D10W'
        );

        $result = Mage::getModel('postpal_shipping/api')->getOrderValidation($data);

        if (!empty($result) && $result->status == 'true')
            $passed = true;

        if(!$passed)
        {
            if (!empty($result->errors)) {
                foreach ($result->errors as $key => $error) {

                    if($key == 'token' || $key == 'warehouse') {
                        $errors .= Mage::helper('postpal')->__('PostPal shipping plugin is not configured correctly').';';
                    }
                    elseif($error->Code == '201') {
                        $errors .= Mage::helper('postpal')->__('Address not Found').';';
                    }
                    elseif($error->Code == '202') {
                        $errors .= Mage::helper('postpal')->__('Address out of range').';';
                    }
                    elseif($error->Code == '001' && ($key == 'destinationFirstName' ||
                            $key == 'destinationLastName' || $key == 'destinationFullName')) {
                        $errors .= Mage::helper('postpal')->__('Name is missing').';';
                    }
                    elseif($error->Code == '001' && $key == 'destinationAddress') {
                        $errors .= Mage::helper('postpal')->__('Address is missing').';';
                    }
                    elseif($error->Code == '001' && $key == 'destinationPhone') {
                        $errors .= Mage::helper('postpal')->__('Phone number is missing').';';
                    }
                    elseif($error->Code == '001' && $key == 'packageSize') {
                        $errors .= Mage::helper('postpal')->__('Package size is missing').';';
                    }
                    elseif($error->Code == '002' && ($key == 'destinationFirstName' ||
                            $key == 'destinationLastName' || $key == 'destinationFullName')) {
                        $errors .= Mage::helper('postpal')->__('Name is not correct').';';
                    }
                    elseif($error->Code == '002' && $key == 'destinationCompany') {
                        $errors .= Mage::helper('postpal')->__('Company name is not correct').';';
                    }
                    elseif($error->Code == '002' && $key == 'destinationEmail') {
                        $errors .= Mage::helper('postpal')->__('E-mail is not correct').';';
                    }
                    elseif($error->Code == '002' && $key == 'destinationApartment') {
                        $errors .= Mage::helper('postpal')->__('Apartment in address is not correct').';';
                    }
                    elseif($error->Code == '002' && $key == 'destinationAddress') {
                        $errors .= Mage::helper('postpal')->__('Address is not correct').';';
                    }
                    elseif($error->Code == '002' && $key == 'destinationLocality') {
                        $errors .= Mage::helper('postpal')->__('Locality in address is not correct').';';
                    }
                    elseif($error->Code == '002' && $key == 'destinationCountry') {
                        $errors .= Mage::helper('postpal')->__('Country in address is not correct').';';
                    }
                    elseif($error->Code == '002' && $key == 'destinationPostalCode') {
                        $errors .= Mage::helper('postpal')->__('Postal code is not correct').';';
                    }
                    elseif($error->Code == '002' && $key == 'destinationPhone') {
                        $errors .= Mage::helper('postpal')->__('Phone number is not correct').';';
                    }
                    elseif($error->Code == '002' && $key == 'notes') {
                        $errors .= Mage::helper('postpal')->__('Notes are not correct').';';
                    }
                    elseif($error->Code == '002' && $key == 'packageSize') {
                        $errors .= Mage::helper('postpal')->__('Package size is not correct').';';
                    }
                }
            }
            elseif($result == 'Unauthorized.')
                $errors .= Mage::helper('postpal')->__('PostPal shipping plugin is not configured correctly').';';
            
            Mage::throwException($errors);
        }
    }

    public function sendOrderData($observer)
    {
        /* @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getEvent()->getInvoice();
        /* @var $order Mage_Sales_Model_Order */
        $order = $invoice->getOrder();

        $shippingMethod = $order->getShippingMethod();

        if ($shippingMethod != 'postpal_shipping_fixed')
            return $this;

        $data = array(
            'shippingAddress' => $order->getShippingAddress()->getData(),
            'packageSize' => 'size20x36x60D10W'
        );

        if ($invoice instanceof Mage_Sales_Model_Order_Invoice)
        {
            $result = Mage::getModel('postpal_shipping/api')->sendNewOrder($data);
            if (!empty($result) && $result->status == 'true') {
                Mage::getModel('postpal_shipping/shipping')->completeShipment($order, $result);
            }

        }

        return $this;
    }

}
