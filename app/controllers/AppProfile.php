<?php

namespace controllers;


use models\BaseReader;
use models\content\AppOrderReader;

class AppProfile extends Profile
{
    protected $tpldir = 'tmart';
    protected $layout = 'layout.html';
    protected $page_layout = 'inner';

    public function edit()
    {
        parent::edit();
        $this->html['page_title'] = 'Мои данные';
        $this->html['head_title'] = 'Мои данные';
        $this->set('html.inc', $this->tpldir.'/lk/profile/edit.html');
//        $this->print_r($this->data['node'] = $this->node);
        $this->set('list_orders', BaseReader::instance('order')->getListByFilter([['field' => 'user_id', 'value' => $this->user_id]]));
//        $this->print_r();

    }

    protected function setEditFormFields()
    {
        parent::setEditFormFields();
        unset($this->data['fields']['af_org']);
        unset($this->data['fields']['af_pkcard']);
    }



}