import { __ } from '@wordpress/i18n';

/**
 * Add custom button on Gutenberg header to open Tutor frontend
 * builder
 *
 * @since v2.0.5
 */

 ( function( window, wp ){
    const buttonId = 'tutor-frontend-builder-trigger';
    // prepare our custom link's html.
    const buttonHtml = `
        <a id="${buttonId}" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-text-nowrap" href="${tutorInlineData.frontend_dashboard_url}" target="_blank">
        ${__('Edit with Frontend Course Builder', 'tutor')}
        </a>
    `;

    // check if gutenberg's editor root element is present.
    const editorElement = document.getElementById( 'editor' );
    if( !editorElement ){ // do nothing if there's no gutenberg root element on page.
        return;
    }
    const unsubscribe = wp.data.subscribe( function () {
        setTimeout( function () {
            if ( !document.getElementById( buttonId ) ) {
                var toolbarElement = editorElement.querySelector( '.edit-post-header-toolbar' );
                if( toolbarElement instanceof HTMLElement ){
                    toolbarElement.insertAdjacentHTML( 'beforeend', buttonHtml );
                }
            }
        }, 100 )
    } );
    // unsubscribe is a function - it's not used right now 
    // but in case you'll need to stop this link from being reappeared at any point you can just call unsubscribe();

} )( window, wp )