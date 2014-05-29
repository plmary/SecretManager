<?php
include_once( 'Constants.inc.php' );
include_once( DIR_LIBRARIES . '/Class_Security.inc.php' );


class Secret_Server extends Security {
/**
* Cette classe gère les communications entre les clients "SecretManager" et le serveur "SecretServer".
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2013-12-03
*
*/
public $IP_Address;
public $IP_Port;
public $SecretFile;


public function __construct() {
	/**
	* Constructeur de l'objet.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-03-10
	*
	* @return TRUE .
	*/
	include( DIR_LIBRARIES . '/Config_SM-secrets-server.inc.php' );
	
	$this->IP_Address = $IP_Address;
	$this->IP_Port = $IP_Port;
	$this->SecretFile = $SecretFile;
	
	return TRUE;
}


private function __sendServerSocket( $Message ) {
	/**
	* Routine interne qui envoi un message et attend la réponse du serveur.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-03-18
	*
	* @param[in] $Message Message à envoyer au serveur.
	*
	* @return array Retourne un tableau, le premier élément est le statut et le deuxième
	* est le message associé au statut.
	*
	* Exception 
	*/

	// Crée un socket TCP/IP.
	$PF_Socket = @fsockopen( "tcp://" .$this->IP_Address, $this->IP_Port,
	 $errno, $errmsg, 10 );
	if ( $PF_Socket === FALSE ) {
		if ( $errno == 10061 or $errno == 61 )
		    throw new Exception( 'L_ERR_SERVER_NOT_STARTED' );
		
		throw new Exception( $errmsg, $errno );
	} else {
		$Result = '';


		// Lit un éventuel message envoyé par le serveur.
		while( TRUE ) {
			stream_set_timeout( $PF_Socket, 0, 9000 ); // Attend 9000 microsecondes

			$Record = fgets( $PF_Socket, 2048 );

			$info = stream_get_meta_data( $PF_Socket );
			if ( $info[ 'unread_bytes' ] == 0 ) break;

			$Result .= $Record;
		}

       	$Result .= $Record;

		// Envoi le message au serveur. 
		fwrite( $PF_Socket, $Message );
		
		$Result = '';

		
		// Lit la réponse du serveur suite à l'envoi du message précédent.
		while( TRUE ) {
			stream_set_timeout( $PF_Socket, 0, 9000 ); // Attend 9000 microsecondes

			$Record = fgets( $PF_Socket, 2048 );

			$info = stream_get_meta_data( $PF_Socket );

			if ( $info[ 'unread_bytes' ] == 0 && $info[ 'eof' ] == TRUE ) break;

			$Result .= $Record;
		}

       	$Result .= $Record;
		
		fclose( $PF_Socket );
	}

	// Retourne le tableau issu de la réponse du serveur.
	return explode( '###', $Result );
}


public function SS_setSessionPath() {
	/**
	* Met en place la localisation des sessions des utilisateurs.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-03-10
	*
	* @return string Retourne la valeur du PATH qui vient d'être initialisé sinon
	* lève une Exception.
	*/
	$Command = session_id() . "###set_path###" . session_save_path() . "\n";
	
	$Result = $this->__sendServerSocket( $Command );

	if ( $Result[0] == FLAG_ERROR ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}
	
	return trim( $Result[1] );
}


public function SS_Shutdown() {
	/**
	* Demande l'arrêt logique au serveur.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-03-18
	*
	* @return string Confirme l'arrêt du serveur ou lève une Exception.
	*/
	$Result = $this->__sendServerSocket( "shutdown\n" );
	
	if ( $Result[0] == FLAG_ERROR ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}
	
	return trim( $Result[ 1 ] );
}


public function SS_Quit() {
	/**
	* Demande l'arrêt de la communication entre le client et le serveur.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-03-10
	*
	*/
	return trim( $this->__sendServerSocket( "quit\n" ) );
}


public function SS_createMotherKey( $Operator_Key, $Mother_Key ) {
	/**
	* Créé une nouvelle clé Mère (sans transchiffrement et sans la charger dans le SecretServer).
	* A n'utiliser qu'à la première initialisation du SecretManager.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-03-18
	*
	* @param[in] $Operator_Key Clé opérateur utilisée pour chiffrer la clé mère.
	* @param[in] $Mother_Key Clé mère qui sera utilisée pour chiffrer les données dans la base.
	*
	* @return Retourne un tableau avec les valeurs ci-dessous :
	*  "file" = nom du fichier généré,
	*  "date" = date à laquelle la nouvelle clé a été générée,
	*  "operator_key" = Clé Opérateur utilisée.
	*  "mother_key" = Clé Mère utilisée.
	*  En cas d'erreur, cette fonction lève une exception.
	*/

	$Result = $this->setTransportKey( session_id() );
	if ( $Result[ 0 ] === FALSE ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}

	$T_Key = $Result[ 1 ];
		
	$Keys = $this->mc_encrypt( $Operator_Key . '===' . $Mother_Key, $T_Key );
	
	$Result = $this->__sendServerSocket( session_id() . '###create###' . $Keys . "\n" );

	if ( $Result[0] == FLAG_ERROR ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}
	
	$Keys = $this->mc_decrypt( $Result[ 1 ], $T_Key );

	$Keys = explode( '===', $Keys );

    return array( $this->SecretFile,
        trim( $Result[2] ), htmlentities( trim( $Keys[0] ), ENT_QUOTES ),
        htmlentities( trim( $Keys[1] ), ENT_QUOTES ) );
}


public function SS_changeMotherKey( $Operator_Key, $Mother_Key ) {
	/**
	* Demande au SecretServer de changer la clé Mère et éventuellement de transchiffrer 
	* les Secrets de la base.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-16
	*
	* @param[in] $Operator_Key Clé opérateur utilisée pour chiffrer la clé mère.
	* @param[in] $Mother_Key Clé mère utilisée pour chiffrer les données dans la base.
	* S'il n'y a pas de paramètre le SecretServer génère une clé mère et une clé opérateur
	*
	* @return array Retourne un tableau où le 1er élément est le statut de création de
	* la clé mère, le 2ème élément est la clé opérateur qui a été utilisée et
	* le 3ème élément est la mère utilisée est stockée ou lève une Exception.
	*/
	include( DIR_LIBRARIES . '/Class_Backup_PDO.inc.php' );
    
    $Backup = new Backup();
    
    try {
        $Date_Backup = $Backup->backup_secrets();
    } catch( Exception $e ) {
        throw new Exception( $e->getMessage() );
    }

    $Backup->setParameter( 'Backup_Secrets_Date', $Date_Backup );

	// Récupère l'enregistrement contenant la clé de transport.
	$Result = $this->setTransportKey( session_id() );
	if ( $Result[ 0 ] === FALSE ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}

    // Extrait la clé de transport de l'enregistrement.
	$T_Key = $Result[ 1 ];
		
    // Chiffre les clés avec la clé de transport avant de l'envoyer au SecretServer.
	$Keys = $this->mc_encrypt( $Operator_Key . '===' . $Mother_Key, $T_Key );
	
	// Envoi l'information au SecretServer.
	$Result = $this->__sendServerSocket( session_id() . '###change###' . $Keys . "\n" );
		
	if ( $Result[0] == FLAG_ERROR ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}
	
	$Keys = $this->mc_decrypt( $Result[ 1 ], $T_Key );
	$Keys = explode( '===', trim( $Keys ) );
	
	return array( trim( $Keys[ 0 ] ), trim( $Keys[ 1 ] ), trim( $Result[ 2 ] ) );
}


public function SS_transcryptMotherKey( $Operator_Key ) {
	/**
	* Demande au SecretServer de transchiffrer la clé Mère avec la nouvelle clé Opérateur.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-11-28
	*
	* @param[in] $Operator_Key Clé opérateur utilisée pour chiffrer la clé mère.
	*
	* @return array Retourne un tableau où le 1er élément est le statut de création de
	* la clé mère, le 2ème élément est la clé opérateur qui a été utilisée et
	* le 3ème élément est la mère utilisée est stockée ou lève une Exception.
	*/
	$Result = $this->setTransportKey( session_id() );
	if ( $Result[ 0 ] === FALSE ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}

	$T_Key = trim( $Result[ 1 ] );
		
	$Keys = $this->mc_encrypt( $Operator_Key, $T_Key );
	
	$Result = $this->__sendServerSocket( session_id() . '###transcrypt-mk###' . $Keys . "\n" );
		
	if ( $Result[0] == FLAG_ERROR ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}

    return array( trim($Result[0]), trim($Result[1]) );
}


public function SS_loadMotherKey( $Operator_Key ) {
	/**
	* Charge dans le SecretServer une clé mère précédemment créée en la déchiffrant avec
	* la clé opérateur associée.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2013-03-10
	*
	* @param[in] $Operator_Key Clé opérateur utilisée pour déchiffrer la clé mère.
	*
	* @return string Confirme le chargement de la clé ou lève une Exception.
	*/
	$Result = $this->setTransportKey( session_id() );
	if ( $Result[ 0 ] === FALSE ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}
		
	$O_Key = $this->mc_encrypt( $Operator_Key, $Result[ 1 ] );

	$Result = $this->__sendServerSocket( session_id() . "###load###" . $O_Key .
	 "\n" );

	if ( $Result[ 0 ] == FLAG_ERROR ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}
	
	return array( trim( $Result[ 1 ] ), trim( $Result[ 2 ] ), trim( $Result[ 3 ] ) );
}


public function SS_statusMotherKey() {
	/**
	* Affiche le statut de la clé mère chargée dans le SecretServer.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2013-03-10
	*
	*
	* @return array Retourne un tableau où :
	*  le 1er élément est le statut de la clé en mémoire (obligatoirement "OK"),
	*  le 2ème élément est le nom de l'opérateur qui a créé la clé
	*  et le 3ème élément est la date de création de la clé. Sinon,
	* lève une Exception.
	*/
	$Result = $this->__sendServerSocket( session_id() . "###status###\n" );
	 
	if ( $Result[0] == FLAG_ERROR ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}
	
	return array( trim( $Result[ 1 ] ), trim( $Result[ 2 ] ), trim( $Result[ 3 ] ) );
}


public function SS_encryptValue( $Encrypt_Value ) {
	/**
	* Fait chiffrer une valeur par le SecretServer.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2013-03-18
	*
	* @param[in] $Encrypt_Value Valeur à chiffrer par le SecretServer.
	*
	* @return string Retourne la valeur chiffrée.
	*/

	if ( $Encrypt_Value == '' ) {
		throw new Exception( 'L_ERR_NO_VALUE' );
	}
	
	$Result = $this->setTransportKey( session_id() );
	if ( $Result[ 0 ] === FALSE ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}

	$T_Key = $Result[ 1 ];
		
	$Encrypted = $this->mc_encrypt( $Encrypt_Value, $T_Key );

	$Result = $this->__sendServerSocket( session_id() . "###encrypt###" . $Encrypted . "\n" );
	 
	if ( $Result[0] == FLAG_ERROR ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}
	
	return trim( $Result[ 1 ] );
}


public function SS_decryptValue( $Decrypt_Value ) {
	/**
	* Fait déchiffrer une valeur par le SecretServer.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2013-03-18
	*
	* @param[in] $Decrypt_Value Valeur à chiffrer par le SecretServer.
	*
	* @return string Retourne la valeur chiffrée.
	*/

	if ( $Decrypt_Value == '' ) {
		throw new Exception( 'L_ERR_NO_VALUE' );
	}

	$Data = $this->__sendServerSocket( session_id() . "###decrypt###" . $Decrypt_Value . "\n" );
	 
	if ( $Data[0] == FLAG_ERROR ) {
		throw new Exception( trim( $Data[ 1 ] ) );
	}
	
	$Result = $this->getTransportKey( session_id() );
	if ( $Result[ 0 ] === FALSE ) {
		throw new Exception( trim( $Result[ 1 ] ) );
	}

	$T_Key = $Result[ 1 ];
		
	$Decrypted = $this->mc_decrypt( $Data[ 1 ], $T_Key );
	
	return trim( $Decrypted );
}


public function SS_startServer( $Operator_Key ) {
	$this->SS_setSessionPath();
	$this->SS_loadMotherKey( $Operator_Key );
}


public function SS_saveExternalMotherKey( $Operator_Key, $Mother_Key ) {
	$this->__sendServerSocket( session_id() . "###save###" . $Mother_Key . "\n" );

	$this->SS_loadMotherKey( $Operator_Key );
}

} // Fin Class.
?>