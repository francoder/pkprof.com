<?php

namespace controllers\front;

use models\content\ContentReader;
use models\content\AppTovarReader;

class AppContent extends Content {
	
	protected $tpldir = 'tmart';
	protected $layout = 'layout.html';
	protected $page_layout = 'inner';
	
	/**
	 * 
	 * @return \helpers\AppLayout
	 */
	protected function layout() {
		return \helpers\AppLayout::instance();
	}
    
    public function beforeroute() {
        parent::beforeroute();
        $this->basket = new \Basket();
    }
    public function afterroute() {
		$basket = AppTovarReader::instance()->getBasket($this->basket);
		$this->set('basket', $basket);
        parent::afterroute();
		if ($this->DEBUG == 3) {
//			$this->print_r($basket);
		}
    }


	public function show() {
		parent::show();
		$this->page_layout = 'inner';
	}

	public function home() {
		$this->show();
		$this->set('list_banners', ContentReader::instance('banner')->getListPublished());
		$this->set('list_banners_small', ContentReader::instance('banner_small')->getListPublished());
		$this->set('list_banners_home', ContentReader::instance('banner_home')->getListPublished(['limit' => 3]));
		$this->set('list_novosti', ContentReader::instance('novost')->getListPublished(['order' => ['af_date DESC'], 'limit' => 7]));
		$this->page_layout = 'home';
	}
    
    public function loginForm() {
        if ($this->user) {
            $this->reroute(\models\group\GroupReader::instance()->getURI($this->user['group_id']));
        }
        parent::loginForm();
        $this->layout = 'layout.html';
        $this->page_layout = 'login-register';
    }
    
    public function registrationForm() {
        parent::registrationForm();
        $this->layout = 'layout.html';
        $this->page_layout = 'login-register';
    }
    
    protected function makeSearch($types) {
        parent::makeSearch(['htmlpage', 'tovar', 'razdel', 'novost']);
    }
    
    public function search() {
        parent::search();
        $this->page_layout = 'search';
    }
	
}
