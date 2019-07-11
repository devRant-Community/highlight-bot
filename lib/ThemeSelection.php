<?php


class ThemeSelection {
	private static $store = false;

	private static function log ($msg) {
		if (DEBUG) echo 'ThemeSelection > ' . $msg . PHP_EOL;
	}


	public static function setStore($store) {
		self::$store = $store;
	}

	public static function get ($userID) {
		if (isset(self::$store->in('theme-selection')[$userID])) {
			return self::$store->in('theme-selection')[$userID];
		}

		return false;
	}

	public static function set ($userID, $themeID) {
		self::log("Setting theme to $themeID for user $userID...");

		self::$store->in('theme-selection')[$userID] = $themeID;
	}
}