<?php
class HusseyCoding_Sirportly_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_verified;
    
    public function verifyCredentials($token = null, $secret = null)
    {
        if (!isset($this->_verified)):
            $client = $this->_getRequestObject('/api/v2/tickets/all', null, $token, $secret);
            $this->_verified = $this->_sendRequest($client);
        endif;
        
        return $this->_verified;
    }
    
    public function createTicket(array $data, $config = 'ticketassign')
    {
        if ($this->_canSend($data)):
            $status = $this->_getConfig($config, 'status');
            $priority = $this->_getConfig($config, 'priority');
            $team = $this->_getConfig($config, 'team');
            $department = $this->_getConfig($config, 'department');
            
            $params = array(
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'],
                'status' => $status,
                'priority' => $priority,
                'team' => $team,
                'department' => $department
            );
            
            if (!empty($data['html_body']) && !empty($data['html_safe'])):
                $client = $this->_getRequestObject('/api/v2/tickets/submit', $params);
                if ($result = $this->_sendRequest($client, true)):
                    if (!empty($result['reference'])):
                        $params = array();
                        $params['html_body'] = $data['html_body'];
                        $params['html_safe'] = $data['html_safe'];
                        $params['message'] = $data['comment'];
                        $params['ticket'] = $result['reference'];
                        $client = $this->_getRequestObject('/api/v2/tickets/post_update', $params);
                        return $this->_sendRequest($client);
                    endif;
                endif;
            else:
                $params['message'] = $data['comment'];
                $client = $this->_getRequestObject('/api/v2/tickets/submit', $params);
                return $this->_sendRequest($client);
            endif;
        endif;
        
        return false;
    }
    
    private function _getRequestObject($url, $params = null, $token = null, $secret = null)
    {
        if (!isset($token)):
            $token = Mage::getStoreConfig('sirportly/api/token');
        endif;
        
        if (!isset($secret)):
            $secret = Mage::getStoreConfig('sirportly/api/secret');
        endif;
        
        if (isset($url)):
            $url = '/' . trim($url, '/');
            $url = $this->_getApiUrl() . $url;
        else:
            $url = $this->_getApiUrl();
        endif;
        
        $client = new Zend_Http_Client();
        $client
            ->setUri($url)
            ->setMethod(Zend_Http_Client::POST)
            ->setHeaders(array(
                'X-Auth-Token' => $token,
                'X-Auth-Secret' => $secret
            ));
        
        $client
            ->setConfig(array(
                'timeout' => 5
            ));
        
        $params = isset($params) ? $params : array();
        
        foreach ($params as $key => $value):
            $client->setParameterPost($key, $value);
        endforeach;
        
        return $client;
    }
    
    private function _getApiUrl()
    {
        return 'https://api.sirportly.com';
    }
    
    private function _sendRequest($client, $sendbody = false)
    {
        try {
            $result = $client->request();
        } catch (Exception $e) {
            return false;
        }
        $body = $result->getBody();
        $body = Zend_Json::decode($body);
        
        if (array_key_exists('error', $body) || array_key_exists('errors', $body)):
            return false;
        endif;
        
        return $sendbody ? $body : true;
    }
    
    public function getSelectOptions($url, $optgroup = false, $empty = '--Please Select--')
    {
        if (!$this->verifyCredentials()):
            return array(
                array('value' => '', 'label' => $this->__('Invalid API Credentials'))
            );
        else:
            $client = $this->_getRequestObject($url);
            if ($result = $this->_sendRequest($client, true)):
                $return = array();
                if ($result):
                    if ($optgroup):
                        $return = $this->_getOptGroups($result, $optgroup);
                    else:
                        foreach ($result as $option):
                            if (!empty($option['name']) && !empty($option['id'])):
                                $return[] = array('value' => $option['id'], 'label' => $option['name']);
                            endif;
                        endforeach;
                    endif;
                endif;

                if ($return):
                    array_unshift($return, array('value' => '', 'label' => $this->__($empty)));
                    return $return;
                endif;
            endif;

            return array(
                array('value' => '', 'label' => $this->__('No Options Found'))
            );
        endif;
    }
    
    private function _getOptGroups($options, $optgroup)
    {
        $groups = array();
        foreach ($options as $option):
            if (!empty($option['name']) && !empty($option['id'])):
                if (isset($option[$optgroup]) && is_array($option[$optgroup])):
                    if (!empty($option[$optgroup]['name']) && !empty($option[$optgroup]['id'])):
                        $id = $option[$optgroup]['id'];
                        $value = $option[$optgroup]['name'];
                        if (!isset($groups[$id]['value'])):
                            $groups[$id]['value'] = array();
                        endif;
                        if (!isset($groups[$id]['label'])):
                            $groups[$id]['label'] = $value;
                        endif;
                        $groups[$id]['value'][] = array('value' => $option['id'], 'label' => $option['name']);
                    endif;
                endif;
            endif;
        endforeach;
        
        return $groups;
    }
    
    private function _canSend($data)
    {
        if (!empty($data['name']) && !empty($data['email']) && !empty($data['subject'])):
            if (Mage::getStoreConfig('sirportly/ticketassign/status') && Mage::getStoreConfig('sirportly/ticketassign/priority') && Mage::getStoreConfig('sirportly/ticketassign/team') && Mage::getStoreConfig('sirportly/ticketassign/department')):
                return true;
            endif;
        endif;
        
        return false;
    }
    
    private function _getConfig($config, $field)
    {
        $return = Mage::getStoreConfig('sirportly/' . $config . '/' .  $field);
        return !empty($return) ? $return : Mage::getStoreConfig('sirportly/ticketassign/' .  $field);
    }
    
    public function getTicketsByEmail($email)
    {
        if ($id = $this->_getContactIdByEmail($email)):
            $params = array('contact' => $id, 'sort_by' => 'updated_at', 'order' => 'desc');
            $client = $this->_getRequestObject('/api/v2/tickets/contact', $params);
            $result = $this->_sendRequest($client, true);
            
            if (!empty($result['records'])):
                $tickets = $result['records'];
                $pages = (int) $result['pagination']['pages'];
                if ($pages > 1):
                    $current = 1;
                    while ($pages >= $current):
                        $current++;
                        $params = array('contact' => $id, 'sort_by' => 'updated_at', 'order' => 'desc', 'page' => $current);
                        $client = $this->_getRequestObject('/api/v2/tickets/contact', $params);
                        $result = $this->_sendRequest($client, true);
                        if (!empty($result['records'])):
                            $tickets = array_merge($tickets, $result['records']);
                        endif;
                        
                        if ($current >= 10 || empty($result['records'])):
                            break;
                        endif;
                    endwhile;
                endif;
                
                return $tickets;
            endif;
        endif;
        
        return array();
    }
    
    private function _getContactIdByEmail($email)
    {
        $params = array('query' => $email, 'limit' => 1, 'types' => 'email');
        $client = $this->_getRequestObject('/api/v2/contacts/search', $params);
        $result = $this->_sendRequest($client, true);
        
        if (!empty($result[0]['contact']['id'])):
            return $result[0]['contact']['id'];
        endif;
        
        return false;
    }
    
    public function getTicketUpdates($reference)
    {
        $params = array('ticket' => $reference);
        $client = $this->_getRequestObject('/api/v2/ticket_updates/all', $params);
        return $this->_sendRequest($client, true);
    }
}