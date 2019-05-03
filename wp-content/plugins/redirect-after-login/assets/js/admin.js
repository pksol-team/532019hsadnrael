jQuery(function() {
	jQuery( '.mtral .block label.custom input[type="text"]' ).live( 'keypress keyup keydown focus blur', function(e){
		if( jQuery( this ).val() ){
			jQuery( this ).closest( 'label' ).closest( '.block' ).find( 'label select' ).prop( 'disabled', true ); 
		}else{
			jQuery( this ).closest( 'label' ).closest( '.block' ).find( 'label select' ).prop( 'disabled', false ); 
		}
	});
});