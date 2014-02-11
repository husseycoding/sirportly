<?php
class HusseyCoding_Sirportly_Block_Adminhtml_Sales_Order_View_Tab_SirportlyTickets_UpdateTicket_Responses extends Mage_Adminhtml_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sirportly/sales/order/view/tab/sirportlytickets/updateticket/responses.phtml');
    }
    
    public function _beforeToHtml()
    {
        if ($department = $this->getRequest()->getPost('department')):
            $this->setDepartment((int) $department);
        elseif ($reference = Mage::app()->getRequest()->getPost('reference')):
            $data = Mage::helper('sirportly')->getTicketTeamUserDepartmentSla($reference);
            if (!empty($data['department'])):
                $this->setDepartment($data['department']);
            endif;
        endif;
    }
    
    public function getResponses()
    {
        if ($this->getDepartment()):
            $helper = Mage::helper('sirportly');
            return $helper->getResponses($this->getDepartment());
        endif;
    }
}