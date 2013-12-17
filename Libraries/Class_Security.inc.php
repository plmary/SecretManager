<?php

/**
* Cette classe gère les problématiques de sécurité. Tel que le contrôle des variables en
* entrées, en sortie (notamment pour l'affichage à l'écran) ou calcule des grains de sel,
* etc.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-08
*
*/

class Security {
	public function __construct() {
		return;
	}


	public function XSS_Protection( $value, $mode='ASCII' ) {
	/**
	* Anti-injection XSS (à utiliser avant l'affichage d'une variable).
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-08
	*
	* @param[in] $value Chaine de caractère à contrôler
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
	* A utiliser avant l'affichage d'une variable.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-08
	*
	* @param[in] $value Chaine de caractère à contrôler
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
	* @version 1.0
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
	* @version 1.0
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
	* @version 1.0
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
	* @version 1.0
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
	* @version 1.0
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
	* @version 1.0
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
	* @version 1.0
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
	* @version 1.0
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
	* @version 1.0
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
	* @version 1.0
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
 	
 	
	public function writeLog( $message, $priority = LOG_WARNING ) {
	/**
	* Envoi le message dans le flux "Syslog"
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-08
	*
	* @param[in] $message Message à envoyer dans le flux.
	* @param[in] $priority Type de priorité dans le "Syslog" (par défaut LOG_WARNING)
	* Les autres valeurs sont :
	* LOG_EMERG	système inutilisable
	* LOG_ALERT	une décision doit être prise immédiatement
	* LOG_CRIT	condition critique
	* LOG_ERR	condition d'erreur
	* LOG_WARNING	condition d'alerte
	* LOG_NOTICE	condition normale, mais significative
	* LOG_INFO	message d'information
	* LOG_DEBUG	message de déboguage
	*
	* @return Retourne vrai si le message a été envoyé dans Syslog, sinon retrouve faux
	*/
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
	
	
	public function writeMail( $message, $from, $to ) {
	/**
	* Envoi le message par courriel
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-08
	*
	* @param[in] $message Message à envoyer dans le courriel.
	* @param[in] $from Emetteur du courriel
	* @param[in] $to Destinataires du courriel
	*
	* @return Retourne vrai si le message a été envoyé au serveur de messagerie, sinon retrouve faux (attention, envoyé au serveur de messagerie, ne signifie pas bien arrivé auprès des destinataires)
	*/
		// Sujet
		$subject = 'Alert SecretManager';

		// message
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

		// Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// En-têtes additionnels
		$headers .= 'To: ' . $to . "\r\n";
		$headers .= 'From: ' .$from . "\r\n";
//		$headers .= 'Cc: anniversaire_archive@example.com' . "\r\n";
//		$headers .= 'Bcc: anniversaire_verif@example.com' . "\r\n";

		// Envoi
		return mail($to, $subject, $body ); //, $headers);
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
	* @version 1.0
	* @date 2012-11-08
	*
	* @param[in] $encrypt Données à chiffrer.
	* @param[in] $mc_key Clé de chiffrement.
	*
	* @return string Retourne la chaine de données chiffrée.
	*/
		if ( $mc_key == '' ) {
			include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
			
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
	* @version 1.0
	* @date 2012-11-08
	*
	* @param[in] $decrypt Données à déchiffrer.
	* @param[in] $mc_key Clé de déchiffrement.
	*
	* @return string Retourne la chaine de données déchiffrée.
	*/
		if ( $mc_key == '' ) {
			include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
			
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


	/* ===============================================================================
	*/
	public function getTransportKey( $ID_Session, $FLAG_DEBUG = 0 ) {
	/**
	* Récupère la clé de transport utilisée pour chiffrer les données en transit.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
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
	* @version 1.0
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

}

?>