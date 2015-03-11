<?php

class Session_Parser {
/**
* Cette classe g�re la lecture des fichiers de "session".
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-06-26
*/

	public $Session_Path;


	public function __construct( $_session_path = '' ) {
	/**
	* Constructeur de la classe :
	* D�finition du "chemin" d'acc�s aux fichiers de "session".
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-27
	*
	* @param[in] $_session_path D�finit le chemin o� sont stock�s les fichiers de "session"
	*
	* @return Renvoi un bool�en de succ�s
	*/
		if ( $_session_path == '' ) {
			$this->Session_Path = session_save_path();
		} else {
			$this->Session_Path = $_session_path;
		}

		if ( $this->Session_Path == '' ) return false;
		else return true;
	}


	public function set_session_path( $_session_path = '' ) {
	/**
	* Modification du "chemin" d'acc�s aux fichiers de "session".
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-27
	*
	* @param[in] $_session_path D�finit le chemin o� sont stock�s les fichiers de "session"
	*
	* @return Renvoi un bool�en : vrai si chemin modifi� et faux si chemin non modifi�.
	*/
		if ( $_session_path == '' ) {
			return false;
		} else {
			$this->Session_Path = $_session_path;
		}

		return true;
	}


	public function string_var_session( $Flow, $Offset, $Max_Offset = 1000 ) {
	/**
	* R�cup�re la cha�ne qui vient d'�tre identifi�e par le "parseur" de session.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-27
	*
	* @param[in] $Flow Flux repr�sentant une "session"
	* @param[in] $Offset Position courante dans le flux
	* @param[in] $Max_Offset D�finit une taille limite pour l'Offset.
	*
	* @return Renvoi un tableau
	*  1er �l�ment du tableau = taille de la cha�ne trouv�e,
	*  2�me �l�m�nt du tableau = valeur de la cha�ne,
	*  3�me �l�ment du tableau = nouvelle position courante.
	*/
		$Step = 1;
	
		$Size = '';
		$Value = '';
	
		for( ; $Offset < $Max_Offset; $Offset++ ) {
			switch( $Step ) {
			 case '1': // R�cup�re la taille de la chaine.
				if ( $Flow[ $Offset ] == ':' ) {
					$Step = 2;
					$Offset += 1;
					continue;
				}
				
				$Size .= $Flow[ $Offset ];

				break;

			 case '2': // R�cup�re la chaine associ�e � la variable.
				for( $ii = 0; $ii < $Size; $ii++, $Offset++ ) {
					$Value .= $Flow[ $Offset ];
				}
				
				if ( $Flow[ $Offset ] == '"' ) {
					$Offset += 1;
					break 2;
				} else {
					print( "Bad end string\n" );
					exit(1);
				}

				break;
			}
		}
		
		return array( $Size, $Value, $Offset );
	}


	public function numeric_var_session( $Flow, $Offset, $Max_Offset = 1000 ) {
	/**
	* R�cup�re la cha�ne num�rique qui vient d'�tre identifi�e par le "parseur" de session.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-27
	*
	* @param[in] $Flow Flux repr�sentant une "session"
	* @param[in] $Offset Position courante dans le flux
	* @param[in] $Max_Offset D�finit une taille limite pour l'Offset.
	*
	* @return Renvoi un tableau
	*  1er �l�ment du tableau = taille du num�rique trouv�e,
	*  2�me �l�m�nt du tableau = valeur du num�rique,
	*  3�me �l�ment du tableau = nouvelle position courante.
	*/
		$Value = '';
	
		for( ; $Offset < $Max_Offset; $Offset++ ) {
			if ( $Flow[ $Offset ] == ';' ) {
				break;
			}

			$Value .= $Flow[ $Offset ];
		}
		
		return array( strlen( $Value), $Value, $Offset );
	}


	public function array_var_session( $Flow, $Offset, $Max_Offset = 1000 ) {
	/**
	* R�cup�re le tableau qui vient d'�tre identifi�e par le "parseur" de session.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-27
	*
	* @param[in] $Flow Flux repr�sentant une "session"
	* @param[in] $Offset Position courante dans le flux
	* @param[in] $Max_Offset D�finit une taille limite pour l'Offset.
	*
	* @return Renvoi un tableau
	*  1er �l�ment du tableau = nombre d'�l�ments dans le tableau,
	*  2�me �l�m�nt du tableau = valeur du tableau,
	*  3�me �l�ment du tableau = nouvelle position courante.
	*/
		$Step = 1;
	
		$Associate = 0;
		$Size = '';
		$Key = '';
		$Value = '';
		$Buffer = '';
		$Elements = array();
	
		for( ; $Offset < $Max_Offset; $Offset++ ) {
		 	switch( $Flow[ $Offset ] ) {
		 	 case '}':
				break 2;

		 	 case 'i':
		 		$Offset += 2;
		 		for( ; $Offset < $Max_Offset; $Offset++ ) {
		 			if ( $Flow[ $Offset ] == ';' ) {
		 				if ( $Associate == 0 ) {
		 					$Key = $Value;
		 					$Associate = 1;
		 				} else {
							$Elements[ $Key ] = $Value;
				 			$Key = '';
		 					$Associate = 0;
				 		}
			 			$Value = '';
		 				break;
		 			}
			 			
					$Value .= $Flow[ $Offset ];
				}
			 	 
				break;

		 	 case 's':
		 		$Offset += 2;
		 	 	$String_Size = '';
			 	 	
		 		for( ; $Offset < $Max_Offset; $Offset++ ) {
		 			if ( $Flow[ $Offset ] == ':' ) {
		 				$Offset += 1;
		 				break;
		 			}
			 			
					$String_Size .= $Flow[ $Offset ];
				}
			 	 
	 			if ( $Flow[ $Offset ] == '"' ) {
	 				$Offset += 1;
	 			}

		 		for( $ii = 0; $ii < $String_Size; $ii++, $Offset++ ) {
					$Value .= $Flow[ $Offset ];
				}
				
				if ( $Flow[ $Offset ] == '"' ) {
	 				if ( $Associate == 0 ) {
	 					$Key = $Value;
	 					$Associate = 1;
	 				} else {
						$Elements[ $Key ] = $Value;
			 			$Key = '';
	 					$Associate = 0;
			 		}
		 			$Value = '';
					$Offset += 1;
					break;
				} else {
					print( "Bad end string\n" );
					exit(1);
				}

				break;
				
		 	 case 'a':
		 		$Offset += 2;
		 	 	$Array_Size = '';
			 	 	
		 		for( ; $Offset < $Max_Offset; $Offset++ ) {
		 			if ( $Flow[ $Offset ] == ':' ) {
		 				$Offset += 1;
		 				break;
		 			}
			 			
					$Array_Size .= $Flow[ $Offset ];
				}
			 	 
	 			if ( $Flow[ $Offset ] == '{' ) {
	 				$Offset += 1;
	 			}

				list( $Size, $Value, $Offset ) = $this->array_var_session(
				 $Flow, $Offset );

				$Elements[ $Key ] = $Value;
				
	 			$Key = '';
				$Associate = 0;

				break;
			}
		}
		
		return array( count( $Elements ), $Elements, $Offset );
	}


	public function parseSession( $ID_Session ) {
	/**
	* R�cup�re le tableau qui vient d'�tre identifi�e par le "parseur" de session.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-27
	*
	* @param[in] $ID_Session Identifiant de la session � analyser.
	*
	* @return Renvoi un tableau
	*  Chaque entr�e du tableau correspond � un nom et � une valeur de variable pr�c�demment stock�e dans la session.
	*/
		if ( $ID_Session == '' ) return FALSE;
		
		$Filename = realpath( $this->Session_Path . '/sess_' . $ID_Session );

		if ( is_readable ( $Filename ) ) {
			// Cr�ation d'un nom de fichier unique (pour fichier temporaire).
			$Filename1 = tempnam( sys_get_temp_dir(), 'SM_' );
			
			// Duplique le fichier de Session (pour pouvoir lire la copie).
			if ( ! copy( $Filename, $Filename1 ) ) {
				return FALSE;
			}
			
			// Lecture de la copie.
			$Records = file( $Filename1, FILE_IGNORE_NEW_LINES );
			if ( $Records === FALSE ) {
				return FALSE;
			}
			
			// Suppression de la copie.
			unlink( $Filename1 );

			$Step = 0;
		
			$Max_Offset = strlen( $Records[0] );
		
			for( $Offset = 0; $Offset < $Max_Offset; $Offset++ ) {

				switch( $Step ) {
				 case '0': // R�cup�re le nom de la variable.
					$Entry = '';
					$Type = '';
					$Size = '';
					$Value = '';

					$Step = 1;

				 case '1': // R�cup�re le nom de la variable.
					if ( $Records[0][ $Offset ] == '|' ) {
						$Step = 2;
						continue;
					}
				
					$Entry .= $Records[0][ $Offset ];
				
					break;

				 case '2': // R�cup�re le type de variable.
					$Type = $Records[0][ $Offset ];
					$Offset += 2;
				
					switch( $Type ) {
					 case 's': // Cha�ne
						list( $Size, $Value, $Offset ) = $this->string_var_session(
						 $Records[0], $Offset, $Max_Offset );

						$Elements[ $Entry ] = $Value;
					
						$Step = 0;
					
						break;

					 case 'i': // Entier
					 case 'd': // Double
					 case 'r': // R��l
					 case 'b': // Bool�en
						list( $Size, $Value, $Offset ) = $this->numeric_var_session(
						 $Records[0], $Offset, $Max_Offset );
						
						if ( $Type == 'i' ) {
							$OriginType = 'integer';
						} elseif ( $Type == 'r' ) {
							$OriginType = 'float';
						} elseif ( $Type == 'b' ) {
							$OriginType = 'boolean';
						} elseif ( $Type == 'd' ) {
							$OriginType = 'double';
						}

						settype( $Value, $OriginType );

						$Elements[ $Entry ] = $Value;
					
						$Step = 0;

						break;

					 case 'a': // Tableau
						list( $Size, $Value, $Offset ) = $this->array_var_session(
						 $Records[0], $Offset, $Max_Offset );

						$Elements[ $Entry ] = $Value;
					
						$Step = 0;

						break;
					}

					break;
				}
			}
		} else {
			return false;
		}

		return $Elements;
	}
} // Fin "class"

?>