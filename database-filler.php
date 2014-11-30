<?php

/*
*	Little WTF Script by Quadrifoglio
*	Use it to fill a test DreamVids database (version 2)
*/

define('WEBROOT', str_replace('database-filler.php', '', $_SERVER['SCRIPT_NAME']), true);

class DatabaseFiller {

	private $pdo = null;
	private $dbHost = '127.0.0.1';
	private $dbUser = 'root';
	private $dbPass = '';
	private $dbName = 'dreamvids_v2';

	private $names = array('Robert', 'Norbert', 'Roger', 'Papidabowi', 'Lawl', 'Dantresangle', 'Jean-Eude', 'Dimou', 'Peter', 'DarkWos', 'Benji', 
		'Jerem', 'Charlie', 'Delta', 'Gwomos', 'Brezh', 'Michel', 'Kass', 'Pierre', 'Snapcube', 'Thib', 'Vincent',
		'Quadrifoglio', 'Olivier', 'Carglass', 'Maman', 'Grmble', 'NyanCat', 'Pedobear', 'DreamMec', 'DreamTruc', 'DreamBidule');


	public function connectToDatabase() {
		$this->pdo = new PDO('mysql:host='.$this->dbHost.';dbname='.$this->dbName, $this->dbUser, $this->dbPass);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function generateUsersAndChannels() {
		foreach ($this->names as $username) {
			$this->pdo->query("INSERT INTO users VALUES (
				'',
				'".$username."',
				'mail@wtf.com',
				'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3',
				'',
				'1398787741',
				'lol.lol.lol.lol',
				'lol.lol.lol.lol',
				'0',
				'',
				0
			)");

			$channelId = $this->generateChannelId(6);
			$userId = -1;
			$rows = $this->pdo->prepare("SELECT id FROM users WHERE username=?");
			$rows->execute(array($username));
			$userId = $rows->fetch()['id'];

			if($userId != -1) {
				$this->pdo->query("INSERT INTO users_channels VALUES (
					'".$channelId."',
					'".$username."',
					'Chaine de ".$username."',
					'".$userId."',
					';".$userId.";',
					'".WEBROOT."/assets/img/default-avatar.png',
					'".WEBROOT."/assets/img/default-background.png',
					'0',
					'',
					'0',
					'0'
				)");
			}
		}
	}

	public function generateVideos() {
		$res = $this->pdo->query("SELECT * FROM users_channels");

		$timestamp = 1398947680;

		foreach($res as $data) {
			$channelId = $data['id'];
			$channelName = $data['name'];
			$vidsPerUser = 3;

			for($i = 0; $i < $vidsPerUser; $i++) {
				$vidId = $this->generateVideoId(6);
				$posterId = $channelId;
				$vidTitle = 'Video trop cool '.$i.' de '.$channelName;
				$vidDesc = 'Description de la video: '.$vidTitle;
				$vidTags = 'lol lol2';

				$this->pdo->query("INSERT INTO videos VALUES (
					'".$vidId."',
					'".$posterId."',
					'".$vidTitle."',
					'".$vidDesc."',
					'".$vidTags."',
					'',
					'0',
					'uploads/".$channelId."/videos/".$vidId.".mp4',
					'0',
					'0',
					'0',
					'".$timestamp."',
					'2',
					'0',
					'0'
				)");

				$this->pdo->query("INSERT INTO channels_actions VALUES (
					'".$this->generateChannelActionId(6)."',
					'".$posterId."',
					';',
					'upload',
					'".$vidId."',
					null,
					'".$timestamp."'
				)");

				$timestamp++;
			}
		}
	}

	private function generateChannelId($length) {
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$id = '';
	
		for ($i = 0; $i < $length - 2; $i++) {
			$id .= $chars[rand(0, strlen($chars) - 1)];
		}

		$id = 'c_'.$id;

		return $id;
	}

	private function generateVideoId($length) {
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$id = '';
	
		for ($i = 0; $i < $length; $i++) {
			$id .= $chars[rand(0, strlen($chars) - 1)];
		}

		return $id;
	}

	private function generateChannelActionId($length) {
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$id = '';
	
		for ($i = 0; $i < $length - 2; $i++) {
			$id .= $chars[rand(0, strlen($chars) - 1)];
		}

		$id = 'a_'.$id;

		return $id;
	}

}

function letsDoThisMotherFucker() {
	$lawl = new DatabaseFiller();

	$lawl->connectToDatabase();
	$lawl->generateUsersAndChannels();
	$lawl->generateVideos();
}

letsDoThisMotherFucker();

?>
