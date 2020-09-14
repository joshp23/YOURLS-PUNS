<?php
/*
Plugin Name: PUNS - Plugin Update Notification System
Plugin URI: https://github.com/joshp23/YOURLS-PUNS
Description: Provides notification updates for YOURLS plugins under certain conditions
Version: 0.5.0
Author: Josh Panter
Author URI: https://unfettered.net
*/
// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();
/*
 *
 * ADMIN PAGE FUNCTIONS
 *
 *
*/
// Register admin forms
yourls_add_action( 'plugins_loaded', 'puns_add_pages' );
function puns_add_pages() {
        yourls_register_plugin_page( 'puns', 'Plugin Updates', 'puns_do_page' );
}
// Maybe add some files to head
yourls_add_action( 'html_head', 'puns_head' );
function puns_head($context) {
	$home = YOURLS_SITE;
	if ( $context[0] == 'plugin_page_puns' ) {
		echo "<link rel=\"stylesheet\" href=\"".$home."/css/infos.css?v=".YOURLS_VERSION."\" type=\"text/css\" media=\"screen\" />\n";
		echo "<script src=\"".$home."/js/infos.js?v=".YOURLS_VERSION."\" type=\"text/javascript\"></script>\n";
		$file = dirname( __FILE__ )."/plugin.php";
		$data = yourls_get_plugin_data( $file );
		$v = $data['Version'];
		echo "\n<link rel=\"stylesheet\" href=\"".$home."/css/tablesorter.css?v=".$v."\" type=\"text/css\" media=\"screen\" />\n";
		echo "<script src=\"".$home."/js/jquery.tablesorter.min.js?v=".$v."\" type=\"text/javascript\"></script>\n";
	}
}
// Draw the page, etc
function puns_do_page() {

	puns_update_ops();

	$opt = puns_config();
	$ghAuth = ( $opt[0] == 'true' ? 'checked' : null );
	$ghU = $opt[1];
	$ghP = $opt[2];
	$punsNotify = ( $opt[3] == 'true' ? 'checked' : null );
	$adminEmail = $opt[4];

	// Misc for cron example pre-formatting
	$sig	= yourls_auth_signature();
	$site   = YOURLS_SITE;
	$cronEG   =  rawurlencode('<html><body><pre>0 * * * * curl --silent --data "signature=<strong>'.$sig.'</strong>&format=simple&action=puns-fast"  <strong>'.$site.'</strong>/yourls-api.php 2>&1</pre></body></html>');

	// Create nonce
	$nonce = yourls_create_nonce( 'puns' );

echo <<<HTML
	<div id="wrap">
		<div id="tabs">

			<div class="wrap_unfloat">
				<ul id="headers" class="toggle_display stat_tab">
					<li><a href="#stat_tab_report"><h2>Report</h2></a></li>
					<li><a href="#stat_tab_config"><h2>Config</h2></a></li>
					<li><a href="#stat_tab_api"><h2>API</h2></a></li>
				</ul>
			</div>

			<div  id="stat_tab_report" class="tab">

	 			<h3> Update Report</h3>

				<table id="main_table" class="tblSorter" cellpadding="0" cellspacing="1">
				<thead>
					<tr>
						<th>Plugin Name</th>
						<th>Version</th>
						<th>Note</th>
						<th>Latest</th>
					</tr>
				</thead>
				<tbody>
HTML;
	puns_run_report();
echo <<<HTML
				</tbody>
				</table>				
				<form method="post">

					<label>
							<input name="puns_run_report" type="hidden" value="false" />
							<input name="puns_run_report" type="checkbox" value="true" > Run report? 
					</label>
					<br>
					<input type="hidden" name="nonce" value="$nonce" />
					<p><input type="submit" value="Submit" /></p>

				</form>
			</div>

			<div id="stat_tab_config" class="tab">

				<form method="post">
					<br>

					<h3>GitHub Authentication</h3>

					<div class="checkbox" style="padding-left: 10pt;border-left:1px solid blue;border-bottom:1px solid blue;">
						<label>
							<input name="puns_github_auth" type="hidden" value="false" />
							<input name="puns_github_auth" type="checkbox" value="true" $ghAuth > Use authentication? 
						</label>
						<p>If selected, checks against GitHub's API will be authenticated with your username and a <a href="https://github.com/settings/tokens" target="_blank" >personal access token</a>. This dramatically increases the hourly rate limit. Leave unchecked for anononymity and to be limited to 50 calls per hour.</p> 
						<p>note: personal access token needs NO scopes assigned to it to work with PUNS</p>
					</div>
					<br>

					<h4>GitHub Credentials</h4>

					<div style="padding-left: 10pt;border-left:1px solid blue;border-bottom:1px solid blue;">
						<p><label for="puns_github_user">GitHub Username</label> <input type="text" size=40 id="puns_github_user" name="puns_github_user" value="$ghU" /></p>
						<p><label for="puns_github_pass">GitHub PA Token</label> <input type="password" size=40 id="puns_github_pass" name="puns_github_pass" value="$ghP" /></p>
					</div>
					<br>

					<h3>Notify Admin: Email</h3>

					<div class="checkbox" style="padding-left: 10pt;border-left:1px solid blue;border-bottom:1px solid blue;">
						<label>
							<input name="puns_notify_do" type="hidden" value="false" />
							<input name="puns_notify_do" type="checkbox" value="true" $punsNotify > Notify admin? 
						</label>
						<p>If selected, PUNS will attempt to mail a notification to the email set below when there is an update available. This is intended for use with the API and a cron job, or something similar.</p>
					</div>
					<br>

					<h4>Admin Email</h4>

					<div style="padding-left: 10pt;border-left:1px solid blue;border-bottom:1px solid blue;">
						<p><label for="puns_notify_email">Admin Email</label> <input type="text" size=40 id="puns_notify_email" name="puns_notify_email" value="$adminEmail" /></p>
					</div>
					<br>

					<input type="hidden" name="puns_opts_go" value="pronk" />
					<input type="hidden" name="nonce" value="$nonce" />
					<p><input type="submit" value="Submit" /></p>
				</form>
			</div>

			<div  id="stat_tab_api" class="tab">

				<p>PUNS will accept requests at the normal YOURLS API end point with a custom action in order to do a fast check for updates. This is intended for use with a cron job and a system capable of sending email.</p>
			
				<div style="padding-left: 10pt;border-left:1px solid blue;border-bottom:1px solid blue;">
					<h3>Performing a fast check via API</h3>
					<p>Send the new action:</p>
					<ul>
						<li><code>action = "puns-fast"</code></li>
					</ul>

					<h4>Cron example:</h3>
					<p>Use the following pre-formatted example to set up a daily cron to check for updated plugins:</p>
					 <iframe src="data:text/html;charset=utf-8,$cronEG" width="100%" height="51"/></iframe>
					<p>Look here for more info on <a href="https://help.ubuntu.com/community/CronHowto" target="_blank" >cron</a> and <a href="https://curl.haxx.se/" target="_blank">curl</a>.</p>
				</div>
			</div>
		</div>
	</div>
HTML;

}
/*
 *
 * 	Form submissions
 *
 *
*/
// Options updater
function puns_update_ops() {
	if(isset( $_POST['puns_opts_go'])) {
		// Check nonce
		yourls_verify_nonce( 'puns' );
		// Get Opts
		if(isset( $_POST['puns_github_auth'] )) yourls_update_option( 'puns_github_auth', $_POST['puns_github_auth'] );
		if(isset( $_POST['puns_github_user'] )) yourls_update_option( 'puns_github_user', $_POST['puns_github_user'] );
		if(isset( $_POST['puns_github_pass'] )) yourls_update_option( 'puns_github_pass', $_POST['puns_github_pass'] );
		if(isset( $_POST['puns_notify_do'] )) yourls_update_option( 'puns_notify_do', $_POST['puns_notify_do'] );
		if(isset( $_POST['puns_notify_email'] )) yourls_update_option( 'puns_notify_email', $_POST['puns_notify_email'] );
	}
}
// run full report
function puns_run_report() {
	if(isset( $_POST['puns_run_report'])) {
		// Check nonce
		yourls_verify_nonce( 'puns' );
		puns_cycle();
	}
}
/*
 *
 * 	Update Checking
 *
 *
*/
// cycle through
function puns_cycle(){
	$plugins = (array)yourls_get_plugins();
	$i = 0;
	foreach( $plugins as $file=>$plugin ) {
		// default fields to read from the plugin header
		$fields = array(
			'name'       => 'Plugin Name',
			'uri'        => 'Plugin URI',
			'version'    => 'Version',
		);
		// Loop through all default fields, get value if any and reset it
		foreach( $fields as $field=>$value ) {
			if( isset( $plugin[ $value ] ) ) {
				$data[ $field ] = $plugin[ $value ];
			} else {
				$data[ $field ] = '(no info)';
			}
			unset( $plugin[$value] );
		}

		$result = puns_check($data['uri'],$data['version']);

		if ( yourls_is_API() ) {

			if ($result['code'] == -1) $i++;

		} else {		

			if( yourls_is_active_plugin( $file ) ) {
				$class = 'active';
			} else {
				$class = 'inactive';
			}

			switch ($result['code']) {
				case -1: $msg = "<span style=\"color:red\"><strong>Update available</strong></span>"; break;
				case 0:  $msg = "<span style=\"color:green\">Up to date</span>"; break;
				case 1:  $msg = "<span style=\"color:blue\">Ahead of latest</span>"; break;
				case 2:  $msg = "This repo has not utilized proper release tags."; break;
				case 3:  $msg = $result['err']; break;
				case 403:$msg = "Forbidden!"; break;
				case 404:$msg = "No tags found in this repo"; break;
			}

			printf( "<tr class='plugin %s'><td class='plugin_name'><a target='_blank' href='%s'>%s</a></td><td class='plugin_version'>%s</td><td class='plugin_desc'>%s</td><td class='plugin_version'>%s</td></tr>",
				$class, $data['uri'], $data['name'], $data['version'], $msg, (isset($result['latest']) ? $result['latest'] : null)
			);
		}		
	} return $i;
}
// check individual plugin updates
function puns_check($url,$version) {
	if (function_exists('curl_init')) {
		$parse = parse_url($url);
		$host = $parse['host'];
		// add gitlab instances to this array!
		$gitlab = array( "gitlab.com", "gitgud.io", "framagit.org", "git.gnu.io");
		// TODO: add option to define custom gogs, gitea, and gitlab hosts!
		if ($host == "github.com" || explode('.', $host)[1] == "github.com") $data = puns_github($parse, $version);
		elseif ($host == "bitbucket.org") $data = puns_bitbucket($parse, $version);
		elseif (in_array($host, $gitlab)) $data = puns_gitlab($parse, $version);
		else {
			$data['code'] = 3;
			$data['err'] = 'Unsupported host: <code>'.$parse['host'].'</code>';
		}
		return $data;
	} else print "Please isntall PHP-CURL for this plugin to work";
}
/*
 *
 *	Helpers
 *
 *
*/
// github API
function puns_github($parse, $version){
	
	$opt = puns_config();
	if($parse['host'] == 'github.com') {
		$creds = explode('/', $parse['path']);
		$owner = $creds[1];
		$repo = $creds[2];
	} else {
		$owner = explode('.', $parse['host'])[0];
		$repo = explode('/', $parse['path'])[1];
	}
	if ($opt[0] == true) {
		$endpoint = 'https://'.$opt[1].':'.$opt[2].'@api.github.com/repos/'.$owner.'/'.$repo.'/releases/latest';
	} else {
		$endpoint = 'https://api.github.com/repos/'.$owner.'/'.$repo.'/releases/latest';
	}

	$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL,$endpoint);
		curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Accept: application/vnd.github.v3+json'));
		curl_setopt($cURL,CURLOPT_USERAGENT,'YOURLS-PUNS-v0.4.5');
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURL, CURLOPT_TIMEOUT, 30);
	$result = curl_exec($cURL);
	$status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
		curl_close($cURL);
	$result = json_decode($result,true);
	if($status == 200) {
		if(isset($result["tag_name"])) {
			$latest = $result["tag_name"];
			if (substr($latest, 0, 1) === 'v') $latest = substr($latest, 1);
			$data['code'] = version_compare($version, $latest);
			$data['latest'] = $latest;
		} else $data['code'] = 2;
	} else $data['code'] = $status;
	return $data;
}
// bitbucket API
function puns_bitbucket($parse, $version){

	$creds = explode('/', $parse['path']);
	$owner = $creds[1];
	$repo = $creds[2];
	$endpoint = 'https://api.bitbucket.org/2.0/repositories/'.$owner.'/'.$repo.'/refs/tags';

	$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL,$endpoint);
		curl_setopt($cURL,CURLOPT_USERAGENT,'YOURLS-PUNS-v0.4.5');
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURL, CURLOPT_TIMEOUT, 30);
	$result = curl_exec($cURL);
	$status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
		curl_close($cURL);
	$result = json_decode($result,true);
	if($status == 200) {
		if($result['values']) {
			$latest = 0;
			foreach($result['values'] as $value) {
				if($value['name'] && $value['name'] !== 'tip') {
					$test = $value["name"];
					if (substr($test, 0, 1) === 'v') $test = substr($test, 1);
					if (version_compare($test, $latest ) >= 0) $latest = $test;
				}
			}
			$data['code'] = version_compare($version, $latest);
			$data['latest'] = $latest;
		} else $data['code'] = 2;
	} else	$data['code'] = $status;
	return $data;
}
// gitlab API
function puns_gitlab($parse, $version){

	$id = urlsencode($parse['path'], 1);
	$endpoint = 'https://'.$parse['host'].'/api/v4/projects/'.$id.'/repository/tags';

	$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL,$endpoint);
		curl_setopt($cURL,CURLOPT_USERAGENT,'YOURLS-PUNS-v0.4.5');
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURL, CURLOPT_TIMEOUT, 30);
	$result = curl_exec($cURL);
	$status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
		curl_close($cURL);
	$result = json_decode($result,true);
	if($status == 200) {
		if(!empty($result) && $result[0]['name']) {
				$latest = $result['0']['name'];
				if (substr($test, 0, 1) === 'v') $latest = substr($latest, 1);
			$data['code'] = version_compare($version, $latest);
			$data['latest'] = $latest;
		} else $data['code'] = 2;
	} else	$data['code'] = $status;
	return $data;
}
// config options array
function puns_config() {
	// Get values from DB
	$ghAuth = yourls_get_option( 'puns_github_auth' );
	$ghUser = yourls_get_option( 'puns_github_user' );
	$ghPass = yourls_get_option( 'puns_github_pass' );
	$punsNotify = yourls_get_option( 'puns_notify_do' );
	$adminEmail = yourls_get_option( 'puns_notify_email' );
	
	// Set defaults if necessary
	if( $ghAuth	== null ) $ghAuth 	= 'false';
	if( $punsNotify	== null ) $punsNotify 	= 'false';

	return array(
	$ghAuth,		// opt[0]
	$ghUser,		// opt[1]
	$ghPass,		// opt[2]
	$punsNotify,	// opt[3]
	$adminEmail		// opt[4]
	);
}
/*
 *
 *	API
 *
 *
*/
// Check All Update data
yourls_add_filter( 'api_action_puns-fast', 'puns_fast_api' );
function puns_fast_api() {
	$auth = yourls_is_valid_user();
	if( $auth !== true ) {
		$format = ( isset($_REQUEST['format']) ? $_REQUEST['format'] : 'xml' );
		$callback = ( isset($_REQUEST['callback']) ? $_REQUEST['callback'] : '' );
		yourls_api_output( $format, array(
			'simple' => $auth,
			'message' => $auth,
			'errorCode' => 403,
			'callback' => $callback,
		) );
	}
	$data = puns_cycle();
	if ($data > 0) {
		$opt = puns_config();
		$parse = parse_url(YOURLS_SITE);
		if ($opt[3] == 'true') {
			$vars['subject'] 	= "YOURLS Update Status";
			$vars['message'] 	= "There are updates for one or more of the YOURS plugins at ".YOURLS_SITE;
			$vars['to_address'] = $opt[4];
			$vars['to_name']	= "YOULS Admin";
			if( ( yourls_is_active_plugin( 'SMTP-contact/plugin.php' ) ) )
				ysc_send ( $vars );
			else 
				mail ($vars['to_address'], $vars['subject'], $vars['message'], "no-reply@".$parse['host']);
		}
		return array(
			'statusCode' => 200,
			'code'		 => 1,
			'simple'     => "Updates are available for one or more of your plugins",
			'message'    => 'update_status: available',
		);
	} else {
		$opt = puns_config();
		$parse = parse_url(YOURLS_SITE);
		return array(
			'statusCode' => 200,
			'code'		 => 0,
			'simple'     => "There are no updates available at this time",
			'message'    => 'update_status: up to date',
		);

	}
}
