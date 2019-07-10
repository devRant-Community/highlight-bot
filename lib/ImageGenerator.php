<?php

use Nesk\Puphpeteer\Puppeteer;

require_once 'vendor/autoload.php';
require_once 'ThemeSelection.php';

class ImageGenerator {

	private static $puppeteer = false;

	private static function saveImageFromCarbon ($url, $saveFile) {
		if (!self::$puppeteer)
			self::$puppeteer = new Puppeteer();

		$browser = self::$puppeteer->launch();

		$page = $browser->newPage();

		$page->setViewport([
			'width'             => 1600,
			'height'            => 1000,
			'deviceScaleFactor' => 2,
		]);

		$page->goto($url);

		$exportContainer = $page->querySelector('#export-container');
		$elementBounds = $exportContainer->boundingBox();

		$exportContainer->screenshot([
			'path' => $saveFile,
			'clip' => [
				'x'      => round($elementBounds['x']),
				'y'      => $elementBounds['y'],
				'width'  => $elementBounds['width'],
				'height' => round($elementBounds['height']) - 1,
			],
		]);

		$browser->close();
	}

	private static function getCarbonUrl ($options) {
		$defaultOptions = [
			't' => 'seti', // Theme
			'l' => 'auto', // Language
			'bg' => '#fff', // Background
			'wt' => 'sharp', // Window Theme
			'wc' => false, // Window Controls
			'fm' => 'Fira Code', // Font Family
			'fs' => '18px', // Font Size
			'ln' => false, // Line Numbers
			'ds' => false, // Drop Shadow
			'dsyoff' => '20px', // Drop Shadow Offset
			'dsblur' => '68px', // Drop Shadow Blur
			'wa' => true, // Auto Adjust Width
			'lh' => '133%', // Line Height
			'pv' => '0px', // Padding Vertical
			'ph' => '0px', // Padding Horizontal
			'si' => false, // Squared Image
			'wm' => false, // Watermark
			'es' => '2x', // Export Size
			'type' => 'png', // Export Type
			'code' => 'console.log("Hello World!");'
		];

		$options = array_merge($defaultOptions, $options);

		foreach ($options as $key => $value) {
			if (is_bool($value))
				$options[$key] = $value === true ? 'true' : 'false';
		}

		$params = http_build_query($options);

		return 'https://carbon.now.sh/?' . $params;
	}

	public static function generateAndSaveImage($saveFile, $code, $theme) {
		$url = self::getCarbonUrl([
			't' => $theme,
			'code' => $code
		]);

		self::saveImageFromCarbon($url, $saveFile);
	}



	public static function handleNotif($devRant, $commentID) {
		$comment = $devRant->getComment($commentID);
		if (!$comment) return;

		$themeSelection = ThemeSelection::get($comment['user_id']);

		if (!$themeSelection)
			$themeSelection = 'seti';

		$saveFile = "./temp/$commentID.png";

		$commentBody = $comment['body'];
		$commentBody = str_replace('@highlight', '', $commentBody);

		$code = trim($commentBody);

		self::generateAndSaveImage($saveFile, $code, $themeSelection);

		if (file_exists($saveFile)) {
			$msg = '@' . $comment['user_username'];

			$devRant->postComment($comment['rant_id'], $msg, $saveFile);
		}
	}
}