<?php
namespace controllers\api;

use models\ACL\ACLReader;

class AppUser extends User {

    protected function checkAccess($action, $owner_id = null) {
        if ($action == ACLReader::ACTION_UPDATE && $this->user['group_id'] == 2) {
            return true;
        }
        return parent::checkAccess($action, $owner_id);
    }

    protected function updateProcess($post) {
        if ($this->user['group_id'] == 2 
            && isset($post['group_id']) 
            && !in_array($post['group_id'], [0,3,4])) {
            return $this->resNotSaved();
        }
        return parent::updateProcess($post);
    }
}