$( document ).ready(function() {
	
	$('#copy-button').click(function(e) {
		// https://developer.mozilla.org/en-US/docs/Web/API/Clipboard/writeText
		// Doesn't work on localhost https://developer.mozilla.org/en-US/docs/Web/API/Clipboard
		navigator.clipboard.writeText($('#generated-url').val().trim()).then(
			() => {
				$('#ot-clipboard').addClass('success');
			},
			() => {
				$('#ot-clipboard').addClass('fail');
			}
		);
	});
});