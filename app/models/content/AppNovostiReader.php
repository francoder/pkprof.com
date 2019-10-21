<?php

namespace models\content;

class AppNovostiReader extends ContentReader {

	/**
	 * @return \models\content\AppNovostiReader
	 */
	public static function instance($table = 'novosti') {
		return parent::instance($table);
	}

}
