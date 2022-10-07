$( document ).ready(function() {
	
	$('#copy-button').click(function(e) {
		// https://developer.mozilla.org/en-US/docs/Web/API/Clipboard/writeText
		// Doesn't work on localhost https://developer.mozilla.org/en-US/docs/Web/API/Clipboard
		
		navigator.clipboard.writeText($('#generated_url').val().trim()).then(
			() => {
				alert('URL copied to clipboard');
			},
			() => {
				alert('URL could not be copied to clipboard.');
			}
		);
	});
});