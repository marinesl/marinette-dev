/*
 * Welcome to your DataTables JS JavaScript file!
 *
 */

// Import DataTables CSS
import 'datatables/media/css/jquery.datatables.min.css';

// Require Moment
var moment = require('moment');

// Require DataTables JS
require('datatables');

// Require DateTime Moment (for DataTables and Moment)
require('datetime-moment');

// Require DataTables Buttons
require('datatables.net-buttons');



/********************************************************************************/



/***** 
 * Initialisation du tableau DataTables 
 * 
 * @param buttons tableau des boutons à afficher
 * @param columnUnsearchableArray tableau des index des colonnes à rechercher
 * @param pageLength nombre de lignes
 *****/

export default function initDatatables(
    buttons, 
    columnUnsearchableArray,
    pageLength = 10
) {
    // Format de la colonne date
    $.fn.dataTable.moment( "DD/MM/YYYY HH:mm", 'fr' )

    $('#table').DataTable({

        // Format d'affichage du DOM
        dom: 'fBtip',

        // Nombre de ligne à afficher
        pageLength: pageLength,

        // Pagination
        pagingType: "simple",

        // Traduction
        // https://datatables.net/manual/i18n#Configuration
        language: {
            processing:     "Traitement en cours...",
            search:         "",
            lengthMenu:     "Afficher _MENU_ &eacute;l&eacute;ments",
            info:           "_START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            infoEmpty:      "0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
            infoFiltered:   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            infoPostFix:    "",
            loadingRecords: "Chargement en cours...",
            zeroRecords:    "Aucun &eacute;l&eacute;ment &agrave; afficher",
            emptyTable:     "Aucune donnée disponible dans le tableau",
            paginate: {
                first:      "Premier",
                // previous:   "Pr&eacute;c&eacute;dent",
                // next:       "Suivant",
                previous:   '<img src="/img/picto/arrow-left.svg"/>',
                next:       '<img src="/img/picto/arrow-right.svg"/>',
                last:       "Dernier"
            },
            aria: {
                sortAscending:  ": activer pour trier la colonne par ordre croissant",
                sortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        },

        // On désative la recherche dans les colonnes Statut et Date
        // https://datatables.net/reference/option/columns.searchable
        columnDefs: [
            { searchable: false, targets: columnUnsearchableArray },
            { visible: false, target: 0 }
        ],

        // Le bouton pour créer une nouvelle page
        buttons: buttons
    });

    // Search input configuration
    $('.dataTables_filter input').addClass('form-control');
    $('.dataTables_filter input').attr('placeholder', 'Rechercher');
}


