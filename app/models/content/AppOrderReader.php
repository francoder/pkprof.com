<?php

namespace models\content;

class AppOrderReader extends ContentReader {
	
	public static function instance($table = 'order') {
		return parent::instance($table);
	}
	
	public function getOne($id) {
		$one = parent::getOne($id);
		if ($one) {
			$this->setInfo($one);
			$one['content'] = $this->getItems($id);
		}
		return $one;
	}
	
	public function getList($start = 0, $limit = 10, $all = true, $sort = null, $filter = null, $additional_conditions = null, $with_related_fields = true, $or_conditions = null) {
		$res = parent::getList($start, $limit, $all, $sort, $filter, $additional_conditions, $with_related_fields, $or_conditions);
		if (!empty($res['subset'])) {
			foreach ($res['subset'] as $k => $v) {
				$this->setInfo($res['subset'][$k]);
			}
		}
		return $res;
	}
	
	public function setInfo(&$one) {
		$one['user'] = \models\user\UserReader::instance()->getOneWithProfile($one['user_id']);
		$order_basket = new \Basket('order_basket');
		$order_basket->drop();
		$items = json_decode($one['af_basket'], true);
		foreach ($items as $item) {
			$order_basket->reset();
			$order_basket->copyFrom($item);
			$order_basket->save();
		}
		$one['basket'] = AppTovarReader::instance()->getBasket($order_basket);
	}
}