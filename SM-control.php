<?php

/**
* Ce script gère le tableau de bord d'administration de l'outil SecretManager.
*
* PHP version 5.4
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.3
* @date 2013-10-28
*
*/

function testURL( $URL ) {
 	$URL_Test = parse_url( $URL );

 	if ( $URL_Test['scheme'] == 'https' ){
 		$Secure_Protocol = 'ssl://';
 		$Port = 443;
 	} else {
 		$Secure_Protocol = '';
 		$Port = 80;
 	}

 	// Ouvre une connexion vers le serveur.
	$fp = @fsockopen( $Secure_Protocol . $URL_Test['host'], $Port, $errno, $errstr, 30);
	if (!$fp) {
 		$tStatus = array( 'Status' => 'KO (' . $errstr . ')', 'Color' => 'orange' );
	} else {
	    $out = "GET / HTTP/1.1\r\n";
    	$out .= "Host: " . $URL_Test['host'] . "\r\n";
    	$out .= "Connection: Close\r\n\r\n";

    	// Envoi la requête HTTP.
    	fwrite($fp, $out);

    	// Lit la réponse HTTP du serveur.
       	$Response = explode( ' ', fgets($fp, 128) );
       	if ( $Response[1] != '200' and $Response[1] != '302' ) {
	 		$tStatus = array( 'Status' => 'KO (' . implode(' ', $Response) . ')', 'Color' => 'orange' );
		} else {
 			$tStatus = array( 'Status' => 'OK', 'Color' => 'green' );
 		}

	    fclose($fp);
    }

    return $tStatus;
}


function testDIR( $DIR ) {
	$tStatus = array();

   	$tStatus[ "Read" ] = FALSE;
    $tStatus[ "Write" ] = FALSE;
 	$tStatus['Directory'] = TRUE;

 	if ( ! is_dir( $DIR ) ) {
 		$tStatus['Directory'] = FALSE;
 	} else {
	    if ( $pDIR = @opendir( $DIR ) ) {
    	    if ( ( $file = @readdir( $pDIR ) ) !== FALSE ) {
        	    $tStatus[ "Read" ] = TRUE;
	        	@closedir( $pDIR );
        	}

        	$tmpName = $DIR . '/' . time(). '.sm' ;

	    	if ( ( $tempFILE = @fopen( $tmpName, 'w+' ) ) !== FALSE ) {
	    		fclose( $tempFILE );
	    		unlink($tmpName);

        	    $tStatus[ "Write" ] = TRUE;
	    	}
    	}

	 	// Récupère les informations sur le répertoire.
		$Info = stat( $DIR );

		$tStatus['Mode'] = decoct($Info['mode']);
 	}

    return $tStatus;
}


include( 'Constants.inc.php' );

session_save_path( DIR_SESSION );
session_start();

$Search_Style = 2;

// Par défaut langue Française.
if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'fr';

if ( array_key_exists( 'Lang', $_GET ) ) {
   $_SESSION[ 'Language' ] = $_GET[ 'Lang' ];
}
	
$Script = $_SERVER[ 'SCRIPT_NAME' ];

if ( ! array_key_exists( 'HTTPS', $_SERVER ) )
	header( 'Location: https://localhost/' . $Script );

$Action = '';
$Choose_Language = 0;


include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LIBRARIES . '/Class_HTML.inc.php' );


// Création du gestionnaire de pages sans accès à la base.
$DB_Access = 1;
$PageHTML = new HTML( $DB_Access );


include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-users.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );


// Charge les différents objets utiles à cet écran.


$Javascripts = array( );

print( $PageHTML->enteteHTML( $L_Title, $Choose_Language, $Javascripts ) .
 "   <!-- debut : zoneTitre -->\n" .
 "   <div id=\"zoneTitre\">\n" .
 "    <div id=\"icon-tools\" class=\"icon36\"></div>\n" .
 "    <span id=\"titre\">". $L_Title . "</span>\n" .
// $PageHTML->afficherActions( $PageHTML->is_administrator() ) .
 "    </div> <!-- Fin : zoneTitre -->\n" .
 "\n" .
 "   <!-- debut : zoneMilieuComplet -->\n" .
 "   <div id=\"zoneMilieuComplet\">\n" .
 "\n" );

switch( $Action ) {
 default:
 	print( "     <div id=\"dashboard\">\n\n" .
		 "     <!-- Début : affiche la synthèse des constantes du SecretManager -->\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\">" . $L_Constants_File . "</p>\n" .
		 "      <div class=\"corps\" id=\"constants_file\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_DNS_or_IP . " : </span>\n" .
		 "        <span class=\"bold\">" . SERVER . "</span>\n" .
		 "       </p>\n" );

 	$Test = testURL( URL_BASE );

	print ( "       <p>\n" .
		 "        <span>" . $L_URL_Access . " \"URL_BASE\" : </span>\n" .
		 "        <span class=\"bg-" . $Test['Color'] . " bold rl_padding\">" . $Test['Status'] . "</span>\n" .
		 "       </p>\n" );

 	$Test = testURL( URL_PICTURES );

	print( "       <p>\n" .
		 "        <span>" . $L_URL_Access . " \"URL_PICTURES\" : </span>\n" .
		 "        <span class=\"bg-" . $Test['Color'] . " bold rl_padding\">" . $Test['Status'] . "</span>\n" .
		 "       </p>\n" );

 	$Test = testURL( URL_LIBRARIES );

	print( "       <p>\n" .
		 "        <span>" . $L_URL_Access . " \"URL_LIBRARIES\" : </span>\n" .
		 "        <span class=\"bg-" . $Test['Color'] . " bold rl_padding\">" . $Test['Status'] . "</span>\n" .
		 "       </p>\n" );

 	$Test = testDIR( DIR_SESSION );

 	if ( $Test['Directory'] === TRUE and $Test['Read'] === TRUE and $Test['Write'] === TRUE ) {
 		$Color = 'green';
 		$Status = 'OK';
 	} else {
 		$Color = 'orange';
 		if ( $Test['Directory'] === FALSE ) {
	 		$Status = 'This file is not a directory';
	 	} else {
	 		$Status = '';
	 		if ( $Test['Read'] === FALSE ) $Status .= 'read';

	 		if ( $Test['Write'] === FALSE ) {
	 			if ( $Status != '' ) $Status .= ' and ';

	 			$Status .= 'write';
	 		}

	 		$Status = 'no rights ' . $Status;
	 	}
 	}

	print( "       <p>\n" .
		 "        <span>" . $L_Dir_Access . " \"DIR_SESSION\" : </span>\n" .
		 "        <span class=\"bg-" . $Color . " bold rl_padding\">" . $Status . "</span>\n" .
		 "       </p>\n" );

 	$Test = testDIR( DIR_BACKUP );

 	if ( $Test['Directory'] === TRUE and $Test['Read'] === TRUE and $Test['Write'] === TRUE ) {
 		$Color = 'green';
 		$Status = 'OK';
 	} else {
 		$Color = 'orange';
 		if ( $Test['Directory'] === FALSE ) {
	 		$Status = 'This file is not a directory';
	 	} else {
	 		$Status = '';
	 		if ( $Test['Read'] === FALSE ) $Status .= 'read';

	 		if ( $Test['Write'] === FALSE ) {
	 			if ( $Status != '' ) $Status .= ' and ';

	 			$Status .= 'write';
	 		}

	 		$Status = 'no rights ' . $Status;
	 	}
 	}

	print( "       <p>\n" .
		 "        <span>" . $L_Dir_Access . " \"DIR_BACKUP\" : </span>\n" .
		 "        <span class=\"bg-" . $Color . " bold rl_padding\">" . $Status . "</span>\n" .
		 "       </p>\n" );

 	$Test = testDIR( DIR_LIBRARIES );

 	if ( $Test['Directory'] === TRUE and $Test['Read'] === TRUE ) {
 		$Color = 'green';
 		$Status = 'OK';
 	} else {
 		$Color = 'orange';
 		if ( $Test['Directory'] === FALSE ) {
	 		$Status = 'This file is not a directory';
	 	} else {
	 		$Status = '';
	 		if ( $Test['Read'] === FALSE ) $Status .= 'read';

	 		$Status = 'no right ' . $Status;
	 	}
 	}

	print( "       <p>\n" .
		 "        <span>" . $L_Dir_Access . " \"DIR_LIBRARIES\" : </span>\n" .
		 "        <span class=\"bg-" . $Color . " bold rl_padding\">" . $Status . "</span>\n" .
		 "       </p>\n" );

 	$Test = testDIR( DIR_LABELS );

 	if ( $Test['Directory'] === TRUE and $Test['Read'] === TRUE ) {
 		$Color = 'green';
 		$Status = 'OK';
 	} else {
 		$Color = 'orange';
 		if ( $Test['Directory'] === FALSE ) {
	 		$Status = 'This file is not a directory';
	 	} else {
	 		$Status = '';
	 		if ( $Test['Read'] === FALSE ) $Status .= 'read';

	 		$Status = 'no right ' . $Status;
	 	}
 	}

	print( "       <p>\n" .
		 "        <span>" . $L_Dir_Access . " \"DIR_LABELS\" : </span>\n" .
		 "        <span class=\"bg-" . $Color . " bold rl_padding\">" . $Status . "</span>\n" .
		 "       </p>\n" );

 	$Test = testDIR( DIR_RADIUS );

 	if ( $Test['Directory'] === TRUE and $Test['Read'] === TRUE ) {
 		$Color = 'green';
 		$Status = 'OK';
 	} else {
 		$Color = 'orange';
 		if ( $Test['Directory'] === FALSE ) {
	 		$Status = 'This file is not a directory';
	 	} else {
	 		$Status = '';
	 		if ( $Test['Read'] === FALSE ) $Status .= 'read';

	 		$Status = 'no right ' . $Status;
	 	}
 	}

	print( "       <p>\n" .
		 "        <span>" . $L_Dir_Access . " \"DIR_RADIUS\" : </span>\n" .
		 "        <span class=\"bg-" . $Color . " bold rl_padding\">" . $Status . "</span>\n" .
		 "       </p>\n" );

 	$Test = testDIR( DIR_PICTURES );

 	if ( $Test['Directory'] === TRUE and $Test['Read'] === TRUE ) {
 		$Color = 'green';
 		$Status = 'OK';
 	} else {
 		$Color = 'orange';
 		if ( $Test['Directory'] === FALSE ) {
	 		$Status = 'This file is not a directory';
	 	} else {
	 		$Status = '';
	 		if ( $Test['Read'] === FALSE ) $Status .= 'read';

	 		$Status = 'no right ' . $Status;
	 	}
 	}

	print( "       <p>\n" .
		 "        <span>" . $L_Dir_Access . " \"DIR_PICTURES\" : </span>\n" .
		 "        <span class=\"bg-" . $Color . " bold rl_padding\">" . $Status . "</span>\n" .
		 "       </p>\n" );

	
	// Test les accès aux différents fichiers précisés dans le fichier des Constantes.
	$Files_List = array(
			'MAIL_BODY' => MAIL_BODY,
			'SYSLOG_BODY' => SYSLOG_BODY,
			'INTEGRITY_FILENAME' => INTEGRITY_FILENAME,
			'MASTER_INTEGRITY_FILENAME' => MASTER_INTEGRITY_FILENAME,
			'CONSTRAINTS_DB_FILENAME' => CONSTRAINTS_DB_FILENAME,
			'FILE_AUTHORIZED_CLIENT_LIST' => FILE_AUTHORIZED_CLIENT_LIST
			);

	foreach( $Files_List as $Key => $File ) {
		if ( file_exists( $File ) ) {
			if ( is_readable( $File ) )	{
 				$Color = 'green';
				$Status = 'OK';
			} else {
 				$Color = 'orange';
				$Status = 'File not readable';
			}
		} else {
 			$Color = 'orange';
			$Status = 'No file found';
		}

		print( "       <p>\n" .
			 "        <span>" . $L_File_Access . " \"" . $Key . "\" : </span>\n" .
			 "        <span class=\"bg-" . $Color . " bold rl_padding\">" . $Status . "</span>\n" .
			 "       </p>\n" );
	}	
	
	print( "      </div> <!-- Fin : corps -->\n" .
		 //"      <p class=\"align-center\"><a class=\"button\" href=\"#\">" . $L_Management . "</a></p>\n" .
		 "     </div> <!-- Fin : affichage de la synthèse des utilisateurs -->\n\n" );

	include_once( DIR_LIBRARIES . '/Class_IICA_DB_Connector_PDO.inc.php');

	try {
		$DB_Connector = new IICA_DB_Connector();

		$Status = 'OK';
		$Color = 'green';
	} catch( Exception $e ) {
		$Status = $e->getMessage();
		$Color = 'orange';
	}

	print( "     <!-- Début : affiche la synthèse à la connexion du SecretManager -->\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\">" . $L_DB_Connection_Control . "</p>\n" .
		 "      <div class=\"corps\" id=\"db_connection\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_DB_Connection . " : </span>\n" .
		 "        <span class=\"bg-" . $Color . " bold rl_padding\">" . $Status . "</span>\n" .
		 "       </p>\n" .
		 "      </div> <!-- Fin : corps -->\n" .
		 //"      <p class=\"align-center\"><a class=\"button\" href=\"#\">" . $L_Management . "</a></p>\n" .
		 "     </div> <!-- Fin : affichage de la synthèse des utilisateurs -->\n\n" );

	include_once( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
	include( DIR_LIBRARIES . '/Class_Secrets_Server.inc.php' );

	$Secret_Server = new Secret_Server();

	try {
		list( $Status, $Operator, $Creating_Date ) = $Secret_Server->SS_statusMotherKey();
		$Color = 'green';
	} catch( Exception $e ) {
		$Status = ${$e->getMessage()};
		$Color = 'orange';
	}

	print( "     <!-- Début : affiche la synthèse du SecretServer -->\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\">" . $L_SecretServer_Control . "</p>\n" .
		 "      <div class=\"corps\" id=\"db_connection\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_SecretServer_Status . " : </span>\n" .
		 "        <span class=\"bg-" . $Color . " bold rl_padding\">" . $Status . "</span>\n" .
		 "       </p>\n" .
		 "      </div> <!-- Fin : corps -->\n" .
		 //"      <p class=\"align-center\"><a class=\"button\" href=\"#\">" . $L_Management . "</a></p>\n" .
		 "     </div> <!-- Fin : affichage de la synthèse des utilisateurs -->\n\n" );


	break;
}

print( "    <div style=\"clear: both;\"></div>\n" .
 "    <p class=\"align-center\"><a class=\"button\" href=\"" . URL_BASE . "/SM-admin.php\">" . $L_Return . "</a></p>\n" .
 "   </div> <!-- Fin : dashboard -->\n" .
 "   </div> <!-- Fin : zoneMilieuComplet -->\n" .
 "   <div id=\"afficherSecret\" class=\"tableau_synthese hide modal\" style=\"top:50%;left:40%;\">\n".
 "    <button type=\"button\" class=\"close\">×</button>\n".
 "    <p class=\"titre\">".$L_Secret_View."</p>\n".
 "    <div id=\"detailSecret\" style=\"margin:6px;padding:6px;min-width:150px;\" class=\"corps vertical-align align-center\"></div>\n" .
 "   </div> <!-- Fin : afficherSecret -->\n" .
 $PageHTML->construireFooter( 1, 'home' ) .
 $PageHTML->piedPageHTML() );

?>