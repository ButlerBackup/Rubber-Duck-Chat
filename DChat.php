<?php
//Title: Rubber Duck Chat Server
//Author: Sand
//License: MIT
 
$database = json_decode(file("DBase.php")[1],true);
function ud_filter($string){
	$string = str_replace(" ","_",$string);
	$string = str_replace("-","",$string);
	return $string;
}
if(isset($_POST['action']) && isset($_POST['name']) && isset($_POST[ud_filter($_POST['name'])]) && strlen($_POST['name']) < 15){
	$action = $_POST['action'];
	$name = $_POST['name'];
	$user_data = $_POST[str_replace(" ","_",$_POST['name'])];
	$ip = $_SERVER['REMOTE_ADDR'];
	$login_logs = file_get_contents("login_logs.txt");
	$array_login_logs = json_decode($login_logs,true);
	if(!in_array($name,explode("|",$array_login_logs[$ip][0]))){
		@$array_login_logs[$ip][0] .= $name."|";
	}
	@$array_login_logs[$ip][1] = time();
	$json_login_logs = json_encode($array_login_logs);
	file_put_contents("login_logs.txt",$json_login_logs);
	if(substr($name,0,1) === "-" || strlen($name) < 3){
		if(!(isset($_COOKIE['DEV']))){
			die();
		}
	}
	foreach($database as $duck){
		if(intval($duck[1]) < time()-5){
			unset($database[array_search($duck,$database)]);
		}
	}
	if($_POST['action'] === 'newplayer'){
		if(!isset($database[$name]) && strval(intval($name)) !== $name){
			$database[$name] = [$user_data,time(),$ip];
			echo "ducks=:";
		}
	}
	if($_POST['action'] === 'update'){
		if(isset($database[$name])){
			if($database[$name][2] !== $_SERVER['REMOTE_ADDR']){
				die();
			}
			$players = "ducks=:";
			$users = "";
			foreach($database as $user){
				$user_name = array_search($user,$database);
				if($user_name !== $name){
					$players = $players.$user_name.":";
					$users = $users.$user_name."=".$database[$user_name][0]."&";
				}
			}
			$database[$name] = [$user_data,time(),$ip];
			echo $players."&".$users;
		}
	}

	if($_POST['action'] === "drop"){
		if(isset($database[$name])){
			unset($database[$name]);
			echo "disconnected";
		} else {
			echo "Unknown User ".$database[$name];
		}
	}

	file_put_contents("DBase.php","<?php die();?>\n".json_encode($database));
}
if(isset($_GET['logout'])){
	header("Location: ducks/");
}
if(isset($_GET['database88'])){
	var_dump($database);
	setcookie("DEV","developer_cookie");

}
?>