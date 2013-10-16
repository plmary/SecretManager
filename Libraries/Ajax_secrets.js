$(document).keyup(function(e){
    if(e.which == 27 || e.which == 13){
        hideModal();
    }
});

$(document).ready(function(){
    // Masque la modale quand on clique un objet de class "close"
     $(".close").on('click', function() {
        hideModal();
    });

    // Change couleur si vide ou plein des champs obligatoires
    $('input.obligatoire').focusout(function(){
        if ($(this).val() != '') {
            $(this).css("border", "1px solid #cdcdcd");
        } else {
            $(this).css("border", "2px solid #00608d");
        }
    });

    // Met en place l'écoute des événements pour valider le calque.
    $('#iGroupLabel').keyup(function(e){
        if(e.which == 13){
            addGroup();
        }
    });

    $('#iButtonAddGroup').on('click', function(){
        addGroup();
    });
});

function hideModal() {
    // Remet à zéro les champs du calque
    $('#iGroupLabel').attr('data-id','');    
    $('#iGroupLabel').attr('value','');
    $('#iGroupAlert').attr('checked',false);
    $('#iButtonAddGroup').attr('value','')

    // Cache le calque.
    $('#afficherSecret').hide();
    $('#addGroup').hide();
}

// Traite l'affichage d'un secret.
function viewPassword( scr_id ){
    if ($('#inputlabel').val() != '') {
        $.ajax({
            url: '../../SM-secrets.php?action=SCR_V', // le nom du fichier indiqué dans le formulaire
            type: 'POST', // la méthode indiquée dans le formulaire (get ou post)
            data: $.param({'scr_id': scr_id}),
            dataType: 'json',
            success: function(reponse) {
                $('#afficherSecret').show('slow');

                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                var statut = resultat['Statut'];
                var password = resultat['password'];

                if ( password == null ) {
                    password = '('+resultat['l_nothing']+')';
                    var couleur_fond = '';
                } else {
                    var couleur_fond = 'bg-orange ';                    
                }

                if (statut == 'succes') {
                    Message = '<p><span>'+resultat['l_host']+' : </span>'+
                        '<span class="td-aere">'+resultat['host']+'</span></p>'+
                        '<p><span>'+resultat['l_user']+' : </span>'+
                        '<span class="td-aere">'+resultat['user']+'</span></p>'+
                        '<p><span>'+resultat['l_password']+' : </span>'+
                        '<span class="'+couleur_fond+'td-aere">'+password+'</span></p>';

                    $('#detailSecret').html(Message);
                }
                else if (statut == 'erreur') {
                    $('#detailSecret').text(resultat['Message']);
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

// ============================================
// Gestion des créations de Profil à la volée.
function putAddGroup(Title,Id,GroupName,GroupAlert,ButtonName){
    // Remet à zéro les champs du calque.
    $('#iGroupLabel').attr('data-id','');    
    $('#iGroupLabel').attr('value','');
    $('#iGroupAlert').attr('checked',false);
    $('#iButtonAddGroup').attr('value','')

    // Met à jour les champs du calque en fonction des paramètres reçus.
    $('#addGroupTitle').html(Title);
    $('#iGroupLabel').attr('data-id',Id);
    //$('#iGroupLabel').attr('value',GroupName);
    document.getElementById('iGroupLabel').value = GroupName;
    if(GroupAlert == '1') {
        document.getElementById('iGroupAlert').checked=true;
    } else {
        document.getElementById('iGroupAlert').checked=false;
    }
    $('#iButtonAddGroup').attr('value',ButtonName)

    // Calcule les coordonnées pour centrer le calque dans le navigateur.
    var Pos_X = (window.innerWidth - 650) / 2;
    var Pos_Y = (window.innerHeight - 163) / 2;

    // Affiche le calque.
    $('#addGroup').show('slow').offset({ top: Pos_Y, left: Pos_X });

    // Met le focus sur le 1er champ du calque.
    $('#iGroupLabel').focus();
}


function addGroup(){
    // Gère le cas d'une création d'un Groupe de Secret.
    if ($('#iGroupLabel').val() != '' && $('#iGroupLabel').attr('data-id') == '') {
        var Secret_Alert = document.getElementById('iGroupAlert').checked;
        var Label = $('#iGroupLabel').val()

        $.ajax({
            url: 'SM-secrets.php?action=ADDX',
            type: 'POST',
            data: $.param({'Label': Label, 'Alert': Secret_Alert}),
            dataType: 'json',
            success: function(reponse) {
                // Récupère le statut de l'appel Ajax
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });
                $('#addGroup').hide();

                $('#iGroupLabel').val('');

                var statut = resultat['Status'];

                if (statut == 'success') {
                    var Id = resultat['IdGroup'];
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


    // Gère le cas d'une modification d'un Groupe de Secret.
    if ($('#iGroupLabel').val() != '' && $('#iGroupLabel').attr('data-id') != '') {
        var Id = $('#iGroupLabel').attr('data-id');
        var Label = $('#iGroupLabel').val();
        var Secret_Alert = document.getElementById('iGroupAlert').checked;
        
        $.ajax({
            url: 'SM-secrets.php?action=MX',
            type: 'POST',
            data: $.param({'Label': Label, 'Alert': Secret_Alert,'sgr_id': Id}),
            dataType: 'json',
            success: function(reponse) {
                // Récupère le résultat de la requête.
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                $('#addGroup').hide();


                if ( Secret_Alert == true ) {
                    Image_Name = 'bouton_coche.gif';
                } else {
                    Image_Name = 'bouton_non_coche.gif';
                }

                var statut = resultat['Status'];

                if (statut == 'success') {
                    var URL_PICTURES = resultat['URL_PICTURES'];

                    var Alert_Image = '<img class="no-border" src="' + URL_PICTURES + '/' + Image_Name + '" alt="Ok" />';

                    $('#label_'+Id).text( Label );
                    $('#alert_'+Id).html( Alert_Image );

                    var Action = $('#modify_'+Id).attr('href');
                    var ActionFinal = 'javascript:putAddGroup(';

                    var Tmp = Action.split('(');
                    Tmp = Tmp[1].split(',');

                    ActionFinal += Tmp[0]+","+Tmp[1]+",'"+Label+"',"+Tmp[3]+','+Tmp[4];
                    $('#modify_'+Id).attr('href', ActionFinal);

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
