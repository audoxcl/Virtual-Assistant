<?php

require 'config.php';

function writeLog($content){
	file_put_contents("log.txt", "\n".date("Y-m-d H:i:s: ").print_r($content, true), FILE_APPEND);
}

function formatAmount($amount, $separator){
    $amount = implode($separator, array_map(
		function($key, $value) {
			$decimals = 0;
			$decimal_separator = ".";
			$thousands_separator = ",";
			if(in_array($key, ["CLP", "CLF"])){
				$decimal_separator = ",";
				$thousands_separator = ".";
			}
			elseif(in_array($key, [])){

			}
			$value = number_format($value, $decimals, $decimal_separator, $thousands_separator);
			return "$key$ $value";
		},
		array_keys($amount),
		$amount
	));
	return $amount;
}

function translateString($string, $lang){
	global $config;
	$translation = $config['translations'][$string][$lang];
	return !empty($translation)?$translation:$string;
}

function sendEmailBySendGrid($params){
	global $config;

	// Prepare email data structure
	$email_data = [
		'personalizations' => [
			[
				'to' => array_map(function($to_email) {
					return [
						'email' => $to_email['email'],
						'name' => $to_email['name']
					];
				}, $params['to_emails']),
				'bcc' => !empty($config['bcc_address']) ? [['email' => $config['bcc_address']]] : []
			]
		],
		'from' => [
			'email' => $config['from_email'],
			'name' => $config['from_name'],
		],
		'subject' => $params['subject'],
		'content' => [
			[
				'type' => "text/html",
				'value' => $params['html']
			]
		]
	];

	// Initialize cURL for SendGrid API
	$ch = curl_init('https://api.sendgrid.com/v3/mail/send');
	curl_setopt_array($ch, [
		CURLOPT_HTTPHEADER => [
			'Authorization: Bearer ' . $config['sendgrid_api_key'],
			'Content-Type: application/json'
		],
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => json_encode($email_data),
	]);

	// Execute cURL and handle the response
	$response = curl_exec($ch);
	if(curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 202) writeLog(json_decode($response));

	curl_close($ch);
}

function sendEmailByMailChimp($params){
	global $config;

	// Prepare email data structure
	$email_data = [
		'key' => $config['mailchimp_api_key'],
		'message' => [
			'html' => $params['html'],
			'subject' => $params['subject'],
			'from_name' => $config['from_name'],
			'from_email' => $config['from_email'],
			'to' => array_map(function($to_email) {
				return [
					'email' => $to_email['email'],
					'name' => $to_email['name'],
					'type' => 'to'
				];
			}, $params['to_emails']),
			'bcc_address' => $config['bcc_address'] ?? "",
			'track_opens' => true,
			'track_clicks' => true,
		]
	];

	// Initialize cURL for Mailchimp Transactional API
	$ch = curl_init('https://mandrillapp.com/api/1.0/messages/send.json');
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => json_encode($email_data)
	]);

	// Execute cURL and handle the response
	$response = curl_exec($ch);
	if(curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) writeLog(json_decode($response));

	curl_close($ch);
}

// Helper function to create an amount table
function createAmountTable($params, $language) {
	$amountTable = "<table border=\"1\">";
	foreach (['amount' => "Amount", 'amount_weighted' => "Weighted Amount"] as $key => $label) {
		$amountTable .= sprintf(
			"<tr><td><strong>%s</strong></td><td>%s</td></tr>",
			translateString($label, $language),
			formatAmount($params[$key], "</td><td align=\"right\">")
		);
	}
	$amountTable .= "</table>";
	return $amountTable;
}

function sendEmail($params){
	global $config;
	// writeLog($params);

	// Prepare the subject line
	$subject = translateString(
		in_array($params['app'], ['jira']) ? "Issues Report" : "Opportunities Report",
		$params['language']
	) . " - " . $params['app_name'];

	// Prepare the greeting and quote sections
	$greeting = sprintf(
		"<p>%s%s!</p>",
		translateString("Hi", $params['language']),
		!empty($params['to_name']) ? " " . $params['to_name'] : ""
	);

	$quote_of_the_day = !empty($params['quote_of_the_day'][0]->h) 
		? "<p>Quote of the Day:</p>" . $params['quote_of_the_day'][0]->h 
		: "";

	// Footer and optional banner
	$footer = "<p>Powered by <a href=\"https://www.audox.com\">Audox</a>.</p>";
	$banner = $footer;

	// Create amount table if applicable
	$amount_table = createAmountTable($params, $params['language']);

	// Construct the HTML content
	$html = implode('', [
		$greeting,
		"<p>" . translateString(
			in_array($params['app'], ['jira']) 
				? "Here is the latest report of issues" 
				: "Here is the latest report of opportunities",
			$params['language']
		) . ":</p>",
		"<p>" . translateString("Count", $params['language']) . ": " . $params['count'] . "<br>",
		in_array($params['app'], ['jira']) ? "" : $amount_table,
		$banner,
		$params['table'],
		$quote_of_the_day,
		$footer,
	]);

	$params = array_merge($params, [
		'html' => $html,
		'subject' => $subject,
		'to_emails' => [['email' => $params['to_email'], 'name' => $params['to_name']]],
	]);

	if($config['transactional_email_service'] === "mailchimp") sendEmailByMailChimp($params);
	else sendEmailBySendGrid($params);
}

?>