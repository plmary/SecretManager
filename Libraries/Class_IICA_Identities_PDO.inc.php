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
* @date 2014-06-23
*/
    public $LastInsertId;

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
	 $SuperAdmin, $Operator, $Id_Entity, $Id_Civility, $API, $Salt = '' ) {
	/**
	* Créé ou actualise une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-06-23
	*
	* @param[in] $idn_id Identifiant de l'identité à modifier (si précisé)
	* @param[in] $Login Nom de connexion de l'utilisateur
	* @param[in] $Authenticator Mot de passe de l'utilisateur
	* @param[in] $ChangeAuthenticator Booléen pour indiquer s'il faut changer le mot de passe
	* @param[in] $Attempt Nombre de tentative de connexion
	* @param[in] $SuperAdmin Booléen pour indiquer si l'utilisateur est un Administrateur
	* @param[in] $Operator Booléen pour indiquer si l'utilisateur est un Opérateur
	* @param[in] $Id_Entity Identifiant de l'Entité de rattachement de l'utilisateur
	* @param[in] $Id_Civility Identifiant de la Civilité de rattachement de l'utilisateur
	* @param[in] $Salt Grain de sel à utiliser pour hacher les mots de passe
	*
	* @return Renvoi vrai si l'Identité a été créée ou modifiée, sinon lève une exception
	*/
      include( DIR_PROTECTED . '/Config_Authentication.inc.php' );
	       
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
			 'idn_operator, ' .
			 'idn_api ';
			
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
			 ':idn_operator, ' .
			 ':idn_api ' ;
			
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
			
			$Request = 'UPDATE idn_identities SET ' .
			'ent_id = :ent_id, ' .
			'cvl_id = :cvl_id, ' .
			'idn_login = :idn_login, ' .
			'idn_change_authenticator = :idn_change_authenticator, ' .
			'idn_expiration_date = :idn_expiration_date, ' .
			'idn_updated_authentication = :idn_updated_authentication, ' .
			'idn_super_admin = :idn_super_admin, ' .
			'idn_operator = :idn_operator, ' .
			'idn_api = :idn_api ';
			
			if ( $Authenticator != '' ) {
					$Request .= ', idn_authenticator = :idn_authenticator, ' .
							'idn_salt = :idn_salt ';
			}
			
			$Request .= 'WHERE idn_id = :idn_id';
			

			if ( ! $Result = $this->prepare( $Request ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Command . $Error[ 2 ], $Error[ 1 ] );
			}

			if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
					
			if ( $Authenticator != '' ) {
				if ( ! $Result->bindParam( ':idn_authenticator', $Authenticator, 
				 PDO::PARAM_STR, 64 ) ) {
					$Error = $Result->errorInfo();
					throw new Exception( $Error[ 2 ], $Error[ 1 ] );
				 }

				if ( ! $Result->bindParam( ':idn_salt', $Salt, PDO::PARAM_STR, 32 ) ) {
					$Error = $Result->errorInfo();
					throw new Exception( $Error[ 2 ], $Error[ 1 ] );
				}
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
		
		if ( ! $Result->bindParam( ':idn_operator', $Operator, PDO::PARAM_BOOL ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':idn_api', $API, PDO::PARAM_BOOL ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Command . $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $idn_id == '' ) {
			switch( $this->getAttribute(PDO::ATTR_DRIVER_NAME) ) {
			 default;
				$this->LastInsertId = $this->lastInsertId();
				break;

			 case 'pgsql';
				$this->LastInsertId = $this->lastInsertId( 'idn_identities_idn_id_seq' );
				break;
			}
		}

		return true;
	}


	public function setAuthenticator( $idn_id, $Authenticator ) {
	/**
	* Met à jour l'authentifiant d'une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
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
		
		if ( ! $Result->bindParam( ':idn_operator', $Auditor, PDO::PARAM_BOOL ) ) {
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
		 'idn_operator, ' .
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
	public function detailedListIdentities( $orderBy = '', $SpecificIdentities = '', $Admin = false ) {
	/**
	* Lister les Identités.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @param[in] $orderBy Permet de changer l'ordre d'affichage des identités
	* @param[in] $SpecificIdentities Permet de préciser des critères spécifiques
	* @param[in] $Admin Drapeau pour déterminer si on doit limiter l'affichage des Identités aux seuls administrateurs.
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
		 'T1.idn_operator, ' .
		 'T1.idn_api, ' .
	     'T2.cvl_first_name, ' .
    	 'T2.cvl_last_name, ' .
    	 'T2.cvl_sex, ' .
    	 'T3.ent_code, ' .
    	 'T3.ent_label, ' .
    	 'count(T4.prf_id) AS "total_prf" ' .
		 'FROM idn_identities as T1 ' .
    	 'LEFT JOIN cvl_civilities as T2 ON T1.cvl_id = T2.cvl_id ' .
    	 'LEFT JOIN ent_entities as T3 ON T1.ent_id = T3.ent_id ' .
    	 'LEFT JOIN idpr_identities_profiles AS T4 ON T4.idn_id = T1.idn_id ';


    	// Flag pour déterminer si on doit limiter l'affichage des Identités aux seuls administrateurs.
    	if ( $Admin == true ) {
    		$Request .= 'WHERE t1.idn_operator = true ';
    	}


		// Traite la recherche d'une Identité particulière.
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


		$Request .= 'GROUP BY T1.idn_id,T1.cvl_id,T1.idn_login ';

		
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

		 case 'operator':
			$Request .= 'ORDER BY T1.idn_operator ';
			break;

		 case 'operator-desc':
			$Request .= 'ORDER BY T1.idn_operator DESC ';
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
		 'idn_operator, ' .
		 'idn_api ' .
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
		 'T1.idn_operator, ' .
		 'T1.idn_api, ' .
		 'T2.cvl_last_name, ' .
		 'T2.cvl_first_name, ' .
		 'T2.cvl_sex, ' .
		 'T3.ent_code, ' .
		 'T3.ent_label, ' .
		 'count(T4.prf_id) as "total_prf" ' .
		 'FROM idn_identities AS T1 ' .
		 'LEFT JOIN cvl_civilities AS T2 ON T1.cvl_id = T2.cvl_id ' .
		 'LEFT JOIN ent_entities AS T3 ON T1.ent_id = T3.ent_id ' .
		 'LEFT JOIN idpr_identities_profiles AS T4 ON T1.idn_id = T4.idn_id ' .
		 'WHERE T1.idn_id = :idn_id ' .
		 'GROUP BY T1.cvl_id, T1.idn_login ';
		 
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
	* Supprimer une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @param[in] $idn_id Identifiant de l'Identité à supprimer
	*
	* @return Renvoi vrai si l'Identité a été supprimée, sinon lève une exception
	*/
		
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


		$this->commitTransaction();
 
 		return true;
	}


	public function addGroup( $Id_Identity, $Id_Group, $Flag_Admin = false ) {
	/**
	* Associer un Groupe de Secrets à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité de référence
	* @param[in] $Id_Group Identifiant du Groupe de Secrets à associer à l'Identité
	* @param[in] $Flag_Admin Drapeau permettant de préciser si l'Identité est Administrateur du Groupe de Secrets
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
	* Dissocier une Identité d'un Groupe de Secrets.
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
	* Lister les Groupes de Secret associés à une Identité.
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
	* Lister les Entités associées à une Identité.
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
	* Associer une Entité à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identifiant de l'Identité
	* @param[in] $Id_Entity Identifiant de l'Entité
	* @param[in] $Flag_Admin Permet d'indiquer si l'Identité (utilisateur) sera l'administrateur de l'Entité
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
	* Détruit l'association entre une Entité et une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identité à traiter
	* @param[in] $Id_Entity Entité à traiter
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
	* Lister les Profils associés à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identité à contrôler
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
	* Associer un Profil à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identité à traiter
	* @param[in] $Id_Profile Profil à traiter
	*
	* @return Renvoi vrai si l'association entre l'Identité et le Profil a été créée, sinon lève une exception
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
	* Détruit les Profiles rattaché à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identité à traiter
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
	* Liste les Applications associées à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identité à traiter
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
	* Associe une Application à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identité à associer
	* @param[in] $Id_Application Application à associer
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
	* Détruit l'association entre une Application et une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $Id_Identity Identité à traiter
	* @param[in] $Id_Application Application à traiter
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
	* Calcul le nombre total d'Identités.
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
	* Calcul le nombre total d'Identités désactivées.
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
	* Calcul le nombre total d'Identités expirées.
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
	* Calcul le nombre total d'Identités Super Administrateur.
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


	public function totalOperator() {
	/**
	* Calcul le nombre total d'Identités Opérateur.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @return Renvoi le nombre total d'Identités Opérateur
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 'count(*) as total ' .
		 'FROM idn_identities ' .
		 'WHERE idn_operator = 1 ' ) ) {
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


	public function totalAPI() {
		/**
		 * Calcul le nombre total d'Identités API.
		 *
		 * @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
		 * @author Pierre-Luc MARY
		 * @date 2015-03-14
		 *
		 * @return Renvoi le nombre total d'Identités API
		 */
		if ( ! $Result = $this->prepare( 'SELECT ' .
			 'count(*) as total ' .
			 'FROM idn_identities ' .
			 'WHERE idn_api = 1 ' ) ) {
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
	* Calcul le nombre total d'Identités ayant atteint le maximum de tentative de
	* connexion.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @return Renvoi le nombre total d'Identités ayant atteint le maximum de tentative de
	* connexion.
	*/
		include( DIR_PROTECTED . '/Config_Authentication.inc.php' );

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


	public function getUserForHistory( $idn_id, $oUser = '' ) {
	/**
	* Formatte une chaîne pour remonter des informations dans l'historique.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-06-20
	*
	* @return Renvoi la chaîne formattée
	*/
		include_once( DIR_LIBRARIES . '/Class_HTML.inc.php' );

		$pHTML = new HTML();

		// Récupère l'objet Utilisateur si ce dernier n'a pas été fourni.
		if ( $oUser == '' ) $oUser = $this->detailedGet( $idn_id );
 
    	// Récupère les libellés pour le message
    	$Labels = $pHTML->getTextCode( array( 'L_Username', 'L_Administrator', 'L_Civility', 'L_Entity', 'L_Operator' ), $pHTML->getParameter( 'language_alert' ) );

 		return ' (' . $Labels['L_Username'] . '="' . $oUser->idn_login . '", ' . $Labels['L_Administrator' ] . '="' . $oUser->idn_super_admin .
 			'", ' . $Labels['L_Civility'] . '="' . $oUser->cvl_first_name . ' ' .
 			'", ' . $Labels['L_Operator' ] . '="' . $oUser->idn_operator . $oUser->cvl_last_name . '", ' .
 			$Labels['L_Entity'] . '="' . $oUser->ent_code . ' - ' . $oUser->ent_label . '")';
	}


	public function getCivility( $cvl_id, $idn_id = '' ) {
	/**
	* Récupère les informations d'une Civilité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-05-15
	*
	* @return Renvoi un objet Civlité ou lève une exception en cas d'erreur.
	*/
		$Request = 'SELECT ' .
		 'cvl_last_name, cvl_first_name ' .
		 'FROM cvl_civilities AS T1 ' ;

		if ( $idn_id != '' ) $Request .= 'LEFT JOIN idn_identities AS T2 ON T1.cvl_id = T2.cvl_id WHERE T2.idn_id = :idn_id ';
		else $Request .= 'WHERE cvl_id = :cvl_id ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $idn_id != '' ) {
			if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}			
		} else {
			if ( ! $Result->bindParam( ':cvl_id', $cvl_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Valeur = $Result->fetchObject() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		return $Valeur;
	}


	public function getEntity( $ent_id ) {
	/**
	* Récupère les informations d'une Civilité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-05-15
	*
	* @return Renvoi un objet Entité ou lève une exception en cas d'erreur.
	*/
		$Request = 'SELECT ' .
		 'ent_code, ent_label ' .
		 'FROM ent_entities ' .
		 'WHERE ent_id = :ent_id ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':ent_id', $ent_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Valeur = $Result->fetchObject() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		return $Valeur;
	}


	public function getProfile( $prf_id ) {
	/**
	* Récupère les informations d'un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-05-15
	*
	* @return Renvoi un objet Profil ou lève une exception en cas d'erreur.
	*/
		$Request = 'SELECT ' .
		 'prf_label ' .
		 'FROM prf_profiles ' .
		 'WHERE prf_id = :prf_id ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':prf_id', $prf_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Valeur = $Result->fetchObject() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		return $Valeur;
	}


	public function getGroups( $sgr_id ) {
	/**
	* Récupère les informations sur un Groupe de Secrets.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-05-15
	*
	* @return Renvoi un objet Groupe de Secrets ou lève une exception en cas d'erreur.
	*/
		$Request = 'SELECT ' .
		 'sgr_label, sgr_alert ' .
		 'FROM sgr_secrets_groups ' .
		 'WHERE sgr_id = :sgr_id ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Valeur = $Result->fetchObject() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		return $Valeur;
	}

} // Fin class IICA_Identities

?>