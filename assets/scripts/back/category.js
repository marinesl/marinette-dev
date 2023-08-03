/*
 * Welcome to your category's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// import '../datatables'
import initDatatables from '../librairies/datatables'

// Import method openMadal() from '../service/open_modal.js'
import openModal from '../service/open_modal';



/********************************************************************************/



/***** On récupère la liste des statuts en AJAX  *****/
    var status_list = {}
    status_list = getStatusList()
/**/


/***** Liste des boutons *****/
    if (is_corbeille) {
        var buttons = [
            {
                text: 'Toutes les catégories',
                action: function ( e, dt, node, config ) {
                    window.location.href = Routing.generate('back_category')
                },
                className: 'btn btn-primary'
            }
        ]
    } else {
        var buttons = [
            {
                text: 'Créer une catégorie',
                action: function ( e, dt, node, config ) {
                    
                    var tbody = $("#table tbody")

                    $(tbody).prepend('<tr class="even edit-mode new"><td>'+inputName()+'</td><td>'+selectStatus(status_list)+'</td><td></td><td></td><td></td></tr>')

                    // Au clic sur le bouton enregistrer
                    $('tr').on('click', '#btn-submit', function () {

                        // On récupère la ligne
                        var tr = $(this).parent().parent()
                
                        // Méthode de l'action de modifier des données
                        createEdit(tr)
                
                    });
                },
                className: 'btn btn-primary'
            },
            {
                text: 'Corbeille',
                action: function ( e, dt, node, config ) {
                    window.location.href = Routing.generate('back_category_corbeille')
                },
                className: 'btn btn-info'
            }
        ]
    }
/**/


$(document).ready( function() {

    // Initialisation du tableau DataTables
    initDatatables(buttons, [0, 2, 3, 4])


    var val_name = ''
    var val_status = ''

    /***** Au clic sur une ligne, on ouvre le formulaire de modification *****/
        $('#table').on('click', 'tbody tr .btn-edit', function() {

            // On récupère la ligne
            var tr = $(this).parent().parent()

            // On vérifie si la ligne n'est pas déjà en mode édition
            if (!$(tr).hasClass('edit-mode') && $('tr.edit-mode').length < 1) {
                // On ajoute une classe dans la ligne
                $(tr).addClass('edit-mode')

                // On récupère le slug de la catégorie cliquée
                var slug = $(tr).data('slug')

                // Champ de la colonne Nom
                var cell_name = $(tr).find("td.name")
                val_name = $(cell_name).html()
                $(cell_name).html(inputName(val_name));

                // Champ de la colonne Statut
                var cell_status = $(tr).find("td.status")
                val_status = $(cell_status).html()
                $(cell_status).html(selectStatus(status_list, val_status))
    
                // Au changement du champ status
                $('#status').change(function() {

                    // On récupère la valeur du champ en cours (identifiant du statut)
                    var status_id = $(this).val()

                    $.ajax({
                        url: Routing.generate("back_category_change_status_confirm", { postCategory: $(tr).data('slug'), status: status_id }),
                        method: "get",
                        datatype: 'html'
                    })
                    .done(function(response){

                        // Function in back.js
                        openModal(response)

                        // Au clic sur le bouton Oui
                        $("#myModal #yes-button").click(function() {
                            // Méthode de l'action de modifier des données
                            createEdit(tr)
                        })
                    })
                    .fail(function(error){
                        alert("La requête s'est terminée en échec.");
                    })
                })
            }
        });
    /**/


    /***** Au clic sur le bouton Fermer du formulaire *****/
        $('#table').on('click', 'tbody tr img#btn-close', function() { 

            // On récupère la ligne
            var tr = $(this).parent().parent()
            
            // Si c'est un formulaire de modification
            if (val_name != '' && val_status != '') {
                // On affiche à nouveau la valeur de la colonne Nom
                $(tr).find("td.name").html(val_name)

                // On affiche à nouveau la valeur de la colonne Statut
                $(tr).find("td.status").html(val_status)

                // On supprime la classe dans la ligne
                $(tr).removeClass('edit-mode')

            // Si c'est un formulaire de création
            } else {
                $(tr).remove()
            }
        });
    /**/


    /*****  
     * Au clic sur le bouton Enregistrer du formulaire
     * https://datatables.net/examples/api/form.html
     *****/
        $('tr').on('click', '#btn-submit', function () {

            // On récupère la ligne
            var tr = $(this).parent().parent()

            // Méthode de l'action de modifier des données
            createEdit(tr)
        });
    /**/
});


/**
 * Get la liste des statuts
 * @returns array
 */
function getStatusList() {
    var status_list = {}

    $.ajax({
        url: Routing.generate("back_status_category_ajax") + "?isCorbeille="+is_corbeille,
        method: "get",
        datatype: 'json',
        async: false
    })
    .done(function(response){
        status_list = response
    })
    .fail(function(error){
        alert("La requête s'est terminée en échec.");
    })

    return status_list
}


/**
 * Action de créer et modifier des données
 * @param {DOM} tr 
 */
function createEdit(tr) {

    // On vérifie si c'est une création ou une modification
    var is_new = ($(tr).hasClass('new')) ? true : false

    // On récupère les données des champs du formulaire
    var data = (is_new) ? $('table input, table select').serialize() : $('#table').DataTable().$('input, select').serialize();

    /***** On va transformer le nouveau nom de la catégorie en slug ****/
        var name = $('input#name').val().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9\s]/gi, '').toLowerCase()
        // On remplace les espaces par des tirets
        var slug = name.replace(/[_\s]/g, '-')
        // Si le dernier caractère est une virgule
        const last_slug = slug.slice(-1);
        if (last_slug == '-') slug = slug.slice(0,-1)
    /**/
    
    /***** Requête Ajax *****/
        // La route est différente en fonction de la création et de la modification
        var route = (is_new) ? Routing.generate("back_category_create") + '?' + data + '&slug=' + slug : Routing.generate("back_category_edit", { 'slug': $(tr).data('slug') }) + '?' + data + '&slug=' + slug

        $.ajax({
            url: route,
            method: "get",
            datatype: 'json',
        })
        .done(function(response){
            // Si le message retourné n'est pas un succès
            if (response != "success") alert(response)
            else location.reload()
        })
        .fail(function(error){
            alert("La requête s'est terminée en échec.");
        })
    /**/
}


/**
 * Input name du formulaire de création et de modification
 * @param {string} val_name Valeur du nom dans le tableau
 * @returns string
 */
function inputName(val_name = null) {
    return (val_name != null) ? '<input type="text" id="name" name="name" value="'+val_name+'" class="form-control" required>' : '<input type="text" id="name" name="name" class="form-control" required>'
}


/**
 * Select pour les statuts du formulaire de création et de modification
 * @param {object} status_list Liste des statuts récupérés en Ajax
 * @param {string} val_status Valeur du statut dans le tableau
 * @returns string
 */
function selectStatus(status_list, val_status = null) {
    var select = '<select name="status" id="status" class="form-control">'

    status_list.forEach(element => {
        var is_selected = (val_status == element.name) ? 'selected' : ''
        select += '<option value="'+element.id+'" '+is_selected+'>'+element.name+'</option>'
    });

    select += '</select>'

    var button = '<button type="submit" id="btn-submit"><img src="'+path_asset_img+'picto/save.svg" alt="Enregistrer"></button><img src="'+path_asset_img+'picto/close.svg" alt="Fermer" id="btn-close">'

    return select + button
}