$( document ).ready(function() {
	
	$('#btn-doc').click(function() {
		$('#apiInfo').slideToggle('fast');
	});	
	$('#id-form-button').click(function() {
		$('#id-form').slideDown('fast');
		$('#param-form').slideUp('fast');
	});
	$('#param-form-button').click(function() {		
		$('#param-form').slideDown('fast');
		$('#id-form').slideUp('fast');
	});
});