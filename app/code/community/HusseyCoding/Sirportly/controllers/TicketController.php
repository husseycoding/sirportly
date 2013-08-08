<?php
class HusseyCoding_Sirportly_TicketController extends Mage_Core_Controller_Front_Action
{
    public function createAction()
    {
        if (Mage::helper('sirportly')->createTicket($this->getRequest()->getPost())):
            Mage::getSingleton('customer/session')->addSuccess(Mage::helper('sirportly')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
            $response = Zend_Json::encode(array('status' => 'success'));
        else:
            $response = Zend_Json::encode(array('status' => 'failed'));
        endif;
        
        $this->getResponse()->setBody($response);
    }
}