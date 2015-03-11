<?php

/**
 * Ce script fait l'interface de programmation (API) entre des Clients non interactif et le SecretManager.
 *
 * PHP version 5
 * @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
 * @author Pierre-Luc MARY
 * @date 2015-03-03
 */

include( 'Constants.inc.php' );

include( DIR_LIBRARIES . '/Class_IICA_Authentications_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_Security.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );

$Authentication = new IICA_Authentications();
$Secrets = new IICA_Secrets();


$XML_Header = '<?xml version="1.0" encoding="UTF-8"?>'."\n<SecretManager>\n";
$XML_Footer = "\n</SecretManager>\n";

$Public_Key_File = $Authentication->getParameter( 'Public_Key_Localization' );
$Private_Key_File = $Authentication->getParameter( 'Private_Key_Localization' );
$Private_Key = file_get_contents( $Private_Key_File );

// Teste le format de message demandé par le Client de l'API. 
if ( array_key_exists( 'format', $_GET ) ) {
	$Format = strtoupper( $_GET[ 'format' ] );
	
	if ( $Format != 'JSON' and $Format != 'XML' ) {
		print( json_encode( array( 'code' => 100, 'label' => '%E, invalid format "' . $Format . '"' ) ) );
		exit();
	}
} else {
	$Format = 'JSON';
}


// Gère l'information en fonction de l'appel réalisé.
switch( $_SERVER[ 'REQUEST_METHOD' ] ) {
 case 'GET' : // Gère les informations reçues via la méthode GET de HTTP.
	if ( array_key_exists( 'action', $_GET ) ) {
		$Action = strtoupper( $_GET[ 'action' ] );

		if ( $Action != 'GETKEY' ) {
			$statusCode = 10;
			$statusLabel = '%E, unknow action "' . $Action . '"';
		
			if ( $Format == 'JSON' ) {
				print( json_encode( array( 'code' => $statusCode, 'label' => $statusLabel ) ) );
			} else {
				print( $XML_Header .
						'<code>' . $statusCode . '</code><label>' . $statusLabel . '</label>' .
						$XML_Footer );
			}
			exit();
		}
	} else {
		$statusCode = 10;
		$statusLabel = '%E, no action';
		
		if ( $Format == 'JSON' ) {
			print( json_encode( array( 'code' => $statusCode, 'label' => $statusLabel ) ) );
		} else {
			print( $XML_Header .
					'<code>' . $statusCode . '</code><label>' . $statusLabel . '</label>' .
					$XML_Footer );
		}
		exit();
	}

	// Récupère la clé publique.
	$fileName = basename( $Public_Key_File );
	$keyValue = file_get_contents( $Public_Key_File );

	$statusCode = 0;
	$statusLabel = '%S, transferred key';
	
	if ( $Format == 'JSON' ) {
		print( json_encode( array(
				'code' => $statusCode, 'label' => $statusLabel,
				'keyFileName' => $fileName, 'key' => $keyValue
		) ) );
	} else {
		$Value = $XML_Header .
				'<code>' . $statusCode .'</code><label>' . $statusLabel . '</label>' .
				'<keyFileName>' . $fileName . '</keyFileName><key>' . $keyValue . '</key>' .
				$XML_Footer;

		print( $Value );
	}
	
	break;


 case 'POST' : // Gère les informations reçues via la méthode POST de HTTP
	session_save_path( DIR_SESSION );
	session_start();

 	if ( $Format == 'JSON' ) {
 		print_r( $_POST );
 	} else { // Récupère les informations du flux XML transmis.
		$xml_parser = xml_parser_create();
 	    if ( ! xml_parse( $xml_parser, $_POST[ 'xml' ] ) ) {
        	$Internal_Error = xml_error_string( xml_get_error_code( $xml_parser ) );
 	    	
 	    	die( $XML_Header .
				'<code>100</code><label>%E, invalid format (' . $Internal_Error . ')</label>' .
				$XML_Footer );
    	}
		xml_parser_free($xml_parser);
		
 		$Flux_XML = new DOMDocument();
 		
 		// Charge le flux XML en mémoire.
 		if ( ! @$Flux_XML->loadXML( $_POST[ 'xml' ] ) ) {
			print(
				$XML_Header .
				'<code>100</code><label>%E, invalid format (load XML)</label>' .
				$XML_Footer
				);
			exit();
 		}

		
		// Récupère la racine et vérifie qu'elle est éagle à "SecretManager".
		$Root = $Flux_XML->documentElement;

		if ( $Root->nodeName != 'SecretManager' ) {
			print(
				$XML_Header .
				'<code>100</code><label>%E, unknow format (root node not SecretManager)</label>' .
				$XML_Footer
				);
			exit();
		}

		
		// Récupère l'action précisée et vérifie que l'action soit reconnue.
		$NodesList = $Flux_XML->getElementsByTagName( 'action' );
		
		if ( $NodesList->length == 0 ) {
			print(
				$XML_Header .
				'<code>10</code><label>%E, no action</label>' .
				$XML_Footer
				);
			exit();
		} else {
			$Action = strtoupper( $NodesList->item(0)->nodeValue );
			
			if ( $Action != 'CREATE' and $Action != 'UPDATE' ) {
				print(
					$XML_Header .
					'<code>10</code><label>%E, unknow action</label>' .
					$XML_Footer
					);
				exit();
			}
		}

		
		// Récupère le nom de l'utilisateur pour se connecter à l'API de SecretManager.
		$NodesList = $Flux_XML->getElementsByTagName( 'user' );
		if ( $NodesList->length == 0 ) {
			print(
				$XML_Header .
				'<code>100</code><label>%E, unknow format (no user found)</label>' .
				$XML_Footer
				);
			exit();
		} else {
			$User = $NodesList->item(0)->nodeValue;
		}

		
		// Récupère le mot de passe de l'utilisateur de l'API.
		$NodesList = $Flux_XML->getElementsByTagName( 'password' );
		if ( $NodesList->length == 0 ) {
			print(
				$XML_Header .
				'<code>100</code><label>%E, unknow format (no password found)</label>' .
				$XML_Footer
				);
			exit();
		} else {
			if ( ! openssl_private_decrypt( base64_decode( $NodesList->item(0)->nodeValue ), $Password,
				$Private_Key ) ) {
				print(
					$XML_Header .
					'<code>200</code><label>%E, decrypt error</label>' .
					$XML_Footer
					);
				exit();
			}
		}
 	}

 	
 	// Contrôle l'authentification de l'utilisateur à l'API de SecretManager.
 	try {
 		$Salt = $Authentication->getSalt( $User );
 	
 		switch ( strtoupper( $Authentication->getParameter( 'authentication_type' ) ) ) {
 			default:
 				$Authentication_Type = 'database';
 				break;
 	
 			case 'R':
 				$Authentication_Type = 'radius';
 				break;
 	
 			case 'L':
 				$Authentication_Type = 'ldap';
 				break;
 		}
 	
 		$_SESSION[ 'Language' ] = 'en';
 	
 		$Status = $Authentication->authentication(
 				$User, $Password, $Authentication_Type, $Salt, FALSE, TRUE
 		);
 	} catch ( Exception $e ) {
		print(
			$XML_Header .
			'<code>12</code><label>%E, ' . $e->getMessage() . '</label>' .
			$XML_Footer
			);
		exit();
 	}

 	session_write_close();

 	session_save_path( DIR_SESSION );
 	session_start();
 	
 	if ( $Format == 'json' ) {
 	} else {
	 	// Récupération des Secrets à traiter (création ou mise à jour).
	 	$NodesList = $Flux_XML->getElementsByTagName( 'record' );
	 	$Total_Records = $NodesList->length;
	 	if ( $Total_Records == 0 ) {
	 		print(
	 				$XML_Header .
	 				'<code>2</code><label>%E, no Secret found</label>' .
	 				$XML_Footer
	 		);
	 		exit();
	 	} else {
	 		// Lit tous les "records" du flux.
	 		foreach( $NodesList as $Node ) {
	 			$Record_ID = $Node->getAttribute('id');
	 			
	 			// Met à zéro tous les champs.
	 			$sgr_id = '';
	 			$sgr_label = '';
	 			$stp_id = '';
	 			$env_id = '';
	 			$app_id = '';
	 			$scr_host = '';
	 			$scr_user = '';
	 			$scr_password = '';
	 			$scr_alert = '';
	 			
	 			// Lit toutes les variables contenues dans un "record".
	 			if( $Node->childNodes->length ) {
	 				foreach($Node->childNodes as $i) {
			 			$Elements[ strtolower( $i->nodeName ) ] = $i->nodeValue;
	 				}
	 			}
	 			

  				if ( array_key_exists( 'sgr_id', $Elements ) ) {
 					$sgr_id = $Elements[ 'sgr_id' ];
 				}
 				
 				if ( array_key_exists( 'sgr_label', $Elements ) ) {
 					$Groups = new IICA_Groups();
 					$sgr_id = $Groups->searchIdByLabel( $Elements[ 'sgr_label' ] );
 				}

 				if ( array_key_exists( 'stp_id', $Elements ) ) {
 					$stp_id = $Elements[ 'stp_id' ];
 				}
 				
 				if ( array_key_exists( 'env_id', $Elements ) ) {
 					$env_id = $Elements[ 'env_id' ];
 				}
 				
 				if ( array_key_exists( 'scr_host', $Elements ) ) {
 					$scr_host = $Elements[ 'scr_host' ];
 				} else {
 					print(
 							$XML_Header .
 							'<code>100</code><label>%E, unknow format (no Secret host)</label>' .
 							$XML_Footer
 					);
 					exit();
 				}
 				
 				if ( array_key_exists( 'scr_user', $Elements ) ) {
 					$scr_user = $Elements[ 'scr_user' ];
 				} else {
 					print(
 							$XML_Header .
 							'<code>100</code><label>%E, unknow format (no Secret user)</label>' .
 							$XML_Footer
 					);
 					exit();
 				}
 				
 				if ( array_key_exists( 'scr_password', $Elements ) ) {
 					$scr_password = $Elements[ 'scr_password' ];
 					if ( ! openssl_private_decrypt( base64_decode( $scr_password ), $scr_password,
 							$Private_Key ) ) {
 								print(
 										$XML_Header .
 										'<code>200</code><label>%E, decrypt error</label>' .
 										$XML_Footer
 								);
 								exit();
 							}
 				} else {
 					print(
 							$XML_Header .
 							'<code>100</code><label>%E, unknow format (no Secret password)</label>' .
 							$XML_Footer
 					);
 					exit();
 				}
	 		}
 					

	 		// Gestion d'une "création" d'un Secret dans SecretManager.
	 		if ( $Elements[ 'action' ] == 'create' ) {
	 			$scr_comment = 'API insert';
	 				
	 			 if ( $sgr_id == '' and $sgr_label == '' ) {
	 			 	if ( $Format == 'json' ) {
	 			 		
	 			 	} else {
		 				print(
	 							$XML_Header .
	 							'<code>100</code><label>%E, unknow format (no ID Group or label Group of Secrets)</label>' .
	 							$XML_Footer
	 					);
	 					exit();
	 			 	}
 				}
 					
	 				
 				if ( $stp_id == '' ) {
	 			 	if ( $Format == 'json' ) {
	 			 		
	 			 	} else {
	 					print(
	 							$XML_Header .
	 							'<code>100</code><label>%E, unknow format (no Secret Type)</label>' .
	 							$XML_Footer
	 					);
	 					exit();
	 			 	}
 				}

	 				
 				if ( $env_id == '' ) {
	 			 	if ( $Format == 'json' ) {
	 			 		
	 			 	} else {
	 					print(
	 							$XML_Header .
	 							'<code>100</code><label>%E, unknow format (no Secret Environment)</label>' .
	 							$XML_Footer
	 					);
	 					exit();
	 			 	}
 				}
	 				
 				// Mise à jour de la base de données du SecretManager.
				try {
					$Secrets->set( '', $sgr_id, $stp_id, $scr_host, $scr_user, $scr_password,
 						$scr_comment, $scr_alert, $env_id, $app_id );
				} catch ( Exception $e ) {
	 			 	if ( $Format == 'json' ) {
	 			 		
	 			 	} else {
						print(
	 							$XML_Header .
	 							'<code>20</code><label>%E, ' . $e->getMessage() . '</label>' .
	 							$XML_Footer
	 					);
	 					exit();
	 			 	}
				}
 			}
 			elseif ( $Elements[ 'action' ] == 'update' ) {
 				$scr_comment = 'API update';

				// Recherche l'ID du Secret.
 				$scr_id = $Secrets->searchSecret( $scr_host, $scr_user );
 				if ( $scr_id == '' ) {
	 			 	if ( $Format == 'json' ) {
	 			 		
	 			 	} else {
	 					print(
	 							$XML_Header .
	 							'<code>105</code><label>%E, Secret doesn\'t exists</label>' .
	 							$XML_Footer
	 					);
	 					exit();
	 			 	}
 				}
 				
				// Mise à jour de la base de données du SecretManager.
				try {
					$Secrets->set( $scr_id, $sgr_id, $stp_id, $scr_host, $scr_user, $scr_password,
 						$scr_comment, $scr_alert, $env_id, $app_id );
				} catch ( Exception $e ) {
	 			 	if ( $Format == 'json' ) {
	 			 		
	 			 	} else {
						print(
	 							$XML_Header .
	 							'<code>20</code><label>%E, ' . $e->getMessage() . '</label>' .
	 							$XML_Footer
	 					);
	 					exit();
	 			 	}
				}
 			}
	 		
	 		print(
	 			$XML_Header .
	 			'<total>' . $Total_Records . '</total>' .
	 			$XML_Footer
	 		);
	 		exit();
	 	}
 	} 	
 	
	break;
	
	
 default:
	$statusCode = 10;
	$statusLabel = '%E, invalid call method';
		
	print( json_encode( array( 'code' => $statusCode, 'label' => $statusLabel ) ) );

	break;
}

?>