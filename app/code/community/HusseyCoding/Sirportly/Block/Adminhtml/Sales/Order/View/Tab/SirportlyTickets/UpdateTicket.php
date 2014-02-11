<?php
class HusseyCoding_Sirportly_Block_Adminhtml_Sales_Order_View_Tab_SirportlyTickets_UpdateTicket extends Mage_Adminhtml_Block_Template
{
    private $_order;
    
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sirportly/sales/order/view/tab/sirportlytickets/updateticket.phtml');
    }
    
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('sirportly')->__('Submit Update'),
                'class' => 'save'
            ));
        $this->setChild('submit_button', $button);
        
        $teams = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_updateTicket_teams');
        $this->setChild('order_tab_sirportly_updateticket_teams', $teams);
        
        $users = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_updateTicket_users');
        $this->setChild('order_tab_sirportly_updateticket_users', $users);
        
        $responses = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_updateTicket_responses');
        $this->setChild('order_tab_sirportly_updateticket_responses', $responses);
        
        return parent::_prepareLayout();
    }
    
    public function _beforeToHtml()
    {
        if ($reference = Mage::app()->getRequest()->getPost('reference')):
            $data = Mage::helper('sirportly')->getTicketTeamUserDepartmentSla($reference);
            if (!empty($data['department'])):
                $this->setDepartment($data['department']);
            endif;
            if (!empty($data['sla'])):
                $this->setSla($data['sla']);
            endif;
        endif;
    }
    
    public function getStatuses()
    {
        return Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/statuses', false, '--Unchanged--');
    }
    
    public function getPriorities()
    {
        return Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/priorities', false, '--Unchanged--');
    }
    
    public function getSlas()
    {
        $options = Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/slas', false, '');
        array_unshift($options, array('value' => '', 'label' => 'None'));
        
        $sla = $this->getSla();
        if (!empty($sla)):
            foreach ($options as $option):
                if ($option['value'] == $sla):
                    array_unshift($options, array('value' => $sla, 'label' => '--Unchanged--'));
                endif;
            endforeach;
        endif;
        
        return $options;
    }
    
    public function getDepartments()
    {
        $options = Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/departments', 'brand', '');
        
        $department = $this->getDepartment();
        if (!empty($department)):
            $break = false;
            foreach ($options as $option):
                foreach ($option['value'] as $item):
                    if ($item['value'] == $department):
                        array_unshift($options, array('value' => $department, 'label' => '--Unchanged--'));
                        $break = true;
                        break;
                    endif;
                endforeach;
                if ($break) break;
            endforeach;
        endif;
        
        if (!$this->canEditUser()):
            $options = $this->filterDepartmentsByUser($options);
        endif;
        
        return $options;
    }
    
    public function verifyCredentials()
    {
        return Mage::helper('sirportly')->verifyCredentials();
    }
    
    public function getOrder()
    {
        if (!isset($this->_order)):
            if ($orderid = (int) $this->getOrderId()):
                $order = Mage::getModel('sales/order')->load($orderid);
                if ($order->getId()):
                    $this->_order = $order;
                endif;
            endif;
        endif;
        
        return $this->_order;
    }
    
    public function getCustomerEmail()
    {
        return $this->getOrder()->getCustomerEmail();
    }
    
    public function getCustomerName()
    {
        return $this->getOrder()->getCustomerName();
    }
    
    public function getReference()
    {
        return parent::getReference();
    }
    
    public function getOrderId()
    {
        return parent::getOrderId();
    }
    
    public function getSubject()
    {
        return parent::getSubject();
    }
    
    public function canUpdate()
    {
        if ($restrictions = Mage::helper('sirportly')->getUserRestrictions()):
            if ($restrictions['permissions'] == 'full'):
                return true;
            endif;
        endif;
        
        return false;
    }
    
    public function canEditUser()
    {
        if ($restrictions = Mage::helper('sirportly')->getUserRestrictions()):
            if ($restrictions['user']):
                return false;
            endif;
        endif;
        
        return true;
    }
    
    public function filterDepartmentsByUser($options)
    {
        return Mage::helper('sirportly')->filterDepartmentsByUser($options);
    }
}
