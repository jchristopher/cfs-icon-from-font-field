/**
 * Backbone Application File
 * @internal Obviously, I've dumped all the code into one file. This should probably be broken out into multiple
 * files and then concatenated and minified but as it's an example, it's all one lumpy file.
 * @package cfsifff.backbone_modal
 */

/**
 * @type {Object} JavaScript namespace for our application.
 */
var cfsifff = {
    backbone_modal: {
        __instance: undefined
    }
};

/**
 * Primary Modal Application Class
 */
cfsifff.backbone_modal.Application = Backbone.View.extend(
    {
        id: "backbone_modal_dialog",
        events: {
            "click .backbone_modal-close": "closeModal",
            "click .navigation-bar a": "showIconSet",
            "click .cfsifff-chooser-trigger": "chooseIcon"
        },

        /**
         * Simple object to store any UI elements we need to use over the life of the application.
         */
        ui: {
            nav: undefined,
            content: undefined
        },

        /**
         * Container to store our compiled templates. Not strictly necessary in such a simple example
         * but might be useful in a larger one.
         */
        templates: {},

        /**
         * Instantiates the Template object and triggers load.
         */
        initialize: function () {
            "use strict";

            _.bindAll( this, 'render', 'preserveFocus', 'closeModal', 'saveModal', 'showIconSet' );
            this.initialize_templates();
            this.render();
        },


        /**
         * Creates compiled implementations of the templates. These compiled versions are created using
         * the wp.template class supplied by WordPress in 'wp-util'. Each template name maps to the ID of a
         * script tag ( without the 'tmpl-' namespace ) created in template-data.php.
         */
        initialize_templates: function () {
            this.templates.window = wp.template( "cfsifff-modal-window" );
            this.templates.backdrop = wp.template( "cfsifff-modal-backdrop" );
        },

        /**
         * Assembles the UI from loaded templates.
         * @internal Obviously, if the templates fail to load, our modal never launches.
         */
        render: function () {
            "use strict";

            // Build the base window and backdrop, attaching them to the $el.
            // Setting the tab index allows us to capture focus and redirect it in Application.preserveFocus
            this.$el.attr( 'tabindex', '0' )
                .append( this.templates.window() )
                .append( this.templates.backdrop() );

            if ( typeof cfsifff_backbone_modal_l10n === "object" ) {
                this.ui.content = this.$( '.navigation-bar nav ul' )
                    .append( cfsifff_backbone_modal_l10n.icon_set_nav );
            }

            if ( typeof cfsifff_backbone_modal_l10n === "object" ) {
                this.ui.content = this.$( '.backbone_modal-main article' )
                    .append( "<div>" + cfsifff_backbone_modal_l10n.icon_sheets + "</div>" );
            }

            // Handle any attempt to move focus out of the modal.
            jQuery( document ).on( "focusin", this.preserveFocus );

            // set overflow to "hidden" on the body so that it ignores any scroll events while the modal is active
            // and append the modal to the body.
            // TODO: this might better be represented as a class "modal-open" rather than a direct style declaration.
            jQuery( "body" ).css( {"overflow": "hidden"} ).append( this.$el );

            // Set focus on the modal to prevent accidental actions in the underlying page
            // Not strictly necessary, but nice to do.
            this.$el.focus();
        },

        /**
         * Ensures that keyboard focus remains within the Modal dialog.
         * @param e {object} A jQuery-normalized event object.
         */
        preserveFocus: function ( e ) {
            "use strict";
            if ( this.$el[0] !== e.target && ! this.$el.has( e.target ).length ) {
                this.$el.focus();
            }
        },

        /**
         * Closes the modal and cleans up after the instance.
         * @param e {object} A jQuery-normalized event object.
         */
        closeModal: function ( e ) {
            "use strict";

            e.preventDefault();
            this.undelegateEvents();
            jQuery( document ).off( "focusin" );
            jQuery( "body" ).css( {"overflow": "auto"} );
            this.remove();
            cfsifff.backbone_modal.__instance = undefined;
        },

        /**
         * Responds to the btn-ok.click event
         * @param e {object} A jQuery-normalized event object.
         * @todo You should make this your own.
         */
        saveModal: function ( e ) {
            "use strict";
            this.closeModal( e );
        },

        showIconSet: function ( e ) {
            "use strict";
            e.preventDefault();

            jQuery('.cfsifff-chooser-family').hide();
            jQuery('.cfsifff-chooser'+jQuery(e.currentTarget).data('cfsifffFont')).show();
        },

        chooseIcon: function ( e ) {
            "use strict";
            e.preventDefault();

            // send the data back
            jQuery(document).trigger('cfsifffChosen',[ jQuery(e.currentTarget).data('font'), jQuery(e.currentTarget).data('char') ]);

            this.closeModal( e );
        }

    } );

jQuery( function ( $ ) {
    "use strict";
    /**
     * Attach a click event to the meta-box button that instantiates the Application object, if it's not already open.
     */
    $('body').on( 'click', '.cfs_input .cfsifff-button.button.add', function ( e ) {
        e.preventDefault();
        if ( cfsifff.backbone_modal.__instance === undefined ) {
            cfsifff.backbone_modal.__instance = new cfsifff.backbone_modal.Application();
            jQuery('.cfsifff-chooser-family:gt(0)').hide();
            jQuery(this).addClass('cfsifff-context');
        }
    } );
} );