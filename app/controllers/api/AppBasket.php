<?php

namespace controllers\api;

use models\content\AppTovarReader;

class AppBasket {

	use \FrameworkAbstraction;
	use \Debug;
	use \APIResult;
	
    /**
     * @var \Basket
     */
	protected $basket;
	protected $id;
	protected $product;


	public function __construct() {
		$this->fw = \Base::instance();
	}

	public function beforeroute() {
		$this->basket = new \Basket;
	}
	
	/**
	 * Проверяет существаование SKU в БД
	 * @return bool
	 */
	private function existsPostId() {
		if (!$this->exists('POST.id', $this->id)) {
			return false;
		}
		$this->id *= 1;
		$this->product = AppTovarReader::instance()->getOne($this->id);
		return !is_null($this->product);
	}
	
	/**
	 * Добавляет SKU в корзину
	 */
	public function add() {
		if (!$this->existsPostId()) {
			return $this->result($this->res406());
		}
		// Добавляемое определённое кол-во
		if ($this->exists('POST.amount', $amount)) {
			$amount *= 1;
		}
		// Добавляем 1 единицу
		if (!isset($amount) || $amount < 1) {
			$amount = 1;
		}
		
		$res = null;
		// Находим по id товара все SKU, ранее добавленные в корзину
		$in_basket = $this->basket->find('id', $this->product['id']);
		if (empty($in_basket)) {
			$this->basket->reset();
			$this->basket->set('id', $this->id);
			$this->basket->set('amount', $amount);
			$this->basket->save();
		} else {
            $this->basket->load('id', $this->product['id']);
            $amount += intval($this->basket->get('amount'));
            $this->basket->set('amount', $amount);
            $this->basket->save();
        }
        $res = ['count' => $amount, 'price' => $this->product['af_price']];
//		$this->print_r($res_sku);
		$this->result([
			'result' => true,
			'count' => $this->getBasketCount(),
			'sum' => $this->getBasketSum(),
			'product' => $res
		]);
	}
	
	/**
	 * Удаляем 1шт SKU из корзины
	 */
	public function remove() {
		if (!$this->existsPostId()) {
			return $this->result($this->res406());
		}
		$res = false;
		$res_tovar = null;
		// Находим по id товара все SKU, ранее добавленные в корзину
		if ($in_basket = $this->basket->find('id', $this->product['id'])) {
			foreach ($in_basket as $item) {
				if ($item->get('id') == $this->id) {
					$amount = $item->get('amount')-1;
					if ($amount > 0) {
						$item->set('amount', $amount);
						$item->save();
						// Информация по обновлённому SKU
						$res_tovar = ['count' => $amount, 'price' => $this->product['af_price']];
						$res = true;
					}
					break;
				}
			}
		}
		$this->result([
			'result' => $res,
			'count' => $this->getBasketCount(),
			'sum' => $this->getBasketSum(),
			'sku' => $res_tovar
		]);
	}
	
	/**
	 * Меняет кол-во товара в корзине
	 */
    public function change() {
		if (!$this->existsPostId() || !$this->exists('POST.amount', $amount)) {
			return $this->result($this->res406());
		}
		$amount = intval($amount);
		if ($amount < 1) {
			return $this->result($this->res406());
		} 
		$res_tovar = null;
		$this->basket->load('id', $this->id);
		if (!$this->basket->dry()) {
			$this->basket->set('amount', $amount);
			// Информация по обновлённому SKU
			$res_tovar = ['count' => $amount, 'price' => $this->product['af_price']];
			$res = true;
		} else {
			$res = false;
		}
		$this->basket->save();
		$this->result([
			'result' => $res,
			'count' => $this->getBasketCount(),
			'sum' => $this->getBasketSum(),
			'product' => $res_tovar
		]);
	}
    
	/**
	 * Удаляет товар из корзины
	 */
	public function erase() {
		// Проверяем id
		if (!$this->existsPostId()) {
			return $this->result($this->res406());
		}
		$this->result([
			'result' => $this->basket->erase('id', $this->id),
			'count' => $this->getBasketCount(),
			'sum' => $this->getBasketSum(),
			'product' => null
		]);
	}
	
	/**
	 * Возвращает информацию по товару
	 */
	public function getTovar() {
		if (!$this->existsPostId()) {
			return $this->result($this->res406());
		}
		$res_tovar = null;
		$this->basket->load('id', $this->id);
		if (!$this->basket->dry()) {
			$amount = $this->basket->get('amount');
			// Информация по обновлённому SKU
			$res_tovar = ['count' => $amount, 'price' => $this->product['af_price']];
			$res = true;
		} else {
			$res = false;
		}
		$this->basket->save();
		$this->result([
			'result' => $res,
			'product' => $res_tovar
		]);
	}
	
	/**
	 * Кол-во товаров в корзине
	 * @return int
	 */
	protected function getBasketCount() {
		$all = $this->basket->find();
		$count = 0;
		foreach ($all as $i) {
			if ($i->get('id') != 0) {
				$count += $i->get('amount');
			}
		}
		return $count;
	}
	
	/**
	 * Стоимость товаров в корзине
	 * @return int
	 */
	protected function getBasketSum() {
		$all = $this->basket->find();
		$sum = 0;
		foreach ($all as $i) {
			$node = AppTovarReader::instance()->getOne($i->get('id'));
			$sum += $i->get('amount') * $node['af_price'];
		}
		return $sum;
	}
	
	public function widget() {
		$this->set('basket', AppTovarReader::instance()->getBasket($this->basket));
		echo \Template::instance()->render($this->get('site.tpl').'/tovar/list-widget.html', 'text/html');
	}
}