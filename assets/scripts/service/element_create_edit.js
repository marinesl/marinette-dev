/*
 * Welcome to your element_create_edit's service JS JavaScript file!
 *
 */

// Import TinyMCE JS
import '../librairies/tinymce'

// Import method openMadal() from './open_modal.js'
import openModal from './open_modal';

// Import la méthode d'initialisation du TinyMCE
import initTinymce from '../librairies/tinymce'



/********************************************************************************/



$(document).ready(function() {

    // Si l'utilisateur a demandé la prévisualisation
    if (is_preview == 1) {
        window.open(Routing.generate('front_'+element_toString+'_preview', { 'slug': $('h1').data('slug') }))
    }

    // Initialisation de l'éditor TinyMCE
    initTinymce(element_toString+'_content')
    
    // Au changement du champ title
    $('#'+element_toString+'_title').on('input', function() {

        // On récupère la valeur du champ en cours
        var title = $(this).val()

        // On met en bas de casse et on change les accents
        var new_value = title.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9\s]/gi, '').toLowerCase()

        /***** Remplissage des mots clés avec le titre *****/
            // On remplace les espaces par des virgules
            var keyword = new_value.replace(/[_\s]/g, ',')

            // Si le dernier caractère est une virgule
            const last_keyword = keyword.slice(-1);
            if (last_keyword == ',') keyword = keyword.slice(0,-1)
            
            // On met dans le champ de meta keyword le résultat
            $('#'+element_toString+'_meta_keyword').val(keyword)
        /**/

        /***** Remplissage du slug avec le titre *****/
            // On remplace les espaces par des tirets
            var slug = new_value.replace(/[_\s]/g, '-')

            // Si le dernier caractère est une virgule
            const last_slug = slug.slice(-1);
            if (last_slug == '-') slug = slug.slice(0,-1)
            
            // On met dans le champ de slug le résultat
            $('#'+element_toString+'_slug').val(slug)
        /**/
    })


    // Au changement du champ status
    $('#'+element_toString+'_status').on('input', function() {

        // On récupère la valeur du champ en cours (identifiant du statut)
        var status_id = $(this).val()

        // Statut Corbeille = 4
        if (status_id == 4) {
            $.ajax({
                url: Routing.generate('back_'+element_toString+'_change_status_confirm'),
                method: "get",
                datatype: 'html',
                data: {ids: $('h1').data(element_toString), status: 'Corbeille'}
            })
            .done(function(response){

                // Function in back.js
                openModal(response)

                // Au clic sur le bouton Oui
                $("#myModal #yes-button").click(function() {
                    // On envoie le formulaire
                    $('form').submit()
                })
            })
            .fail(function(error){
                alert("La requête s'est terminée en échec.");
            })
        }
    })
})