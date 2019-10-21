<?php

namespace helpers;

class AppLayout extends Layout {

	public function setMenus($url) {
		parent::setMenus($url);
		$this->set('MENUS.catalog', \models\content\ContentReader::instance('razdel')->getListPublished());
		
		$main = $this->get('MENUS.main');
		$katalog_url = $this->alias('frontend_tovar_index');
		foreach ($main as $url => $item) {
			if ($item['childs'] > 0) {
				$main[$url]['childs'] = $this->getMenus($url, $item['id'], $url.'/');
			} elseif ($url == $katalog_url) {
				$katalog = $this->get('MENUS.catalog');
				$items = [];
				foreach ($katalog as $v) {
					$items[$this->alias('frontend_tovar_list', $v)] = ['text' => $v['title']];
				}
				$main[$url]['childs'] = $items;
			}
		}
		$this->set('MENUS.main', $main);
	}
}
