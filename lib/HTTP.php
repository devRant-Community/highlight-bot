<?php


class HTTP {
	private static function log ($msg) {
		if (DEBUG) echo 'HTTP > ' . $msg . PHP_EOL;
	}

	private static function curl ($url) {
		$curl = curl_init($url);

		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_USERAGENT      => 'HighlightBot/1.0',
		]);

		return $curl;
	}

	private static function execute ($curl) {
		$raw = curl_exec($curl);

		$response = new stdClass();

		if (!curl_errno($curl)) {
			$response->success = true;
			$response->json = json_decode($raw, true);
			$response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		} else {
			$response->success = false;
			$response->error = curl_error($curl);
			$response->errno = curl_errno($curl);
		}

		curl_close($curl);

		return $response;
	}

	public static function GET ($uri, $params) {
		$params = http_build_query($params);
		$requestURL = $uri . '?' . $params;

		self::log("GET $requestURL");
		$curl = self::curl($requestURL);

		$response = self::execute($curl);

		if ($response->success) {
			return $response->json;
		} else {
			self::log("GET $requestURL - Error (" . $response->errno . ') "' . $response->error . '"');

			return false;
		}
	}

	public static function POST ($uri, $params) {
		$requestURL = $uri;

		self::log("POST $requestURL");
		$curl = self::curl($requestURL);

		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

		$response = self::execute($curl);

		if ($response->success) {
			return $response->json;
		} else {
			self::log("POST $requestURL - Error (" . $response->errno . ') "' . $response->error . '"');

			return false;
		}
	}

	public static function DELETE ($uri, $params) {
		$params = http_build_query($params);
		$requestURL = $uri . '?' . $params;

		self::log("DELETE $requestURL");
		$curl = self::curl($requestURL);

		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');

		$response = self::execute($curl);

		if ($response->success) {
			return $response->json;
		} else {
			self::log("DELETE $requestURL - Error (" . $response->errno . ') "' . $response->error . '"');

			return false;
		}
	}
}