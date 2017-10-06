<?php namespace Craft;

class StoryChiefHelper {
	
	public static function parseBoolean($value) {
		if(is_array($value)) $value = array_shift($value);

		$result = filter_var($value, FILTER_VALIDATE_BOOLEAN);

		// Also check for translated values of boolean-like terms
		if (strtolower($value) === Craft::t('yes')) {
			$result = true;
		}

		if (strtolower($value) === Craft::t('on')) {
			$result = true;
		}

		if (strtolower($value) === Craft::t('open')) {
			$result = true;
		}


		if (strtolower($value) === Craft::t('no')) {
			$result = false;
		}

		if (strtolower($value) === Craft::t('off')) {
			$result = false;
		}

		if (strtolower($value) === Craft::t('closed')) {
			$result = false;
		}

		return $result;
	}

}