<?php

include_once( 'Constants.inc.php' );

include_once( IICA_LIBRARIES . '/Class_IICA_DB_Connector_PDO.inc.php' );

class IICA_Identities extends IICA_DB_Connector {
/**
* Cette classe gère les Identités.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
*/

	public function __construct() {
	/**
	* Connexion à la base de données.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @return Renvoi un booléen sur le succès de la connexion à la base de données
	*/
		parent::__construct();
		
		return true;
	}


	/* ===============================================================================
	** Gestion des Identités
	*/
	
	public function set( $idn_id, $Login, $Authenticator, $ChangeAuthenticator, $Attempt,
	 $SuperAdmin, $Auditor, $Id_Entity, $Id_Civility, $Salt = '' ) {
	/**
	* Créé ou actualise une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $idn_id Identifiant de l'identité à modifier (si précisé)
	* @param[in] $Login Nom de connexion de l'utilisateur
	* @param[in] $Authenticator Mot de passe de l'utilisateur
	* @param[in] $ChangeAuthenticator Booléen pour indiquer s'il faut changer le mot de passe
	* @param[in] $Attempt Nombre de tentative de connexion
	* @param[in] $SuperAdmin Booléen pour indiquer si l'utilisateur est un Administrateur
	* @param[in] $Auditor Booléen pour indiquer si l'utilisateur est un Auditeur
	* @param[in] $Id_Entity Identifiant de l'Entité de rattachement de l'utilisateur
	* @param[in] $Id_Civility Identifiant de la Civilité de rattachement de l'utilisateur
	* @param[in] $Salt Grain de sel à utiliser pour hacher les mots de passe
	*
	* @return Renvoi vrai si l'Identité a été créée ou modifiée, sinon lève une exception
	*/
      include( 'Libraries/Config_Authentication.inc.php' );
	       
		if ( $idn_id == '' ) {
			$Command = 'INSERT : ' ;

			$Request = 'INSERT INTO idn_identities (' .
			 'ent_id, ' .
			 'cvl_id, ' .
			 'idn_login, ' .
			 'idn_authenticator, ' .
			 'idn_change_authenticator, ' .
			 'idn_attempt, ' .
			 'idn_expiration_date, ' .
			 'idn_updated_authentication, ' .
			 'idn_super_admin, ' .
			 'idn_auditor ' ;
			
			if ( $Salt != '' ) {
				$Request .= ', idn_salt ' ;
			}
			
			$Request .= ') VALUES ( ' .
			 ':ent_id, ' .
			 ':cvl_id, ' .
			 ':idn_login, ' .
			 ':idn_authenticator, ' .
			 ':idn_change_authenticator, ' .
			 ':idn_attempt, ' .
			 ':idn_expiration_date, ' .
			 ':idn_updated_authentication, ' .
			 ':idn_super_admin, ' .
			 ':idn_auditor ' ;
			
			if ( $Salt != '' ) {
				$Request .= ', :idn_salt ' ;
			}
			
			$Request .= ')';
			 
			if ( ! $Result = $this->prepare( $Request ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Command . $Error[ 2 ], $Error[ 1 ] );
			}
		
			if ( ! $Result->bindParam( ':idn_attempt', $Attempt,
			 PDO::PARAM_STR, 35 ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
         
			if ( ! $Result->bindParam( ':idn_authenticator', $Authenticator, 
			 PDO::PARAM_STR, 64 ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		
			if ( $Salt != '' ) {
				if ( ! $Result->bindParam( ':idn_salt', $Salt, PDO::PARAM_STR, 32 ) ) {
					$Error = $Result->errorInfo();
					throw new Exception( $Error[ 2 ], $Error[ 1 ] );
				}
			}
		} else {
			$Command = 'UPDATE : ' ;

			if ( ! $Result = $this->prepare(
			 'UPDATE idn_identities SET ' .
			 'ent_id = :ent_id, ' .
			 'cvl_id = :cvl_id, ' .
			 'idn_login = :idn_login, ' .
			 'idn_change_authenticator = :idn_change_authenticator, ' .
			 'idn_expiration_date = :idn_expiration_date, ' .
			 'idn_updated_authentication = :idn_updated_authentication, ' .
			 'idn_super_admin = :idn_super_admin, ' .
			 'idn_auditor = :idn_auditor ' .
			 'WHERE idn_id = :idn_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Command . $Error[ 2 ], $Error[ 1 ] );
			}

			if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
    
		if ( ! $Result->bindParam( ':ent_id', $Id_Entity, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':cvl_id', $Id_Civility, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':idn_login', $Login, PDO::PARAM_STR, 20 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':idn_change_authenticator', $ChangeAuthenticator, 
		 PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$NextDate  = strftime( "%Y-%m-%d",
		 mktime( 0, 0, 0, date("m") + $_Default_User_Lifetime, date("d"), date("Y") ) );
		if ( ! $Result->bindParam( ':idn_expiration_date', $NextDate,
		 PDO::PARAM_STR, 19 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		$Current_Date = date( 'Y-m-d H:n:s' );
		if ( ! $Result->bindParam( ':idn_updated_authentication', $Current_Date,
		 PDO::PARAM_STR, 19 ) ) {
    		$Error = $Result->errorInfo();
    		throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':idn_super_admin', $SuperAdmin,
		 PDO::PARAM_BOOL ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':idn_auditor', $Auditor, PDO::PARAM_BOOL ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Command . $Error[ 2 ], $Error[ 1 ] );
		}
		
		return true;
	}


	public function setAuthenticator( $idn_id, $Authenticator ) {
	/**
	* Met à jour l'authentifiant d'une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $idn_id Identifiant de l'identité à modifier
	* @param[in] $Authenticator Mot de passe à modifier
	*
	* @return Renvoi vrai si le mot de passe a été modifiée, sinon lève une exception
	*/
		if ( $Id == '' ) {
			$NextDate  = strftime( "%Y-%m-%d",
				mktime( 0, 0, 0, date("m") + 3, date("d"), date("Y") ) );

         if ( ! $Result = $this->prepare(
			 'UPDATE idn_identities SET ' .
			 'idn_authenticator = :idn_authenticator, ' .
			 'idn_change_authenticator = :idn_change_authenticator, ' .
			 'idn_expiration_date = :idn_expiration_date, ' .
			 'idn_updated_authentication = :idn_updated_authentication ' .
			 'WHERE idn_id = :idn_id' ) ) {
				$Error = $Result->errorInfo();
				$Command = 'UPDATE : ' ;
				throw new Exception( $Command . $Error[ 2 ], $Error[ 1 ] );
			}

			if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
		
		if ( ! $Result->bindParam( ':ent_id', $Id_Entity, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':cvl_id', $Id_Civility, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':idn_login', $Login, PDO::PARAM_STR, 20 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':idn_super_admin', $SuperAdmin,
		 PDO::PARAM_BOOL ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':idn_auditor', $Auditor, PDO::PARAM_BOOL ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Command . $Error[ 2 ], $Error[ 1 ] );
		}
		
		return true;
	}


	public function listIdentities() {
	/**
	* Lister les Identités.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @return Renvoi une liste d'identités ou une liste vide
	*/
		$Data = false;
		
		$Request = 'SELECT ' .
		 'idn_id, ' .
		 'cvl_id, ' .
		 'idn_login, ' .
		 'idn_authenticator, ' .
		 'idn_change_authenticator, ' .
		 'idn_attempt, ' .
		 'idn_updated_authentication, ' .
		 'idn_super_admin, ' .
		 'idn_auditor, ' .
		 'idn_disable ' .
		 'FROM idn_identities ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	/* -------------------
	** Lister les Identités de façon détaillées.
	*/
	public function detailedListIdentities( $orderBy = '', $SpecificIdentities = '' ) {
	/**
	* Lister les Identités.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @param[in] $orderBy Permet de changer l'ordre d'affichage des identités
	* @param[in] $SpecificIdentities Permet de préciser des critères spécifiques
	*
	* @return Renvoi une liste détaillée d'identités (avec toutes les relations) ou une liste vide
	*/
		$Request = 'SELECT ' .
		 'T1.idn_id, ' .
		 'T1.cvl_id, ' .
		 'T1.idn_login, ' .
		 'T1.idn_authenticator, ' .
		 'T1.idn_change_authenticator, ' .
		 'T1.idn_attempt, ' .
		 'T1.idn_disable, ' .
		 'T1.idn_last_connection, ' .
		 'T1.idn_expiration_date, ' .
		 'T1.idn_updated_authentication, ' .
		 'T1.idn_super_admin, ' .
		 'T1.idn_auditor, ' .
	     'T2.cvl_first_name, ' .
    	 'T2.cvl_last_name, ' .
    	 'T2.cvl_sex, ' .
    	 'T3.ent_code, ' .
    	 'T3.ent_label ' .
		 'FROM idn_identities as T1 ' .
    	 'LEFT JOIN cvl_civilities as T2 ON T1.cvl_id = T2.cvl_id ' .
    	 'LEFT JOIN ent_entities as T3 ON T1.ent_id = T3.ent_id ';

		if ( $SpecificIdentities != '' ) {
			if ( ! preg_match("/WHERE/i", $Request ) ) {
				$Request .= 'WHERE ' ;
			} else {
				$Request .= 'AND ' ;
			}
		}

		$SpecificIdentities = explode( '=', $SpecificIdentities );

		switch( $SpecificIdentities[ 0 ] ) {
		 case 'disable':
			$Request .= 'T1.idn_disable = 1 ';
			break;

		 case 'expiration':
			$Request .= 'T1.idn_expiration_date < date( "Y-m-d H:i:s" ) ';
			break;
			
		 case 'attempt':
			$Request .= 'T1.idn_attempt > ' . $SpecificIdentities[ 1 ] . ' ' ;
			break;
			
		 case 'admin':
			$Request .= 'T1.idn_super_admin = 1 ';
			break;
			
		}

		
		switch( $orderBy ) {
		 default:
		 case 'entity':
			$Request .= 'ORDER BY T3.ent_label ';
			break;

		 case 'entity-desc':
			$Request .= 'ORDER BY T3.ent_label DESC ';
			break;

		 case 'first_name':
			$Request .= 'ORDER BY T2.cvl_first_name ';
			break;

		 case 'first_name-desc':
			$Request .= 'ORDER BY T2.cvl_first_name DESC ';
			break;

		 case 'last_name':
			$Request .= 'ORDER BY T2.cvl_last_name ';
			break;

		 case 'last_name-desc':
			$Request .= 'ORDER BY T2.cvl_last_name DESC ';
			break;

		 case 'username':
			$Request .= 'ORDER BY T1.idn_login ';
			break;

		 case 'username-desc':
			$Request .= 'ORDER BY T1.idn_login DESC ';
			break;

		 case 'last_connection':
			$Request .= 'ORDER BY T1.idn_last_connection ';
			break;

		 case 'last_connection-desc':
			$Request .= 'ORDER BY T1.idn_last_connection DESC ';
			break;

		 case 'administrator':
			$Request .= 'ORDER BY T1.idn_super_admin ';
			break;

		 case 'administrator-desc':
			$Request .= 'ORDER BY T1.idn_super_admin DESC ';
			break;

		 case 'auditor':
			$Request .= 'ORDER BY T1.idn_auditor ';
			break;

		 case 'auditor-desc':
			$Request .= 'ORDER BY T1.idn_auditor DESC ';
			break;
		}

		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function get( $idn_id ) {
	/**
	* Récupérer les informations d'une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @param[in] $idn_id Identifiant de l'Identité à récupérer
	*
	* @return Renvoi l'occurrence d'une Identité
	*/
		$Request = 'SELECT ' .
		 'ent_id, ' .
		 'cvl_id, ' .
		 'idn_login, ' .
		 'idn_authenticator, ' .
		 'idn_change_authenticator, ' .
		 'idn_attempt, ' .
		 'idn_disable, ' .
		 'idn_last_connection, ' .
		 'idn_expiration_date, ' .
		 'idn_updated_authentication, ' .
		 'idn_super_admin, ' .
		 'idn_auditor ' .
		 'FROM idn_identities ' .
		 'WHERE idn_id = :idn_id ';
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
 		return $Result->fetchObject();
	}


	public function detailedGet( $idn_id ) {
	/**
	* Afficher une Identité en détail.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @param[in] $idn_id Identifiant de l'Identité à récupérer
	*
	* @return Renvoi l'occurrence détaillée d'une Identité
	*/
		$Request = 'SELECT ' .
		 'T1.cvl_id, ' .
		 'T1.idn_login, ' .
		 'T1.idn_authenticator, ' .
		 'T1.idn_change_authenticator, ' .
		 'T1.idn_attempt, ' .
		 'T1.idn_disable, ' .
		 'T1.idn_last_connection, ' .
		 'T1.idn_expiration_date, ' .
		 'T1.idn_updated_authentication, ' .
		 'T1.idn_super_admin, ' .
		 'T1.idn_auditor, ' .
		 'T2.cvl_last_name, ' .
		 'T2.cvl_first_name, ' .
		 'T2.cvl_sex, ' .
		 'T3.ent_code, ' .
		 'T3.ent_label ' .
		 'FROM idn_identities AS T1 ' .
		 'LEFT JOIN cvl_civilities AS T2 ON T1.cvl_id = T2.cvl_id ' .
		 'LEFT JOIN ent_entities AS T3 ON T1.ent_id = T3.ent_id ' .
		 'WHERE idn_id = :idn_id ';
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
 		return $Result->fetchObject();
	}


	public function delete( $idn_id ) {
	/**
	* Supprimer une Identité en détail.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @param[in] $idn_id Identifiant de l'Identité à supprimer
	* @param[in] $Type Permet de supprimer les identités logiquement ou physiquement
	*
	* @return Renvoi vrai si l'Identité a été supprimée, sinon lève une exception
	*/
		include( 'Libraries/Config_Access_Tables.inc.php' );
		
		
		/*
		** Commence la transaction.
		*/
		$this->beginTransaction();
	
        if ( ! $Result = $this->prepare( 'DELETE ' .
         'FROM idn_identities ' .
         'WHERE idn_id = :idn_id' ) ) {
            $Error = $Result->errorInfo();
            throw new Exception( $Error[ 2 ], $Error[ 1 ] );
        }
		
		$Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		/*
		** Supprime la relation possible entre l'Identité et les Entités.
		*/
		if ( $_Access_IDN_ENT == 1 ) {
			if ( ! $Result = $this->prepare( 'DELETE ' .
			 'FROM iden_identites_entities ' .
			 'WHERE idn_id = :idn_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		
			$Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ;
		
			if ( ! $Result->execute() ) {
				$this->rollBack();
 
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}


		/*
		** Supprime la relation possible entre l'Identité et les Groupes.
		*/
		if ( $_Access_IDN_GRP == 1 ) {
			if ( ! $Result = $this->prepare( 'DELETE ' .
			 'FROM idgr_identities_groups ' .
			 'WHERE idn_id = :idn_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		
			$Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ;
		
			if ( ! $Result->execute() ) {
				$this->rollBack();
 
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}


		/*
		** Supprime la relation possible entre l'Identité et les Profiles.
		*/
		if ( $_Access_IDN_PRF == 1 ) {
			if ( ! $Result = $this->prepare( 'DELETE ' .
			 'FROM idpr_identities_profiles ' .
			 'WHERE idn_id = :idn_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		
			$Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ;
		
			if ( ! $Result->execute() ) {
				$this->rollBack();
 
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}


		/*
		** Supprime la relation possible entre l'Identité et les Applications.
		*/
		if ( $_Access_IDN_APP == 1 ) {
			if ( ! $Result = $this->prepare( 'DELETE ' .
			 'FROM idpr_identities_applications ' .
			 'WHERE idn_id = :idn_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		
			$Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ;
		
			if ( ! $Result->execute() ) {
				$this->rollBack();
 
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}


		/*
		** Supprime la relation possible entre l'Identité et les Historiques des mots de
		** passe.
		*/
		if ( $_Access_IDN_HST == 1 ) {
			if ( ! $Result = $this->prepare( 'DELETE ' .
			 'FROM psh_passwords_history ' .
			 'WHERE idn_id = :idn_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		
			$Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ;
		
			if ( ! $Result->execute() ) {
				$this->rollBack();
 
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}


		$this->commit();
 
 		return true;
	}


	public function addGroup( $Id_Identity, $Id_Group, $Flag_Admin = false ) {
	/**
	* Ajouter un Groupe à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité de référence
	* @param[in] $Id_Group Identifiant du Groupe de Secrets à associer à l'Identité
	* @param[in] $Flag_Admin Booléen permettant de préciser si l'Identité est Administrateur du Groupe de Secrets
	*
	* @return Renvoi vrai si le Groupe de Secrets a été ajouté à l'Identité, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'INSERT ' .
		 'into idgr_entities_groups ' .
		 '( grp_id, idn_id, idgr_admin ) ' .
		 'VALUES ( :Grp_id, :Idn_id, :Idgr_admin )' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Grp_id', $Id_Group, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Idgr_admin', $Flag_Admin, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function deleteGroup( $Id_Identity, $Id_Group ) {
	/**
	* Supprimer une Identité à un Groupe.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité de référence
	* @param[in] $Id_Group Identifiant du Groupe de Secrets à dissocier de l'Identité
	*
	* @return Renvoi vrai si le Groupe de Secrets a été dissocié de l'Identité, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM idgr_identities_groups ' .
		 'WHERE grp_id = :Grp_id AND idn_id = :Idn_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Grp_id', $Id_Group, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function listGroups( $Id_Identity ) {
	/**
	* Lister les Groupes d'une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité de référence
	*
	* @return Renvoi la liste des Groupes de Secrets associés à l'Identité, sinon retourne une liste vide
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 't3.grp_id, t3.grp_label, t3.grp_alert, t2.rgh_id ' .
		 'FROM idpr_identities_profiles AS t1 ' .
		 'LEFT JOIN prsg_profiles_secrets_groups AS t2 ON t1.prf_id = t2.prf_id ' .
		 'LEFT JOIN sgr_secrets_groups AS t3 ON t2.sgr_id = t3.sgr_id ' .
		 'WHERE t1.idn_id = :Idn_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function listEntities( $Id_Identity ) {
	/**
	* Lister les Entités d'une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité de référence
	*
	* @return Renvoi la liste des Entités associées à l'Identité, sinon retourne une liste vide
	*/
		if ( ! $Result = $this->prepare( '(SELECT ' .
		 'ent_id, ent_code, ent_label ' .
		 'FROM idgr_identities_groups AS t1 ' .
		 'LEFT JOIN engr_entities_groups AS t2 ON t1.grp_id = t2.grp_id ' .
		 'LEFT JOIN ent_entities AS t3 ON t3.ent_id = t2.ent_id ' .
		 'WHERE t1.idn_id = :Idn_id) ' .
		 'UNION DISTINCT ' .
		 '(SELECT ' .
		 'ent_id, ent_code, ent_label ' .
		 'FROM iden_identities_entities AS t4 ' .
		 'LEFT JOIN ent_entities AS t5 ON t4.ent_id = t5.ent_id ' .
		 'WHERE t4.idn_id = :Idn_id) ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function addEntity( $Id_Identity, $Id_Entity, $Flag_Admin = false ) {
	/**
	* Ajouter une Entité à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité
	* @param[in] $Id_Entity Identifiant de l'Entité
	* @param[in] $Flag_Admin Permet d'indiquer si l'Identifiant est administrateur de l'Entité
	*
	* @return Renvoi vrai si l'Identité a été associée à l'Entité, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'INSERT ' .
		 'INTO iden_identities_entities ' .
		 '( idn_id, ent_id, iden_admin ) ' .
		 'VALUES ( :Idn_id, :Ent_id, :Admin ) ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Ent_id', $Id_Entity, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Admin', $Flag_Admin, PDO::PARAM_BOOL ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function deleteEntity( $Id_Identity, $Id_Entity ) {
	/**
	* Détruire une Entité rattachée à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité
	* @param[in] $Id_Entity Identifiant de l'Entité
	*
	* @return Renvoi vrai si l'association entre l'Identité et l'Entité a été supprimée, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM iden_identities_entities ' .
		 'WHERE idn_id = :Idn_id AND ent_id = :Ent_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Ent_id', $Id_Entity, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function listProfiles( $Id_Identity ) {
	/**
	* Lister les Profils d'une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité
	*
	* @return Renvoi la liste des Profiles rattachés à l'Identité, sinon une liste vide
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 't1.prf_id, t2.prf_label ' .
		 'FROM idpr_identities_profiles AS t1 ' .
		 'LEFT JOIN prf_profiles AS t2 ON t1.prf_id = t2.prf_id ' .
		 'WHERE t1.idn_id = :Idn_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[ $Occurrence->prf_id ] = $Occurrence->prf_label;
		}
 
 		return $Data;
	}


	public function addProfile( $Id_Identity, $Id_Profile ) {
	/**
	* Ajouter un Profil à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité
	* @param[in] $Id_Profile Identifiant du Profil
	*
	* @return Renvoi vrai si l'association entre l'Identité et le Profile a été créée, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'INSERT ' .
		 'INTO idpr_identities_profiles ' .
		 '( idn_id, prf_id ) ' .
		 'VALUES ( :Idn_id, :Prf_id ) ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Prf_id', $Id_Profile, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function deleteProfile( $Id_Identity, $Id_Profile ) {
	/**
	* Détruire un Profil rattaché à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité
	* @param[in] $Id_Profile Identifiant du Profil
	*
	* @return Renvoi vrai si l'association entre l'Identité et le Profile a été supprimée, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM idpr_identities_profiles ' .
		 'WHERE idn_id = :Idn_id AND prf_id = :Prf_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Prf_id', $Id_Profile, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function deleteProfiles( $Id_Identity ) {
	/**
	* Détruire les Profiles rattaché à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité
	*
	* @return Renvoi vrai si l'association entre l'Identité et tous ses Profiles ont été supprimées, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM idpr_identities_profiles ' .
		 'WHERE idn_id = :Idn_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function listApplications( $Id_Identity ) {
	/**
	* Lister les Applications d'une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité
	*
	* @return Renvoi la liste des Applications associé à l'Identité, sinon renvoi une liste vide
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 'app_id, app_code, app_label, app_localization ' .
		 'FROM idpr_identities_profiles AS t1 ' .
		 'LEFT JOIN prac_profiles_access_control AS t2 ON t1.prf_id = t2.prf_id ' .
		 'LEFT JOIN app_applications AS t3 ON t2.app_id = t3.app_id ' .
		 'WHERE t1.idn_id = :Idn_id) ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function addApplication( $Id_Identity, $Id_Application ) {
	/**
	* Ajouter une Application à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité
	* @param[in] $Id_Application Identifiant de l'Application
	*
	* @return Renvoi vrai si l'association entre l'Identité et une Application a été créée, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'INSERT ' .
		 'INTO idap_identities_applications ' .
		 '( idn_id, app_id ) ' .
		 'VALUES ( :Idn_id, :App_id ) ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		$Result->bindParam( ':App_id', $Id_Application, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function deleteApplication( $Id_Identity, $Id_Application ) {
	/**
	* Détruire une Application rattachée à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité
	* @param[in] $Id_Application Identifiant de l'Application
	*
	* @return Renvoi vrai si l'association entre l'Identité et une Application a été supprimée, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM idap_identities_applications ' .
		 'WHERE idn_id = :Idn_id AND app_id = :App_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Idn_id', $Id_Identity, PDO::PARAM_INT ) ;
		$Result->bindParam( ':App_id', $Id_Application, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function total() {
	/**
	* Récupère le nombre total d'Identités.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @return Renvoi le nombre total d'Identités
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 'count(*) as total ' .
		 'FROM idn_identities ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject();
 
 		return $Occurrence->total;
	}


	public function totalDisabled() {
	/**
	* Récupère le nombre total d'Identités désactivées.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @return Renvoi le nombre total d'Identités désactivées
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 'count(*) as total ' .
		 'FROM idn_identities ' .
		 'WHERE idn_disable = 1 ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject();
 
 		return $Occurrence->total;
	}


	public function totalExpired() {
	/**
	* Récupère le nombre total d'Identités expirées.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @return Renvoi le nombre total d'Identités expirées
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 'count(*) as total ' .
		 'FROM idn_identities ' .
		 'WHERE idn_expiration_date < "' .  date( 'Y-m-d' ) . '" ' .
		 'AND idn_expiration_date <> "0000-00-00 00:00:00"' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject();
 
 		return $Occurrence->total;
	}


	public function totalSuperAdmin() {
	/**
	* Récupère le nombre total d'Identités Super Administrateur.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @return Renvoi le nombre total d'Identités Super Administrateur
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 'count(*) as total ' .
		 'FROM idn_identities ' .
		 'WHERE idn_super_admin = 1 ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject();
 
 		return $Occurrence->total;
	}


	public function totalAuditor() {
	/**
	* Récupère le nombre total d'Identités Auditor.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @return Renvoi le nombre total d'Identités Auditor
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 'count(*) as total ' .
		 'FROM idn_identities ' .
		 'WHERE idn_auditor = 1 ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject();
 
 		return $Occurrence->total;
	}


	public function totalAttempted() {
	/**
	* Récupère le nombre total d'Identités ayant atteint le maximum de tentative de
	* connexion.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @return Renvoi le nombre total d'Identités ayant atteint le maximum de tentative de
connexion.
	*/
		include( 'Libraries/Config_Authentication.inc.php' );

		$Request = 'SELECT ' .
		 'count(*) as total ' .
		 'FROM idn_identities ' .
		 'WHERE idn_attempt > :Max_Attempt ';
		
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':Max_Attempt', $_Max_Attempt, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject();
 
 		return $Occurrence->total;
	}


	/* -------------------
	** Vérifie si le Groupe est associé.
	*/
	public function isAssociated( $idn_id, $Table ) {
	/**
	* Vérifie si une Identité est associée
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $idn_id Identifiant de l'Identité
	* @param[in] $Table Table d'association à contrôler
	*
	* @return Renvoi vrai si l'association entre l'Identité et la table d'association spécifiée, sinon retourne faux
	*/
		switch( strtoupper( $Table ) ) {
		 case 'ENT':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM iden_identities_entities ' .
			 'WHERE idn_id = :idn_id ' ;
			break;

		 case 'GRP':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM idgr_identities_groups ' .
			 'WHERE idn_id = :idn_id ' ;
			break;

		 case 'PRF':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM idpr_identities_profiles ' .
			 'WHERE idn_id = :idn_id ' ;
			break;

		 case 'APP':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM idap_identities_applications ' .
			 'WHERE idn_id = :idn_id ' ;
			break;
		}

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Valeur = $Result->fetch() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $Valeur[ 0 ] == 0 ) return false ;
		
		return true ;
	}

} // Fin class IICA_Identities

?>