<?php

// This file gets executed every few seconds and handles everything

require_once 'config.php';

require_once 'lib/Store.php';
require_once 'lib/DevRant.php';

function botLog ($msg) {
	if (DEBUG) echo 'Bot > ' . $msg . PHP_EOL;
}

$store = new Store('./store', ['prettify' => true, 'log' => DEBUG]);
$devRant = new DevRant($store);

$notifications = $devRant->getNotifications();
$devRant->clearNotifications();

usort($notifications['items'], function ($a, $b) {
	return $a['created_time'] <=> $b['created_time'];
});

require_once 'lib/NotifHandler.php';

$notifHandler = new NotifHandler($store, $devRant);

$tempDirRequiresClear = false;
$didSomething = false;

if (!isset($store('misc')['lastNotifTime']))
	$store('misc')['lastNotifTime'] = time();

$lastNotifTime = $store('misc')['lastNotifTime'];
$newLastNotifTime = 0;

foreach ($notifications['items'] as $notification) {
	if ($notification['type'] !== 'comment_vote' && $notification['type'] !== 'comment_mention')
		continue;

	if ($notification['type'] === 'comment_vote' && $notification['rant_id'] !== THEME_SELECTION_RANT_ID)
		continue;

	if ($notification['created_time'] > $lastNotifTime || $notification['read'] === 0) {
		if ($notification['type'] === 'comment_vote') {
			botLog('Handling a theme selection notif (User: ' . $notification['uid'] . ')...');
			$notifHandler->handleThemeSelectNotif($notification['uid'], $notification['comment_id']);
		} else if ($notification['type'] === 'comment_mention') {
			$tempDirRequiresClear = true;

			botLog('Handling a code highlighting notif (CommentID: ' . $notification['comment_id'] . ')...');
			$notifHandler->handleHighlightNotif($notification['comment_id']);
		}

		$didSomething = true;
		$newLastNotifTime = $notification['created_time'];
	}
}

if ($didSomething) {
	$store('misc')['lastNotifTime'] = $newLastNotifTime;
} else {
	botLog('Nothing to do...');
}

if ($tempDirRequiresClear) {
	botLog('Clearing temp directory...');
	$files = glob('./temp/*.png');

	foreach ($files as $file) {
		if (is_file($file))
			unlink($file);
	}
}