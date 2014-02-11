<?php
class HusseyCoding_Sirportly_Block_Adminhtml_Sales_Order_View_Tab_SirportlyTickets_NewTicket_Users extends Mage_Adminhtml_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sirportly/sales/order/view/tab/sirportlytickets/newticket/users.phtml');
    }
    
    public function _beforeToHtml()
    {
        if ($team = Mage::app()->getRequest()->getPost('team')):
            $this->setTeam((int) $team);
        endif;
    }
    
    public function getUsers()
    {
        $helper = Mage::helper('sirportly');
        $options = $helper->getSelectOptions('/api/v2/objects/users', false, '', array('id' => array('first_name', 'last_name')));
        $options = $helper->filterUsersByTeam($options, $this->getTeam());
        
        if (!$this->canEditUser()):
            $options = $this->filterUsersByUser($options);
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
    
    public function filterUsersByUser($options)
    {
        return Mage::helper('sirportly')->filterUsersByUser($options);
    }
}