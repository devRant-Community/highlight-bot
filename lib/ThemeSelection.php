<?php


class ThemeSelection {
	private static $themeSelection = [];

	private static function log ($msg) {
		if (DEBUG) echo 'ThemeSelection > ' . $msg . PHP_EOL;
	}

	private static function load () {
		if (!file_exists(THEME_SELECTION_FILE))
			return false;

		if (!empty(self::$themeSelection))
			return true;

		self::log('Loading Theme Selection...');

		$rawJSON = file_get_contents(THEME_SELECTION_FILE);
		self::$themeSelection = json_decode($rawJSON, true);

		register_shutdown_function(function () {
			self::save();
		});

		return true;
	}

	private static function save () {
		self::log('Saving Theme Selection...');

		$rawJSON = json_encode(self::$themeSelection, DEBUG ? JSON_PRETTY_PRINT : 0);
		file_put_contents(THEME_SELECTION_FILE, $rawJSON);
	}



	public static function get ($userID) {
		if (self::load()) {
			return self::$themeSelection[$userID];
		}

		return false;
	}

	public static function set ($userID, $themeID) {
		self::load();

		self::log("Setting theme to $themeID for user $userID...");

		self::$themeSelection[$userID] = $themeID;
	}



	public static function handleNotif ($devRant, $userID, $commentID) {
		$comment = $devRant->getComment($commentID);
		if (!$comment) return;

		$themes = (require 'themes.php');

		foreach ($themes as $theme) {
			if ($theme['name'] === $comment['body']) {
				self::set($userID, $theme['id']);
			}
		}
	}
}