// =========================================================================
// Gestion de l'affichage et de la suppression de la fenêtre d'information.

var myInterval;

function hideInfoMessage() {
    $('#info_message').remove();
    clearInterval( myInterval );
}


function showInfoMessage( Type, Message ) {
    // Type = success, error, info
    if ( Type == 'success' ) {
        var Image_Name = 's_success';
        myInterval = setInterval( function() { hideInfoMessage() }, 3000);
    } else if ( Type == 'error' ) {
        var Image_Name = 'minus';
    } else  if ( Type == 'warning' || Type == 'alert' ) {
        Type = 'alert';
        var Image_Name = 's_warn';
    } else {
        Type = 'info';
        var Image_Name = 's_warn';
        myInterval = setInterval( function() { hideInfoMessage() }, 9000);
    }

    $('#info_message').remove();
    
    $('     <div id="info_message" class="' + Type + '" onClick="hideInfoMessage();">\n' +
     '    <button type="button" class="close" data-dismiss="alert">&times;</button>\n' +
	 '    <img class="no-border" src="../Pictures/' + Image_Name + '.png" alt="' +
	 Type + '" />&nbsp;' + Message + '<br/><br/>' +
	 '     </div>\n' ).prependTo( 'body' );
}

