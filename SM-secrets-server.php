<?php

/**
* Ce script gère les accès aux secrets.
* Il tourne en tâche de fond.
* Et répond aux questions des clients habilités.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2013-02-18
*
*/

$VERSION = '0.2-0';

$PREFIX_SUCCESS = '%S ';
$PREFIX_ERROR	= '%E ';
$PREFIX_WARNING = '%W ';
$PREFIX_DEBUG	= '%D ';

// Récupère les paramètres fournis sur la ligne de commande.
$ShortOpts  = "";
$ShortOpts .= "c:"; // Spécifie un fichier de configuration spécifique
$ShortOpts .= "d";  // Force l'exécution en mode "debug"
$ShortOpts .= "h";  // Affiche l'aide
$ShortOpts .= "v";  // Affiche la version

$LongOpts  = array(
	"config:", // Spécifie un fichier de configuration spécifique
	"debug",   // Force l'exécution en mode "debug"
	"help",    // Affiche l'aide
	"version", // Affiche la version
);

$Options = getopt( $ShortOpts, $LongOpts );
//var_dump($Options);

$FLAG_DEBUG = 0;
$Config_File = 'Libraries/Config_SM-secrets-server.inc.php';
$Security_File = 'Libraries/Class_Security.inc.php';

include( 'Libraries/Constants.php' );

foreach( $Options as $Option => $Valeur ) {
	switch( $Option ) {
	 case 'd':
	 case 'debug':
		$FLAG_DEBUG = 1;
		break;

	 case 'c':
	 case 'config':
		$Config_File = $Options[ $Option ];
		break;

	 case 'h':
	 case 'help':
	 case '?':
		print( $FLAG_WARNING .
		 $argv[ 0 ] . " [-config \"conf_file\"] [-debug] [-version]\n" .
		 "-config \"conf_file\" : declare a specific configuration file\n" .
		 "-c \"conf_file\"      : declare a specific configuration file\n" .
		 "-debug              : script execution is in \"debug\" mode\n" .
		 "-d                  : script execution is in \"debug\" mode\n" .
		 "-version            : show script version\n" .
		 "-v                  : show script version\n"
		);
		exit( 0 );

	 case 'v':
	 case 'version':
		print( "SecretServer v" . $VERSION . "\n" );
		exit( 0 );
	}
}


// ===================================
// Charge le fichier de configuration.
if ( file_exists( $Config_File ) ) {
	include( $Config_File );
} else {
	print( $PREFIX_ERROR . 'configuration file "' . $Config_File .
	 "\" not exists or inaccessible\n" );
	exit( 1 );
}
if ( $FLAG_DEBUG ) {
	print( $PREFIX_DEBUG . 'configuration file "' . $Config_File .
	 "\" loaded\n" );
}


// ===================================
// Charge le module de sécurité.
if ( file_exists( $Security_File ) ) {
	include( $Security_File );
} else {
	print( $PREFIX_ERROR . 'security module "' . $Security_File .
	 "\" not exists or inaccessible\n" );
	exit( 1 );
}

$Security = new Security();
if ( $FLAG_DEBUG ) {
	print( $PREFIX_DEBUG . "\"Security Object\" loaded\n" );
}


// ===================================
// Charge le module utile pour les transchiffrements.
if ( file_exists( 'Libraries/Class_IICA_Secrets_PDO.inc.php' ) ) {
	include( 'Libraries/Class_IICA_Secrets_PDO.inc.php' );
	include( 'Libraries/Config_Access_DB.inc.php' );
} else {
	print( $PREFIX_ERROR . 'transcrypt module "Libraries/Class_IICA_Secrets_PDO.inc.php" ' .
	 " not exists or inaccessible\n" );
	exit( 1 );
}


// ==============================================
// Charge l'environnement d'analyse des Sessions.
include( 'Libraries/Class_Session.inc.php' );

if ( is_dir( '/Applications/XAMPP/xamppfiles/temp' ) ) {
	$Rep = '/Applications/XAMPP/xamppfiles/temp';
} else {
	$Rep = '';
}

if ( ($Session_Parser = new Session_Parser( $Rep )) == false ) {
	print( $PREFIX_ERROR . "\"Session_Parser\" not loaded\n" );
	exit(1);
}
if ( $FLAG_DEBUG ) {
	print( $PREFIX_DEBUG . "\"Session_Parser\" loaded\n" );
}


// =====================================================================================
// Cette fonction valide que l'utilisateur appelant le serveur est Administrateur dans 
// SecretManager.
function validAdminUser( $user_session ) {
	$ValidUser = 0;
	
	if ( array_key_exists( 'idn_super_admin', $user_session ) ) {
		if ( $user_session[ 'idn_super_admin' ] == 1 ) $ValidUser = 1;
	}

	if ( $ValidUser == 0 ) {
		return FALSE;
	}
	
	return TRUE;
}


// ====================================================
// Cette fonction envoie un message au client appelant.
function sendMessageToClient( $MsgSock, $Message ) {
	$Size = strlen( $Message );

	if ( socket_write( $MsgSock, $Message, $Size ) < $Size )
		ob_flush();

	if ( $GLOBALS[ 'FLAG_DEBUG' ] ) {
		print( $GLOBALS[ 'PREFIX_DEBUG' ] . $Message );
	}

	return;
}


// Recherche et lit le fichier de configuration.
$ID_Session = '';
$Secret_Key = '';
$Protect_Key = '';
$Transport_Key = '';


// Autorise l'exécution infinie du script, en attente de connexion.
set_time_limit( 0 );

// Active le vidage implicite des buffers de sortie, pour que nous puissions voir ce que
// nous lisons au fur et à mesure.
ob_implicit_flush();

$Address = $IP_Address; //'127.0.0.1';
$Port = $IP_Port; // Information provenant du fichier de configuration.

if ( ($Sock = socket_create( AF_INET, SOCK_STREAM, SOL_TCP )) === false) {
	print( $PREFIX_ERROR . "socket_create() failed : reason : " .
	 socket_strerror(socket_last_error()) . "\n" );
	exit( 1 );
}
if ( $FLAG_DEBUG ) {
	print( $PREFIX_DEBUG . "\"socket_create\" = OK\n" );
}

if ( socket_set_option( $Sock, SOL_SOCKET, SO_REUSEADDR, 1 ) === FALSE ) {
	print( $PREFIX_ERROR . "socket_set_option() failed : reason : " .
	 socket_strerror(socket_last_error()) . "\n" );
	exit( 1 );
}

if ( socket_bind( $Sock, $Address, $Port ) === false ) {
	print( $PREFIX_ERROR . "socket_bind() failed : reason : " .
	 socket_strerror( socket_last_error( $Sock ) ) . "\n" );
	exit( 1 );
}
if ( $FLAG_DEBUG ) {
	print( $PREFIX_DEBUG . "\"socket_bind\" = OK\n" );
}

if ( socket_listen( $Sock, 5 ) === false ) {
	print( $PREFIX_ERROR . "socket_listen() failed : reason : " .
	 socket_strerror( socket_last_error( $Sock ) ) . "\n" );
	exit( 1 );
}
if ( $FLAG_DEBUG ) {
	print( $PREFIX_DEBUG . "\"socket_listen\" = OK\n" );
}

$Buf = '';

// ===========================================================================
do {
	if ( ($MsgSock = socket_accept( $Sock )) === false ) {
		print( $PREFIX_ERROR . "socket_accept() failed : reason : " .
		 socket_strerror( socket_last_error( $Sock ) ) . "\n" );
		break;
	}
	
	/* Send instructions. */
	$Msg = FLAG_SUCCESS . ",Welcome on SecretServer v" . $VERSION . "\n" .
		"*** You must 'load' or 'init' the 'Mother Key' for begin. ***\n" ;
	socket_write( $MsgSock, $Msg, strlen( $Msg ) );


	// ===========================================================================
	do {
		if ( false === ($Buf = @socket_read( $MsgSock, 2048, PHP_NORMAL_READ )) ) {
			$errCode = socket_last_error( $MsgSock );
			$errMsg = socket_strerror( $errCode );
			print( $PREFIX_ERROR . "socket_read() failed : reason : (" . $errCode .
			  ") " . $errMsg . "\n" );
			if ( $errCode != 10054 ) {
				break 2; // Arrête le SecretServer
			} else {
				if ( $FLAG_DEBUG ) {
					print( $PREFIX_DEBUG . "Reinitialize read socket\n" );
				}
				break; // Réinitialise l'écoute du SecretServer
			}
		}
		
		if ( !$Buf = trim( $Buf ) ) {
			continue;
		}
		
		// Arrête la connexion du client.
		// Cependant le serveur continue à écouter.
		if ( $Buf == 'quit' ) {
			sendMessageToClient( $MsgSock, FLAG_SUCCESS . "###quit\n" );
			break;
		}
		
		// Arrête les connexions des clients et arrête le serveur.
		if ($Buf == 'shutdown') {
			sendMessageToClient( $MsgSock, FLAG_SUCCESS . "###shutdown\n" );
			break;
		}
		
		// Traite les autres cas.
		$ID_Session = '';
		$Command = '';
		$Parameter = '';
		
		if ( ! isset( $Flag_Path ) ) $Flag_Path = 0;
		
		@list( $ID_Session, $Command, $Parameter ) = explode( '###', $Buf );
		if ( $FLAG_DEBUG ) {
			print( $PREFIX_DEBUG . 'Session: "' . $ID_Session . 
			 '", Command: "' . $Command . '", Parameter: "' . $Parameter . "\"\n" );
		}

		if ( $Command == '' ) {
			sendMessageToClient( $MsgSock, FLAG_ERROR . "###L_ERR_NO_CMD_SEND\n" );
			break; // Déconnecte le client.
		}
		 
		if ( ($user_session = $Session_Parser->parseSession( $ID_Session )) == false ) {
			sendMessageToClient( $MsgSock, FLAG_ERROR . "###L_ERR_INVALID_SESSION\n" );
			break; // Déconnecte le client.
		}
		

		// Contrôle si la session a expiré.
		if ( array_key_exists( 'Expired', $user_session ) ) {
			$current_time = time();
			if ( $user_session[ 'Expired' ] < $current_time ) {
				if ( $FLAG_DEBUG ) {
					print( $PREFIX_DEBUG .
					 "Session time: " . $user_session[ 'Expired' ] .
					 ", Current time: " . $current_time . "\n" );
				}

				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_SESSION_EXPIRED\n" );
				break; // Déconnecte le client.
			}
		} else {
			sendMessageToClient( $MsgSock, FLAG_ERROR .
			 "###L_ERR_USER_NOT_CONNECTED\n" );
			break; // Déconnecte le client.
		}
		if ( $FLAG_DEBUG ) {
			print( $PREFIX_DEBUG . "User session : OK\n" );
		}


		// ****************************************
		// Coeur de traitement du serveur.
		// ****************************************
		if ( $FLAG_DEBUG ) {
			print( $PREFIX_DEBUG . "\"" . $Command . "\" command received\n" );
		}

		// ==============================
		// Affiche l'état de la clé mère.
		if ( $Command == 'set_path' ) {
			if ( ! validAdminUser( $user_session ) ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_USER_NOT_ADMIN\n" );
				break; // Déconnecte le client.
			}
			
	 		$Session_Parser->set_session_path( $Parameter );
	 		$Flag_Path = 1;
			sendMessageToClient( $MsgSock, FLAG_SUCCESS . "###" .
			 $Parameter . "\n" );
			break; // Déconnecte le client.
	 	}


		// ========================================
		// Création ou modification de la clé mère.
		if ( $Command == 'init' ) {
			if ( ! validAdminUser( $user_session ) ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_USER_NOT_ADMIN\n" );
				break; // Déconnecte le client.
			}
			
			// Récupère la clé de transport.
			$T_Key = $Security->getTransportKey( $ID_Session );
			if ( $T_Key[ 0 ] === TRUE ) {
				 $Transport_Key = $T_Key[ 1 ];
			} else {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_READ_TRANSPORT_FILE\n" );
				break; // Déconnecte le client.
			}
			
			if ( $Parameter == '' ) {
				if ( $FLAG_DEBUG ) {
					print( $PREFIX_DEBUG . "Mode: create key activated\n" );
				}
				$Mother_Key = $Security->passwordGeneration( 32, 2 );
				$Operator_Key = $Security->passwordGeneration( 10, 2 );
				
				$Result = FLAG_SUCCESS . '###L_MOTHER_KEY_AUTOMATICALLY_CREATED';
			} else {
				if ( $FLAG_DEBUG ) {
					print( $PREFIX_DEBUG . "Mode: modify key activated\n" );
				}
				
				$Mother_Key = '';
				$Operator_Key = '';
				
				// Déchiffre le paramètre protégé par la clé de transport.
				if ( $FLAG_DEBUG ) {
					print( $PREFIX_DEBUG . "Decrypt given parameter\n" );
				}
				$Parameter = $Security->mc_decrypt( $Parameter, $Transport_Key );

				// Sépare la clé opérateur de la clé mère.
				@list( $Operator_Key, $Mother_Key ) = explode( '===', $Parameter );
				
				if ( $Operator_Key == '' ) {
    				$Operator_Key = $Security->passwordGeneration( 10, 2 );
				}
				
				$Result = FLAG_SUCCESS . '###L_MOTHER_KEY_MANUALLY_CREATED';
				
				if ( $Mother_Key == '' ) {
    				$Mother_Key = $Security->passwordGeneration( 32, 2 );
    				$Result = FLAG_SUCCESS . '###L_MOTHER_KEY_AUTOMATICALLY_CREATED';
				}
			}
			
			
			// Stockage de la clé mère.
			if ( $Mother_Key == '' ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_MOTHER_KEY_EMPTY\n" );
				break; // Déconnecte le client.
			} else {
				$Secrets = new IICA_Secrets( 
				 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );


				if ( $FLAG_DEBUG ) {
					print( $PREFIX_DEBUG . "Save old mother key\n" );
				}
				
				$old_Secret_Key = explode('###', $Secret_Key );
				$old_Secret_Key = $old_Secret_Key[ 3 ];


				if ( $old_Secret_Key != $Mother_Key ) {
					if ( $old_Secret_Key  == '' ) {
						sendMessageToClient( $MsgSock, FLAG_ERROR .
						 "###L_ERR_MOTHER_KEY_NOT_LOADED\n" );
						break; // Déconnecte le client.

					}

					if ( $FLAG_DEBUG ) {
						print( $PREFIX_DEBUG . "Database transcrypt : begin\n" );
					}

					try {
print( $old_Secret_Key . ' => ' . $Mother_Key ."\n" );
						$Secrets->transcrypt( $old_Secret_Key, $Mother_Key );
					} catch( Exception $e ) {
						print( $PREFIX_ERROR . $e->getCode() . ' -  ' . $e->getMessage() );

						sendMessageToClient( $MsgSock, FLAG_ERROR .
						 "###L_ERR_TRANSCRYPT\n" );
						
						break; // Déconnecte le client.
					}

					if ( $FLAG_DEBUG ) {
						print( $PREFIX_DEBUG . "Database transcrypt : success\n" );
					}
				}


				if ( $FLAG_DEBUG ) {
					print( $PREFIX_DEBUG . "Prepare new mother key\n" );
				}
				
				$_Create_Date = time();
				
				$Secret_Key = "OK###" . $user_session[ 'idn_login' ] . "###" .
				 $_Create_Date . "###" . $Mother_Key;
				
				$Record = $Security->mc_encrypt( $Secret_Key, $Operator_Key );

				// Sauvegarde le chiffré de la clé mère.
				if ( $FLAG_DEBUG ) {
					print( $PREFIX_DEBUG . "Create new Secret file (for Mother Key)\n" );
				}
				
				if ( file_exists( $SecretFile ) ) {
				    rename( $SecretFile, $SecretFile . '-' . date( 'Y_m_d-H_i_s',
				     $_Create_Date ) );
				}
				
				$PF_Data = fopen( $SecretFile, 'w' );
				if ( $PF_Data === FALSE ) {
					sendMessageToClient( $MsgSock, FLAG_ERROR .
	 				 "###L_ERR_SECRET_FILE_CREATION\n" );
		 			break; // Déconnecte le client
				}
				
				fwrite( $PF_Data, $Record ); // . "\n" );
				fclose( $PF_Data );
				

					 // Remonte au client la clé opérateur et la clé mère qui ont été
					 // utilisées.				
				$Secret = $Security->mc_encrypt( $Operator_Key . '===' . $Mother_Key,
				 $Transport_Key ) ;
				
				sendMessageToClient( $MsgSock, $Result . '###' . $Secret . "###" .
				 $_Create_Date . "\n" );

				break; // Déconnecte le client
			}
		}


		// ===================================================
		// Charge la clé mère dans la mémoire du SecretServer.
		if ( $Command == 'load' ) {
			if ( ! validAdminUser( $user_session ) ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_USER_NOT_ADMIN\n" );
				break; // Déconnecte le client.
			}
			
			// Sauvegarde l'ancienne clé mère.
			if ( $Secret_Key != '' ) $Old_Secret_Key = $Secret_Key;
			
			$T_Key = $Security->getTransportKey( $ID_Session );
			if ( $T_Key[ 0 ] === FALSE ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR . "###" . $T_Key[ 1 ] . "\n" );
				break;
			}

				
			// Déchiffre la clé opérateur protégée par la clé de transport.
			if ( $FLAG_DEBUG ) {
				print( $PREFIX_DEBUG . "Decrypt given parameter\n" );
			}
			$O_Key = $Security->mc_decrypt( $Parameter, $T_Key[ 1 ] );


			// Récupère la clé mère.
			$PF_Data = fopen( $SecretFile, 'r' );
			if ( $PF_Data === FALSE ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_OPEN_SECRET_FILE\n" );
				break;
			}

			$Record = fgets( $PF_Data );
			if ( $Record === FALSE ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_READ_TRANSPORT_FILE\n" );
				break;
			}

			fclose( $PF_Data );
			
			
			// Déchiffre la clé mère avec la clé opérateur.
			$Secret_Key = $Security->mc_decrypt( $Record, $O_Key );
			
			$Record = explode( '###', $Secret_Key );
			if ( $Record[ 0 ] != 'OK' ) {
				if ( isset( $Old_Secret_Key ) ) $Secret_Key = $Old_Secret_Key;
				else $Secret_Key = '';

				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_INVALID_OPERATOR_KEY\n" );

				break; // Déconnecte le client.
			} else {
				sendMessageToClient( $MsgSock, FLAG_SUCCESS .
				 "###L_MOTHER_KEY_LOADED\n" );

				break; // Déconnecte le client.
			}
		}


		// ==============================
		// Affiche l'état de la clé mère.
		if ( $Command == 'status' ) {
			 if ( $Secret_Key == '' ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_MOTHER_KEY_NOT_LOADED\n" );
				break; // Déconnecte le client.
			}
			
			$Record = explode( '###', $Secret_Key );

			if ( $Record[ 0 ] == 'OK' ) {
				sendMessageToClient( $MsgSock, FLAG_SUCCESS . "###OK###". $Record[ 1 ] .
				 "###" . date( 'Y-m-d H:i:s', $Record[ 2 ] ) . "\n" );

				break; // Déconnecte le client.
			} else {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_MOTHER_KEY_CORRUPTED\n" );

				break; // Déconnecte le client.
			}
		}


		// ===========================================
		// Déchiffre une information avec la clé mère.
		if ( $Command == 'encrypt' ) {
			if ( $Secret_Key == '' ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_MOTHER_KEY_NOT_LOADED\n" );
				break; // Déconnecte le client.
			}

			// Récupère la clé de transport.
			$T_Key = $Security->getTransportKey( $ID_Session );
			if ( $T_Key[ 0 ] === FALSE ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR . "###" . $T_Key[ 1 ] . "\n" );
				
				break;
			}


			// Déchiffre les données protégées par la clé de transport.
			if ( $FLAG_DEBUG ) {
				print( $PREFIX_DEBUG . "Decrypt given parameter\n" );
			}
			$Data = $Security->mc_decrypt( $Parameter, $T_Key[ 1 ] );


			// Récupère la clé mère.
			$Record = explode( '###', $Secret_Key );
			if ( $Record[ 0 ] != 'OK' ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_MOTHER_KEY_CORRUPTED\n" );

				break; // Déconnecte le client.
			}
			
			// Chiffre les données avec la clé mère.
			$Data = $Security->mc_encrypt( $Data, $Record[ 3 ] );
			
			
			// Retourne les données chiffrées au client.
			sendMessageToClient( $MsgSock, FLAG_SUCCESS . "###" . $Data . "\n" );

			break; // Déconnecte le client.
		}


		// ===========================================
		// Déchiffre une information avec la clé mère.
		if ( $Command == 'decrypt' ) {
			if ( $Secret_Key == '' ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_MOTHER_KEY_NOT_LOADED\n" );
				break; // Déconnecte le client.
			}

			// Récupère la clé mère.
			$Record = explode( '###', $Secret_Key );
			if ( $Record[ 0 ] != 'OK' ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR .
				 "###L_ERR_MOTHER_KEY_CORRUPTED\n" );

				break; // Déconnecte le client.
			}
			
			// Déchiffre les données avec la clé mère.
			$Data = $Security->mc_decrypt( $Parameter, $Record[ 3 ] );

			// Génère la clé de transport.
			$T_Key = $Security->setTransportKey( $ID_Session );
			if ( $T_Key[ 0 ] === FALSE ) {
				sendMessageToClient( $MsgSock, FLAG_ERROR . "###" . $T_Key[ 1 ] . "\n" );
				break;
			}


			// Chiffre la donnée par la clé de transport.
			if ( $FLAG_DEBUG ) {
				print( $PREFIX_DEBUG . "Encrypt parameter\n" );
			}
			$Data = $Security->mc_encrypt( $Data, $T_Key[ 1 ] );

			sendMessageToClient( $MsgSock, FLAG_SUCCESS . "###" . $Data . "\n" );

			break; // Déconnecte le client.
		}
		

		// Normalement, on devrait pas arriver jusqu'ici
		sendMessageToClient( $MsgSock, FLAG_ERROR . "###Invalid command: '$Buf'\n" );

	} while( true );
	
	socket_close($MsgSock);
	
	if ($Buf == 'shutdown') {
		 break;
	}

} while( true );

socket_close( $Sock );

?>