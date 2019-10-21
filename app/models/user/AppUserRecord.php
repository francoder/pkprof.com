<?php

namespace models\user;

class AppUserRecord extends UserRecord {
	
	/**
	 * @return AppUserRecord
	 */
	public static function instance($table = '_user') {
		return parent::instance($table);
	}
	
	/**
	 * Переопределяем, чтобы не отправлять пользователю пароль, после оформления заказа
	 */
	protected function mailCredentials($params, $action, $tpl) {
		return;
	}
	
}