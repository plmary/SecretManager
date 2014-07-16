/**
* Ce script gère une partie des fonctions Ajax disponible pour le script "SM-users.php.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-06-19
*/


// Active les fonctions ci-dessous quand le DOM de la page HTML est fini de charger.
$(document).ready(function(){
    // Supervise l'utilisation des touches du clavier dans tout le document.
    $(document).keyup(function(e){
        // Gestion de la touche "Echap" pour annuler les modifications et supprimer la fenêtre de confirmation.
        if(e.which == 27) {
            endAllModifyEntity();
            
            hideConfirmMessage();
        }
    });

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


// Affiche la boîte de dialogue de création d'une Entité à la volée.
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

    $('#iEntityLabel, #iEntityCode').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iEntityLabel').val() != '' || $('#iEntityCode').val() != '' ) addEntity();
        }
    });
}


// Création d'une Entité à la volée.
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
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

                if (reponse['Status'] == 'success') {
                    var Id = reponse['IdEntity'];
                    var Script = reponse['Script'];
                    var URL_PICTURES = reponse['URL_PICTURES'];
                    var L_Modify = reponse['L_Modify'];
                    var L_Delete = reponse['L_Delete'];
                    var L_Warning = reponse['L_Warning'];
                    var L_Cancel = reponse['L_Cancel'];
                    var L_Confirm_Delete_Entity = reponse['L_Confirm_Delete_Entity'];
                    var L_Confirm = reponse['L_Confirm'];

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
                alert('Erreur serveur : ' + reponse['responseText']);
            }
        });
    }
}


// Affiche la boîte de confirmation d'une Entité.
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


// Suppression d'une Entité à la volée.
function deleteEntity( Id ){
    $.ajax({
        url: 'SM-users.php?action=ENT_DX',
        type: 'POST',
        data: $.param({'ent_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            
            if (reponse['Status'] == 'success') {
                $('#entity_'+Id).remove();
                hideConfirmMessage();

                var Total = $('#total').text();
                Total = Number(Total) - 1;
                $('#total').text( Total );
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur : ' + reponse['responseText']);
        }
    });
}


// Gestion des modifications d'une Entité "en place".
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

    $('#iLabel, #iCode').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iLabel').val() != '' || $('#iCode').val() != '' ) saveModifyEntity( Id );
        }

        if (e.which == 27) {
            endModifyEntity( Id );
        }
    });
}


// Sauvegarde les modifications apportées à une Entité.
function saveModifyEntity( Id ) {
    var Code = $('#iCode').val().toUpperCase();
    var Label = $('#iLabel').val();

    $.ajax({
        url: 'SM-users.php?action=ENT_MX',
        type: 'POST',
        data: $.param({'ent_id': Id, 'Code': Code, 'Label': Label}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

            if (reponse['Status'] == 'success') {
                $('#code-'+Id).text(Code);
                $('#label-'+Id).text(Label);
                
                $('#MODI_'+Id).remove();
                $('#entity_'+Id).show();
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur : ' + reponse['responseText']);
        }
    });
}


// Annule les modifications en cours sur une Entité.
function endModifyEntity(Id) {
    $('#MODI_'+Id).remove();
    $('#entity_'+Id).show();
}


// Annule toutes les modifications en cours sur les Entités.
function endAllModifyEntity() {
    $('tr[id^="MODI_"]').each( function(index) {
        var T_ID = $(this).attr( 'id' );
        T_ID = T_ID.split('_');
        endModifyEntity(T_ID[1]);
    } );
}


// Supprime la fenêtre de Confirmation.
function hideConfirmMessage() {
    $('#confirm_message').remove();
}
