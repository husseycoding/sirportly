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
        $html = false;
        $reference = false;
        $orderid = false;
        if ($reference = $this->getRequest()->getParam('reference')):
            if ($orderid = $this->getRequest()->getParam('orderid')):
                if ($updates = Mage::helper('sirportly')->getTicketUpdates($reference)):
                    $result = 'success';
                    $updates = $this->_buildUpdateHtml($updates);

                    $html = '';
                    $subject = '';
                    if ($this->_canUpdate()):
                        $html .= '<a id="sirportly-ticket-update-' . $reference . '" href="#sirportly-ticket-update-popup-' . $reference . '">Update ticket</a>';
                        if ($this->_canReassign()):
                            $html .= '<a class="ticket-reassign" id="sirportly-ticket-reassign-' . $reference . '" href="#sirportly-ticket-reassign-popup-' . $reference . '">Reassign ticket</a>';
                        endif;
                    endif;
                    foreach ($updates as $update):
                        $html .= '<div class="ticket-update ticket-update-timestamp"><span>Timestamp:</span>' . $update['timestamp'] . '</div>';
                        $html .= '<div class="ticket-update ticket-update-name"><span>Author:</span>' . $update['name'] . '</div>';
                        $html .= '<div class="ticket-update ticket-update-subject"><span>Subject:</span>' . $update['subject'] . '</div>';
                        $html .= '<div class="ticket-update ticket-update-message">' . $update['message'] . '</div>';
                        if ($update['private'] && $update['id']):
                            $html .= '<div class="ticket-update-private">This is a private update</div>';
                            if ($this->_canUpdate()):
                                $html .= '<a href="javascript:void(0)" id="' . $reference . '_' . $update['id'] . '" class="ticket-update-delete">Delete</a>';
                            endif;
                        endif;
                        if ($update['delivered']):
                            $html .= '<div class="ticket-update-delivered">Delivered to customer</div>';
                        endif;
                        $subject = $update['subject'];
                    endforeach;
                    
                    if ($this->_canUpdate()):
                        $updateticket = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_updateTicket');
                        $updateticket->setReference($reference);
                        $updateticket->setOrderId($orderid);
                        $updateticket->setSubject($subject);
                        $html .= $updateticket->toHtml();
                        if ($this->_canReassign()):
                            $reassignticket = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_reassignTicket');
                            $reassignticket->setReference($reference);
                            $reassignticket->setOrderId($orderid);
                            $reassignticket->setSubject($subject);
                            $html .= $reassignticket->toHtml();
                        endif;
                    endif;
                endif;
            endif;
        endif;
        
        $this->getResponse()->setBody(Zend_Json::encode(array('result' => $result, 'html' => $html)));
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
            $return[$count]['private'] = $update['private'];
            $return[$count]['id'] = $update['id'];
            $return[$count]['delivered'] = !empty($update['delivered']) && $update['delivered'] ? true : false;
            $count++;
        endforeach;
        
        return $return;
    }
    
    public function sirportlyNewTicketAction()
    {
        if (Mage::helper('sirportly')->createTicket($this->getRequest()->getPost())):
            $response = Zend_Json::encode(array('status' => 'success'));
        else:
            $response = Zend_Json::encode(array('status' => 'failed'));
        endif;
        
        $this->getResponse()->setBody($response);
    }
    
    public function sirportlyUpdateTicketAction()
    {
        $result = Mage::helper('sirportly')->addUpdate($this->getRequest()->getPost());
        if ($result === 'empty'):
            $response = Zend_Json::encode(array('status' => 'empty'));
        elseif ($result):
            $response = Zend_Json::encode(array('status' => 'success'));
        else:
            $response = Zend_Json::encode(array('status' => 'failed'));
        endif;
        
        $this->getResponse()->setBody($response);
    }
    
    public function sirportlyReassignTicketAction()
    {
        $result = Mage::helper('sirportly')->reassignTicket($this->getRequest()->getPost());
        if ($result === 'empty'):
            $response = Zend_Json::encode(array('status' => 'empty'));
        elseif ($result):
            $response = Zend_Json::encode(array('status' => 'success'));
        else:
            $response = Zend_Json::encode(array('status' => 'failed'));
        endif;
        
        $this->getResponse()->setBody($response);
    }
    
    public function sirportlyTeamsAction()
    {
        $type = $this->getRequest()->getPost("type");
        if ($type == "new"):
            $teams = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_newTicket_teams');
            $users = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_newTicket_users');
        elseif ($type == "reassign"):
            $teams = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_reassignTicket_teams');
            $users = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_reassignTicket_users');
        else:
            $teams = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_updateTicket_teams');
            $users = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_updateTicket_users');
            $responses = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_updateTicket_responses');
        endif;
        
        $teamshtml = $teams->toHtml();
        $users->setTeam($teams->getTeam());
        $usershtml = $users->toHtml();
        
        $return = array('teamshtml' => $teamshtml, 'usershtml' => $usershtml);
        
        if (isset($responses)):
            $responseshtml = $responses->toHtml();
            $return['responseshtml'] = $responseshtml;
        endif;
        
        $response = Zend_Json::encode($return);
        
        $this->getResponse()->setBody($response);
    }
    
    public function sirportlyUsersAction()
    {
        $type = $this->getRequest()->getPost("type");
        if ($type == "new"):
            $users = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_newTicket_users');
        elseif ($type == "reassign"):
            $users = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_reassignTicket_users');
        else:
            $users = $this->getLayout()->createBlock('sirportly/adminhtml_sales_order_view_tab_sirportlyTickets_updateTicket_users');
        endif;
        
        $this->getResponse()->setBody($users->toHtml());
    }
    
    public function sirportlyDeleteUpdateAction()
    {
        $result = Mage::helper('sirportly')->deleteUpdate($this->getRequest()->getPost());
        if ($result):
            $response = Zend_Json::encode(array('status' => 'success'));
        else:
            $response = Zend_Json::encode(array('status' => 'failed'));
        endif;
        
        $this->getResponse()->setBody($response);
    }
    
    private function _canUpdate()
    {
        if ($restrictions = Mage::helper('sirportly')->getUserRestrictions()):
            if ($restrictions['permissions'] == 'full'):
                return true;
            endif;
        endif;
        
        return false;
    }
    
    private function _canReassign()
    {
        if ($restrictions = Mage::helper('sirportly')->getUserRestrictions()):
            if ($restrictions['user']):
                if ($restrictions['view'] == 'user'):
                    return true;
                else:
                    $reference = $this->getRequest()->getParam('reference');
                    $data = Mage::helper('sirportly')->getTicketTeamUserDepartmentSla($reference);
                    if (!empty($data['user']) && $data['user'] == $restrictions['user']):
                        return true;
                    endif;
                endif;
            endif;
        endif;
        
        return false;
    }
}