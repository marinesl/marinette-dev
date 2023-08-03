/*
 * Welcome to your element_list's service JS JavaScript file!
 *
 */

// import '../datatables'
import initDatatables from '../librairies/datatables'

// Import method ajaxCommand() from '../service/ajax_command.js'
import ajaxCommand from '../service/ajax_command';


/********************************************************************************/



$(document).ready( function() {

    // Pour la liste des pages dont le statut n'est pas "Corbeille"
    if (!is_corbeille) {
        
        // Liste des boutons
        var buttons = [
            {
                text: 'Créer un'+( (is_female) ? 'e' : '' )+' '+element_toString,
                action: function ( e, dt, node, config ) {
                    window.location.href = Routing.generate('back_'+element_toString+'_create', { 'is_preview': 0 })
                },
                className: 'btn btn-primary'
            },
            {
                text: 'Corbeille',
                action: function ( e, dt, node, config ) {
                    window.location.href = Routing.generate('back_'+element_toString+'_corbeille')
                },
                className: 'btn btn-info'
            }
        ]

        // Au clic sur une ligne, on ouvre la page de modification
        $('#table').on('click', 'tbody tr', function() {
            window.location.href = Routing.generate('back_'+element_toString+'_edit', { 'slug': $(this).data('slug'), 'is_preview': 0 })
        });

    // Pour la liste des éléments dont le statut est "Corbeille"
    } else {

        // Liste des boutons
        var buttons = [
            {
                text: 'Tou'+( (is_female) ? 'tes' : 's' )+' les '+element_toString+'s',
                action: function ( e, dt, node, config ) {
                    window.location.href = Routing.generate('back_'+element_toString)
                },
                className: 'btn btn-primary'
            },
            {
                text: 'Tout sélectionner',
                attr: {   
                    'id': 'select-all'
                },
                className: 'btn btn-info',
                action: function ( e, dt, node, config ) {
                    // On parcourt toutes les lignes du tableau
                    $('#table tbody tr').each(function() {
                        // Si la ligne n'est pas sélectionnée, on la sélectionne
                        if (!$(this).hasClass('selected')) $(this).toggleClass('selected');
                    })

                    // On cache ce bouton
                    $(node).toggleClass('disabled')

                    // On affiche l'autre bouton
                    $('button#deselect-all').toggleClass('disabled')

                    // On affiche la liste des actions qu'une fois
                    if ($('div#block-actions').hasClass('disabled')) $('div#block-actions').toggleClass('disabled')
                },
            },
            {
                text: 'Tout désélectionner',
                attr: {   
                    'id': 'deselect-all'
                },
                className: 'btn btn-info disabled',
                action: function ( e, dt, node, config ) {
                    // On parcourt toutes les lignes du tableau
                    $('#table tbody tr').each(function() {
                        // Si la ligne est sélectionnée, on la désélectionne
                        if ($(this).hasClass('selected')) $(this).toggleClass('selected');
                    })

                    // On cache ce bouton
                    $(node).toggleClass('disabled')

                    // On affiche l'autre bouton
                    $('button#select-all').toggleClass('disabled')

                    // On cache la liste des actions
                    if (!$('div#block-actions').hasClass('disabled')) $('div#block-actions').toggleClass('disabled')
                },
            }
        ]

        // Au clic sur une ligne, elle est sélectionnée
        $('#table tbody').on('click', 'tr', function () {
            $(this).toggleClass('selected');

            // On récupère les lignes sélectionnées dans le tableau
            var selected_rows = $('#table').DataTable().rows('.selected').data().length

            // On affiche la liste des actions qu'une fois
            if ($('div#block-actions').hasClass('disabled') || selected_rows == 0) $('div#block-actions').toggleClass('disabled')
        });
    }


    // Initialisation du tableau DataTables
    initDatatables(buttons, [0, 2, 3], pageLength)


    // Pour la liste des pages dont le statut est "Corbeille"
    if (is_corbeille) {

        // Au changement de la liste d'actions
        $('select#actions').change(function() {
            // On recupère la valeur sélectionnée dans la liste
            var action = $(this).val();

            // Si l'action n'est pas vide
            if (action != "") {
                // On récupère les lignes sélectionnées dans le tableau
                var selected_rows = $('#table').DataTable().rows('.selected').data()

                // Texte qui va contenir le(s) identifiant(s)
                var ids = "";

                // On récupère le(s) identifiant(s)
                for (let row = 0; row < selected_rows.length; row++) {
                    const the_row = selected_rows[row];
                    ids += the_row[0]
                    if (row != selected_rows.length - 1) ids += ','
                }
            }

            // Si l'action est de Supprimer
            if (action == "delete")
                ajaxCommand('back_'+element_toString+'_delete_confirm', 'back_'+element_toString+'_delete', ids)

            // Si l'action est de Changer le statut
            else if (action == "change") 
                ajaxCommand('back_'+element_toString+'_change_status_confirm', 'back_'+element_toString+'_change_status', ids, 'Brouillon')

        })
    }

});