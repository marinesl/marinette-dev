/*
 * Welcome to your media's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// Script for Dropzone JS
import Dropzone from "dropzone";

// Optionally, import the dropzone file to get default styling.
import "dropzone/dist/dropzone.css";



/********************************************************************************/

$(document).ready(function() {
    // Au clic sur le bouton pour Ajouter un fichier
    $('#add-file').click(function() {
        $('form[name=drag_and_drop]').toggle("slow")
    })

    // Initialisation de Dropzone JS
    let myDropzone = new Dropzone("#my-dropzone", { 
        // Required options
        url: Routing.generate('back_media'),
        
        // https://www.iana.org/assignments/media-types/media-types.xhtml
        // acceptedFiles: 'image/*,application/pdf,application/mp4',
        acceptedFiles: 'image/*',

        // Default message
        dictDefaultMessage: "Cliquez ou déposez vos fichiers ici"
    });

    // Quand l'upload est terminé
    myDropzone.on('success', function () {
        location.reload();
    });
})

