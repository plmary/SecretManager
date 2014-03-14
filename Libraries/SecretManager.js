// =========================================================================
// Gestion de l'affichage et de la suppression de la fen�tre d'information.

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


var myVar=setInterval(function(){controlValiditeSession()},1000 * 60); // D�clenche la fonction toutes les 60 secondes.

function controlValiditeSession() {
    $.ajax({
        url: '../../SM-Login.php?action=CTRL_SESSION',
        type: 'POST',
        //data: $.param({'libelle': $('#inputlibelle').val()}), // les param�tres sont prot�g�s avant envoi
        dataType: 'json', // le r�sultat est transmit dans un objet JSON
        success: function(reponse) { // Le serveur n'a pas rencontr� de probl�me lors de l'�change ou de l'ex�cution.
            if ( reponse['status'] == 'OK' ) { // La session n'a pas expir�.
                $('#session_timer').text( reponse['session_timer'] );
                return;
            } else { // La session a expir�.
                window.location = '../../SM-Login.php?action=DCNX&expired';
            }
        },
        error: function(reponse) {
            alert(reponse['responseText']);
        }
    });
}


function initSession() {
    $.ajax({
        url: '../../SM-Login.php?action=INIT_SESSION',
        type: 'POST',
        //data: $.param({'libelle': $('#inputlibelle').val()}), // les param�tres sont prot�g�s avant envoi
        dataType: 'json', // le r�sultat est transmit dans un objet JSON
        success: function(reponse) { // Le serveur n'a pas rencontr� de probl�me lors de l'�change ou de l'ex�cution.
            if ( reponse['status'] == 'OK' ) { // La session n'a pas expir�.
                $('#session_timer').text( reponse['session_timer'] );
                return;
            }
        },
        error: function(reponse) {
            alert(reponse['responseText']);
        }
    });
}
