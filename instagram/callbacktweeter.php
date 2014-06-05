<?
include 'comet.php';


$mysqli = new mysqli("p:localhost", "instagram", "", "instagram");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$config = array(
        'client_id' => '3f5fe543985246eba3b26703dc166117',
        'client_secret' => '715379dc66674209a39d7728a5937f73',
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'http://natame.com/instagram/',
     );

$fp = fopen('/tmp/request.log', 'a');


if (isset ($_POST['URL'])){
	$URL = $_POST['URL'];
	$ID = $_POST['ID'];
	$HASHTAG = $_POST['HASHTAG'];
	fwrite($fp, $URL);
	fwrite($fp, $ID);
	fwrite($fp, $HASHTAG);
	fwrite($fp, "\n");
	$query = "INSERT INTO `instagram`(`id`, `subscription_id`, `object`, `object_id`, `created_time`, `url`) VALUES ('$ID','2','tweet','$HASHTAG',UNIX_TIMESTAMP(),'$URL')\n";
	//fwrite($fp,$query);
        if ($mysqli->query($query)){
		$oPhomet = new Bayeux('http://natame.com:8080/cometd');
		$oPhomet->publish('/stock/INSTAGRAM', array('symbol' => 'INSTAGRAM','oldValue'=>'','newValue'=>$URL));
        }
}
exit;

// check if we have a security challenge
if (isset ($_POST['hub_challenge']))
            echo $_GET['hub_challenge'];  
    else // This is an update
    {
        // read  the content of $_POST
        $myString = file_get_contents('php://input');
	fwrite($fp, $myString);
	fwrite($fp, "\n");

        $answer = json_decode($myString);
//	fwrite($fp, var_dump($answer));
//	fwrite($fp, "\n");

        // This is not working  starting from here
        $object_id = $answer[0]->object_id;
        $object = $answer[0]->object;
        $subscription_id = $answer[0]->subscription_id;
        $time = $answer[0]->time;
        $url = 'https://api.instagram.com/v1/tags/'.$object_id.'/media/recent?client_id='.$config['client_id']; //.'&max_id='.$time;
	//fwrite($fp, $url);
	//fwrite($fp, "\n");
	//die();

/*
$ch = curl_init($url);
$fp = fopen("/tmp/example_homepage.txt", "a");

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);

curl_exec($ch);
curl_close($ch);
fclose($fp);

die();

*/
	$ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
//        curl_setopt($ch, CURLOPT_NOBODY, FALSE); // remove body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $head = curl_exec($ch);
//	fwrite($fp,$head);
//	fwrite($fp,"\n");
	$jhead = json_decode($head,true);
        curl_close($ch);
	if (is_array($jhead['data'])){
		foreach ($jhead['data'] as $pic){
			processPic($pic);
		}
	} else {
		error_log("got single element");
		processPic($jhead['data']);
	}

        if($jhead){

        }
	fclose($fp);

    }
    function processPic ($pic){
		global $query,$subscription_id,$object,$object_id,$time,$mysqli;
		$id = $pic['id'];
		$created_time = $pic['created_time'];
		$url = $pic['images']['standard_resolution']['url'];
		//mysql_query($db,
		$query = "INSERT INTO `instagram`(`id`, `subscription_id`, `object`, `object_id`, `created_time`, `url`) VALUES ('$id','$subscription_id','$object','$object_id',$time,'$url')\n";
		//fwrite($fp,$query);
		if ($mysqli->query($query)){
/*			fwrite($fp, $pic['id']);
			fwrite($fp, " ");
			fwrite($fp, $pic['created_time']);
			fwrite($fp, " ");
			fwrite($fp, date('c',$pic['created_time']));
			fwrite($fp, " ");
			fwrite($fp, $pic['images']['standard_resolution']['url']);
			fwrite($fp,"\n");
*/
		} else {
//			error_log("Error: $mysqli->error in query: $query");
		}
    }
?>
