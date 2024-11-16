<?php

include 'config.php';
include 'functions.php';

global $config;

writeLog("Starting...");

if(isset($argv)){
	$service = $argv[1];
}
elseif(isset($_REQUEST)){
	if(!in_array($_REQUEST['token'], [
		"FREETOKEN",
		"TOKEN1",
		"TOKEN2",
	])) die(json_encode(['error' => "Not authorized"]));
	$service = $_REQUEST['service_id'];
	if(isset($_REQUEST['api_key'])) $config['services'][$service]['crm_api_key'] = $_REQUEST['api_key'];
}
else die;

$params = [
	'quote_of_the_day' => json_decode(file_get_contents($config['quote_of_the_day']['url'])),
];

$apps_names = ["Pipedrive", "HubSpot", "Jira"];
$apps_names = array_combine(array_map('strtolower', $apps_names), $apps_names);

if($config['services'][$service]['app'] == "pipedrive"){
	foreach(['users', 'pipelines', 'stages', 'deals'] as $object){
		$ch = curl_init();
		$fields = [
			'action' => "getRecords",
			'object' => $object,
			'company_domain' => $config['services'][$service]['crm_company_domain'],
			'api_token' => $config['services'][$service]['crm_api_key'],
		];
		$url = $config['url_connectors'].'/pipedrive/?'.http_build_query($fields);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization:Bearer '.$config['pipedrive_connector_token'],
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$results = json_decode(curl_exec($ch));
		${$object} = [];
		foreach($results as $result) ${$object}[$result->id] = $result;
		// echo "<pre>".$object.": "; var_dump(${$object}); echo "</pre>";
	}
	$opportunities = $deals;
	$opportunities_by_users = [];
	$languages = [
		47 => "es",
	];
	foreach($users as $user) $users[$user->id]->language = $languages[$users[$user->id]->lang];
	foreach($opportunities as $opportunity){
		// writeLog($users[$opportunity->user_id->id]->email);
		// writeLog($config['services'][$service]['excluded_users']);
		if(!in_array($users[$opportunity->user_id->id]->email, $config['services'][$service]['excluded_users'])
			&& !in_array($users[$opportunity->user_id->id]->email, $config['services'][$service]['managers'])
			&& $users[$opportunity->user_id->id]->active_flag == true)
			$opportunities_by_users[$opportunity->user_id->id][$opportunity->id] = $opportunity;
	}
	// echo "<pre>"; var_dump($opportunities_by_users); echo "</pre>";
}
elseif($config['services'][$service]['app'] == "hubspot"){
	// return;
	// writeLog("Starting ".$config['services'][$service]['app']."...");
	foreach([
			'owners',
			'companies',
			'deals',
			'pipelines_deals',
			] as $object){
		$ch = curl_init();
		$fields = [
			'action' => "getRecords",
			'object' => $object,
			'hapikey' => $config['services'][$service]['crm_api_key'],
		];
		if($object == "pipelines_deals") $fields['object'] = "pipelines/deals";
		if($object == "deals"){
			$fields['properties'] = "dealname,dealtype,amount,hs_deal_stage_probability,closedate,dealstage,pipeline,hubspot_owner_id,hs_is_closed,hs_projected_amount_in_home_currency,company_id";
			$fields['associations'] = "companies";
		}
		$url = $config['url_connectors'].'/hubspot/?'.http_build_query($fields);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization:Bearer '.$config['hubspot_connector_token'],
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$results = json_decode(curl_exec($ch));
		${$object} = [];
		foreach($results as $result) ${$object}[$result->id] = $result;
		// writeLog(print_r(${$object}, true));

	}
	$users = $owners;
	foreach($users as $user){
		$users[$user->id]->name = $user->firstName." ".$user->lastName;
		$users[$user->id]->email = $user->email;
	}
	$pipelines = [];
	foreach($pipelines_deals as $pipeline_deals){
		$pipelines[$pipeline_deals->id]['label'] = $pipeline_deals->label;
		foreach($pipeline_deals->stages as $pipeline_deals_stage)			
		$pipelines[$pipeline_deals->id]['stages'][$pipeline_deals_stage->id] = $pipeline_deals_stage->label;
	}
	$opportunities = $deals;
	$opportunities_by_users = [];
	foreach($opportunities as $opportunity){
		// writeLog($users[$opportunity->user_id->id]->email);
		// writeLog($config['services'][$service]['excluded_users']);
		if(!in_array($users[$opportunity->user_id->id]->email, $config['services'][$service]['excluded_users'])
			&& !in_array($users[$opportunity->user_id->id]->email, $config['services'][$service]['managers'])
			// && $users[$opportunity->user_id->id]->archived == false
			)
			$opportunities_by_users[$opportunity->properties->hubspot_owner_id][$opportunity->id] = $opportunity;
	}
	// writeLog(print_r(array_slice($opportunities_by_users, 0, 1), true));
	// return print(json_encode($pipelines));

}
elseif($config['services'][$service]['app'] == "jira"){
	foreach([
		'users',
		// 'projects',
		'issues',
		] as $object){
		$fields = [
			'action' => "getRecords",
			'object' => $object,
			'email' => $config['services'][$service]['email'],
			'api_token' => $config['services'][$service]['api_token'],
			'domain' => $config['services'][$service]['domain'],
		];
		if($object == "projects") $fields['object'] = "project";
		elseif($object == "issues") $fields['fields'] = "summary,project,issuetype,priority,status,assignee,reporter";
		$url = $config['url_connectors'].'/jira/?'.http_build_query($fields);
		// writeLog("url: ".$url);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization:Bearer '.$config['jira_connector_token'],
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$results = json_decode(curl_exec($ch));
		${$object} = [];
		foreach($results as $result){
			if($object == "users") ${$object}[$result->accountId] = $result;
			else ${$object}[$result->id] = $result;
		}
	}

	$issues_by_users = [];
	foreach($issues as $issue){
		// writeLog($users[$opportunity->user_id->id]->email);
		// writeLog($config['services'][$service]['excluded_users']);
		if(!in_array($users[$issue->fields->assignee->accountId]->emailAddress, $config['services'][$service]['excluded_users'])
			&& !in_array($users[$issue->fields->assignee->accountId]->emailAddress, $config['services'][$service]['managers'])
			// && $users[$opportunity->user_id->id]->archived == false
			)
			$issues_by_users[$issue->fields->assignee->accountId][$issue->id] = $issue;
	}
	foreach($users as $user_id => $user){
		// writeLog("Checking managers for: ".print_r($user_id, true));
		if(in_array($user->emailAddress, $config['services'][$service]['managers']))
			$issues_by_users[$user_id] = $issues;
	}

	// return print(json_encode($issues_by_users));
}
// writeLog("# users: ".count($users));
// writeLog("# opportunities: ".count($opportunities));

if(in_array($config['services'][$service]['app'], ["pipedrive", "hubspot"])){
	foreach($users as $user_id => $user){
		// writeLog("Checking managers for: ".print_r($user_id, true));
		if(in_array($user->email, $config['services'][$service]['managers']))
			$opportunities_by_users[$user_id] = $opportunities;
	}

	foreach($opportunities_by_users as $user_id => $opportunities_by_user){
		writeLog("user_id: ".$user_id);
		if(empty($user_id)) continue;
		$amount = [];
		$amount_weighted = [];
		$table = "<table border=\"1\">";
		$headers = [
			"id",
			"Opportunity",
			"Account",
			"Sales Stage",
			"Amount",
			"%",
			"% Amount",
			"Date Closed",
			"User",
		];
		$language = $users[$user_id]->language;
		$headers = array_map(function($header) use ($language) {
				return translateString($header, $language);
			},
			$headers
		);
		writeLog("headers: ".print_r($headers, true));
		$table .= "<tr><th>".implode("</th><th>", $headers)."</th></tr>";
		$i = 1;
		foreach($opportunities_by_user as $opportunity){
			$data = [];
			// ###############
			// ## PIPEDRIVE ##
			// ###############
			if($config['services'][$service]['app'] == "pipedrive"
				&& $opportunity->status == "open"
				&& !in_array($opportunity->pipeline_id, $config['services'][$service]['excluded_pipelines'])
				){
				// $opportunity->title = (($config['services'][$service]['active'] == false) && ($i>10))?$opportunity->title:"<a href=\"https://".$config['services'][$service]['crm_company_domain'].".pipedrive.com/deal/$opportunity->id\">".$opportunity->title."</a>";
				/*if(($config['services'][$service]['active'] == false) && ($i>10)){
				}
				else{
				}*/
				$interval = "";
				if(empty($opportunity->next_activity_date)) $interval = "&#x1F7E1;";
				else{
					$interval = (new DateTime($opportunity->next_activity_date))->diff(new DateTime())->days;
					if($interval > 0) $interval = "&#x1F534;";
					elseif($interval = 0) $interval = "&#x1F7E2;";
					else $interval = "&#x25EF;";
				}
				$opportunity->title_with_link = "<a href=\"https://".$config['services'][$service]['crm_company_domain'].".pipedrive.com/deal/$opportunity->id\">".$opportunity->title."</a>";
				$opportunity->title_with_link = "$interval ".$opportunity->title_with_link;
				$opportunity->org_name_with_link = "<a href=\"https://".$config['services'][$service]['crm_company_domain'].".pipedrive.com/organization/".$opportunity->org_id->value."\">".$opportunity->org_name."</a>";
				$data = [
					$opportunity->id,
					$opportunity->title_with_link,
					$opportunity->org_name_with_link,
					$pipelines[$opportunity->pipeline_id]->name."/".$stages[$opportunity->stage_id]->name,
					// $opportunity->value,
					// $opportunity->currency,
					$opportunity->formatted_value,
					$opportunity->probability,
					$opportunity->formatted_weighted_value,
					$opportunity->expected_close_date,
					$opportunity->owner_name,
				];
				$amount[$opportunity->currency] += $opportunity->value;
				$amount_weighted[$opportunity->currency] += $opportunity->weighted_value;
				$i++;
			}
			// #############
			// ## HUBSPOT ##
			// #############
			if($config['services'][$service]['app'] == "hubspot"
				&& $opportunity->properties->hs_is_closed == "false"
				&& !in_array($opportunity->properties->pipeline, $config['services'][$service]['excluded_pipelines'])
				){
				$opportunity->url = "https://app.hubspot.com/contacts/".$config['services'][$service]['crm_account_id']."/deal/".$opportunity->id;
				$opportunity->name = "<a href=\"".$opportunity->url."\">".$opportunity->properties->dealname."</a>";
				$opportunity->account_url = "https://app.hubspot.com/contacts/".$config['services'][$service]['crm_account_id']."/company/".$opportunity->properties->company_id;
				$opportunity->account_name = "<a href=\"".$opportunity->account_url."\">".$companies[$opportunity->properties->company_id]->properties->name."</a>";
				$closedate = new DateTime($opportunity->properties->closedate);
				$data = [
					$opportunity->id,
					$opportunity->name,
					$opportunity->account_name,
					$pipelines[$opportunity->properties->pipeline]['label']."/".$pipelines[$opportunity->properties->pipeline]['stages'][$opportunity->properties->dealstage],
					number_format($opportunity->properties->amount),
					number_format(100*$opportunity->properties->hs_deal_stage_probability),
					number_format($opportunity->properties->hs_projected_amount_in_home_currency),
					$closedate->format('Y-m-d'),
					$users[$opportunity->properties->hubspot_owner_id]->name,
				];
				$amount['USD'] += $opportunity->properties->amount;
				$amount_weighted['USD'] += $opportunity->properties->hs_projected_amount_in_home_currency;
				$i++;
			}
			if(!empty($data)) $table .= "<tr><td>".implode("</td><td>", $data)."</td></tr>";
		}
		$table .= "</table>";
		$params['app'] = $config['services'][$service]['app'];
		$params['app_name'] = $apps_names[$config['services'][$service]['app']];
		$params['count'] = $i-1;
		$params['amount'] = $amount;
		$params['amount_weighted'] = $amount_weighted;
		$params['table'] = $table;
		$params['to_email'] = $users[$user_id]->email;
		$params['to_name'] = $users[$user_id]->name;
		$params['language'] = $users[$user_id]->language;
		writeLog("Sending email to: ".$params['to_email']." (".$user_id.")");
		writeLog("Sending email to: ".print_r($users[$user_id], true));
		sendEmail($params);
	}
}
elseif(in_array($config['services'][$service]['app'], ["jira"])){
	foreach($issues_by_users as $user_id => $issues_by_user){
		if(empty($user_id)) continue;
		$table = "<table border=\"1\">";
		$headers = [
			"id",
			"Summary",
			"Type",
			"Project",
			"Priority",
			"Status",
			"Assignee",
		];
		$language = $users[$user_id]->language;
		$headers = array_map(function($header) use ($language) {
				return translateString($header, $language);
			},
			$headers
		);
		$table .= "<tr><th>".implode("</th><th>", $headers)."</th></tr>";
		$i = 1;
		foreach($issues_by_user as $issue){
			// writeLog("headers: ".print_r($issue, true));
			$data = [];
			// writeLog("issue id: ".print_r($issue->id, true));
			// writeLog("assignee: ".print_r($issue->fields->assignee->accountId, true));
			// writeLog("emailAddress: ".print_r($issue->fields->assignee->emailAddress, true));
			// ##########
			// ## JIRA ##
			// ##########
			if($config['services'][$service]['app'] == "jira"
				&& $issue->fields->status->statusCategory->key != "done"){
				$project_url = "https://".$config['services'][$service]['domain'].".atlassian.net/jira/software/projects/".$issue->fields->project->key."/boards/1";
				$issue_url = $project_url."?selectedIssue=".$issue->key;
				$data = [
					$issue->id,
					"<a href=\"$issue_url\">".$issue->fields->summary."</a>",
					$issue->fields->issuetype->name,
					//$issue->fields->project->name,
					"<a href=\"$project_url\">".$issue->fields->project->name."</a>",
					$issue->fields->priority->name,
					$issue->fields->status->name,
					$issue->fields->assignee->displayName,
				];
				$i++;
			}
			if(!empty($data)) $table .= "<tr><td>".implode("</td><td>", $data)."</td></tr>";
		}
		$table .= "</table>";
		$params['app'] = $config['services'][$service]['app'];
		$params['app_name'] = $apps_names[$config['services'][$service]['app']];
		$params['count'] = $i-1;
		$params['table'] = $table;
		$params['to_email'] = $users[$user_id]->emailAddress;
		$params['to_name'] = $users[$user_id]->displayName;
		$params['language'] = $users[$user_id]->locale;
		if(!empty($params['to_email'])) sendEmail($params);
	}
}

writeLog("Ready!");

?>