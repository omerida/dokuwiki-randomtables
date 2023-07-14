/**
 * RandomTable.js
 */
var random_tables_plugin = {

    /**
     * @brief onclick method for input element
     *
     * @param {jQuery} $chk the jQuery input element
     */
    roll: function($btn) {
        let src = $btn.data('src');
        if (!src) {
            let srcSelect = jQuery('select#' + $btn.data('pick'));
            src = srcSelect.val();
        }

        if (!src) {
            return;
        }

        let target = $btn.data('target');
        let targetDiv = jQuery('#' + target);

        // make an AJAX call
        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: 'plugin_randomtable_roll',
                table_id: src,
            },
            function(response) {
                random_tables_plugin.addResult(targetDiv, response);
            },
            'json'
        );
    }, // roll

    addResult: function(targetDiv, response) {
        // update the result
        targetDiv.append('<div>' + response.result + '<button class="delete">del</button></div>');
        targetDiv.find('button').on('click', function(ev) {
            jQuery(this).parent().first().remove();
        });
    }, // addResult

    init: function() {
        // add clickhandler to rolltable buttons
        jQuery('button.randomtable').on('click', function(ev) {
            ev.preventDefault();
            random_tables_plugin.roll(jQuery(this));
        });
    } // init
};

jQuery(random_tables_plugin.init);
