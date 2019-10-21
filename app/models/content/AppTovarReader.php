<?php

namespace models\content;

class AppTovarReader extends ContentReader {
	
    /**
     * @return AppTovarReader
     */
	public static function instance($table = 'tovar') {
		return parent::instance($table);
	}
	
	public function setNodeInfo(&$one) {
		parent::setNodeInfo($one);
		$one['razdel'] = ContentReader::instance('razdel')->getOne($one['fko_razdel']);
		$one['mera'] = ContentReader::instance('mera')->getOne($one['fko_mera']);
	}
    
	/**
	 * Возвращет информацию по всем товарам в корзине
	 * @param \Basket $basket
	 * @return array ['items' => [], 'count' => 0, 'sum' => 0]
	 */
	public function getBasket($basket) {
		$in_basket = ['items' => [], 'count' => 0, 'sum' => 0];
		$all = $basket->find();
		if (!empty($all)) {
			// Обходим массив данных хранимый в сессии
			foreach ($all as $i) {
				$id = $i->get('id');
				// Если информацию о товаре ещё не получили
				$t = $this->getOne($id);
				$amount = $i->get('amount');
                if (!is_null($t)) {
                    $in_basket['items'][$id]['info'] = $t;
                    $in_basket['items'][$id]['title'] = $t['title'];
                    $in_basket['items'][$id]['price'] = $t['af_price'];
                    $in_basket['items'][$id]['amount'] = $amount;
                    $in_basket['items'][$id]['sum'] = intval($t['af_price']*$amount);
                    $in_basket['sum'] += $t['af_price']*$amount;
                }
				$in_basket['count'] += $amount;
			}
		}
		return $in_basket;
	}

}
