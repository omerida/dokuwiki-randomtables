function RandomTablesPlugin() {
	let that = this;

    /**
     * @brief onclick method for input element
     *
     * @param {jQuery} $chk the jQuery input element
     */
    this.roll = function($btn) {
		let src = $btn.data('src');
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
				console.log(response);
				that.addResult(targetDiv, response);
			},
			'json'
		);
	}

	this.addResult = function(targetDiv, response) {
		// update the result
		targetDiv.append('<div>' + response.result + '<button class="delete">del</button></div>');
		targetDiv.find('button').on('click', function(ev) {
			jQuery(this).parent().first().remove();
		});
	}
}

jQuery(function(){
	let handler = new RandomTablesPlugin();

	// add clickhandler to rolltable buttons
	jQuery('button.randomtable').on('click', function(ev) {
		ev.preventDefault();
		handler.roll(jQuery(this));
	});
});
