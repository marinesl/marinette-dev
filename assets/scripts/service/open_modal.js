/*
 * Welcome to your open_modal's service JS JavaScript file!
 *
 */

/**
 * Afficher le modal
 * @param {JSON} response contenu HTML à afficher sur le modal
 */
export default function openModal(response) {
    // On decode les données JSON récupérées
    // var result = jQuery.parseJSON(response);
    var result = JSON.parse(response);

    // On vide les données du modal
    $("#myModal .modal-title, #myModal .modal-body").html('')

    // Affichage du modal
    $("#myModal").modal('show')

    // Affichage du message et du titre personnalisés dans le modal
    $("#myModal .modal-title").append(result.titre)
    $("#myModal .modal-body").append(result.content)
    
    // Fermeture du modal au clic sur la croix
    $("#myModal button.close").click(function() {
        $("#myModal").modal('hide')
    })
}