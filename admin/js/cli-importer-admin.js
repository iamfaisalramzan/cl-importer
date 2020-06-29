(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(document).ready(function() {
		$(document).on('click', '#runImport', function(e){
			e.preventDefault();
			importFromCSV();
		});

		// Add the following code if you want the name of the file appear on select
		$("input[type=file]").on("change", function() {
			let filenames = [];
			let files = document.getElementById("csvFile").files;
			if (files.length > 1) {
			  	filenames.push("Total Files (" + files.length + ")");
			} else {
			  	for (let i in files) {
					if (files.hasOwnProperty(i)) {
				  		filenames.push(files[i].name);
					}
			  	}
			}
			$(this).next(".custom-file-label").html(filenames.join(","));
		});
	});

	// Perform AJAX
	function importFromCSV() {
		$('#message').hide().removeClass('updates');
		$('#message').hide().removeClass('error');
		$('#message p').empty();
		var form_data = new FormData();
		var importFile = $('#csvFile')[0].files;
		form_data.append('action', 'csv_ajax_call');
		form_data.append('file', importFile[0]);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajax_object.ajax_url,
			processData: false,
			contentType: false,
			cache: false,
			data: form_data,
			enctype: 'multipart/form-data',
			beforeSend: function(){
				$('.spinner-border').show();
				// disabled the submit button
				$("#runImport").prop("disabled", true);
			},
			success: function(data){
				$('.spinner-border').hide();
				$('#message').show().addClass(data.status);
				$('#message p').append(data.message);
				$("#runImport").prop("disabled", false);
				console.log(data);
			},
            error: function (request, status, error) {
                console.log("Status : ", status);
				console.log("Error : ", error);
				$('#message').show().addClass('error');
				$('#message p').append(status);
                $("#runImport").prop("disabled", false);
            }
		});
	};
})( jQuery );
