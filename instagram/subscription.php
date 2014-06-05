<?php
require_once 'Instagram.php';

/**
 * Configuration params, make sure to write exactly the ones
 * instagram provide you at http://instagr.am/developer/
 */
$config = array(
        'client_id' => '3f5fe543985246eba3b26703dc166117',
        'client_secret' => '715379dc66674209a39d7728a5937f73',
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'http://natame.com/instagram/',
     );

/**
 * This is how a wrong response looks like
 * array(1) { ["InstagramOAuthToken"]=> string(89) "{"code": 400, "error_type": "OAuthException", "error_message": "No matching code found."}" }
 */
session_start();
//if (isset($_SESSION['InstagramAccessToken']) && !empty($_SESSION['InstagramAccessToken'])) {
//    header('Location: callback.php');
//    die();
//}
// Instantiate the API handler object
$instagram = new Instagram($config);
//Setup subscription
//print $_SESSION['InstagramAccessToken']."bye";
//if(isset($rs[0]["access_token"])) {
//	$instagram->setAccessToken($_SESSION['InstagramAccessToken']);
	if(isset($_GET['list'])) {
		$subscriptions = $instagram->listSubscriptions();
	} else if(isset($_GET['deleteall'])) {
		$subscriptions = $instagram->deleteSubscription(array(
			"object" => "all"
		));
	} else if(isset($_GET['delete'])) {
		$subscriptions = $instagram->deleteSubscription(array(
//			"object" => "tag",
			"id" => $_GET['delete']
		));
	} else if(isset($_GET['subscribe'])) {
//		print $_GET['subscribe'];
		$subscriptions = $instagram->createSubscription(array(
			// "object" => "user",
			// "object_id" => "371870962",
			"object" => "tag",
			"object_id" => $_GET['subscribe'],
			"aspect" => "media",
			"callback_url" => $config['redirect_uri']."callback.php",
		));
	} else if(isset($_GET['subscribegeo'])) {
		$subscriptions = $instagram->createSubscription(array(
			// "object" => "user",
			// "object_id" => "371870962",
			"object" => "geography",
			"lat" => "48.8582",
			"lng" => "2.2945",
			"radius" => "5000",
			"aspect" => "media",
			"callback_url" => $config['redirect_uri']."callback.php",
		));
	}

// need to record result

	print_r($subscriptions);
//}
/*else {
		$subscriptions = $instagram->createSubscription(array(
			// "object" => "user",
			// "object_id" => "371870962",
			"object" => "tag",
			"object_id" => "mydowntown",
			"aspect" => "media",
			"callback_url" => $config['redirect_uri']."subscription.php",
		));
	print "subscribing";
}
*/
//phpinfo();
