<?php
class HusseyCoding_Sirportly_Block_Adminhtml_Sales_Order_View_Tab_SirportlyTickets_ReassignTicket_Teams extends Mage_Adminhtml_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sirportly/sales/order/view/tab/sirportlytickets/reassignticket/teams.phtml');
    }
    
    public function _beforeToHtml()
    {
        if ($department = $this->getRequest()->getPost('department')):
            $this->setDepartment((int) $department);
            if ($reference = Mage::app()->getRequest()->getPost('reference')):
                $data = Mage::helper('sirportly')->getTicketTeamUserDepartmentSla($reference);
                if (!empty($data['team'])):
                    $this->setTeam($data['team']);
                endif;
            endif;
        elseif ($reference = Mage::app()->getRequest()->getPost('reference')):
            $data = Mage::helper('sirportly')->getTicketTeamUserDepartmentSla($reference);
            if (!empty($data['department'])):
                $this->setDepartment($data['department']);
            endif;
            if (!empty($data['team'])):
                $this->setTeam($data['team']);
            endif;
        endif;
    }
    
    public function getTeams()
    {
        if ($this->getDepartment()):
            $helper = Mage::helper('sirportly');
            $options = $helper->getSelectOptions('/api/v2/objects/teams', false, '');

            $options = $helper->filterTeamsByDepartment($options, $this->getDepartment());
            
            $team = $this->getTeam();
            if (!empty($team)):
                foreach ($options as $option):
                    if ($option['value'] == $team):
                        array_unshift($options, array('value' => $team, 'label' => '--Unchanged--'));
                        break;
                    endif;
                endforeach;
            endif;
            
            $option = reset($options);
            if (!empty($option['value'])):
                $this->setTeam($option['value']);
            endif;
            
            return $options;
        endif;
    }
}