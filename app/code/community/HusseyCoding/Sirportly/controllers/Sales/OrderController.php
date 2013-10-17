<?php
include_once("Mage/Adminhtml/controllers/Sales/OrderController.php");
class HusseyCoding_Sirportly_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    public function sirportlyTicketsAction()
    {
        $this->_initOrder();
        $html = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets')->toHtml();
        $translate = Mage::getModel('core/translate_inline');
        if ($translate->isAllowed()) {
            $translate->processResponseBody($html);
        }
        $this->getResponse()->setBody($html);
    }
    
    public function sirportlyUpdatesAction()
    {
        $result = 'failed';
        $updates = false;
        if ($reference = $this->getRequest()->getParam('reference')):
            if ($updates = Mage::helper('sirportly')->getTicketUpdates($reference)):
                $result = 'success';
                $updates = $this->_buildUpdateHtml($updates);
            endif;
        endif;
        
        $this->getResponse()->setBody(Zend_Json::encode(array('result' => $result, 'updates' => $updates)));
    }
    
    private function _buildUpdateHtml($updates)
    {
        $return = array();
        $count = 0;
        foreach ($updates as $update):
            $timestamp = strtotime($update['posted_at']);
            $timestamp = date('M jS, Y, g:i a', $timestamp);
            $return[$count]['timestamp'] = $timestamp;
            if (!empty($update['author']['name'])):
                $return[$count]['name'] = $update['author']['name'];
            elseif (!empty($update['author']['first_name']) && !empty($update['author']['last_name'])):
                $return[$count]['name'] = $update['author']['first_name'] . ' ' . $update['author']['last_name'];
            else:
                $return[$count]['name'] = 'Unknown';
            endif;
            $return[$count]['subject'] = $update['subject'];
            $return[$count]['message'] = nl2br($update['message']);
            $count++;
        endforeach;
        
        return $return;
    }
}