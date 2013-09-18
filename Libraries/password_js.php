<?php

	$innerJS = 
	 "function checkPassword(Password_Field, Result_Field, Complexity, Size) {\n" .
	 " var Ok_Size = 0;\n" .
	 " var Result = '';\n" .
	 " var pwd = document.getElementById(Password_Field).value;\n" .
	 " if ( Complexity < 1 || Complexity > 3 ) Complexity = 3;\n" .
	 " if ( pwd.length < Size ) {\n" .
	 "  Result += '" . $L_No_Good_Size . " ' + Size + '). ';\n" .
	 "  document.getElementById(Result_Field).title = Result;\n" .
	 " }\n" .
	 " switch( Complexity ) {\n" .
	 "  case 1:\n" .
	 "   var regex_lcase = new RegExp('[a-z]', 'g');\n" .
	 "   var regex_ucase = new RegExp('[A-Z]', 'g');\n" .
	 "   if ( ! pwd.match( regex_lcase ) ) {\n" .
	 "    Result += '" . $L_Use_Lowercase . ". ';\n" .
	 "    document.getElementById(Result_Field).title = Result;\n" .
	 "   }\n" .
	 "   if ( ! pwd.match( regex_ucase ) ) {\n" .
	 "    Result += '" . $L_Use_Uppercase . ". ';\n" .
	 "    document.getElementById(Result_Field).title = Result;\n" .
	 "   }\n" .
	 "   break;\n" .
	 "  case 2:\n" .
	 "   var regex_lcase = new RegExp('[a-z]', 'g');\n" .
	 "   var regex_ucase = new RegExp('[A-Z]', 'g');\n" .
	 "   var regex_num = new RegExp('[0-9]', 'g');\n" .
	 "   if ( ! pwd.match( regex_lcase ) ) {\n" .
	 "    Result += '" . $L_Use_Lowercase . ". ';\n" .
	 "    document.getElementById(Result_Field).title = Result;\n" .
	 "   }\n" .
	 "   if ( ! pwd.match( regex_ucase ) ) {\n" .
	 "    Result += '" . $L_Use_Uppercase . ". ';\n" .
	 "    document.getElementById(Result_Field).title = Result;\n" .
	 "   }\n" .
	 "   if ( ! pwd.match( regex_num ) ) {\n" .
	 "    Result += '" . $L_Use_Number . ". ';\n" .
	 "    document.getElementById(Result_Field).title = Result;\n" .
	 "   }\n" .
	 "   break;\n" .
	 "  case 3:\n" .
	 "   var regex_lcase = new RegExp('[a-z]', 'g');\n" .
	 "   var regex_ucase = new RegExp('[A-Z]', 'g');\n" .
	 "   var regex_num = new RegExp('[0-9]', 'g');\n" .
	 "   var regex_sc = new RegExp('[^\\\\w]', 'g');\n" .
	 "   if ( ! pwd.match( regex_lcase ) ) {\n" .
	 "    Result += '" . $L_Use_Lowercase . ". ';\n" .
	 "    document.getElementById(Result_Field).title = Result;\n" .
	 "   }\n" .
	 "   if ( ! pwd.match( regex_ucase ) ) {\n" .
	 "    Result += '" . $L_Use_Uppercase . ". ';\n" .
	 "    document.getElementById(Result_Field).title = Result;\n" .
	 "   }\n" .
	 "	 if ( ! pwd.match( regex_num ) ) {\n" .
	 "    Result += '" . $L_Use_Number . ". ';\n" .
	 "    document.getElementById(Result_Field).title = Result;\n" .
	 "   }\n" .
	 "   if ( ! pwd.match( regex_sc ) ) {\n" .
	 "    Result += '" . $L_Use_Special_Chars . ". ';\n" .
	 "    document.getElementById(Result_Field).title = Result;\n" .
	 "   }\n" .
	 "   break;\n" .
	 "  }\n" .
//		 "  element = document.getElementById(Result_Field);\n" .
//		 "  element.innerHTML = Result;\n" . 
	 "  if ( Result != '' && pwd != '' ) {\n" .
	 "   document.getElementById(Result_Field).alt = 'Ko';\n" .
	 "   document.getElementById(Result_Field).src = '" . URL_PICTURES . "/s_attention.png'\n" .
	 "  }\n" .
	 "  if ( Result == '' && pwd != '' ) {\n" .
	 "   document.getElementById(Result_Field).alt = 'Ok';\n" .
	 "   document.getElementById(Result_Field).title = 'Ok';\n" .
	 "   document.getElementById(Result_Field).src = '" . URL_PICTURES . "/s_okay.png'\n" .
	 "  }\n" .
	 "}\n" .
	 "function generatePassword( Password_Field, Complexity, Size ){\n" .
	 "	Size	= parseInt( Size );\n" .
	 "	if ( ! Size )\n" .
	 "		Size = 8;\n" .
	 "	if ( ! Complexity )\n" .
	 "		Complexity = 3;\n" .
	 "	var Password = '';\n" .
	 "	var Numbers  = '0123456789';\n" .
	 "	var Accentuations = 'àçèéêëîïôöùûüÿ';\n" .
	 "	var Special_Chars = '&~\"#\'{([-|_\\@)]=}+£\$€µ*%<>?,.;/:§!';\n" .
	 "	var Chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';\n" .
	 "	var NextChar;\n" .
	 "	var Attempt  = 0;\n" .
	 "	switch( Complexity ) {\n" .
	 "	 case 2:\n" .
	 "	 	Chars += Numbers;\n" .
	 "		break;\n" .
	 "	 default:\n" .
	 "	 case 3:\n" .
	 "	 	Chars += Numbers + Special_Chars;\n" .
	 "		break;\n" .
	 "	 case 4:\n" .
	 "	 	Chars += Numbers + Special_Chars + Accentuations;\n" .
	 "		break;\n" .
	 "	}\n" .
	 "	var CharsN   = Chars.length;\n" .
	 "	var regex_lower = new RegExp('[a-z]', 'g');\n" .
	 "	var regex_upper = new RegExp('[A-Z]', 'g');\n" .
	 "	var regex_num = new RegExp('[0-9]', 'g');\n" .
	 "	var regex_sc = new RegExp('[^\\w]', 'g');\n" .
	 "	while( Attempt < 50 ) {\n" .
	 "		for( i = 0; i < Size; i++ ){\n" .
	 "			NextChar = Chars.charAt( Math.floor( Math.random() * CharsN ) );\n" .
	 "			Password += NextChar;\n" .
	 "		}\n" .
	 "		if ( Password.match( regex_lower ) != null\n" .
	 "		 && Password.match( regex_upper ) != null\n" .
	 "		 && Password.match( regex_num ) != null\n" .
	 "		 && Password.match( regex_sc ) != null ) break;\n" .
	 "		else Password = '';\n" .
	 "		Attempt++;\n" .
	 "	}\n" .
	 "	element = document.getElementById( Password_Field );\n" .
//		 "	element.innerHTML = Password;\n" .
	 "	element.value = Password;\n" .
	 "}\n";

?>