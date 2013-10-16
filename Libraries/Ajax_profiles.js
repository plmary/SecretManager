$(document).keyup(function(e){
    if(e.which == 27) { // || e.which == 13){
        $('#addProfile').hide();
    }
});

$(document).ready(function(){
    // Masque la modale quand on clique un objet de class "close"
     $(".close").on('click', function() {
        $('#addProfile').hide();
    });

    // Change couleur si vide ou plein des champs obligatoires
    $('input.obligatoire').focusout(function(){
        if ($(this).val() != '') {
            $(this).css("border", "1px solid #cdcdcd");
        } else {
            $(this).css("border", "2px solid #00608d");
        }
    });

    $('#iProfileLabel').keyup(function(e){
        if (e.which == 13) {
            addProfile();
        }
    });

    $('#iButtonCreateProfile').on('click', function(){
        addProfile();
    });

    $('#iButtonDeleteProfile').on('click', function(){
        deleteProfile();
    });
});


// Gestion des créations de Profil à la volée.
function putAddProfile(){
    $('#addProfile').show('slow');
    $('#iProfileLabel').focus();
}

function addProfile(){
    if ($('#iProfileLabel').val() != '') {
        $.ajax({
            url: 'SM-users.php?action=PRF_AX',
            type: 'POST',
            data: $.param({'Label': $('#iProfileLabel').val()}),
            dataType: 'json',
            success: function(reponse) {
                $('#addProfile').hide();

                var Label = $('#iProfileLabel').val();

                $('#iProfileLabel').val('');

                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                var statut = resultat['Status'];

                if (statut == 'success') {
                    var Id = resultat['IdProfile'];
                    var Label = resultat['Label'];
                    var Script = resultat['Script'];
                    var URL_PICTURES = resultat['URL_PICTURES'];
                    var L_Modify = resultat['L_Modify'];
                    var L_Delete = resultat['L_Delete'];
                    var L_Groups_Associate = resultat['L_Groups_Associate'];

                    if ($('#dashboard').length == 0) {
                        $('#iListProfiles').prepend(
                         '<tr class="pair td-aere">'+
                         '<td class="align-middle align-center"><input type="checkbox" name="'+Id+'" id="P_'+Id+'" /></td>'+
                         '<td class="td-aere align-middle"><label for="P_'+Id+'">'+Label+'</label></td>'+
                         '<td class="align-center"><a class="simple" href="'+Script+'?action=PRF_G&prf_id='+Id+'">'+
                         '<img src="'+URL_PICTURES+'/b_usrscr_2.png" class="no-border" alt="'+L_Groups_Associate+'" title="'+L_Groups_Associate+'" /></a>'+
                         '</td>'+
                         '</tr>'
                        );
                    } else {
                        $('#iListProfiles').prepend(
                         '<tr class="pair surline">'+
                         '<td class="align-middle">'+Label+'</td>'+
                         '<td>'+
                         '<a class="simple" href="'+Script+'?action=PRF_M&prf_id='+Id+'">'+
                         '<img class="no-border" src="'+URL_PICTURES+'/b_edit.png" alt="'+L_Modify+'" title="'+L_Modify+'" /></a>'+
                         '<a class="simple" href="'+Script+'?action=PRF_D&prf_id='+Id+'">'+
                         '<img class="no-border" src="'+URL_PICTURES+'/b_drop.png" alt="'+L_Delete+'" title="'+L_Delete+'" /></a>'+
                         '<a class="simple" href="'+Script+'?action=PRF_G&prf_id='+Id+'">'+
                         '<img class="no-border" src="'+URL_PICTURES+'/b_usrscr_2.png" alt="'+L_Groups_Associate+'" title="'+L_Groups_Associate+'" /></a>'+
                         '</td>'+
                         '</tr>'
                        );
                    }

                    $('body').notif({title: resultat['Title'],
                        content: resultat['Message'],
                        cls: 'success',
                        timeout: 2000});
                } else if (statut == 'error') {
                    $('body').notif({title: resultat['Title'],
                        content: resultat['Message'],
                        cls: 'error'});
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
}


// Gestion des suppressions de Profil à la volée.
function deleteProfile( Id ){
    $.ajax({
        url: 'SM-users.php?action=PRF_DX',
        type: 'POST',
        data: $.param({'prf_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            var resultat = new Array();

            $.each(reponse, function(attribut, valeur) {
                resultat[attribut]=valeur;
            });

            var statut = resultat['Status'];

            if (statut == 'success') {
                $('#profil_'+Id).remove();
                $('body').notif({title: resultat['Title'],
                    content: resultat['Message'],
                    cls: 'success',
                    timeout: 2000});
            } else if (statut == 'error') {
                $('body').notif({title: resultat['Title'],
                    content: resultat['Message'],
                    cls: 'error'});
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
function modifyProfile(event, Id) {
    var oldValue = $('#field_'+Id).html();

    if ( document.getElementById('field_'+Id).tagName.toLowerCase() == 'span' ) {
        $('#label_'+Id).html( '<input id="field_'+Id+'" data-old-value="'+oldValue+'" value="'+oldValue+'" onkeydown="modifyProfile(event,\''+Id+'\');"/>' );
        document.getElementById('field_'+Id).focus();
        document.getElementById('field_'+Id).selectionStart = oldValue.length;
    } else if(event.keyCode == 27) {
        $('#label_'+Id).html( '<span id="field_'+Id+'">'+$('#field_'+Id).data('oldValue')+'</span>' );
    } else if (event.keyCode == 13) {
        $.ajax({
            url: 'SM-users.php?action=PRF_MX',
            type: 'POST',
            data: $.param({'prf_id': Id, 'Label': $('#field_'+Id).val()}),
            dataType: 'json',
            success: function(reponse) {
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                var statut = resultat['Status'];

                if (statut == 'success') {
                    $('#label_'+Id).html( '<span id="field_'+Id+'">'+$('#field_'+Id).val()+'</span>' );
    
                    $('body').notif({
                        title: resultat['Title'],
                        content: resultat['Message'],
                        cls: 'success',
                        timeout: 2000
                    });
                } else if (statut == 'error') {
                    $('body').notif({
                        title: resultat['Title'],
                        content: resultat['Message'],
                        cls: 'error'
                    });
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

}
