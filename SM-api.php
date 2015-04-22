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
include( DIR_LIBRARIES . '/Class_HTML.inc.php' );

$Authentication = new IICA_Authentications();
$Secrets = new IICA_Secrets();
$PageHTML = new HTML();
$Security = new Security();
$Groups = new IICA_Groups();

$XML_Header = '<?xml version="1.0" encoding="UTF-8"?>'."\n<SecretManager>\n";
$XML_Footer = "\n</SecretManager>\n";

$Public_Key_File = $Authentication->getParameter( 'Public_Key_Localization' );
$Private_Key_File = $Authentication->getParameter( 'Private_Key_Localization' );
$Private_Key = file_get_contents( $Private_Key_File );


/**
 * Cette fonction controle si l'utilisateur à les bons droits d'accès sur un Groupe de Secrets.
 * 
 * @author Pierre-Luc MARY
 *
 * @param[in] int $sgr_id Id. du Groupe de Secrets qui va être accédé
 * @param[in] array $Rights Liste des Droits de l'utilisateur sur les Groupes de Secrets
 * @param[in] int $Access Type d'accès demandé par l'utilisateur (1 = Lecture, 2 = création, 3 = Modification, 4 = suppression)
 * @return boolean Vrai si l'utilisateur à le droit, sinon Faux 
 */
function rightsControl( $sgr_id, $Rights, $Access ) {
	global $Authentication;
	
	if ( $Authentication->is_administrator() ) return TRUE;

	if ( ! array_key_exists( $sgr_id, $Rights ) ) return FALSE;

	if ( $Rights[ $sgr_id ]->rgh_id < $Access ) return FALSE;
	
	return TRUE;
}


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

$Verbosity_Alert = $Authentication->getParameter( 'verbosity_alert' );


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

	// Contrôle si l'IP de l'appelant fait partie des IP autorisées.
	$AuthorizedClients = file_get_contents( FILE_AUTHORIZED_CLIENT_LIST );
	if ( $AuthorizedClients != '' ) {
		if ( strpos( $AuthorizedClients, ',' ) !== FALSE ) {
			$T_AuthorizedClients = explode( ',', $AuthorizedClients );
		} else {
			$T_AuthorizedClients = explode( ';', $AuthorizedClients );
		}
		
		foreach( $T_AuthorizedClients as $Node ) {
			$Temp[] = trim( $Node ); 
		}
		
		$T_AuthorizedClients = $Temp;

		if ( in_array( $_SERVER[ 'REMOTE_ADDR' ], $T_AuthorizedClients ) === FALSE ) {
			if ( $Format == 'JSON' ) {
				print( json_encode( array(
						'code' => 300, 'label' => 'IP client not authorized'
				) ) );
			} else { // Récupère les informations du flux XML transmis.
				$Value = $XML_Header .
						'<code>300</code><label>IP client not authorized</label>' .
						$XML_Footer;
		
				print( $Value );
			}
				
			exit();
		}
	}
	
	// Récupère les éléments d'authentification et test le format dans le cas d'un flux XML.
 	if ( $Format == 'JSON' ) {
 		if ( array_key_exists( 'user', $_POST ) ) {
 			$User = $_POST[ 'user' ];
 		} else {
			print( json_encode( array(
					'code' => 100, 'label' => '%E, unknow format (no user found)'
			) ) );
			
			exit();
 		}
 		
 		if ( array_key_exists( 'password', $_POST ) ) {
 		 	if ( ! openssl_private_decrypt( base64_decode( $_POST[ 'password' ] ), $Password,
 			 $Private_Key ) ) {
				print( json_encode( array(
						'code' => 200, 'label' => '%E, decrypt error'
				) ) );

				exit();
 			}
 		} else {
			print( json_encode( array(
					'code' => 100, 'label' => '%E, unknow format (no password found)'
			) ) );
			
 			exit();
 		}
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

		
		// Récupère la racine et vérifie qu'elle est égale à "SecretManager".
		$Root = $Flux_XML->documentElement;

		if ( $Root->nodeName != 'SecretManager' ) {
			print(
				$XML_Header .
				'<code>100</code><label>%E, unknow format (root node not SecretManager)</label>' .
				$XML_Footer
				);
			exit();
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
 		
 		$GroupsList = $Groups->listGroups( $_SESSION['idn_id'] );
 		if ( $GroupsList == array() and ! $Authentication->is_administrator() ) {
		 	if ( $Format == 'JSON' ) {
				print( json_encode( array(
						'code' => 13, 'label' => '%E, no Group associated'
				) ) );
		 	} else {
	 			print(
					$XML_Header .
					'<code>13</code><label>%E, no Group associated</label>' .
					$XML_Footer
		 		);
		 	}
 			exit();
 		}
 	} catch ( Exception $e ) {
	 	if ( $Format == 'JSON' ) {
			print( json_encode( array(
					'code' => 12, 'label' => '%E, ' . $e->getMessage()
			) ) );
	 	} else {
	 		print(
				$XML_Header .
				'<code>12</code><label>%E, ' . $e->getMessage() . '</label>' .
				$XML_Footer
				);
	 	}
		exit();
 	}

 	
 	// Démarre une session pour stocker le résultat de l'authentification (utile par la suite pour les
 	// contrôles du SecretServer).
 	session_write_close();
 	session_save_path( DIR_SESSION );
 	session_start();
 	
	$Records_Status = '';


	// Lit tous les Secrets à mettre à jour et les stockent en mémoire.
 	if ( $Format == 'JSON' ) {
	 	if ( ! array_key_exists( 'records', $_POST ) ) {
			print( json_encode( array(
					'code' => 2, 'label' => '%E, no Secret found'
			) ) );

			exit();
	 	}
	 	
	 	$Total_Records = count( $_POST[ 'records' ] );
	 	if ( $Total_Records == 0 ) {
	 		print( json_encode( array(
	 				'code' => 2, 'label' => '%E, no Secret found'
	 		) ) );
	 		
	 		exit();
	 	}

 		foreach( $_POST[ 'records' ] as $Record_ID => $Record ) {
 			foreach( $Record as $Key => $Value ) {
	 			$Records[ $Record_ID ][ strtolower( $Key ) ] = $Value;
 			}
  		}
 	} else {
	 	// Recherche les "Records" dans le flux (1 record = 1 Secret).
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
	 		// Lit tous les "Records" trouvés dans le flux.
	 		foreach( $NodesList as $Node ) {
	 			$Record_ID = $Node->getAttribute('id');
	 			
	 			// Lit tous les "Champs" contenus dans un "Record" et les stockent en mémoire.
	 			if( $Node->childNodes->length ) {
	 				foreach($Node->childNodes as $i) {
	 					$Records[ $Record_ID ][ strtolower( $i->nodeName ) ] = $i->nodeValue;
	 				}
	 			}
	 		}
	 	}
 	}


	// Réalise les mises à jour en fonction des Secrets stockés en mémoire.
	foreach( $Records as $Record_ID => $Record ) {
 		if ( ! array_key_exists( 'action', $Record ) ) {
 			if ( $Format == 'XML' ) {
				print(
					$XML_Header .
					'<code>10</code><label>%E, no action</label>' .
					$XML_Footer
				);
 			} else {
 				 if ( $Total_Records == 0 ) {
				 	print( json_encode( array(
				 		'code' => 10, 'label' => '%E, no action'
				 	) ) );
				 }
 			}
				 		
			exit();
 		} else {
			$action = strtolower( $Record[ 'action' ] );

			if ( $action != 'create' and $action != 'update' ) {
				if ( $Format == 'XML' ) {
					print(
						$XML_Header .
						'<code>10</code><label>%E, unknow action</label>' .
						$XML_Footer
					);
				} else {
					print( json_encode( array(
							'code' => 10, 'label' => '%E, unknow action'
					) ) );
				}
				exit();
			}
		}
	 			
		
	 	// Initialise les variables utiles aux mises à jour.
		if ( array_key_exists( 'sgr_id', $Record ) ) {
			$sgr_id = $Record[ 'sgr_id' ];
		} else {
			$sgr_id = '';
		}
 				
		if ( array_key_exists( 'sgr_label', $Record ) ) {
			$sgr_id = $Groups->searchIdByLabel( $Record[ 'sgr_label' ] );
		}

		if ( array_key_exists( 'stp_id', $Record ) ) {
			$stp_id = $Record[ 'stp_id' ];
		} else {
			$stp_id = '';
		}
 				
		if ( array_key_exists( 'env_id', $Record ) ) {
			$env_id = $Record[ 'env_id' ];
		} else {
			$env_id = '';
		}
 				
		if ( array_key_exists( 'scr_host', $Record ) ) {
			$scr_host = $Record[ 'scr_host' ];
		} else {
		 	if ( $Format == 'JSON' ) {
				print( json_encode( array(
						'code' => 100, 'label' => '%E, unknow format (no Secret host)'
				) ) );
		 	} else {
				print(
					$XML_Header .
					'<code>100</code><label>%E, unknow format (no Secret host)</label>' .
					$XML_Footer
				);
		 	}
		 	
			exit();
		}
 				
		if ( array_key_exists( 'scr_user', $Record ) ) {
			$scr_user = $Record[ 'scr_user' ];
		} else {
		 	if ( $Format == 'JSON' ) {
				print( json_encode( array(
						'code' => 100, 'label' => '%E, unknow format (no Secret user)'
				) ) );
		 	} else {
				print(
					$XML_Header .
					'<code>100</code><label>%E, unknow format (no Secret user)</label>' .
					$XML_Footer
				);
		 	}
		 	
			exit();
		}
 				
		if ( array_key_exists( 'scr_password', $Record ) ) {
			$scr_password = $Record[ 'scr_password' ];
			if ( ! openssl_private_decrypt( base64_decode( $scr_password ), $scr_password,
			 $Private_Key ) ) {
			 	if ( $Format == 'JSON' ) {
					print( json_encode( array(
							'code' => 200, 'label' => '%E, decrypt error'
					) ) );
			 	} else {
				 	print(
						$XML_Header .
						'<code>200</code><label>%E, decrypt error</label>' .
						$XML_Footer
					);
			 	}
			 	
				exit();
			}
		} else {
		 	if ( $Format == 'JSON' ) {
				print( json_encode( array(
						'code' => 100, 'label' => '%E, unknow format (no Secret password)'
				) ) );
		 	} else {
				print(
					$XML_Header .
					'<code>100</code><label>%E, unknow format (no Secret password)</label>' .
					$XML_Footer
				);
		 	}
		 	
			exit();
		}

		
		// Initialise ces valeurs par défaut pour ne pas générer d'erreur.
		if ( array_key_exists( 'scr_alert', $Record ) ) {
			$scr_alert = $Record[ 'scr_alert' ];
		} else {
			$scr_alert = '';
		}

		if ( array_key_exists( 'app_id', $Record ) ) {
			$app_id = $Record[ 'app_id' ];
		} else {
			$app_id = '';
		}
		
		if ( array_key_exists( 'scr_id', $Record ) ) {
			$scr_id = $Record[ 'scr_id' ];
		} else {
			$scr_id = '';
		}
		

 		// Gestion d'une "création" d'un Secret dans SecretManager.
 		if ( $action == 'create' ) {
 			$scr_comment = 'API insert';
	 				
			if ( $sgr_id == '' and $sgr_label == '' ) {
			 	if ( $Format == 'JSON' ) {
					print( json_encode( array(
							'code' => 100, 'label' => '%E, unknow format (no ID Group or label Group of Secrets)'
					) ) );
			 	} else {
		 			print(
						$XML_Header .
						'<code>100</code><label>%E, unknow format (no ID Group or label Group of Secrets)</label>' .
						$XML_Footer
					);
			 	}

			 	exit();
			}
 					
			if ( $stp_id == '' ) {
			 	if ( $Format == 'JSON' ) {
			 		print( json_encode( array(
			 				'code' => 100, 'label' => '%E, unknow format (no Secret Type)'
			 		) ) );
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
			 	if ( $Format == 'JSON' ) {
			 		print( json_encode( array(
			 				'code' => 100, 'label' => '%E, unknow format (no Secret Environment)'
			 		) ) );
			 	} else {
					print(
						$XML_Header .
						'<code>100</code><label>%E, unknow format (no Secret Environment)</label>' .
						$XML_Footer
					);
					exit();
			 	}
			}

			// Contrôle les droits de l'utilisateur sur ce Groupe de Secrets.
			$Right = rightsControl( $sgr_id, $GroupsList, 2 );
 					
			// Mise à jour de la base de données du SecretManager.
			try {
				if ( $Right ) {
					$Secrets->set( '', $sgr_id, $stp_id, $scr_host, $scr_user, $scr_password,
						$scr_comment, $scr_alert, $env_id, $app_id );

					$alert_message = $PageHTML->getTextCode( 'L_Secret_Created' ) . ' [' . $Secrets->LastInsertId . ']';

	 			 	if ( $Format == 'JSON' ) {
	 			 		$Records_Status[ $Record_ID ] = array( 'code' => 0, 'label' => $alert_message );
			 		} else {
						$Records_Status .= ' <record id="' . $Record_ID . '"><code>0</code><label>' . $alert_message . '</label>'."\n";
			 		}
				} else {
					$alert_message = 'User not authorized for work with Group of Secret: "' . $sgr_id . '"';
					
	 			 	if ( $Format == 'JSON' ) {
	 			 		$Records_Status[ $Record_ID ] = array( 'code' => 13, 'label' => $alert_message );
			 		} else {
						$Records_Status .= ' <record id="' . $Record_ID . '"><code>13</code><label>' . $alert_message . '</label>'."\n";
			 		}
				}
					
				if ( $Verbosity_Alert == 2 ) {
					$alert_message .= $Secrets->getMessageForHistory( $Secrets->LastInsertId );
				}
					
				$Security->updateHistory( 'L_ALERT_SCR', $alert_message, 2 );
			} catch ( Exception $e ) {
				$alert_message = $e->getMessage();
				
				if ( $Format == 'JSON' ) {
 			 		$Records_Status[ $Record_ID ] = array( 'code' => 21, 'label' => $alert_message );
 			 	} else {
					$Records_Status .= ' <record id="' . $Record_ID . '"><code>21</code><label>' . $alert_message . '</label></record>'."\n";
 			 	}
			}
		}
		elseif ( $action == 'update' ) {
			$scr_comment = 'API update';

			// Recherche l'ID du Secret.
			list( $scr_id, $sgr_id ) = $Secrets->searchSecret( $scr_host, $scr_user );

			if ( $scr_id == '' ) {
 			 	if ( $Format == 'JSON' ) {
 			 		print( json_encode( array(
 			 				'code' => 105, 'label' => '%E, Secret doesn\'t exists (Host: ' . $scr_host .
 			 				', User: ' . $scr_user . ')'
 			 		) ) );
		 		} else {
					print(
						$XML_Header .
						'<code>105</code><label>%E, Secret doesn\'t exists (Host: ' . $scr_host .
 			 				', User: ' . $scr_user . ')</label>' .
						$XML_Footer
					);
					exit();
		 		}
		 	}
		 				
			// Contrôle les droits de l'utilisateur sur ce Groupe de Secrets.
			$Right = rightsControl( $sgr_id, $GroupsList, 3 );
	 				
			// Mise à jour de la base de données du SecretManager.
			try {
				if ( $Right ) {
					$Secrets->set( $scr_id, $sgr_id, $stp_id, $scr_host, $scr_user, $scr_password,
						$scr_comment, $scr_alert, $env_id, $app_id );
	
					$alert_message = $PageHTML->getTextCode( 'L_Secret_Modified' ) . ' [' . $scr_id . ']';
					
 			 		if ( $Format == 'JSON' ) {
						$Records_Status[ $Record_ID ] = array( 'code' => 0, 'label' => $alert_message );
 			 		} else {
						$Records_Status .= ' <record id="' . $Record_ID . '"><code>0</code><label>' . $alert_message . '</label></record>'."\n";
 			 		}
				} else {
					$alert_message = 'User not authorized for work with Group of Secret: ' . $sgr_id ;
					
 			 		if ( $Format == 'JSON' ) {
						$Records_Status[ $Record_ID ] = array( 'code' => 13, 'label' => $alert_message );
 			 		} else {
						$Records_Status .= ' <record id="' . $Record_ID . '"><code>13</code><label>' . $alert_message . '</label></record>'."\n";
 			 		}
				}
						
				if ( $Verbosity_Alert == 2 ) {
					$alert_message .= $Secrets->getMessageForHistory( $scr_id );
				}
						
				$Security->updateHistory( 'L_ALERT_SCR', $alert_message, 3 );
			} catch ( Exception $e ) {
				$alert_message = $e->getMessage();
				
				if ( $Format == 'JSON' ) {
			 		$Records_Status[ $Record_ID ] = array( 'code' => 21, 'label' => $alert_message );
			 	} else {
					$Records_Status .= ' <record id="' . $Record_ID . '"><code>21</code><label>' . $alert_message . '</label></record>'."\n";
			 	}
			}
		}
	}
	 		
	if ( $Format == 'JSON' ) {
 		print( json_encode( array(
			'records' => $Records_Status,
 			'total' => $Total_Records ) ) );
 	} else {
		print(
			$XML_Header .
		 	$Records_Status .
			' <total>' . $Total_Records . '</total>' .
			$XML_Footer
		);
 	}
 	
	exit();
	
	
 default:
	$statusCode = 10;
	$statusLabel = '%E, invalid call method';
		
	print( json_encode( array( 'code' => $statusCode, 'label' => $statusLabel ) ) );

	break;
}

?>