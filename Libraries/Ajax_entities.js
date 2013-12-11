$(document).keyup(function(e){
    if(e.which == 27) { // || e.which == 13){
        endAllModifyEntity();
        
        hideConfirmMessage();
    }
});

$(document).ready(function(){
    // Masque la modale quand on clique un objet de class "close"
     $(".close").on('click', function() {
        $('#addEntity').hide();
    });

    // Change couleur si vide ou plein des champs obligatoires
    $('input.obligatoire').focusout(function(){
        if ($(this).val() != '') {
            $(this).css("border", "1px solid #cdcdcd");
        } else {
            $(this).css("border", "2px solid #00608d");
        }
    });

});


// Création d'une Entité à la volée.
function putAddEntity(Title,L_Code,L_Label,L_Cancel,L_Create){
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h4 id="myModalLabel">'+Title+'</h4>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
	 "       <p><span class=\"td-aere align-right\" style=\"width:150px;\">" + L_Code + "</span>"+
	 "<span  class=\"td-aere\">"+
	 "<input id=\"iEntityCode\" type=\"text\" class=\"obligatoire input-large\" " +
	 "maxlength=\"10\" /></span></p>\n" +
	 "       <p><span class=\"td-aere align-right\" style=\"width:150px;\">" + L_Label + "</span>" +
	 "<span  class=\"td-aere\"><input id=\"iEntityLabel\" type=\"text\" class=\"obligatoire input-xlarge\" " +
	 "maxlength=\"60\"/></span></p>\n" +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">'+L_Cancel+
     '</a>&nbsp;<a class="button" href="javascript:addEntity();">'+
     L_Create+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    // Met le focus sur le 1er champ du calque.
    $('#iEntityCode').focus();

    $('#iEntityLabel').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iEntityLabel').val() != '' || $('#iEntityCode').val() != '' ) addEntity();
        }
    });

    $('#iEntityCode').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iEntityLabel').val() != '' || $('#iEntityCode').val() != '' ) addEntity();
        }
    });
}


function addEntity(){
    var Code = $('#iEntityCode').val().toUpperCase();
    var Label = $('#iEntityLabel').val();

    if ( Code != '' && Label != '') {        
        $.ajax({
            url: 'SM-users.php?action=ENT_CX',
            type: 'POST',
            data: $.param({'Code': Code, 'Label': Label}),
            dataType: 'json',
            success: function(reponse) {
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                var statut = resultat['Status'];

                showInfoMessage( resultat['Status'], resultat['Message'] ); // SecretManager.js

                if (statut == 'success') {
                    var Id = resultat['IdEntity'];
                    var Script = resultat['Script'];
                    var URL_PICTURES = resultat['URL_PICTURES'];
                    var L_Modify = resultat['L_Modify'];
                    var L_Delete = resultat['L_Delete'];
                    var L_Warning = resultat['L_Warning'];
                    var L_Cancel = resultat['L_Cancel'];
                    var L_Confirm_Delete_Entity = resultat['L_Confirm_Delete_Entity'];
                    var L_Confirm = resultat['L_Confirm'];

                    hideConfirmMessage();

                    if ( $('#dashboard').length == 0 ) {
                        $('#iSelectEntity option').attr('selected','off')

                        $('#iSelectEntity').prepend('<option value="'+Id+'" selected>'+Code+' - '+Label+'</option>');
                    } else {
                        $('#listeSecrets').prepend(
                         '<tr id="entity_' + Id + '" class="surline">'+
                         '<td id="code-' + Id + '">'+Code+'</td>'+
                         '<td id="label-' + Id + '">'+Label+'</td>'+
                         '<td>'+
                         '<a class="simple" href="javascript:modifyEntity(\''+Id+'\',\''+L_Cancel+'\',\''+L_Modify+'\');">'+
                         '<img class="no-border" src="'+URL_PICTURES+'/b_edit.png" alt="'+L_Modify+'" title="'+L_Modify+'" />'+
                         '</a> '+
                         '<a class="simple" href="javascript:confirmDeleteEntity(\'' +
			             Id + "','" + L_Warning + "','" + L_Confirm_Delete_Entity + "','" + L_Cancel +
			             "','" + L_Confirm + '\');">'+
                         '<img class="no-border" src="'+URL_PICTURES+'/b_drop.png" alt="'+L_Delete+'" title="'+L_Delete+'" />'+
                         '</a>'+
                         '</td>'+
                         '</tr>'
                        );

                        var Total = $('#total').text();
                        Total = Number(Total) + 1;
                        $('#total').text( Total );
                    }
                }
            },
            error: function(reponse) {
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                alert('Erreur serveur : ' + resultat['responseText']);
            }
        });
    }
}


// Création d'une Entité à la volée.
function confirmDeleteEntity(Id,Title,Message,L_Cancel,L_Confirm){
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h4 id="myModalLabel">'+Title+'</h4>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
	 "<p>" + Message + "<b>" + $('#code-'+Id).text() + " - " + $('#label-'+Id).text() +  "</b></p>\n" +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">'+L_Cancel+
     '</a>&nbsp;<a class="button" href="javascript:deleteEntity('+Id+');">'+
     L_Confirm+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    // Met le focus sur le 1er champ du calque.
    $('#iCancel').focus();
}


// Gestion des suppressions d'Entité à la volée.
function deleteEntity( Id ){
    $.ajax({
        url: 'SM-users.php?action=ENT_DX',
        type: 'POST',
        data: $.param({'ent_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            var resultat = new Array();

            $.each(reponse, function(attribut, valeur) {
                resultat[attribut]=valeur;
            });

            var statut = resultat['Status'];

            showInfoMessage( resultat['Status'], resultat['Message'] ); // SecretManager.js
            
            if (statut == 'success') {
                $('#entity_'+Id).remove();
                hideConfirmMessage();

                var Total = $('#total').text();
                Total = Number(Total) - 1;
                $('#total').text( Total );
            }
        },
        error: function(reponse) {
            var resultat = new Array();

            $.each(reponse, function(attribut, valeur) {
                resultat[attribut]=valeur;
            });

            alert('Erreur sur serveur : ' + resultat['responseText']);
        }
    });
}


// Gestion des modifications "en place".
function modifyEntity( Id, CancelButton, ModifyButton ) {
    endAllModifyEntity();

    $('#entity_'+Id).hide();
    
    var Code = $('#code-'+Id).text();
    var Label = $('#label-'+Id).text();

	$( "       <tr id=\"MODI_" + Id + "\" class=\"pair\">\n" +
		"        <td class=\"align-middle blue-border-line\"><input id=\"iCode\" value=\"" +
		Code + "\" class=\"input-xlarge\" /></td>\n" +
		"        <td class=\"align-middle blue-border-line\"><input id=\"iLabel\" value=\"" +
		Label + "\" class=\"input-xxlarge\" /></td>\n" +
		"        <td class=\"align-middle blue-border-line\"><a class=\"button\" href=\"javascript:endModifyEntity('" +
		Id + "');\">" + CancelButton + "</a>&nbsp;<a class=\"button\" href=\"javascript:saveModifyEntity('" +
		Id + "');\">" + ModifyButton + "</a></td>\n" +
		"       </tr>\n" ).insertAfter( '#entity_'+Id );
    
    document.getElementById('iCode').focus();
    document.getElementById('iCode').selectionStart = Label.length;

    $('#iLabel').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iLabel').val() != '' || $('#iCode').val() != '' ) saveModifyEntity( Id );
        }

        if (e.which == 27) {
            endModifyEntity( Id );
        }
    });

    $('#iCode').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iLabel').val() != '' || $('#iCode').val() != '' ) saveModifyEntity( Id );
        }

        if (e.which == 27) {
            endModifyEntity( Id );
        }
    });
}

function saveModifyEntity( Id ) {
    var Code = $('#iCode').val().toUpperCase();
    var Label = $('#iLabel').val();

    $.ajax({
        url: 'SM-users.php?action=ENT_MX',
        type: 'POST',
        data: $.param({'ent_id': Id, 'Code': Code, 'Label': Label}),
        dataType: 'json',
        success: function(reponse) {
            var resultat = new Array();

            $.each(reponse, function(attribut, valeur) {
                resultat[attribut]=valeur;
            });

            var statut = resultat['Status'];

            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

            if (statut == 'success') {
                $('#code-'+Id).text(Code);
                $('#label-'+Id).text(Label);
                
                $('#MODI_'+Id).remove();
                $('#entity_'+Id).show();
            }
        },
        error: function(reponse) {
            var resultat = new Array();

            $.each(reponse, function(attribut, valeur) {
                resultat[attribut]=valeur;
            });

            alert('Erreur sur serveur : ' + resultat['responseText']);
        }
    });
}


// Annule la modification en cours.
function endModifyEntity(Id) {
    $('#MODI_'+Id).remove();
    $('#entity_'+Id).show();
}


// Annule toutes les modifications en cours.
function endAllModifyEntity() {
    $('tr[id^="MODI_"]').each( function(index) {
        var T_ID = $(this).attr( 'id' );
        T_ID = T_ID.split('_');
        endModifyEntity(T_ID[1]);
    } );
}


function hideConfirmMessage() {
    $('#confirm_message').remove();
}


function confirmDeleteProfile( Id, Message, Warning, Cancel, Confirm) {
    var Label = $('#label_'+Id).text();
    
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">'+Warning+'</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
     '<p>' + Message + '<b>' + Label + '</b></p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">'+Cancel+
     '</a>&nbsp;<a class="button" href="javascript:deleteProfile('+Id+');">'+
     Confirm+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );
    
    document.getElementById('iCancel').focus();

}
