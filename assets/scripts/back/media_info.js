/*
 * Welcome to your media_info's service JS JavaScript file!
 *
 */


// Import method ajaxCommand() from '../service/ajax_command.js'
import ajaxCommand from '../service/ajax_command';


/********************************************************************************/

$(document).ready( function() {
    $(".btn-delete").click(function() {
        ajaxCommand('back_media_delete_confirm', 'back_media_delete', id)
    })
});