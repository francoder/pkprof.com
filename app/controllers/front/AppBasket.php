<?php

namespace controllers\front;

use models\content\ContentReader;
use models\content\AppTovarReader;
use models\content\AppOrderRecord;
use helpers\Msg;

class AppBasket extends AppContent {

	public function show() {
		parent::show();
		if ($this->basket->count() == 0 && !$this->exists('POST')) {
			Msg::warning('Корзина пуста');
		} else {
			$this->setForm();
			$this->setBasketInfo();
		}
        $this->page_layout = 'search';
	}
	
	protected function setForm() {
		$this->set('delivery', ContentReader::instance('delivery')->getListPublished());
		$this->set('payment', ContentReader::instance('payment')->getListPublished());
		$this->set('html.inc', $this->tpldir.'/basket/list_form.html');
	}

	protected function setBasketInfo() {
		$info = AppTovarReader::instance()->getBasket($this->basket);
		$this->set('basket', $info);
	}


	public function order() {

		if (!$this->exists('POST', $post) || empty($post)) {
			Msg::warning('Заполните форму заказа', null, true);
			$this->reroute('@frontend_basket');
        }
        if($this->user['group_id'] != 4 || $this->user == null) {
            Msg::warning('Оформить заказ могут только члены кооператива. По всем вопросам обращайтесь к администратору');
            $this->reroute('@frontend_basket');
        }
        parent::show();
        // Добавляем заказ
        $res = AppOrderRecord::instance()->createOrder($post, $this->basket);
        if ($res['result'] !== false) {
            $this->set('html.page_title', 'Заказ оформлен');
            $this->page_layout = 'order';
        } else {
            $this->set('html.page_title', 'Заказ не оформлен');
            if (!empty($res['error']) && is_array($res['error'])) {
                foreach ($res['error'] as $error) {
                    Msg::error($error, null, true);
                }
            }
            $this->setForm();
        }
	}

}
