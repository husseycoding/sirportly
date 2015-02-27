<?php
class HusseyCoding_Sirportly_Block_Adminhtml_Sales_Order_View_Tab_SirportlyTickets extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    private $_customeremail;
    private $_domain;
    
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sirportly/sales/order/view/tab/sirportlytickets.phtml');
    }
    
    protected function _prepareLayout()
    {
        $newticket = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_newTicket');
        $this->setChild('order_tab_sirportly_newticket', $newticket);
        
        return parent::_prepareLayout();
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
        return $this->getUrl('adminhtml/sales_order/sirportlyTickets', array('_current' => true));
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
        if (!isset($this->_customeremail)):
            $this->_customeremail = $this->getOrder()->getCustomerEmail();
        endif;
        
        return $this->_customeremail;
    }
    
    public function getCustomerName()
    {
        return $this->getOrder()->getCustomerName();
    }
    
    public function getTicketsByEmail()
    {
        return Mage::helper('sirportly')->getTicketsByEmail($this->getCustomerEmail());
    }
    
    public function getSlaHtml($ticket)
    {
        $html = '';
        
        if (!empty($ticket['sla']['name'])):
            $html .= '<div>'. $ticket['sla']['name'] . '</div>';
            $name = $ticket['sla']['name'];
            $now = time();
            $helper = Mage::helper('sirportly');
            if (empty($ticket['resolution_time'])):
                if (!empty($ticket['reply_due_at'])):
                    $due = strtotime($ticket['reply_due_at']);
                    if ($now > $due):
                        $html .= '<div class="ticket-update-overdue ticket-update-sla">Reply overdue</div><div class="ticket-update-sla">(' . $helper->getTimestamp($ticket['reply_due_at']) . ')</div>';
                    else:
                        $html .= '<div class="ticket-update-sla">Reply due  at<br />' . $helper->getTimestamp($ticket['reply_due_at']) . '</div>';
                    endif;
                endif;
                if (!empty($ticket['resolution_due_at'])):
                    $due = strtotime($ticket['resolution_due_at']);
                    if ($now > $due):
                        $html .= '<div class="ticket-update-overdue ticket-update-sla">Resolution overdue</div><div class="ticket-update-sla">(' . $helper->getTimestamp($ticket['resolution_due_at']) . ')</div>';
                    else:
                        $html .= '<div class="ticket-update-sla">Resolution due at<br />' . $helper->getTimestamp($ticket['resolution_due_at']) . '</div>';
                    endif;
                endif;
            endif;
        endif;
        
        return $html;
    }
    
    public function canCreate()
    {
        if ($restrictions = Mage::helper('sirportly')->getUserRestrictions()):
            if ($restrictions['permissions'] == 'full'):
                return true;
            endif;
        endif;
        
        return false;
    }
    
    public function getSirportlyDomain()
    {
        if (!isset($this->_domain)):
            $this->_domain = false;
            if ($domain = Mage::getStoreConfig('sirportly/general/domain')):
                $domain = preg_replace('/http(s)?:\/\//', '', $domain);
                $domain = trim($domain, '/');
                if ($domain)  $this->_domain = $domain;
            endif;
        endif;
        
        return $this->_domain;
    }
}