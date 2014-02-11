<?php
class HusseyCoding_Sirportly_Block_Adminhtml_Sales_Order_View_Tab_SirportlyTickets_NewTicket_Teams extends Mage_Adminhtml_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sirportly/sales/order/view/tab/sirportlytickets/newticket/teams.phtml');
    }
    
    public function _beforeToHtml()
    {
        if ($department = Mage::app()->getRequest()->getPost('department')):
            $this->setDepartment((int) $department);
        endif;
    }
    
    public function getTeams()
    {
        $helper = Mage::helper('sirportly');
        $options = $helper->getSelectOptions('/api/v2/objects/teams', false, '');
        $options = $this->_addUseAdmin($options);
        $options = $helper->filterTeamsByDepartment($options, $this->getDepartment());
        
        if (!$this->canEditUser()):
            $options = $this->filterTeamsByUser($options);
        endif;
        
        $option = reset($options);
        if (!empty($option['value'])):
            if ($parent = $this->getParentBlock()):
                $this->getParentBlock()->getChild('order_tab_sirportly_newticket_users')->setTeam($option['value']);
            else:
                $this->setTeam($option['value']);
            endif;
        endif;
        
        return $options;
    }
    
    private function _addUseAdmin($options)
    {
        if ($default = (int) Mage::getStoreConfig('sirportly/orderscreen/team')):
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
    
    public function canEditUser()
    {
        if ($restrictions = Mage::helper('sirportly')->getUserRestrictions()):
            if ($restrictions['user']):
                return false;
            endif;
        endif;
        
        return true;
    }
    
    public function filterTeamsByUser($options)
    {
        return Mage::helper('sirportly')->filterTeamsByUser($options);
    }
}