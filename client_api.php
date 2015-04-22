<?php

/**
 * Ce script donne un exemple d'appel à l'interface de programmation (API) du SecretManager.
 *
 * PHP version 5
 * @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
 * @author Pierre-Luc MARY
 * @date 2015-03-03
 */

// Contrôle les options reçues en argument sur la ligne de commande.
$ShortOpts  = "";
$ShortOpts .= "f:";  // Précise le fichier de test externe à utiliser.
$ShortOpts .= "i:"; // Précise le format du flux à envoyer et recevoir.

$Arguments = getopt( $ShortOpts );
//print_r($Arguments);exit();

if ( array_key_exists( 'i', $Arguments ) ) {
	$Arguments[ 'i' ] = strtolower( $Arguments[ 'i' ] );
	if ( $Arguments[ 'i' ] != 'json' and $Arguments[ 'i' ] != 'xml' ) {
		print( '%E, unexpected value for "-i" (possible values : "json" or "xml")'."\n");
		exit();
	}
	$Format = $Arguments[ 'i' ];
} else {
	$Format = 'xml';
}

$URL = 'https://secretmanager.localhost/SM-api.php?format=' . $Format;

$XML_Header = '<?xml version="1.0" encoding="UTF-8"?>'."\n<SecretManager>\n";
$XML_Footer = "\n</SecretManager>";

// Récupération de la clé publique.
$Response = @file_get_contents( $URL . "&action=getKey" );
if ( $Response === FALSE ) {
	print("\r\n" . '%ERR, Connection refused or SecretManager not running'. "\r\n\r\n");
	exit();
}

if ( $Format == 'json' ) {
	$Response = json_decode( $Response );
	
	if ( $Response->code != 0 ) {
		print( $Response->label );
		exit();
	}
	
	$Key = $Response->key;
} else {
	//print_r($Response);
	$Flux_XML = new DOMDocument();
	$Flux_XML->loadXML( $Response );

	if ( $Flux_XML->getElementsByTagName('code')->item(0)->nodeValue != 0 ) {
		print( $Flux_XML->getElementsByTagName('label')->item(0)->nodeValue );
		exit();
	}
	
	$Key = $Flux_XML->getElementsByTagName('key')->item(0)->nodeValue;
}


// Chiffre le mot de passe avant envoi.
if ( ! openssl_public_encrypt('pouet', $Password, $Key ) ) {
	print('%E, encrypt error');
	exit();
}
$Password = base64_encode( $Password );


// Création d'une nouvelle clé
if ( $Format == 'json' ) {
	$Data = array(
			'user' => 'uapi',
			'password' => $Password,
			'records' => array(
					'1' => array(
							'action' => 'update',
							'scr_host' => 'http://secretmanager.free.fr',
							'scr_user' => 'root',
							'scr_password' => 'B+UsKDB+kCoPe3tKZbi93amIRgYSdRMoCbxdEqISWZNWFDSLZ20I9cZu8TKJ9wDPNTcUG+lbWiLcUmPAF9pvLzBK/iqMEVjxinHkgZMi+aK8o89aAMcB4hwlSBLdtAi+JmReWOUp995qkCn1DGSTY9vxyuSfLPsPH/C0Cmo88gc='
					),
					'2' => array(
							'action' => 'create',
							'sgr_label' => 'pré-prod',
							'stp_id' => 1,
							'env_id' => 1,
							'scr_host' => 'secretmanager.free.org.3',
							'scr_user' => 'root',
							'scr_password' => 'B+UsKDB+kCoPe3tKZbi93amIRgYSdRMoCbxdEqISWZNWFDSLZ20I9cZu8TKJ9wDPNTcUG+lbWiLcUmPAF9pvLzBK/iqMEVjxinHkgZMi+aK8o89aAMcB4hwlSBLdtAi+JmReWOUp995qkCn1DGSTY9vxyuSfLPsPH/C0Cmo88gc='
					)
			)
	);
} else {
	$Data = array(
			'xml' => $XML_Header .
				'<user>uapi</user><password>' . $Password . '</password>' .
				'<record id="2">' .
				'<action>update</action>' .
				'<scr_host>http://secretmanager.free.fr</scr_host>' .
				'<scr_user>root</scr_user>' .
				'<scr_password>B+UsKDB+kCoPe3tKZbi93amIRgYSdRMoCbxdEqISWZNWFDSLZ20I9cZu8TKJ9wDPNTcUG+lbWiLcUmPAF9pvLzBK/iqMEVjxinHkgZMi+aK8o89aAMcB4hwlSBLdtAi+JmReWOUp995qkCn1DGSTY9vxyuSfLPsPH/C0Cmo88gc=</scr_password>' .
				'</record>' .
				'<record id="1">' .
				'<action>create</action>' .
				'<sgr_label>pré-prod</sgr_label>' .
				'<stp_id>1</stp_id>' .
				'<env_id>1</env_id>' .
				'<scr_host>secretmanager.free.org.1</scr_host>' .
				'<scr_user>root</scr_user>' .
				'<scr_password>B+UsKDB+kCoPe3tKZbi93amIRgYSdRMoCbxdEqISWZNWFDSLZ20I9cZu8TKJ9wDPNTcUG+lbWiLcUmPAF9pvLzBK/iqMEVjxinHkgZMi+aK8o89aAMcB4hwlSBLdtAi+JmReWOUp995qkCn1DGSTY9vxyuSfLPsPH/C0Cmo88gc=</scr_password>' .
				'</record>' .
			$XML_Footer
			);
}


// Formatage de la requête de mise à jour.
$Options = array(
		'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST'
		),
);

// Utiliser le mot clé 'http' même si vous envoyez vos requêtes en 'https'.
if ( $Format == 'JSON' ) {
	$Options[ 'http' ][ 'header' ] = "Content-Type: application/json\r\n"; 
	$Options[ 'http' ][ 'content' ] = json_encode( $Data );
} else {
	$Options[ 'http' ][ 'content' ] = http_build_query( $Data );
}

$Context  = stream_context_create( $Options );

$result = file_get_contents( $URL, FALSE, $Context );

// Affiche le résultat de la requête de mise à jour.
print($result);
?>