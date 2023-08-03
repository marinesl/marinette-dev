/*
 * Welcome to your TinyMCE JS JavaScript file!
 *
 */

/* Import TinyMCE */
import tinymce from 'tinymce';

/* Default icons are required for TinyMCE 5.3 or above */
import 'tinymce/icons/default';

/* A theme is also required */
import 'tinymce/themes/silver';

/* Import the skin */
import 'tinymce/skins/ui/oxide/skin.css';
import 'tinymce/skins/ui/oxide/skin.min.css';
import 'tinymce/skins/ui/oxide/content.css';
import 'tinymce/skins/ui/oxide/content.min.css';
import 'tinymce/skins/content/default/content.css';
import 'tinymce/skins/content/default/content.min.css';

/* Import the models */
import 'tinymce/models/dom';

/* Import plugins */
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/code';
import 'tinymce/plugins/codesample';
import 'tinymce/plugins/emoticons';
import 'tinymce/plugins/emoticons/js/emojis';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';

/* Import premium plugins */
/* NOTE: Download separately and add these to /src/plugins */
/* import './plugins/checklist/plugin'; */
/* import './plugins/powerpaste/plugin'; */
/* import './plugins/powerpaste/js/wordimport'; */

/* A lang is also required */
import 'tinymce-i18n/langs/fr_FR';



/********************************************************************************/



/**
 * Initialisation de Tiny MCE pour le champ content
 */
export default function initTinymce(selector) {
    tinymce.init({
        selector: '#'+selector,
        language : 'fr_FR',
        menubar: false,
        plugins: 'code codesample lists table',
        toolbar: 'formatselect | bold italic underline strikethrough | numlist bullist | aligncenter indent outdent | codesample blockquote | table tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | code ',
    });
}