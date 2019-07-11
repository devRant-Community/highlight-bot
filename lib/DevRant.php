<?php

require_once 'lib/HTTP.php';


class DevRant {
	private $authToken = [];

	private $store;

	public function __construct ($store) {
		$this->store = $store;

		$this->authToken = $store('auth-token');

		if (count($this->authToken->data) === 0 || $this->hasTokenExpired()) {
			$this->log('Auth token has expired or hasn\'t been set yet.');
			$this->login();
		} else {
			$this->log('Auth token valid.');
		}
	}

	private function log ($msg) {
		if (DEBUG) echo 'DevRant > ' . $msg . PHP_EOL;
	}

	private function hasTokenExpired () {
		return (time() > $this->authToken['expire_time']);
	}

	public function login () {
		$this->log('Trying to login...');

		$response = HTTP::POST(DEVRANT_API . '/users/auth-token', [
			'username' => DEVRANT_USERNAME,
			'password' => DEVRANT_PASSWORD,
			'app'      => 3,
		]);

		if ($response) {
			$success = $response['success'];

			if ($success) {
				$this->log('Login successful!');

				$this->authToken = $response['auth_token'];
				$this->store->in('auth-token')->data = $this->authToken;

				return true;
			}

			$this->log('Login unsuccessful - ' . $response['error']);

			return false;
		}

		$this->log('Login Request failed!');

		return false;
	}

	public function postRant ($msg, $tags = [], $type = 6, $image = false) {
		$this->log("Posting rant with message '$msg'" . ($image ? " and with image '$image'" : '') . '...');

		$params = [
			'rant' => $msg,
			'tags' => implode(', ', $tags),
			'type' => $type,
			'app'  => 3,
			'plat' => 2,

			'user_id'   => $this->authToken['user_id'],
			'token_id'  => $this->authToken['id'],
			'token_key' => $this->authToken['key'],
		];

		if ($image) {
			$mimeTypes = [
				'jpg' => 'image/jpg',
				'png' => 'image/png',
				'gif' => 'image/gif',
			];

			$fileExtension = pathinfo($image, PATHINFO_EXTENSION);
			$mimeType = $mimeTypes[$fileExtension];

			$params['image'] = curl_file_create($image, $mimeType, "rant_image.$fileExtension");
		}

		$response = HTTP::POST(DEVRANT_API . "/devrant/rants", $params);

		if ($response) {
			$success = $response['success'];

			if ($success) {
				$this->log('Posting Rant successful!');

				return $response['rant_id'];
			}

			$this->log('Posting Rant unsuccessful - ' . $response['error']);

			return false;
		}

		$this->log('Post Rant Request failed!');

		return false;
	}

	public function postComment ($rantID, $msg, $image = false) {
		$this->log("Posting comment with message '$msg'" . ($image ? " and with image '$image'" : '') . '...');

		$params = [
			'comment' => $msg,
			'app'     => 3,
			'plat'    => 2,

			'user_id'   => $this->authToken['user_id'],
			'token_id'  => $this->authToken['id'],
			'token_key' => $this->authToken['key'],
		];

		if ($image) {
			$mimeTypes = [
				'jpg' => 'image/jpg',
				'png' => 'image/png',
				'gif' => 'image/gif',
			];

			$fileExtension = pathinfo($image, PATHINFO_EXTENSION);
			$mimeType = $mimeTypes[$fileExtension];

			$params['image'] = curl_file_create($image, $mimeType, "comment_image.$fileExtension");
		}

		$response = HTTP::POST(DEVRANT_API . "/devrant/rants/$rantID/comments", $params);

		if ($response) {
			$success = $response['success'];

			if ($success) {
				$this->log('Posting Comment successful!');

				return true;
			}

			$this->log('Posting Comment unsuccessful - ' . $response['error']);

			return false;
		}

		$this->log('Post Comment Request failed!');

		return false;
	}

	public function getRant ($rantID) {
		$this->log("Fetching rant $rantID...");

		$response = HTTP::GET(DEVRANT_API . "/devrant/rants/$rantID", [
			'app' => 3,

			'user_id'   => $this->authToken['user_id'],
			'token_id'  => $this->authToken['id'],
			'token_key' => $this->authToken['key'],
		]);

		if ($response) {
			$success = $response['success'];

			if ($success) {
				$this->log('Fetching Rant successful!');

				return $response;
			}

			$this->log('Fetching Rant unsuccessful - ' . $response['error']);

			return false;
		}

		$this->log('Fetch Rant Request failed!');

		return false;
	}

	public function getComment ($commentID) {
		$this->log("Fetching comment $commentID...");

		$response = HTTP::GET(DEVRANT_API . "/comments/$commentID", [
			'app'  => 3,
			'plat' => 2,

			'user_id'   => $this->authToken['user_id'],
			'token_id'  => $this->authToken['id'],
			'token_key' => $this->authToken['key'],
		]);

		if ($response) {
			$success = $response['success'];

			if ($success) {
				$this->log('Fetching Comment successful!');

				return $response['comment'];
			}

			$this->log('Fetching Comment unsuccessful - ' . $response['error']);

			return false;
		}

		$this->log('Fetch Comment Request failed!');

		return false;
	}

	public function getNotifications ($lastTime = false) {
		$this->log("Fetching Notifications...");

		$response = HTTP::GET(DEVRANT_API . "/users/me/notif-feed", [
			'app'       => 3,
			'last_time' => $lastTime ?: 0,

			'user_id'   => $this->authToken['user_id'],
			'token_id'  => $this->authToken['id'],
			'token_key' => $this->authToken['key'],
		]);

		if ($response) {
			$success = $response['success'];

			if ($success) {
				$this->log('Fetching Notifications successful!');

				return $response['data'];
			}

			$this->log('Fetching Notifications unsuccessful - ' . $response['error']);

			return false;
		}

		$this->log('Fetch Notifications Request failed!');

		return false;
	}

	public function clearNotifications () {
		$this->log('Clearing Notifications...');

		$response = HTTP::DELETE(DEVRANT_API . '/users/me/notif-feed', [
			'app' => 3,

			'user_id'   => $this->authToken['user_id'],
			'token_id'  => $this->authToken['id'],
			'token_key' => $this->authToken['key'],
		]);

		if ($response) {
			$success = $response['success'];

			if ($success) {
				$this->log('Clearing Notifications successful!');

				return true;
			}

			$this->log('Clearing Notifications unsuccessful - ' . $response['error']);

			return false;
		}

		$this->log('Clear Notifications Request failed!');

		return false;
	}
}