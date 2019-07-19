<?php

require_once 'lib/ThemeSelection.php';
require_once 'lib/ImageGenerator.php';


class NotifHandler {

	private $devRant;

	private $store;

	private $themes;

	public function __construct ($store, $devRant) {
		$this->store = $store;
		$this->devRant = $devRant;

		ThemeSelection::setStore($store);
	}

	private function log ($msg) {
		if (DEBUG) echo 'NotifHandler > ' . $msg . PHP_EOL;
	}



	public function handleThemeSelectNotif ($userID, $commentID) {
		$comment = $this->devRant->getComment($commentID);
		if (!$comment) return;

		if (empty($this->themes))
			$this->themes = (require 'themes.php');

		foreach ($this->themes as $theme) {
			if ($theme['name'] === $comment['body']) {
				ThemeSelection::set($userID, $theme['id']);
			}
		}
	}

	public function handleHighlightNotif ($commentID) {
		$comment = $this->devRant->getComment($commentID);
		if (!$comment) return;

		$themeSelection = ThemeSelection::get($comment['user_id']);

		if (!$themeSelection)
			$themeSelection = 'seti';

		$saveFile = "./temp/$commentID.png";

		$commentBody = $comment['body'];

		preg_match('/.*?@' . DEVRANT_USERNAME . '(.*)/si', $commentBody, $matches);

		$code = trim($matches[1]);

		if (empty($code))
			return;

		$this->log("Generating and downloading image (File: $saveFile, Theme: $themeSelection)...");
		ImageGenerator::generateAndSaveImage($saveFile, $code, $themeSelection);

		if (file_exists($saveFile)) {
			$mention = '@' . $comment['user_username'];

			$this->log("Replying with generated image to user $mention...");
			$this->devRant->postComment($comment['rant_id'], $mention, $saveFile);
		}
	}
}