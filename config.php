<?php

$config = [

	'url_connectors' => "https://www.example.com/connectors",

	'pipedrive_connector_token' => '',
	'hubspot_connector_token' => '',
	'jira_connector_token' => '',

	'transactional_email_service' => "{sendgrid|mailchimp}",

	'sendgrid_api_key' => "",
	'mailchimp_api_key' => "",

	'from_email' => "info@example.com",
	'from_name' => "Virtual Assistant",
	'bcc_address' => "bcc@example.com",

	'quote_of_the_day' => ['url' => "https://zenquotes.io/api/today"],

	'services' => [
		'pipedrive' => ['active' => true, 'app' => 'pipedrive', 'crm_api_key' => '', 'crm_company_domain' => '',
						'managers' => [], 'excluded_users' => [], 'excluded_pipelines' => [],],
		'hubspot' => ['active' => true, 'app' => 'hubspot', 'crm_api_key' => '', 'crm_account_id' => '',
						'managers' => [], 'excluded_users' => [], 'excluded_pipelines' => [],],
		'jira' => ['active' => true, 'app' => 'jira', 'email' => '', 'api_token' => '', 'domain' => '',
						'managers' => [], 'excluded_users' => [],],
	],

];

$config['translations'] = [

	'Opportunities Report' => ['es' => "Reporte de Oportunidades"],
	'Hi' => ['es' => "Hola"],
	'Here is the latest report of opportunities' => ['es' => "Aquí está tu reporte de oportunidades"],
	'Count' => ['es' => "Cantidad"],
	'Amount' => ['es' => "Valor"],
	'Weighted Amount' => ['es' => "Valor Ponderado"],

	'Opportunity' => ['es' => "Oportunidad"],
	'Account' => ['es' => "Cuenta"],
	'Sales Stage' => ['es' => "Etapa"],
	'Amount' => ['es' => "Valor"],
	'% Amount' => ['es' => "% Valor"],
	'Date Closed' => ['es' => "Fecha Cierre"],
	'User' => ['es' => "Usuario"],

];

?>