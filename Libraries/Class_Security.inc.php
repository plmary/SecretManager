<?php

include_once( 'Constants.inc.php' );

include_once( IICA_LIBRARIES . '/Class_IICA_Parameters_PDO.inc.php' );

/**
* Cette classe gère les problématiques de sécurité. Tel que le contrôle des variables en
* entrées, en sortie (notamment pour l'affichage à l'écran) ou calcule des grains de sel,
* etc.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2012-11-08
*
*/

class Security extends IICA_Parameters {
	public function __construct() {
		parent::__construct();

		return;
	}


	public function XSS_Protection( $value, $mode='ASCII' ) {
	/**
	* Anti-injection XSS (à utiliser avant l'affichage d'une variable).
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $value Chaine de caractère à contrôler
	* @param[in] $mode Type de contrôle à réaliser
	*
	* @return Retourne le résultat protégé (prêt à l'affichage) ou faux
	*/
		switch( strtoupper( $mode ) ) {
		 case 'NUMERIC' :
			if ( $numeric = ctype_digit( $value ) ) {
				return $value;
			} else return false ;
			break;

		 case 'ALPHA' :
			if ( $alpha = ctype_alpha( $value ) ) {
				$value = stripslashes( $value );
				$value = htmlspecialchars( $value, ENT_QUOTES, 'ISO-8859-15' );
				return $value;
			} else return false ;
			break;
      	
		 case 'ALPHA-NUMERIC' :
			if ( $alnum = ctype_alnum( $value ) ) {
				$value = stripslashes( $value );
				$value = htmlspecialchars( $value );
				return $value;
			} else return false ;
			break;
	        
		 case 'PRINTABLE' :
			if ( $alnum = ctype_print( $value ) ) {
				$value = stripslashes( $value );
				$value = htmlspecialchars( $value, ENT_QUOTES, 'ISO-8859-15' );
				return $value;
			} else return false ;
			break;
	      
		 default:
		 case 'ASCII':
			$value = stripslashes( $value );
			$value = htmlspecialchars( $value, ENT_QUOTES, 'ISO-8859-15' );
			return $value;
			break;
		}
	}


	public function valueControl( $value, $mode='ASCII' ) {
	/**
	* Contrôle et prépare les variables avant un stockage.
	* A utiliser avant le stockage d'une information.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $value Chaine de caractère à contrôler
	* @param[in] $mode Type de contrôle à réaliser
	*
	* @return Retourne le résultat protégé ou faux
	*/
		switch( strtoupper( $mode ) ) {
		 case 'NUMERIC' :
			if ( $numeric = ctype_digit( $value ) ) {
				return $value;
			} else return -1 ;
			break;

		 case 'ALPHA' :
			if ( $alpha = ctype_alpha( $value ) ) {
				$value = addslashes( $value );
				return $value;
			} else return -1 ;
			break;
      	
		 case 'ALPHA-NUMERIC' :
			if ( $alnum = ctype_alnum( $value ) ) {
				$value = addslashes( $value );
				return $value;
			} else return -1 ;
			break;
	        
		 case 'PRINTABLE' :
			if ( $alnum = ctype_print( $value ) ) {
				$value = addslashes( $value );
				return $value;
			} else return -1 ;
			break;
	      
		 default:
		 case 'ASCII':
			$value = addslashes( $value );
			return $value;
			break;
		}
	}


	public function MySQL_Protection( $value ) {
	/**
	* Anti-injection SQL dans MySQL.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $value Chaine de caractère à protéger
	*
	* @return Retourne le résultat protégé.
	*/
		return @mysql_real_escape_string( $value );
	}
	
	
 	
	public function removeAccent( $Value ) {
	/**
	* Supprime les caractères accentués d'une chaîne.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $value Chaine de caractère à protéger
	*
	* @return Retourne le résultat protégé.
	*/
		return strtr( $Value,
		 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
		 'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy' );
	}

 	
	public function validTimeSession( $Time ) {
	/**
	* Vérifie que la session n'a pas expiré (au regard du temps spécifié)
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $Time Valeur temporelle de l'expiration (date et heure d'expiration)
	*
	* @return Retourne faux si le temps n'a pas expiré ou vrai si le temps à expiré.
	*/
		$DiffTime = time() - $Time;
		if ( $DiffTime >= $this->ExpireSession ) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
 	
 	
	public function passwordGeneration( $size = 8, $complexity = 3 ) {
	/**
	* Générateur de mot de passe ou de grain de sel.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $size Longeur du mot de passe à générer (par défaut 8 caractères)
	* @param[in] $complexity Complexité du mot de passe (constitution du mot de passe) (par défaut complexité à 4, soit le mot de passe doit être constitué de "minuscule", "majuscule", "numérique", "accentué" et caractères "spéciaux").
	*
	* @return Retourne la chaîne générée
	*/
		$accentuations = 'àçèéêëîïôöùûüÿ';
		$lowercase_letters = 'abcdefghijklmnopqrstuvwxyz';
		$uppercase_letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$numbers = '0123456789';
		$specials = '#@&"\'(§!)-_*$£%+=/:.;,?><\\{}[]|';
 		
 		switch( $complexity ) {
 		 case 1:
	 		$caracters = $lowercase_letters . $uppercase_letters;
	 		break;

 		 case 2:
	 		$caracters = $lowercase_letters . $uppercase_letters . $numbers;
	 		break;

 		 case 3:
	 		$caracters = $lowercase_letters . $uppercase_letters . $numbers . $specials;
	 		break;

 		 default:
 		 case 4:
	 		$caracters = $lowercase_letters . $uppercase_letters . $numbers . $specials .
	 		 $accentuations ;
	 		break;
	 	}

		$Password = '';
		 		 
 		for( $i = 0; $i < $size; $i++ )
 			$Password .= $caracters[ mt_rand( 0, (strlen( $caracters ) - 1) ) ];
 		
 		return $Password;
 	}
 	
 	
 	public function complexityPasswordControl( $Password, $complexity = 3 ) {
	/**
	* Vérifie si le mot de passe ou le grain de sel respecte la complexité spécifiée.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $Password Mot de passe à contrôler
	* @param[in] $complexity Complexité du mot de passe (constitution du mot de passe) (par défaut complexité à 3, soit le mot de passe doit être constitué de "minuscules", "majuscules", "numériques" et caractères "spéciaux").
	*
	* @return Retourne vrai si la complexité est respectée et faux dans le cas contraire
	*/
		$Accentuation = 0;
		$Lowercase = 0;
		$Uppercase = 0;
		$Numbers = 0;
		$Specials = 0;
 		
 		$Size = strlen( $Password );
 		
 		for( $Position=0; $Position < $Size; $Position++ ) {
 			$Char = ord( $Password[ $Position ] );
 			
			if ( $Char >= 192 and $Char <= 255 ) $Accentuation = 1;

			if ( $Char >= 97 and $Char <= 122 ) $Lowercase = 1;

			if ( $Char >= 65 and $Char <= 90 ) $Uppercase = 1;

			if ( $Char >= 48 and $Char <= 57 ) $Numbers = 1;

			if ( ($Char >= 33 and $Char <= 46)
			 or ($Char >= 58 and $Char <= 64)
			 or ($Char >= 91 and $Char <= 96)
			 or ($Char >= 123 and $Char <= 191)
			 ) $Specials = 1;
		}

		$Status = false;

 		switch( $complexity ) {
 		 case 1:
	 		if ( $Lowercase == 1 and $Uppercase == 1) $Status = true;
	 		break;

 		 case 2:
	 		if ( $Lowercase == 1 and $Uppercase == 1 and $Numbers == 1 ) $Status = true;
	 		break;

 		 default:
 		 case 3:
	 		if ( $Lowercase == 1 and $Uppercase == 1 and $Numbers == 1
	 		 and $Specials == 1 ) $Status = true;
	 		break;

 		 case 4:
	 		if ( $Lowercase == 1 and $Uppercase == 1 and $Numbers == 1
	 		 and $Specials == 1 and $Accentuation ) $Status = true;
	 		break;
	 	}
 		
 		return $Status;
 	}
 	
 	
 	public function asAccent( $String ) {
	/**
	* Vérifie si la chaîne contient un accent.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-09
	*
	* @param[in] $String Chaine à contrôler
	*
	* @return Retourne vrai si un accent est trouvé, sinon faux
	*/
		$Status = false;
		
		for( $Position = 0; $Position < strlen( $String ); $Position++ ) {
			if ( $String[ $Position ] >= 192 and $String[ $Position ] <= 255 ) {
				$Status = true;
				break;
			}
		}
 		
 		return $Status;
 	}
 	
 	
 	public function asLowercase( $String ) {
	/**
	* Vérifie si la chaîne contient une minuscule.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-09
	*
	* @param[in] $String Chaine à contrôler
	*
	* @return Retourne vrai si une minuscule est trouvée, sinon faux
	*/
		$Status = false;
		
		for( $Position = 0; $Position < strlen( $String ); $Position++ ) {
			if ( $String[ $Position ] >= 97 and $String[ $Position ] <= 122 ) {
				$Status = true;
				break;
			}
		}
 		
 		return $Status;
 	}
 	
 	
 	public function asUppercase( $String ) {
	/**
	* Vérifie si la chaîne contient une minuscule.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-09
	*
	* @param[in] $String Chaine à contrôler
	*
	* @return Retourne vrai si une majuscule est trouvée, sinon faux
	*/
		$Status = false;
		
		for( $Position = 0; $Position < strlen( $String ); $Position++ ) {
			if ( $String[ $Position ] >= 65 and $String[ $Position ] <= 90 ) {
				$Status = true;
				break;
			}
		}
 		
 		return $Status;
 	}
 	
 	
 	public function asNumber( $String ) {
	/**
	* Vérifie si la chaîne contient un nombre.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-09
	*
	* @param[in] $String Chaine à contrôler
	*
	* @return Retourne vrai si un nombre est trouvé, sinon faux
	*/
		$Status = false;
		
		for( $Position = 0; $Position < strlen( $String ); $Position++ ) {
			if ( $String[ $Position ] >= 48 and $String[ $Position ] <= 57 ) {
				$Status = true;
				break;
			}
		}
 		
 		return $Status;
 	}
 	
 	
 	public function asSpecial( $String ) {
	/**
	* Vérifie si la chaîne contient un caractère spécial.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-09
	*
	* @param[in] $String Chaine à contrôler
	*
	* @return Retourne vrai si un caractère spécial est trouvé, sinon faux
	*/
		$Status = false;
		
		for( $Position = 0; $Position < strlen( $String ); $Position++ ) {
			if ( ($String[ $Position ] >= 33 and $String[ $Position ] <= 46)
			 or ($String[ $Position ] >= 58 and $String[ $Position ] <= 64)
			 or ($String[ $Position ] >= 91 and $String[ $Position ] <= 96)
			 or ($String[ $Position ] >= 123 and $String[ $Position ] <= 191) ) {
				$Status = true;
				break;
			}
		}
 		
		return $Status;
	}


	/* ===============================================================================
	** Gestion du Chiffrement
	*/
	
	public function mc_encrypt( $encrypt, $mc_key = '' ) {
	/**
	* Chiffrement d'une donnée.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $encrypt Données à chiffrer.
	* @param[in] $mc_key Clé de chiffrement.
	*
	* @return string Retourne la chaine de données chiffrée.
	*/
		if ( $mc_key == '' ) {
			include( DIR_PROTECTED . '/Config_Hash.inc.php' );
			
			if ( isset( $_salt_secret ) ) {
    			$mc_key = $_salt_secret;
    		} else {
    		    $mc_key = 'PLM-Orasys-2013';
    		}
		}
		
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );

		$passcrypt = mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $mc_key, trim($encrypt),
		 MCRYPT_MODE_ECB, $iv );

		$encode = base64_encode($passcrypt);
		
		return $encode;
	}


	public function mc_decrypt( $decrypt, $mc_key = '' ) {
	/**
	* Déchiffrement d'une donnée.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $decrypt Données à déchiffrer.
	* @param[in] $mc_key Clé de déchiffrement.
	*
	* @return string Retourne la chaine de données déchiffrée.
	*/
		if ( $mc_key == '' ) {
			include( DIR_PROTECTED . '/Config_Hash.inc.php' );
			
			if ( isset( $_salt_secret ) ) {
    			$mc_key = $_salt_secret;
    		} else {
    		    $mc_key = 'PLM-Orasys-2013';
    		}
		}
		
		$decoded = base64_decode( $decrypt );
		
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );

		$decrypted = mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $mc_key, $decoded,
		 MCRYPT_MODE_ECB, $iv );
	
		return trim($decrypted);
	}


	public function mc_encrypt2( $encrypt, $mc_key, $mc_salt = '' ) {
	/**
	* Chiffrement d'une donnée.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $encrypt Données à chiffrer.
	* @param[in] $mc_key Clé de chiffrement (maximum 32 caractères, sinon la clé est tronquée).
	* @param[in] $mc_salt Sel de complexication des clés (32 caractères).
	*
	* @return string Retourne la chaine de données chiffrée.
	*/
		if ( $mc_salt == '' ) {
			include( DIR_PROTECTED . '/Config_Hash.inc.php' );
			
    		$mc_salt = $_salt_default;
		}

		$size = strlen( $mc_key );
		if ( $size >= 32 ) {
			$key = substr( $mc_key, 0, 32 );
		} else {
			$key = $mc_key . substr( $mc_salt, ($size - 1) );
		}

		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );

		$passcrypt = mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $key, trim( $encrypt ),
			MCRYPT_MODE_CBC, $iv );

		$encode = base64_encode( $passcrypt );
		
		return $encode;
	}


	public function mc_decrypt2( $decrypt, $mc_key, $mc_salt = '' ) {
	/**
	* Déchiffrement d'une donnée.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $decrypt Données à déchiffrer.
	* @param[in] $mc_key Clé de déchiffrement.
	*
	* @return string Retourne la chaine de données déchiffrée.
	*/
		if ( $mc_salt == '' ) {
			include( DIR_PROTECTED . '/Config_Hash.inc.php' );
			
    		$mc_salt = $_salt_default;
		}

		$size = strlen( $mc_key );
		if ( $size >= 32 ) {
			$key = substr( $mc_key, 0, 32 );
		} else {
			$key = $mc_key . substr( $mc_salt, ($size - 1) );
		}
		
		$decoded = base64_decode( $decrypt );
		
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );

		$decrypted = mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $key, $decoded,
			MCRYPT_MODE_CBC, $iv );
	
		return trim( $decrypted );
	}


	/* ===============================================================================
	*/
	public function getTransportKey( $ID_Session, $FLAG_DEBUG = 0 ) {
	/**
	* Récupère la clé de transport utilisée pour chiffrer les données en transit.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-03-08
	*
	* @param[in] $ID_Session ID de Session (identifie la personne faisant le tranport).
	* @param[in] $FLAG_DEBUG Permet de pister les actions internes de la fonction.
	*
	* @return array(status,value) Retourne le statut de la fonction ainsi que la valeur 
	* associée au résultat.
	*/
	    $PREFIX_DEBUG = '%D ';
	    
		if ( $ID_Session == '' ) {
			$ID_Session = session_id();

			if ( $ID_Session == '' ) {
				return array( FALSE, "L_ERR_NO_SESSION" );
			}
		}
	
	    $Filename = DIR_SESSION . '/trp_' . $ID_Session;

		if ( $FLAG_DEBUG ) {
			print( $PREFIX_DEBUG . "Open Transport file '" . $Filename .
			 "' on read mode\n" );
		}
                
		$P_File = fopen( $Filename, "r" );
		if ( $P_File === FALSE ) {
			return array( FALSE, "L_ERR_OPEN_TRANSPORT_FILE" );
		}
				
		if ( $FLAG_DEBUG ) {
			print( $PREFIX_DEBUG . "Open Transport file : OK\n" );
		}
                
		$Key = fgets( $P_File, 1024 ); // Récupère la clé de transport.
		if ( $Key === FALSE ) {
			fclose( $P_File );
			return array( FALSE, "L_ERR_READ_TRANSPORT_FILE" );
		}
				
		if ( $FLAG_DEBUG ) {
			print( $PREFIX_DEBUG . "Reading Transport file : OK\n" );
		}
                
		fclose( $P_File );

		return array( TRUE, $Key );
	}
	
	
	public function setTransportKey( $ID_Session, $FLAG_DEBUG = 0 ) {
	/**
	* Actualise la clé de transport utilisée pour chiffrer les données en transit.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-03-08
	*
	* @param[in] $ID_Session ID de Session (identifie la personne faisant le tranport).
	* @param[in] $FLAG_DEBUG Permet de pister les actions internes de la fonction.
	*
	* @return array(status,value) Retourne le statut de la fonction ainsi que la valeur 
	* associée au résultat.
	*/
	    $PREFIX_DEBUG = '%D ';
	    
		if ( $ID_Session == '' ) {
			$ID_Session = session_id();

			if ( $ID_Session == '' ) {
				return array( FALSE, "L_ERR_NO_SESSION" );
			}
		}
	
	    $Filename = DIR_SESSION . '/trp_' . $ID_Session;

		if ( $FLAG_DEBUG ) {
			print( $PREFIX_DEBUG . "Open Transport file '" . $Filename .
			 "' on read mode\n" );
		}
        
        $Key = $this->passwordGeneration( 10, 2 );
        
		$P_File = fopen( $Filename, "w" );
		if ( $P_File === FALSE ) {
			return array( FALSE, "L_ERR_OPEN_TRANSPORT_FILE" );
		}
				
		if ( $FLAG_DEBUG ) {
			print( $PREFIX_DEBUG . "Open Transport file : OK\n" );
		}
                
		fwrite( $P_File, $Key ); // Sauvegarde la clé de transport.
				
		if ( $FLAG_DEBUG ) {
			print( $PREFIX_DEBUG . "Writing Transport file : OK\n" );
		}
                
		fclose( $P_File );

		return array( TRUE, $Key );
	}

	
	public function updateHistory( $hac_name, $ach_access = '', $rgh_id = '', $level = LOG_INFO, $pSecret = '' ) {
	/**
	* Met à jour l'historique des actions sur les objets du SecretManager.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-05-11
	*
	* @param[in] $hac_name (string) Code de l'action qui vient d'être réalisée
	* @param[in] $ach_access (string) Texte de description de l'accès (complément d'information)
	* @param[in] $rgh_id (int) Type d'accès réalisé sur l'objet
	* @param[in] $level (integer) Indique le type de LOG à remonter dans le SYSLOG
	* @param[in] $pSecret (object) Pointeur du Secret qui a été accédé
	*
	* @return Renvoi vrai sur le succès de la mise à jour du Groupe, sinon lève une Exception
	*/

		// Récupère l'ID associé au code action.
		if ( ! $Result = $this->prepare( 'SELECT hac_id FROM hac_history_actions_codes ' .
			'WHERE hac_name = :hac_name ;' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':hac_name', $hac_name, PDO::PARAM_STR, 30 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Tmp = $Result->fetchObject();

		if ( $Tmp->hac_id != 0 ) {
			$hac_id = $Tmp->hac_id;
		} else {
			$hac_id = '';
		}

		if ( $pSecret != '' ) {
			$scr_id = $pSecret->scr_id;
		} else {
			$scr_id = '';
		}

		if ( array_key_exists( 'idn_id', $_SESSION ) ) {
			$idn_id = $_SESSION[ 'idn_id' ];
		} else {
			$idn_id = '';
		}

		if ( array_key_exists( 'user_ip', $_SESSION ) ) {
			$user_ip = $_SESSION[ 'user_ip' ];
		} else {
			$user_ip = '';
		}

		if ( ! $Result = $this->prepare( 'INSERT INTO ach_access_history ' .
			'( ' .
			'scr_id, ' .
			'idn_id, ' .
			'rgh_id, ' .
			'hac_id, ' .
			'ach_gravity_level, ' .
			'ach_date, ' .
			'ach_access, ' .
			'ach_ip ' .
			') VALUES ( ' .
			':scr_id, ' .
			':idn_id, ' .
			':rgh_id, ' . 
			':hac_id, ' .
			':ach_gravity_level, ' .
			'CURRENT_TIMESTAMP, ' .
			':ach_access, ' .
			':ip_address ' .
			'); ' 
		) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':scr_id', $scr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':rgh_id', $rgh_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':hac_id', $hac_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':ach_gravity_level', $level, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':ach_access', $ach_access, PDO::PARAM_STR, 300 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':ip_address', $user_ip, PDO::PARAM_STR, 40 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		// Enchaîne sur les autres notifications et transfert les informations du Secret à traiter.
		if ( $pSecret != '' ) {
			include_once( DIR_LIBRARIES . '/Class_IICA_Parameters_PDO.inc.php' );

			$Parameters = new IICA_Parameters();

			if ( $pSecret->sgr_alert == 1 or $pSecret->scr_alert == 1 ) {
				if ( $Parameters->getParameter('alert_syslog') == 1 ) {
					$this->writeLog( $ach_access, $pSecret );
				}

				if ( $Parameters->getParameter('alert_mail') == 1 ) {
					$ach_access = explode( ' (', $ach_access );
					$this->writeMail( $ach_access[0], $pSecret );
				}
			}
		}
		
		return true;
	}

	
	public function formatSyslogMessage( $action, $pSecret = '' ) {
	/**
	* Formate le message à remonter dans l'historique.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $action Action à tracer.
	* @param[in] $pObject Pointeur sur le Secret manipulé
	*
	* @return Retourne la chaîne formatée ou FALSE en cas d'erreur.
	*/
		include( DIR_LABELS . '/' . $this->getParameter( 'language_alert' ) . '_labels_referentials.php' );

		$Separator = '|';

		if ( isset( $_SESSION[ 'idn_login' ] ) ) {
			$idn_login = $_SESSION[ 'idn_login' ];
		} else {
			$idn_login = '';
		}

		if ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) ) $Server = $_SERVER[ 'REMOTE_ADDR' ];
		else $Server = '';

		if ( $pSecret == '' ) return FALSE;

		if ( ! isset( $pSecret->stp_name ) ) $pSecret->stp_name = ${$pSecret->stp_name};

		if ( ! isset( $pSecret->env_name ) ) {
			if ( $pSecret->env_name != '' ) $pSecret->env_name = ${$pSecret->env_name};
		} else $pSecret->env_name = '';

		// Reformate le corps du Syslog
		$message = file_get_contents( SYSLOG_BODY );

		$message = str_ireplace( '%User', $idn_login, $message );
		$message = str_ireplace( '%ActionDate', date('Y-m-d H:i:s'), $message );
		$message = str_ireplace( '%Action', $action, $message );
		$message = str_ireplace( '%UserIP', $_SESSION['user_ip'], $message );
		if ( ! isset($pSecret->sgr_label) ) $pSecret->sgr_label = ''; 
		$message = str_ireplace( '%GroupSecrets', $pSecret->sgr_label, $message );
		$message = str_ireplace( '%SecretType', $pSecret->stp_name, $message );
		$message = str_ireplace( '%SecretEnvironment', $pSecret->env_name, $message );
		$message = str_ireplace( '%SecretApplication', $pSecret->app_name, $message );
		$message = str_ireplace( '%SecretUser', $pSecret->scr_user, $message );
		$message = str_ireplace( '%SecretHost', $pSecret->scr_host, $message );
		if ( ! isset($pSecret->scr_comment) ) $pSecret->scr_comment = ''; 
		$message = str_ireplace( '%SecretComment', $pSecret->scr_comment, $message );

		return $message;
	}	
 	
 	
	public function writeLog( $action, $pObject = '', $priority = LOG_WARNING ) {
	/**
	* Envoi le message dans le flux "Syslog"
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-06-08
	*
	* @param[in] $action Action à tracer.
	* @param[in] $pObject Pointeur sur le Secret manipulé
	* @param[in] $priority Type de priorité dans le "Syslog" (par défaut LOG_WARNING)
	*
	* Les autres valeurs sont :
	*   LOG_EMERG	système inutilisable
	*   LOG_ALERT	une décision doit être prise immédiatement
	*   LOG_CRIT	condition critique
	*   LOG_ERR 	condition d'erreur
	*   LOG_WARNING	condition d'alerte
	*   LOG_NOTICE	condition normale, mais significative
	*   LOG_INFO	message d'information
	*   LOG_DEBUG	message de déboguage
	*
	* @return Retourne vrai si le message a été envoyé dans Syslog, sinon retrouve faux
	*/
		$message = $this->formatSyslogMessage( $action, $pObject );

		// Ouverture de syslog, ajout du PID.
		if ( ! openlog( "SecretManager", LOG_PID, LOG_USER ) ) {
			return false;
		}

		$access = date( "Y-m-d H:i:s" );

		if ( ! syslog( $priority, $access . ' : ' . $message ) ) {
			return false;
		}

		if ( ! closelog() ) {
			return false;
		}
		
		return true;
	}
	
	
	public function writeMail( $Action, $pSecret ) {
	/**
	* Envoi le message par courriel
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-06-08
	*
	* @param[in] $Action Type d'action qui l'on vient de réaliser sur un Secret et que l'on souhaite notifier.
	* @param[in] $pSecret Objet de type Secret qui vient d'être accédé
	*
	* @return Retourne vrai si le message a été envoyé au serveur de messagerie, sinon retrouve faux (attention, envoyé au serveur de messagerie, ne signifie pas bien arrivé auprès des destinataires)
	*/
		// Récupère les paramètres dans la Base de données
		include_once( DIR_LIBRARIES . '/Class_IICA_Parameters_PDO.inc.php' );

		$Parameters = new IICA_Parameters();

		include( DIR_LABELS . '/' . $Parameters->getParameter( 'language_alert' ) . '_labels_referentials.php' );

		$from = $Parameters->getParameter('mail_from');
		$to = $Parameters->getParameter('mail_to');
		$subject = $Parameters->getParameter('mail_title');
		$output = $Parameters->getParameter('mail_body_type');

		// Reformate le corps du Courriel
		$message = file_get_contents( MAIL_BODY );

		$message = str_ireplace( '%User', $_SESSION['idn_login'], $message );
		$message = str_ireplace( '%ActionDate', date('Y-m-d H:i:s'), $message );
		$message = str_ireplace( '%Action', $Action, $message );
		$message = str_ireplace( '%UserIP', $_SESSION['user_ip'], $message );
		$message = str_ireplace( '%GroupSecrets', $pSecret->sgr_label, $message );
		$message = str_ireplace( '%SecretType', ${$pSecret->stp_name}, $message );
		$message = str_ireplace( '%SecretEnvironment', $pSecret->env_name, $message );//${$pSecret->env_name}, $message );
		$message = str_ireplace( '%SecretApplication', $pSecret->app_name, $message );
		$message = str_ireplace( '%SecretUser', $pSecret->scr_user, $message );
		$message = str_ireplace( '%SecretHost', $pSecret->scr_host, $message );
		$message = str_ireplace( '%SecretComment', $pSecret->scr_comment, $message );

		if ( $output == 'HTML') {
			$body = '
		<html>
		 <head>
		  <title>' . $subject . '</title>
		 </head>
		 <body>
		  <p>' . $message . '</p>
		 </body>
		</html>
			';
		} else {
			$body= $message;
		}

		// Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
		$headers = 'From: ' .$from . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";

		if ( $output == 'HTML' ) $headers .= 'Content-type: text/html; charset="UTF-8"' . "\r\n";

		// Envoi
		return mail($to, $subject, $body, $headers);
	}
	
	
	public function writeMailSecurity( $message ) {
	/**
	* Envoi le message par courriel
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-06-08
	*
	* @param[in] $message Alerte de sécurité à envoyer dans le courriel.
	*
	* @return Retourne vrai si le message a été envoyé au serveur de messagerie, sinon retrouve faux (attention, envoyé au serveur de messagerie, ne signifie pas bien arrivé auprès des destinataires)
	*/
		include( DIR_LABELS . '/' . $this->getParameter( 'language_alert' ) . '_labels_referentials.php' );
		include( DIR_LABELS . '/' . $this->getParameter( 'language_alert' ) . '_labels_generic.php' );

		$from = $this->getParameter('mail_from');
		$to = $this->getParameter('mail_to');
		$subject = $L_Security_Alert;

		$body = '<html><head><title>' . $subject . '</title></head><body><p>' . $message . '</p></body></html>';

		// Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
		$headers = 'From: ' .$from . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset="UTF-8"' . "\r\n";

		// Envoi
		return mail($to, $subject, $body, $headers);
	}


	function checkFilesIntegrity( $ForceCreating = FALSE ) {
	/**
	* Crée le fichier des "empreintes" des fichiers à surveiller en intégrité ou Vérifie l'intégrité des fichiers surveiller.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-08
	*
	* @param[in] $ForceCreating Permet de forcer la création du fichier des empreintes.
	*
	* @return Retourne vrai si aucun fichier n'a été modifié ou si le fichier a été créé OU un tableau contenant la liste des fichiers qui ont été modifiés.
	*/
		include( DIR_LABELS . '/en_labels_generic.php' );

		$Directories = array(
			'Master' => '.',
			'Libraries' => 'Libraries'
			);

		$Patterns = array(
			'Master' => array( '/^SM/', '/^index/' ), // Surveillance des fichiers commençant par "SM-" et "index".
			'Libraries' => array( '/^Class/', '/^password_js/', '/\.js$/' ) // Surveillance des fichiers commençant par "Class_" et "password_js" et des fichiers terminant par ".js".
			);

			
		if ( $ForceCreating === TRUE ) { // Construit le fichier des contrôles d'intégrité.
			$File_P = fopen( INTEGRITY_FILENAME, 'w' );
			
			foreach( $Directories as $Directory => $Directory_Location ) {
				$Directory_P = dir( $Directory_Location );
			
				while ( false !== ($File = $Directory_P->read()) ) {
					foreach( $Patterns[ $Directory ] as $Pattern ) {
						if ( preg_match( $Pattern, $File ) ) {
							//$File = $Directory_P->path . DIRECTORY_SEPARATOR . $File;
							$File = $Directory_P->path . '/' . $File;
							$Hash_File = hash_file( 'sha256', $File );

							if ( fputs( $File_P, $File . '=' . $Hash_File . "\n" ) === FALSE ) {
								print( 'fputs error<br/>' );
								break;
							}
						}
					}
				}
			}
			
			fclose( $File_P );
			$Directory_P->close();

			return TRUE;
		} else { // Utilise le fichier des contrôles d'intégrité.
			if ( ! file_exists( INTEGRITY_FILENAME ) ) {
				return array( FALSE, array( 'Not exists' ) );
			}

			$Hashes = parse_ini_file( INTEGRITY_FILENAME );
			$Files_Corrupted = '';

			foreach( $Directories as $Directory => $Directory_Location ) {
				$Directory_P = dir( $Directory_Location );
			
				while ( false !== ($File = $Directory_P->read()) ) {
					foreach( $Patterns[ $Directory ] as $Pattern ) {
						if ( preg_match( $Pattern, $File ) ) {
							$FileIdx = $Directory_P->path . '/' . $File;
							$File = $Directory_P->path . DIRECTORY_SEPARATOR . $File;
							$Hash_File = hash_file( 'sha256', $File );

							if ( ! array_key_exists($FileIdx, $Hashes) or $Hash_File != $Hashes[ $FileIdx ] ) {
								$pObject = new stdClass();

								$pObject->scr_id = '';
								$pObject->stp_name = '';
								$pObject->env_name = '';
		 						$pObject->app_name = '';
		 						$pObject->scr_host = '';
		 						$pObject->scr_user = '';

		 						$Message = sprintf( $L_File_Integrity_Alert, $File );

		 						$this->writeLog( $Message, $pObject, LOG_ERR );
		 						$this->writeMailSecurity( $Message );

		 						$Files_Corrupted[] = $File;
							}
						}
					}
				}
			}

			if ( $Files_Corrupted != '' ) {
				return array( FALSE, $Files_Corrupted );
			} else {
				return array( TRUE );
			}
		}
	}



	function createMasterFileIntegrity() {
	/**
	* Crée le fichier "d'empreinte" du fichier des "empreintes" des fichiers à surveiller.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-08
	*
	* @return Retourne le Hash du fichier de contrôle du SecretManager ou faux si erreur durant la création du fichier stockant ce Hash.
	*/
		$Hash_File = hash_file( 'sha256', INTEGRITY_FILENAME );

		$File_P = fopen( MASTER_INTEGRITY_FILENAME, 'w' );
		if ( $File_P === FALSE ) {
			print("%E error creating file\n");
			return FALSE;
		}

		$path_parts = pathinfo( INTEGRITY_FILENAME );

		if ( fputs( $File_P, $path_parts[ 'dirname' ] . '/' . $path_parts[ 'basename' ] . '=' . $Hash_File . "\n" ) === FALSE ) {
			print( '%E fputs error<br/>' );
			break;
		}

		fclose( $File_P );

		return $Hash_File;
	}


	function checkMasterFileIntegrity( $ForceCreating = FALSE, $Memory_Hash = '' ) {
	/**
	* Crée le fichier "d'empreinte" du fichier des "empreintes" des fichiers à surveiller ou utilise le fichier "d'empreintes" précédemment créé.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-08
	*
	* @param[in] $ForceCreating Permet de forcer la création du fichier des empreintes.
	* @param[in] $Memory_Hash Permet de contrôler le Hash qui va être calculé avec celui qui a été précédemment calculé.
	*
	* @return Retourne vrai si aucun fichier n'a été modifié ou si le fichier a été créé OU faux si une erreur survient ou si le fichier un tableau contenant la liste des fichiers qui ont été modifiés.
	*/
		include( DIR_LABELS . '/en_labels_generic.php' );

			
		if ( $ForceCreating === TRUE ) { // Construit le fichier protégeant le fichier des contrôles d'intégrité.
			if ( ( $Hash_File = $this->createMasterFileIntegrity() ) === FALSE ) {
				return array( FALSE );
			}

			return array( TRUE, $Hash_File );
		} else { // Utilise le fichier des contrôles d'intégrité.
			if ( $Memory_Hash == '' ) {
				if ( file_exists( MASTER_INTEGRITY_FILENAME ) ) {
					if ( $this->createMasterFileIntegrity() === FALSE ) return array( FALSE );

					$Hashes = parse_ini_file( MASTER_INTEGRITY_FILENAME );
				}
			} else {
				$Hashes[ INTEGRITY_FILENAME ] = $Memory_Hash;
			}

			$Hash_File = hash_file( 'sha256', INTEGRITY_FILENAME );

			if ( DIRECTORY_SEPARATOR != '/' ) {
				$Idx_Name = str_replace( DIRECTORY_SEPARATOR, '/', INTEGRITY_FILENAME );
			} else {
				$Idx_Name = INTEGRITY_FILENAME;
			}
			
			if ( $Hash_File != $Hashes[ $Idx_Name ] ) {
				$pObject = new stdClass();

				$pObject->scr_id = '';
				$pObject->stp_name = '';
				$pObject->env_name = '';
				$pObject->app_name = '';
				$pObject->scr_host = '';
				$pObject->scr_user = '';

				$Message = sprintf( $L_File_Integrity_Alert, INTEGRITY_FILENAME );

				$this->writeLog( $Message, $pObject, LOG_ERR );
				$this->writeMailSecurity( $Message );

				return array( FALSE );
			} else {
				return array( TRUE, $Hash_File );
			}
		}
	}

}

?>