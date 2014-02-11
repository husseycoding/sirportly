<?php
class HusseyCoding_Sirportly_Block_Adminhtml_Sales_Order_View_Tab_SirportlyTickets_UpdateTicket_Users extends Mage_Adminhtml_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sirportly/sales/order/view/tab/sirportlytickets/updateticket/users.phtml');
    }
    
    public function _beforeToHtml()
    {
        if ($team = $this->getRequest()->getPost('team')):
            $this->setTeam((int) $team);
            if ($reference = Mage::app()->getRequest()->getPost('reference')):
                $data = Mage::helper('sirportly')->getTicketTeamUserDepartmentSla($reference);
                if (!empty($data['user'])):
                    $this->setUser($data['user']);
                endif;
            endif;
        elseif ($reference = Mage::app()->getRequest()->getPost('reference')):
            $data = Mage::helper('sirportly')->getTicketTeamUserDepartmentSla($reference);
            if (!$this->getTeam()):
                if (!empty($data['team'])):
                    $this->setTeam($data['team']);
                endif;
            endif;
            if (!empty($data['user'])):
                $this->setUser($data['user']);
            endif;
        endif;
    }
    
    public function getUsers()
    {
        if ($this->getTeam()):
            $helper = Mage::helper('sirportly');
            $options = $helper->getSelectOptions('/api/v2/objects/users', false, '', array('id' => array('first_name', 'last_name')));

            $options = $helper->filterUsersByTeam($options, $this->getTeam());
            
            $user = $this->getUser();
            if (!empty($user)):
                foreach ($options as $option):
                    if ($option['value'] == $user):
                        array_unshift($options, array('value' => $user, 'label' => '--Unchanged--'));
                        break;
                    endif;
                endforeach;
            endif;
            
            if (!$this->canEditUser()):
                $options = $this->filterUsersByUser($options);
            endif;
            
            return $options;
        endif;
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