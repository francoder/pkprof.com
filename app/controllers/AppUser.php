<?php


namespace controllers;

use models\ACL\ACLReader as ACL;

class AppUser extends User {

    protected function checkAccess($action, $owner_id = null) {
        if ($action == ACL::ACTION_UPDATE && $this->user['group_id'] == 2) {
            return true;
        }
        return parent::checkAccess($action, $owner_id);
    }
    
    protected function setDataList() {
        if ($this->user['group_id'] == 2) {
            $this->list_filter = [['field' => 'group_id', 'value' => [0,3,4]]];
        }
        parent::setDataList();
    }

    protected function setData()
    {
        parent::setData();
        if ($this->user_group_id == 2) {
            $this->data['rights']['read'] = true;
            $this->data['rights']['update'] = true;
        }
    }

    protected function setFormFields()
    {
        parent::setFormFields();
        if ($this->user_group_id == 2) {
            $mapper = new \DB\SQL\Mapper($this->db(), '_group');
            $groups = [];
            foreach ($mapper->find('id IN (0,3,4)') as $g) {
                $groups[$g->id] = $g->name;
            }
            $this->data['fields']['group_id']['options'][$this->locale] = $groups;
        }
    }
    
    public function listView() {
        parent::listView();
        if (!$this->get('AJAX')) {
            $this->set('inc', 'tmart/user/list_form.html');
        }
    }
}