<?php

namespace models\content;

use models\user\UserReader;
use models\user\AppUserRecord;

class AppOrderRecord extends ContentRecord {
	
	/**
	 * @return AppOrderRecord
	 */
	public static function instance($table = 'order') {
		return parent::instance($table);
	}
	
	protected function isValidOrder($post, $basket, &$errors) {
		if ($basket->count() < 1) {
			$errors[] = 'Для оформления заказа необходимо добавить хотя бы один товар';
		}
		if (empty($post['user']['title'])) {
			$errors[] = 'Укажите ваше имя';
		}

		if (empty($post['user']['af_tel']) && empty($post['user']['email'])) {
			$errors[] = 'Укажите контактный телефон или email';
		} elseif (!empty($post['user']['email']) && !\helpers\Tools::checkEmail($post['user']['email'])) {
			$errors[] = 'Укажите корректный email';
		}
		return empty($errors);
	}
	
	/**
	 * 
	 * @param array $post
	 * @param \Basket $basket
	 * @return boolean
	 */
	public function createOrder($post, $basket) {
		$in_basket = AppTovarReader::instance()->getBasket($basket);
		$errors = [];
		if ($in_basket['count'] == 0) {
			return ['result' => false, 'error' => ['Корзина пуста']];
		}
		if (!$this->isValidOrder($post, $basket, $errors)) {
			return ['result' => false, 'error' => $errors];
		}
		$order_data = $post['order'];
		$user_data = $post['user'];
		
		// Ищем пользователя по email
		$user = UserReader::instance()->getByEmail($user_data['email']);
		if (!is_null($user)) {
			$order_data['user_id'] = $user['id'];
		} else {
			// Создаем нового пользователя
			$user_data['genpassword'] = true;
			$create_res = AppUserRecord::instance()->create($user_data);
//			$this->print_r($create_res);
			if ($create_res['result'] === false) {
				return $create_res;
			} else {
				$order_data['user_id'] = $create_res['result'];
			}
		}
		$order_data['af_basket'] = json_encode($basket->checkout());
		$order_data['fko_order_status'] = 1;
		$order_data['af_contacts'] = 'Имя:'.$user_data['title']."\r\n";
		$order_data['af_contacts'] .= 'Телефон:'.$user_data['af_tel']."\r\n";
		$order_data['af_contacts'] .= 'Адрес:'.$user_data['af_address']."\r\n";
		$res = $this->create($order_data);
		if ($res['result'] !== false) {
			// Отправляем запрос на почту
			$this->set('basket', $in_basket);
			$post['delivery'] = ContentReader::instance('delivery')->getOne($post['fko_delivery']);
			$post['payment'] = ContentReader::instance('payment')->getOne($post['fko_payment']);
			$this->set('msg', $post);
			$msg = \Template::instance()->render($this->get('site.tpl') . '/basket/mail.html');
			$subj = 'Новый заказ с сайта ' . $this->get('SITE_URL');
			\helpers\Tools::mail($this->get('email.admin'), $subj, $msg, $this->get('email.from'));
		}
		return $res;
	}
	
}