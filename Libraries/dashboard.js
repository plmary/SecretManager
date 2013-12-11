$(function(){
    $("div.tableau_synthese p.titre#users").on('click', function() {
        if ( $('div.corps#c_users').css('display') != 'block' ) {
            $('div.corps#c_users').css('display','block');
        } else {
            $('div.corps#c_users').css('display','');
        }
    });

    $("div.tableau_synthese p.titre#groups").on('click', function() {
        if ( $('div.corps#c_groups').css('display') != 'block' ) {
            $('div.corps#c_groups').css('display','block');
        } else {
            $('div.corps#c_groups').css('display','');
        }
    });

    $("div.tableau_synthese p.titre#profiles").on('click', function() {
        if ( $('div.corps#c_profiles').css('display') != 'block' ) {
            $('div.corps#c_profiles').css('display','block');
        } else {
            $('div.corps#c_profiles').css('display','');
        }
    });

    $("div.tableau_synthese p.titre#entities").on('click', function() {
        if ( $('div.corps#c_entities').css('display') != 'block' ) {
            $('div.corps#c_entities').css('display','block');
        } else {
            $('div.corps#c_entities').css('display','');
        }
    });

    $("div.tableau_synthese p.titre#civilities").on('click', function() {
        if ( $('div.corps#c_civilities').css('display') != 'block' ) {
            $('div.corps#c_civilities').css('display','block');
        } else {
            $('div.corps#c_civilities').css('display','');
        }
    });

/*    $("div.tableau_synthese").on('mouseleave', function() {
        $('div.corps').css('display','');
    }); */
});
