<?php

include_once( 'Constants.inc.php' );

include_once( IICA_LIBRARIES . '/Class_IICA_DB_Connector_PDO.inc.php' );


class IICA_Profiles extends IICA_DB_Connector {
/**
* Cette classe gère les profils.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
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
	** Gestion des Profils
	*/
	
	public function set( $prf_id, $Label ) {
	/**
	* Créé ou actualise un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $prf_id Identifiant du Profil à mettre à jour s'il est précisé
	* @param[in] $Label Libellé à donner au Profil
	*
	* @return Renvoi vrai si le Profil a été créé ou mis à jour, sinon lève une exception
	*/
		if ( $prf_id == '' ) {
			$Command = 'INSERT : ' ;

			if ( ! $Result = $this->prepare( 'INSERT INTO prf_profiles ' .
				'( prf_label ) ' .
				'VALUES ( :Label )' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Command . $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			$Command = 'UPDATE : ' ;

			if ( ! $Result = $this->prepare( 'UPDATE prf_profiles SET ' .
				'prf_label = :Label ' .
				'WHERE prf_id = :prf_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Command . $Error[ 2 ], $Error[ 1 ] );
			}

			if ( ! $Result->bindParam( ':prf_id', $prf_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
		
		if ( ! $Result->bindParam( ':Label', $Label, PDO::PARAM_STR, 40 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Command . $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $prf_id == '' ) {
			switch( $this->getAttribute(PDO::ATTR_DRIVER_NAME) ) {
			 default;
				$this->LastInsertId = $this->lastInsertId();
				break;

			 case 'pgsql';
				$this->LastInsertId = $this->lastInsertId( 'prf_profiles_prf_id_seq' );
				break;
			}
		}

		return true;
	}


	public function listProfiles( $order = '' ) {
	/**
	* Lister les Profils.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $order Ordre d'affichage des éléments dans la liste
	*
	* @return Renvoi un tableau des Profils trouvés, sinon un tableau vide
	*/
		$Request = 'SELECT ' .
		 'prf_id, prf_label ' .
		 'FROM prf_profiles ' ;
		
		switch ( $order ) {
		 default:
		 case 'label':
			$Request .= 'ORDER BY prf_label ';
			break;
			
		 case 'label-desc':
			$Request .= 'ORDER BY prf_label DESC ';
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


	public function get( $prf_id ) {
	/**
	* Récupère les informations d'un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $prf_id Identifiant du Profil à récupérer
	*
	* @return Renvoi l'instance du Profil trouvé, sinon une instance vide
	*/
		$Request = 'SELECT ' .
		 'prf_label ' .
		 'FROM prf_profiles ' .
		 'WHERE prf_id = :prf_id ';
		 
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
		
 		return $Result->fetchObject();
	}


	public function delete( $prf_id ) {
	/**
	* Supprime le Profil spécifié.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $prf_id Identifiant du Profil à supprimer
	*
	* @return Renvoi vrai si Profil a été supprimé, sinon lève une exception
	*/
		/*
		** Démarre la transaction.
		*/
		$this->beginTransaction();
		
	
		/*
		** Détruit le Profil.
		*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM prf_profiles ' .
		 'WHERE prf_id = :prf_id' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':prf_id', $prf_id, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		/*
		** Supprime la relation possible entre les Profils et les Identités.
		*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM idpr_identities_profiles ' .
		 'WHERE prf_id = :prf_id' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':prf_id', $prf_id, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$this->rollBack();
 
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		/*
		** Supprime la relation possible entre les Profils et les Groupes de Secrets.
		*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM prsg_profiles_secrets_groups ' .
		 'WHERE prf_id = :prf_id' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':prf_id', $prf_id, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$this->rollBack();
 
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		$this->commit();
 
 		return true;
	}


	public function addMenu( $Id_Profil, $Id_Menu ) {
	/**
	* Ajouter un Menu à un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $Id_Profil Identifiant du Profil à associer
	* @param[in] $Id_Menu Identifiant du Menu à associer au Profil
	*
	* @return Renvoi vrai si l'association du Menu au Profil a été créée, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'INSERT ' .
		 'INTO prap_profiles_menus ' .
		 '( prf_id, mns_id ) ' .
		 'VALUES ( :Prf_id, :Mns_id )' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Prf_id', $Id_Profil, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Mns_id', $Id_Menu, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function deleteMenu( $Id_Profil, $Id_Menu ) {
	/**
	* Supprimer un Menu à un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $Id_Profil Identifiant du Profil à dissocier
	* @param[in] $Id_Menu Identifiant du Menu à dissocier du Profil
	*
	* @return Renvoi vrai si l'association du Menu au Profil a été supprimée, sinon lève une exception	
	*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM prap_profiles_menus ' .
		 'WHERE prf_id = :Prf_id AND mns_id = :Mns_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Prf_id', $Id_Profil, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Mns_id', $Id_Menu, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function listMenus( $Id_Profil ) {
	/**
	* Liste les Menus d'un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $Id_Profil Identifiant du Profil à recherher
	*
	* @return Renvoi un tableau d'occurrences de Menu, sinon un tableau vide
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 't2.mns_id, t2.mns_menu_name ' .
		 'FROM prap_profiles_menus AS t1 ' .
		 'LEFT JOIN mns_menus AS t2 ON t1.mns_id = t2.mns_id ' .
		 'WHERE mns_id = :Mns_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Mns_id', $Id_Profil, PDO::PARAM_INT ) ;
		
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


	public function addApplication( $Id_Profil, $Id_Application, $Id_Right ) {
	/**
	* Ajoute une Application à un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $Id_Profil Identifiant du Profil à associer
	* @param[in] $Id_Application Identifiant de l'Application à associer
	* @param[in] $Id_Right Identifiant du Droit sur cette association
	*
	* @return Renvoi vrai si l'association entre le Profil et l'Application a été créée, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'INSERT ' .
		 'INTO prap_profiles_access_control ' .
		 '( prf_id, app_id, rgh_id ) ' .
		 'VALUES ( :Prf_id, :App_id, :Rgh_id )' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Prf_id', $Id_Profil, PDO::PARAM_INT ) ;
		$Result->bindParam( ':App_id', $Id_Application, PDO::PARAM_INT ) ;
		$Result->bindParam( ':Rgh_id', $Id_Right, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function deleteApplication( $Id_Profil, $Id_Application ) {
	/**
	* Supprime une Application à un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $Id_Profil Identifiant du Profil à dissocier
	* @param[in] $Id_Application Identifiant de l'Application à dissocier
	*
	* @return Renvoi vrai si l'association entre le Profil et l'Application a été supprimée, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM prap_profiles_access_control ' .
		 'WHERE prf_id = :Prf_id AND app_id = :App_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Prf_id', $Id_Profil, PDO::PARAM_INT ) ;
		$Result->bindParam( ':App_id', $Id_Application, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function listApplications( $Id_Profil ) {
	/**
	* Liste les Applications d'un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $Id_Profil Identifiant du Profil de référence
	*
	* @return Renvoi un tableau d'occurrences d'Applications associées au Profil, sinon retourne un tableau vide
	*/
		if ( ! $Result = $this->prepare( 'SELECT ' .
		 't1.app_id, t1.rgh_id, t2.app_code, t2.app_label ' .
		 'FROM prap_profiles_access_control AS t1 ' .
		 'LEFT JOIN app_applications AS t2 ON t1.app_id = t2.app_id ' .
		 'WHERE prf_id = :Prf_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':Prf_id', $Id_Profil, PDO::PARAM_INT ) ;
		
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
	*/
	public function isAssociated( $prf_id, $Table ) {
	/**
	* Vérifie si le Profil est associé.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $prf_id Identifiant du Profil à contrôler
	* @param[in] $Table Table d'association à contrôler
	*
	* @return Renvoi vrai si le Profil est associé par l'une des tables spécifiés
	*/
		switch( strtoupper( $Table ) ) {
		 case 'PRAP':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM prap_profiles_menus ' .
			 'WHERE prf_id = :prf_id ' ;
			break;

		 case 'IDPR':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM idpr_identities_profiles ' .
			 'WHERE prf_id = :prf_id ' ;
			break;

		 case 'PRAC':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM prac_profiles_access_control ' .
			 'WHERE prf_id = :prf_id ' ;
			break;
		}

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
		
		if ( ! $Valeur = $Result->fetch() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $Valeur[ 0 ] == 0 ) return false ;
		
		return true ;
	}


	public function listGroups( $prf_id ) {
	/**
	* Lister les Groupes.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $prf_id Identifiant du Profil de référence
	*
	* @return Renvoi un tableau d'occurrences de Groupes de Secrets associés au Profil, sinon retourne un tableau vide
	*/
		$Request = 'SELECT DISTINCT ' .
		 'T1.rgh_id, T1.prf_id, T1.sgr_id, T2.sgr_label ' .
		 'FROM prf_profiles AS T3 ' .
		 'LEFT JOIN prsg_profiles_secrets_groups AS T1 ON T1.prf_id = T3.prf_id ' .
		 'LEFT JOIN sgr_secrets_groups AS T2 ON T1.sgr_id = T2.sgr_id ' .
		 'WHERE T1.prf_id = :prf_id ';
		

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
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function total() {
	/**
	* Calcul le nombre total de Profils.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @return Renvoi un entier représentant le total de Profils trouvé en base
	*/
		$Request = 'SELECT ' .
		 'count(*) AS total ' .
		 'FROM prf_profiles ';

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject() ;
		
		return $Occurrence->total;
	}

} // Fin class IICA_Profiles

?>