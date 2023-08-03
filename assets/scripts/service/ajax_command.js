/*
 * Welcome to your ajax_command's service JS JavaScript file!
 *
 */

// Import method openModal() from './open_modal.js'
import openModal from './open_modal';


/**
 * Requêtes Ajax pour la suppression et le changement de statut
 * @param {string} route_in La route de la requête AJAX pour confirmer l'action
 * @param {string} route_out La route de l'action finale
 * @param {array} ids tableau des identifiants des pages sélectionnée
 */
export default function ajaxCommand(route_in, route_out, ids, status = '') {
    $.ajax({
        url: Routing.generate(route_in),
        method: "get",
        datatype: 'html',
        data: {ids: ids, status: status}
    })
    .done(function(response){

        // Function in back.js
        openModal(response)

        // Au clic sur le bouton Oui
        $("#myModal #yes-button").click(function() {
            window.location = Routing.generate(route_out) + "?ids=" + ids
        })

    })
    .fail(function(error){
        alert("La requête s'est terminée en échec.");
    })
}