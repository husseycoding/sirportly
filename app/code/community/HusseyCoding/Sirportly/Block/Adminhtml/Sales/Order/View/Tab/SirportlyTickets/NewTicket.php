<?php
class HusseyCoding_Sirportly_Block_Adminhtml_Sales_Order_View_Tab_SirportlyTickets_NewTicket extends Mage_Adminhtml_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sirportly/sales/order/view/tab/sirportlytickets/newticket.phtml');
    }
    
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('sirportly')->__('Submit Ticket'),
                'class' => 'save'
            ));
        $this->setChild('submit_button', $button);
        
        $teams = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_newTicket_teams');
        $this->setChild('order_tab_sirportly_newticket_teams', $teams);
        
        $users = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_newTicket_users');
        $this->setChild('order_tab_sirportly_newticket_users', $users);
        
        return parent::_prepareLayout();
    }
    
    public function getStatuses()
    {
        $options = Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/statuses', false, '');
        
        return $this->_addUseAdmin('status', $options);
    }
    
    public function getPriorities()
    {
        $options = Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/priorities', false, '');
        
        return $this->_addUseAdmin('priority', $options);
    }
    
    public function getSlas()
    {
        $options = Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/slas', false, '');
        array_unshift($options, array('value' => '', 'label' => 'None'));
        
        return $this->_addUseAdmin('sla', $options);
    }
    
    public function getDepartments()
    {
        $options = Mage::helper('sirportly')->getSelectOptions('/api/v2/objects/departments', 'brand', '');
        if ($default = (int) Mage::getStoreConfig('sirportly/orderscreen/department')):
            $label = '';
            $break = false;
            foreach ($options as $option):
                if (!empty($option['label'])):
                    $partial = $option['label'] . ' &rarr; ';
                    if (!empty($option['value']) && is_array($option['value'])):
                        foreach ($option['value'] as $entry):
                            if (!empty($entry['value']) && $entry['value'] == $default):
                                if (!empty($entry['label'])):
                                    $label = $partial . $entry['label'];
                                    $break = true;
                                    break;
                                endif;
                            endif;
                        endforeach;
                        if ($break) break;
                    endif;
                endif;
            endforeach;
            
            if ($label):
                array_unshift($options, array('value' => $default, 'label' => 'Default (' . $label . ')'));
            endif;
        endif;
        
        if (!$this->canEditUser()):
            $options = $this->filterDepartmentsByUser($options);
        endif;
        
        $option = reset($options);
        if (!empty($option['value'])):
            if (is_array($option['value'])):
                foreach ($option['value'] as $department):
                    if (!empty($department['value'])):
                        $value = $department['value'];
                        break;
                    endif;
                endforeach;
            else:
                $value = $option['value'];
            endif;
            if (!empty($value)):
                $this->getChild('order_tab_sirportly_newticket_teams')->setDepartment($value);
            endif;
        endif;
        
        return $options;
    }
    
    private function _addUseAdmin($config, $options)
    {
        if ($default = (int) Mage::getStoreConfig('sirportly/orderscreen/' . $config)):
            $label = '';
            foreach ($options as $option):
                if (!empty($option['label']) && !empty($option['value']) && $option['value'] == $default):
                    $label = $option['label'];
                    break;
                endif;
            endforeach;
            
            if ($label):
                array_unshift($options, array('value' => $default, 'label' => 'Default (' . $label . ')'));
            endif;
        endif;
        
        return $options;
    }
    
    public function verifyCredentials()
    {
        return Mage::helper('sirportly')->verifyCredentials();
    }
    
    public function getOrder()
    {
        return Mage::registry('current_order');
    }
    
    public function getCustomerEmail()
    {
        return $this->getOrder()->getCustomerEmail();
    }
    
    public function getCustomerName()
    {
        return $this->getOrder()->getCustomerName();
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
