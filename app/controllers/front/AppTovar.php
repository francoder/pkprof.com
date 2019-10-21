<?php

namespace controllers\front;

class AppTovar extends AppContent {
	
	protected $table = 'tovar';
	protected $table_cat = 'razdel';
	protected $struct_path = '/katalog';
    protected $list_perpage = 1000;

    protected function reader() {
		return \models\content\AppTovarReader::instance();
	}
	
	public function node() {
		parent::node();
		$cat_link = $this->alias('frontend_tovar_list', ['slug' => $this->get('node.razdel.slug')]);
		$this->layout()->addLevelToBreadCrumbs([$cat_link => $this->get('node.razdel.title')]);
		$this->page_layout = 'katalog';
	}
	
	public function listSyncCat1NFlat() {
		parent::listSyncCat1NFlat();
		$this->page_layout = 'katalog';
	}
	
	public function index1NFlat() {
		parent::index1NFlat();
		$this->page_layout = 'katalog';
	}
}