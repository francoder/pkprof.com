<?php

namespace controllers\front;

class AppNovost extends AppContent {
	
	protected $table = 'novost';
	protected $struct_path = '/novosti';
	
	public function nodeById() {
		parent::nodeById();
		$other = $this->reader()->getListOtherPublished($this->get('node.id'), 6, 'novost.af_date DESC');
		$this->set('node.other', $other);
	}
}