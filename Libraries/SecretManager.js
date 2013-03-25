function checkPassword( Password_Field, Result_Field, Complexity, Size)
{
	var Ok_Size = 0;
	var Result = '';

    var pwd = document.getElementById( Password_Field ).value;
    
    if ( Complexity < 1 || Complexity > 3 ) Complexity = 3;
    
    if ( pwd.length < Size ) {
    	Result += 'No good size (min ' + Size + '). ';
    }

	switch( Complexity ) {
	 case 1:
		var regex_lcase = new RegExp('[a-z]', 'g');
		var regex_ucase = new RegExp('[A-Z]', 'g');

		if ( ! pwd.match( regex_lcase ) ) Result += 'Use lowercase. ';
		if ( ! pwd.match( regex_ucase ) ) Result += 'Use uppercase. ';
		break;

	 case 2:
		var regex_lcase = new RegExp('[a-z]', 'g');
		var regex_ucase = new RegExp('[A-Z]', 'g');
		var regex_num = new RegExp('[0-9]', 'g');

		if ( ! pwd.match( regex_lcase ) ) Result += 'Use lowercase. ';
		if ( ! pwd.match( regex_ucase ) ) Result += 'Use uppercase. ';
		if ( ! pwd.match( regex_num ) ) Result += 'Use number. ';
		break;

	 case 3:
		var regex_lcase = new RegExp('[a-z]', 'g');
		var regex_ucase = new RegExp('[A-Z]', 'g');
		var regex_num = new RegExp('[0-9]', 'g');

		var regex_sc = new RegExp('[^\\w]', 'g');

		if ( ! pwd.match( regex_lcase ) ) Result += 'Use lowercase. ';
		if ( ! pwd.match( regex_ucase ) ) Result += 'Use uppercase. ';
		if ( ! pwd.match( regex_num ) ) Result += 'Use number. ';
		if ( ! pwd.match( regex_sc ) ) Result += 'Use special chars. ';
		break;
	}
	
    element = document.getElementById( Result_Field );
    element.innerHTML = Result;
}

function generatePassword( Password_Field, Complexity, Size ){
	Size	= parseInt( Size );
	
	if ( ! Size )
		Size = 8;
	
	if ( ! Complexity )
		Complexity = 3;

	var Password = "";
	var Numbers  = "0123456789";
	var Accentuations = 'àçèéêëîïôöùûüÿ';
	var Special_Chars = "&~\"#'{([-|_\\@)]=}+£$¤µ*%<>?,.;/:§!";
	var Chars    = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var CharsN   = Chars.length;
	var NextChar;
	var Attempt  = 0;
	
	switch( Complexity ) {
	 case 2:
	 	Chars += Numbers;
		break;

	 default:
	 case 3:
	 	Chars += Numbers + Special_Chars;
		break;

	 case 4:
	 	Chars += Numbers + Special_Chars + Accentuations;
		break;
	}
	
	var regex_lower = new RegExp('[a-z]', 'g');
	var regex_upper = new RegExp('[A-Z]', 'g');
	var regex_num = new RegExp('[0-9]', 'g');
	var regex_sc = new RegExp('[^\\w]', 'g');
 
	while( Attempt < 50 ) {
		for( i = 0; i < Size; i++ ){
			NextChar = Chars.charAt( Math.floor( Math.random() * CharsN ) );
			Password += NextChar;
		}

		if ( Password.match( regex_lower ) != null
		 && Password.match( regex_upper ) != null
		 && Password.match( regex_num ) != null
		 && Password.match( regex_sc ) != null ) break;
		else Password = "";
		
		Attempt++;
	}
	
	window.alert( Password );
	
    element = document.getElementById( Password_Field );
    element.value = Password;
}

