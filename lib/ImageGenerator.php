<?php

use Nesk\Puphpeteer\Puppeteer;

require_once 'vendor/autoload.php';
require_once 'ThemeSelection.php';

// TODO: Prettify

class ImageGenerator {

	private static $puppeteer = false;

	private static function log ($msg) {
		if (DEBUG) echo 'ImageGenerator > ' . $msg . PHP_EOL;
	}

	private static function saveImageFromCarbon ($url, $saveFile) {
		self::log('Starting Puppeteer...');

		if (!self::$puppeteer)
			self::$puppeteer = new Puppeteer();

		$browser = self::$puppeteer->launch();

		$page = $browser->newPage();

		$page->setViewport([
			'width'             => 1600,
			'height'            => 1000,
			'deviceScaleFactor' => 2,
		]);

		self::log("Going to url $url...");
		$page->goto($url);

		$exportContainer = $page->querySelector('#export-container');

		try {
			$elementBounds = $exportContainer->boundingBox();

			self::log("Saving screenshot of #export-container...");
			$exportContainer->screenshot([
				'path' => $saveFile,
				'clip' => [
					'x'      => round($elementBounds['x']),
					'y'      => $elementBounds['y'],
					'width'  => $elementBounds['width'],
					'height' => round($elementBounds['height']) - 1,
				],
			]);
		} catch (Error $error) {
			self::log("Carbon returned an unexpected error - not saving screenshot...");
		}

		$browser->close();
	}

	private static function getCarbonUrl ($options) {
		self::log('Getting Carbon URL...');
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
}