<?php
require_once 'lib/KindleFeeder.php';
require_once '_config.php';

$feeder = new KindleFeeder;
$feeder->setCredentials($username, $password);
$success = $feeder->request_push();

$message = 'KindleFeeder: ';

if ($success) {
	$message .= "Delivery queued. Sit tight.";
}
else {
	$message .= "There was a problem queueing the delivery.";
}

echo $message;