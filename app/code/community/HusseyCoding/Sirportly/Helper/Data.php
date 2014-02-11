<?php
class HusseyCoding_Sirportly_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_verified;
    private $_canchange;
    private $_adminuser;
    private $_userdata;
    private $_restrictions;
    private $_isconfigured;
    
    
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
            $status = !empty($data['status']) ? $data['status'] : $this->_getConfig($config, 'status');
            $priority = !empty($data['priority']) ? $data['priority'] : $this->_getConfig($config, 'priority');
            $team = !empty($data['team']) ? $data['team'] : $this->_getConfig($config, 'team');
            $department = !empty($data['department']) ? $data['department'] : $this->_getConfig($config, 'department');
            
            $params = array(
                'contact_name' => $data['name'],
                'contact_method_type' => 'email',
                'contact_method_data' => $data['email'],
                'contact_email' => $data['email'],
                'subject' => $data['subject'],
                'status' => $status,
                'priority' => $priority,
                'team' => $team,
                'department' => $department
            );
            
            if (!empty($data['sla'])) $params['sla'] = $data['sla'];
            
            $client = $this->_getRequestObject('/api/v2/tickets/submit', $params);
            if ($result = $this->_sendRequest($client, true)):
                if (!empty($result['reference'])):
                    $params = array();
                    
                    if (!empty($data['notify']) && $data['notify'] == 'on'):
                        $client = $this->_getRequestObject('/api/v2/objects/email_addresses', array('department' => $department));
                        $departments = $this->_sendRequest($client, true);

                        if (!empty($departments['department']) && is_array($departments['department'])):
                            foreach ($departments['department'] as $item):
                                if (!empty($item['address'])):
                                    $params['outgoing'] = $item['address'];
                                    $params['user'] = $data['user'];
                                endif;
                            endforeach;
                        endif;
                    endif;
                    
                    if (!empty($data['html_body']) && !empty($data['html_safe'])):
                        $params['html_body'] = $data['html_body'];
                        $params['html_safe'] = $data['html_safe'];
                        $params['message'] = $data['comment'];
                        $params['ticket'] = $result['reference'];
                        $client = $this->_getRequestObject('/api/v2/tickets/post_update', $params);
                    else:
                        $params['ticket'] = $result['reference'];
                        $params['message'] = $data['comment'];
                        $client = $this->_getRequestObject('/api/v2/tickets/post_update', $params);
                    endif;
                    
                    return $this->_sendRequest($client);
                endif;
            endif;
        endif;
        
        return false;
    }
    
    public function addUpdate(array $data)
    {
        $change = false;
        $update = false;
        if ($this->_canUpdate($data) || $this->_canChange($data)):
            if ($params = $this->_canChange($data)):
                $client = $this->_getRequestObject('/api/v2/tickets/update', $params);
                $change = $this->_sendRequest($client);
            endif;
            if ($this->_canUpdate($data)):
                $params = array();
                $params['ticket'] = $data['ticket'];
                $params['message'] = $data['comment'];
                $params['parse_ticket_variables'] = true;
                $params['user'] = $data['user'];
                if (!empty($data['subject'])) $params['subject'] = $data['subject'];
                if (!empty($data['private'])) $params['private'] = true;
                
                if (!empty($data['notify']) && $data['notify'] == 'on'):
                    $client = $this->_getRequestObject('/api/v2/objects/email_addresses', array('department' => $data['department']));
                    $result = $this->_sendRequest($client, true);
                
                    if (!empty($result['department']) && is_array($result['department'])):
                        foreach ($result['department'] as $department):
                            if (!empty($department['address'])):
                                $params['outgoing'] = $department['address'];
                            endif;
                        endforeach;
                    endif;
                endif;
                
                $client = $this->_getRequestObject('/api/v2/tickets/post_update', $params);
                $update = $this->_sendRequest($client);
            endif;
        endif;
        
        if (($this->_canChange($data) && $change) || ($this->_canUpdate($data) && $update)):
            return true;
        elseif ($this->_canChange($data) || $this->_canUpdate($data)):
            return false;
        endif;
            
        return 'empty';
    }
    
    public function reassignTicket(array $data)
    {
        if ($restrictions = $this->getUserRestrictions()):
            if ($restrictions['user'] && $restrictions['permissions'] == 'full'):
                $change = false;
                $update = false;
                if ($params = $this->_canChange($data)):
                    $client = $this->_getRequestObject('/api/v2/tickets/update', $params);
                    $change = $this->_sendRequest($client);
                endif;

                if ($this->_canChange($data) && $change):
                    return true;
                elseif ($this->_canChange($data)):
                    return false;
                endif;

                return 'empty';
            endif;
        endif;
        
        return false;
    }
    
    public function deleteUpdate(array $data)
    {
        if (!empty($data['ticket']) && !empty($data['update'])):
            $params = array();
            $params['ticket'] = $data['ticket'];
            $params['update'] = $data['update'];
            $client = $this->_getRequestObject('/api/v2/ticket_updates/destroy', $params);
            
            return $this->_sendRequest($client);
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
    
    public function getSelectOptions($url, $optgroup = false, $empty = '--Please Select--', $keys = false)
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
                            elseif ($keys && is_array($keys)):
                                foreach ($keys as $key => $values):
                                    if (!is_array($values)) $values = array($values);
                                    $data = '';
                                    foreach ($values as $value):
                                        if (!empty($option[$value])):
                                            $data .= $option[$value] . ' ';
                                        endif;
                                    endforeach;
                                    $data = trim($data);
                                    if ($data && $option[$key]):
                                        $return[] = array('value' => $option[$key], 'label' => $data);
                                    endif;
                                endforeach;
                            endif;
                        endforeach;
                    endif;
                endif;

                if ($return):
                    if ($empty) array_unshift($return, array('value' => '', 'label' => $this->__($empty)));
                    return $return;
                endif;
            endif;

            return array(
                array('value' => '', 'label' => $this->__('No Options Found'))
            );
        endif;
    }
    
    public function filterUsersByTeam($options, $selectedteam)
    {
        $users = array();
        $validusers = array();
        foreach ($options as $user):
            if (!empty($user['value'])) $users[] = $user['value'];
        endforeach;
        if ($users):
            $client = $this->_getRequestObject('/api/v2/objects/teams');
            if ($result = $this->_sendRequest($client, true)):
                foreach ($result as $team):
                    if ($team['id'] == $selectedteam):
                        foreach ($team['users'] as $user):
                            $validusers[] = $user['id'];
                        endforeach;
                        break;
                    endif;
                endforeach;
            endif;
            
            if ($validusers):
                foreach ($options as $key => $user):
                    if (!in_array($user['value'], $validusers)):
                        unset($options[$key]);
                    endif;
                endforeach;
            endif;
        endif;
        
        return $options;
    }
    
    public function filterTeamsByDepartment($options, $selecteddepartment)
    {
        $teams = array();
        $validteams = array();
        foreach ($options as $team):
            if (!empty($team['value'])) $teams[] = $team['value'];
        endforeach;
        if ($teams):
            $client = $this->_getRequestObject('/api/v2/objects/departments');
            if ($result = $this->_sendRequest($client, true)):
                $break = false;
                foreach ($result as $department):
                    if ($department['id'] == $selecteddepartment):
                        $selectedpath = $department['escalation_path']['id'];
                        $client = $this->_getRequestObject('/api/v2/objects/escalation_paths');
                        if ($result = $this->_sendRequest($client, true)):
                            foreach ($result as $path):
                                if ($path['id'] == $selectedpath):
                                    foreach ($path['teams'] as $team):
                                        $validteams[] = $team['id'];
                                    endforeach;
                                    $break = true;
                                    break;
                                endif;
                            endforeach;
                        endif;
                    endif;
                    if ($break) break;
                endforeach;
            endif;
            
            if ($validteams):
                foreach ($options as $key => $team):
                    if (!in_array($team['value'], $validteams)):
                        unset($options[$key]);
                    endif;
                endforeach;
            endif;
        endif;
        
        return $options;
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
            if ($this->_isConfigured()):
                return true;
            endif;
        endif;
        
        return false;
    }
    
    private function _canUpdate($data)
    {
        if (!empty($data['ticket']) && !empty($data['comment']) && !empty($data['user']) && !empty($data['department'])):
            if ($this->_isConfigured()):
                return true;
            endif;
        endif;
        
        return false;
    }
    
    private function _canChange($data)
    {
        if (!isset($this->_canchange)):
            $this->_canchange = false;
            $canchange = !empty($data['status']) || !empty($data['priority']) || !empty($data['department']) || !empty($data['team']) || !empty($data['user']) || !empty($data['subject']);
            if (!empty($data['ticket']) && $canchange):
                if ($detail = $this->getTicketDetail($data['ticket'])):
                    $status = !empty($data['status']) && $data['status'] != $detail['status']['id'] ? true : false;
                    $priority = !empty($data['priority']) && $data['priority'] != $detail['priority']['id'] ? true : false;
                    $department = !empty($data['department']) && $data['department'] != $detail['department']['id'] ? true : false;
                    $team = !empty($data['team']) && $data['team'] != $detail['team']['id'] ? true : false;
                    $currentsla = !empty($detail['sla']['id']) ? $detail['sla']['id'] : false;
                    $newsla = !empty($data['sla']) ? $data['sla'] : false;
                    if ($currentsla != $newsla):
                        $sla = true;
                    else:
                        $sla = false;
                    endif;
                    $user = !empty($data['user']) && $data['user'] != $detail['user']['id'] ? true : false;
                    $subject = !empty($data['subject']);
                    if ($status || $priority || $department || $team || $sla || $user || $subject):
                        $return = array();
                        $return['ticket'] = $data['ticket'];
                        if ($status) $return['status'] = $data['status'];
                        if ($priority) $return['priority'] = $data['priority'];
                        if ($department) $return['department'] = $data['department'];
                        if ($team) $return['team'] = $data['team'];
                        if ($sla) $return['sla'] = $data['sla'];
                        if ($user) $return['user'] = $data['user'];
                        if ($subject) $return['subject'] = $data['subject'];
                        if ($this->_isConfigured()):
                            $this->_canchange = $return;
                        endif;
                    endif;
                endif;
            endif;
        endif;
        
        return $this->_canchange;
    }
    
    private function _getConfig($config, $field)
    {
        $return = Mage::getStoreConfig('sirportly/' . $config . '/' .  $field);
        return !empty($return) ? $return : Mage::getStoreConfig('sirportly/ticketassign/' .  $field);
    }
    
    public function getTicketsByEmail($email)
    {
        $restrictions = $this->getUserRestrictions();
        if ($restrictions['permissions'] != 'none'):
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

                    if ($user = $restrictions['user']):
                        if ($restrictions['view'] == 'user'):
                            foreach ($tickets as $key => $ticket):
                                if (empty($ticket['user']['id']) || $ticket['user']['id'] != $user):
                                    unset($tickets[$key]);
                                endif;
                            endforeach;
                        else:
                            $userdata = $this->_getUserData($user);
                            foreach ($tickets as $key => $ticket):
                                if (empty($ticket['team']['id']) || !in_array($ticket['team']['id'], $userdata['teams'])):
                                    unset($tickets[$key]);
                                endif;
                            endforeach;
                        endif;
                    endif;

                    return $tickets;
                endif;
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
        $result = $this->_sendRequest($client, true);
        
        return array_reverse($result);
    }
    
    public function getTicketDetail($reference)
    {
        $params = array();
        $params['ticket'] = $reference;
        $client = $this->_getRequestObject('/api/v2/tickets/ticket', $params);
        return $this->_sendRequest($client, true);
    }
    
    public function getTicketTeamUserDepartmentSla($reference)
    {
        $result =  $this->getTicketDetail($reference);
        
        $return = array();
        if (!empty($result['team']['id'])):
            $return['team'] = (int) $result['team']['id'];
        endif;
        if (!empty($result['user']['id'])):
            $return['user'] = (int) $result['user']['id'];
        endif;
        if (!empty($result['department']['id'])):
            $return['department'] = (int) $result['department']['id'];
        endif;
        if (!empty($result['sla']['id'])):
            $return['sla'] = (int) $result['sla']['id'];
            if (!empty($result['reply_due_at'])):
                $return['reply'] = $this->getTimestamp($result['reply_due_at']);
            endif;
            if (!empty($result['resolution_due_at'])):
                $return['resolution'] = $this->getTimestamp($result['resolution_due_at']);
            endif;
        endif;
        
        return $return;
    }
    
    public function getTimestamp($time)
    {
        $time = strtotime($time);
        return date('jS M, g.ia', $time);
    }
    
    public function getUserRestrictions()
    {
        if (!isset($this->_restrictions)):
            $user = $this->_getAdminUser();
            $permissions = $user->getSirportlyRestrict();
            $view = $user->getSirportlyView();
            $user = $user->getSirportlyUser();

            $restrictions = array();

            if (!empty($permissions)):
                if ($permissions == 2):
                    $restrictions['permissions'] = 'full';
                elseif ($permissions == 1):
                    $restrictions['permissions'] = 'read';
                endif;
            else:
                $restrictions['permissions'] = 'none';
            endif;

            if (!empty($user)):
                $restrictions['user'] = $user;
            else:
                $restrictions['user'] = false;
            endif;
            
            if (!empty($view) && $view):
                $restrictions['view'] = 'team';
            else:
                $restrictions['view'] = 'user';
            endif;
            
            $this->_restrictions = $restrictions;
        endif;
        
        return $this->_restrictions;
    }
    
    private function _getAdminUser()
    {
        if (!isset($this->_adminuser)):
            $user = Mage::getSingleton('admin/session')->getUser();
            if ($user->getId()):
                $this->_adminuser = Mage::getSingleton('admin/session')->getUser();
            endif;
        endif;
        
        return $this->_adminuser;
    }
    
    private function _getUserData($userid)
    {
        if (!isset($this->_userdata)):
            $client = $this->_getRequestObject('/api/v2/users/info', array('user' => $userid));
            $user = $this->_sendRequest($client, true);
            $client = $this->_getRequestObject('/api/v2/objects/escalation_paths');
            $paths = $this->_sendRequest($client, true);
            $client = $this->_getRequestObject('/api/v2/objects/departments');
            $departments = $this->_sendRequest($client, true);

            $userdata = array();
            $userdata['teams'] = array();
            $userdata['departments'] = array();

            if (!empty($user['teams']) && is_array($user['teams'])):
                foreach ($user['teams'] as $team):
                    $userdata['teams'][] = (int) $team['id'];
                endforeach;
            endif;

            $validpaths = array();
            foreach ($paths as $path):
                if (!empty($path['teams']) && is_array($path['teams'])):
                    foreach ($path['teams'] as $team):
                        if (in_array($team['id'], $userdata['teams'])):
                            $validpaths[] = (int) $path['id'];
                        endif;
                    endforeach;
                endif;
            endforeach;

            foreach ($departments as $department):
                if (!empty($department['escalation_path']['id']) && in_array($department['escalation_path']['id'], $validpaths)):
                    $userdata['departments'][] = (int) $department['id'];
                endif;
            endforeach;
            
            $this->_userdata = $userdata;
        endif;
        
        return $this->_userdata;
    }
    
    public function filterDepartmentsByUser($options)
    {
        if ($restrictions = $this->getUserRestrictions()):
            if ($restrictions['user']):
                $userdata = $this->_getUserData($restrictions['user']);
                foreach ($options as $key => $option):
                    if (is_array($option['value'])):
                        foreach ($option['value'] as $k => $o):
                            if (!in_array($o['value'], $userdata['departments'])):
                                unset($options[$key]['value'][$k]);
                            endif;
                        endforeach;
                        if (empty($options[$key]['value'])):
                            unset($options[$key]);
                        endif;
                    else:
                        if (!in_array($option['value'], $userdata['departments'])):
                            unset($options[$key]);
                        endif;
                    endif;
                endforeach;
            endif;
        endif;
        
        return $options;
    }
    
    public function filterTeamsByUser($options)
    {
        if ($restrictions = $this->getUserRestrictions()):
            if ($restrictions['user']):
                $userdata = $this->_getUserData($restrictions['user']);
                foreach ($options as $key => $option):
                    if (!in_array($option['value'], $userdata['teams'])):
                        unset($options[$key]);
                    endif;
                endforeach;
            endif;
        endif;
        
        return $options;
    }
    
    public function filterUsersByUser($options)
    {
        if ($restrictions = $this->getUserRestrictions()):
            if ($restrictions['user']):
                foreach ($options as $key => $option):
                    if ($option['value'] != $restrictions['user']):
                        unset($options[$key]);
                    endif;
                endforeach;
            endif;
        endif;
        
        return $options;
    }
    
    private function _isConfigured()
    {
        if (!isset($this->_isconfigured)):
            $configs = array('status' => false, 'priority' => false, 'team' => false, 'department' => false);
            foreach (Mage::app()->getStores() as $store):
                foreach ($configs as $config => $configured):
                    if (!$configured):
                        if (Mage::getStoreConfig('sirportly/ticketassign/' . $config, $store)):
                            $configs[$config] = true;
                        endif;
                    endif;
                endforeach;
            endforeach;

            $this->_isconfigured = $configs['status'] && $configs['priority'] && $configs['team'] && $configs['department'];
        endif;
        
        return $this->_isconfigured;
    }
    
    public function getResponses($department)
    {
        if (!$this->verifyCredentials()):
            return array(
                array('value' => '', 'label' => $this->__('Invalid API Credentials'))
            );
        else:
            $client = $this->_getRequestObject('/api/v2/objects/responses', array('department' => $department));
            $return = array();
            if ($responses = $this->_sendRequest($client, true)):
                foreach ($responses as $area):
                    foreach ($area as $r):
                        $return[] = array('value' => $r['id'], 'label' => $r['name']);
                    endforeach;
                endforeach;
                
                if ($return):
                    array_unshift($return, array('value' => '', 'label' => $this->__('--Select to Insert--')));
                    
                    return $return;
                endif;
            endif;
            
            return array(
                array('value' => '', 'label' => $this->__('No Options Found'))
            );
        endif;
    }
}