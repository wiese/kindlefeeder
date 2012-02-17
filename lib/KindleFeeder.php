<?php
require_once 'vendor/HttpRequester.php';

class KindleFeeder {

	const BASE_URL = 'http://kindlefeeder.com';
	const LOGIN_ROUTE = '/sessions';

	protected $username = '';
	protected $password = '';

	protected $browser = null;

	public function __construct() {
		$this->browser = new HttpRequester;
		$this->browser->postfollowredirs = true;
	}

	public function setCredentials($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}

	public function request_push() {
		$dashboard = $this->login();

		$url = $this->getDeliveryUrlFromDashboard($dashboard);
		$email = $this->getEmailFromDashboard($dashboard);
		$token = $this->getCsrfTokenFromPage($dashboard);

		$fields = array(
			'authenticity_token' => $token,
			'submit' => $email
		);

		$response = $this->browser->post(
			self::BASE_URL . $url,
			$fields
		);

		$success = false;
		if (preg_match('|Delivery queued|', $response)) {
			$success = true;
		}

		return $success;
	}

	public function getCsrfTokenFromPage($dashboard) {
		return $this->getFirstMatchForPattern(
			'|name="authenticity_token" type="hidden" value="(.+?)"|',
			$dashboard
		);
	}

	public function getDeliveryUrlFromDashboard($dashboard) {
		return $this->getFirstMatchForPattern(
			'|action="(\/users\/\d+\/deliveries\?emailed_to=.+%40free\.kindle\.com)"|',
			$dashboard
		);
	}

	public function getEmailFromDashboard($dashboard) {
		return $this->getFirstMatchForPattern(
			'|type="submit" value="([^@]+@free\.kindle\.com)"|',
			$dashboard
		);
	}

	public function getFirstMatchForPattern($pattern, $subject) {
		if (preg_match($pattern, $subject, $match)) {
			return $match[1];
		}
		return null;
	}

	public function login() {
		$loginUrl = self::BASE_URL . self::LOGIN_ROUTE;

		// collect cookie
		$loginPage = $this->browser->get($loginUrl);

		// fetch dashboard
		$dashboard = $this->browser->post(
			$loginUrl,
			array(
				'login' => $this->username,
				'password' => $this->password,
				'commit' => 'Log in'
			)
		);

		return $dashboard;
	}
}
