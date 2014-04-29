<?php
class HusseyCoding_Sirportly_Block_Orders extends Mage_Core_Block_Template
{
    public function getOrders()
    {
        $customer = $this->getCustomer();
        $orders = Mage::getResourceModel('sales/order_collection');
        $orders->getSelect()
            ->where('customer_email = ?', $customer->getEmail())
            ->order('created_at DESC');
        
        if ($orders->count()):
            $return = array();
            
            foreach ($orders as $order):
                $return[$order->getId()] = array(
                    'number' => $order->getIncrementId(),
                    'url' => $this->_getOrderUrl($order->getId()),
                    'date' => $this->_getDate($order->getCreatedAt()),
                    'time' => $this->_getTime($order->getCreatedAt()),
                    'status' => $order->getStatus(),
                    'items' => $this->_getOrderItemsData($order),
                    'total' => $this->_formatPrice($order->getBaseGrandTotal()),
                    'shipping' => $order->getShippingDescription(),
                    'country' => $this->_getShippingCountry($order),
                    'postcode' => $this->_getShippingPostcode($order)
                );
            endforeach;
            
            if (!empty($return)) return $return;
        endif;
        
        return false;
    }
    
    public function getCustomerName()
    {
        return $this->getCustomer()->getName();
    }
    
    public function getCustomerEmail()
    {
        return $this->getCustomer()->getEmail();
    }
    
    private function _getOrderUrl($id)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $id));
    }
    
    private function _getDate($timestamp)
    {
        if ($epoch = strtotime($timestamp)):
            if ($date = date('jS M', $epoch)):
                return $date;
            endif;
        endif;
        
        return false;
    }
    
    private function _getTime($timestamp)
    {
        if ($epoch = strtotime($timestamp)):
            if ($time = date('H:i', $epoch)):
                return $time;
            endif;
        endif;
        
        return false;
    }
    
    private function _getOrderItemsData(Mage_Sales_Model_Order $order)
    {
        $return = array();
        
        foreach ($order->getAllItems() as $item):
            $return[$item->getId()] = array(
                'sku' => $item->getSku(),
                'quantity' => (float) $item->getQtyOrdered(),
                'url' => $this->_getItemUrl($item->getProductId())
            );
        endforeach;
        
        return !empty($return) ? $return : false;
    }
    
    private function _formatPrice($price)
    {
        return Mage::helper('core')->currency($price, true, false);
    }
    
    private function _getItemUrl($id)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $id));
    }
    
    private function _getShippingCountry(Mage_Sales_Model_Order $order)
    {
        if ($address = $order->getShippingAddress()):
            if ($country = $address->getCountry()):
                return $country;
            endif;
        endif;
        
        return false;
    }
    
    private function _getShippingPostcode(Mage_Sales_Model_Order $order)
    {
        if ($address = $order->getShippingAddress()):
            if ($postcode = $address->getPostcode()):
                return $postcode;
            endif;
        endif;
        
        return false;
    }
}