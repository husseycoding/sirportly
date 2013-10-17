<?php
class HusseyCoding_Sirportly_Block_Adminhtml_Sales_Order_View_Tab_SirportlyTickets extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sirportly/sales/order/view/tab/sirportlytickets.phtml');
    }
    
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getTabLabel()
    {
        return $this->__('Sirportly Tickets');
    }

    public function getTabTitle()
    {
        return $this->__('Sirportly Tickets');
    }
    
    public function getTabClass()
    {
        return 'ajax only';
    }
    
    public function getClass()
    {
        return $this->getTabClass();
    }
    
    public function getTabUrl()
    {
        return $this->getUrl('sirportly/sales_order/sirportlyTickets', array('_current' => true));
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
    
    public function getCustomerEmail()
    {
        return $this->getOrder()->getCustomerEmail();
    }
    
    public function getCustomerName()
    {
        return $this->getOrder()->getCustomerName();
    }
    
    public function getTicketsByEmail()
    {
        return Mage::helper('sirportly')->getTicketsByEmail($this->getCustomerEmail());
    }
}