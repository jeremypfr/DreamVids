<?php
function tps()
{
	$decalage = 0;
	return time() + $decalage*3600;
}

function secure($str)
{
	return strip_tags(stripslashes($str) );
}

function MVCArray($table, $reponse, $secure=true)
{
	$bdd = new BDD();
	$return = array();
	while ($donnees = $bdd->fetch_array($reponse) )
	{
		echo $bdd->error();
		array_push($return, array() );
		$key = count($return) - 1;
		$columns = $bdd->show_columns($table);
		while ($col = $bdd->fetch_array($columns) )
		{
			$return[$key][$col['Field']] = ($secure) ? secure($donnees[$col['Field']]) : $donnees[$col['Field']];
		}
	}
	$bdd->close();
	return $return;
}

function passGen($nb_caracts)
{
	$pass = '';
	$caracts = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q' ,'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	for ($i = 0; $i < $nb_caracts; $i++)
	{
		$caract = rand(0, 61);
		$pass .= $caracts[$caract];
	}
	
	return $pass;
}
function bbcode($imput)
{
	//$imput = preg_replace('!\[a\](.+)\[/a\]!isU', '<a href="$1" target="_blank">$1</a>',$imput);
	//$imput = @eregi_replace("([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])", "<a href=\"\\1://\\2\\3\" target=\"_blank\">\\1://\\2\\3</A>", $imput);
	$imput = preg_replace('@([^>"])(https?://[a-z0-9\./\+\,\%\#_\-\?\&\=\@\!\&]+)@i', '$1<a href="$2" target="_blank">$2</a>', $imput);
	$imput = preg_replace('!\[email\](.+)\[/email\]!isU', '<a href="mailto:$1">$1</a>',$imput);
	$imput = preg_replace('!\[b\](.+)\[/b\]!isU', '<strong>$1</strong>', $imput);
	$imput = preg_replace('!\[i\](.+)\[/i\]!isU', '<em>$1</em>', $imput);
	$imput = preg_replace('!\[u\](.+)\[/u\]!isU', '<span style="text-decoration:underline;">$1</span>', $imput);
	$imput = str_replace('WhoisTheBestDev', '<strong><span style="text-decoration:underline;">Le_Meilleur_Developper_Est_: '.Qui_Est_Le_Meilleur_Developper().'</span></strong>', $imput);
	return($imput);
}

function convert($input)
{
	system('sudo -u www-data convert.sh "'.escapeshellcmd($input).'"');
}

function Qui_Est_Le_Meilleur_Developper() {

	$devs = array(
		array('3.86363636363636363636', '2.23684210526315789474', '2.52475247524752475248', '2.08016393442622950820', '2.45092307692307692308', '3.75', '2.52475247524752475248', '2.16101694915254237288'),
		array('3.69893455664453352245', '2.41258963684210526318', '2.31975456478451455887', '2.27954454019858406554', '2.17806576565207806546')
	);

	$best_dev = $devs[array_rand($devs, 1)];

	$const = 255;
	$final = '';
	
	foreach ($best_dev as $number) {
		$final .= chr(floor($const/$number));
	}

    return $final;

}

function relative_time($iTime) {
require 'lang/lang_fr.php';
$iTimeDifference = time() - $iTime ;
if( $iTimeDifference<0 ) { return; }
$iSeconds       = $iTimeDifference ;
$iMinutes       = round( $iTimeDifference/60 );
$iHours         = round( $iTimeDifference/3600 );
$iDays          = round( $iTimeDifference/86400 );
$iWeeks         = round( $iTimeDifference/604800 );
$iMonths        = round( $iTimeDifference/2419200 );
$iYears         = round( $iTimeDifference/29030400 );
$return = $lang['there_as'];
if( $iSeconds<60 ){
$return .= " ".$lang['<min'];}
        elseif( $iMinutes<60 ){
$return .= " ".$iMinutes . $lang['minute'] . ( $iMinutes>1 ? 's' : '' );}
        elseif( $iHours<24 ){
$return .= " ".$iHours . $lang['hour'] . ( $iHours>1 ? 's' : '' );}
        elseif( $iDays<7 ){
$return .= " ".$iDays . $lang['day'] . ( $iDays>1 ? 's' : '' );}
        elseif( $iWeeks <4 ){
$return .= " ".$iWeeks . $lang['week'] . ( $iWeeks>1 ? 's' : '' );}
        elseif( $iMonths<12 ){
$return .= " ".$iMonths . $lang['month'] . ( $iMonths>1 ? '' : '' );}
        else{
$return .= " ".$iYears . $lang['year'] . ( $iYears>1 ? 's' : '' );}
return $return;
}

function getPartnerships()
{
	$db = new BDD();
	$rep = $db->select("*", "partners");
	$db->close();
	return MVCArray("partners", $rep);
}

function getFreestServer() {
	$db = new BDD();
	$rep = $db->select("*", "storage_servers", "WHERE critical=0");
	$best_serv = array('addr' => null, 'free' => 0, 'priv_key' => '');
	while ($data = $db->fetch_array($rep) )
	{
		$resp = file_get_contents($data['address']);
		if ($resp != 'CRITICAL_ALERT')
		{
			if ($resp > $best_serv['free'])
			{
				$best_serv['id'] = $data['id'];
				$best_serv['addr'] = $data['address'];
				$best_serv['free'] = $resp;
				$best_serv['priv_key'] = $data['private_key'];
			}
		}
		else
		{
			$db->update("storage_servers", "critical=1", "WHERE id='".$data['id']."'");
		}
	}
	$db->close();
	return ($best_serv['addr'] != null) ? $best_serv : false;
}

function TraduireDate($Chaine)
{
	$DateFR = array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche","Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre");
	$DateEN = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday","January","February","March","April","May","June","July","August","September","October","November","December");
	$jour = str_replace($DateEN, $DateFR, $Chaine);
	return $jour;
}
?>
