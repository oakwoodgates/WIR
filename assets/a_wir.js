jQuery(document).ready( function(){	
		
	jQuery('#content').on('click', 'a.rml_bttn', function(e) { 
		e.preventDefault();
		
		var rml_post_id = jQuery(this).data( 'id' );
		
		jQuery.ajax({
			url : rml_obj.ajax_url,
			type : 'post',
			data : {
				action : 'read_me_later',
				security : rml_obj.check_nonce,
				post_id : rml_post_id
			},
			success : function( response ) {
				jQuery('.rml_contents').html(response);
			}
		});
		
		jQuery(this).hide();
	});	
	
});

window.webdev_ajax = ( function( window, document, $ ){
	var app = {};

	app.cache = function(){
		app.$ajax_form = $( '.ajax_tut_form' );
	};

	app.init = function(){
		app.cache();
		app.$ajax_form.on( 'submit', app.form_handler );

	};

	app.form_handler = function( evt ){
		evt.preventDefault();
		var serialized_data = app.$ajax_form.serialize();
		app.post_ajax( serialized_data );
	};

	app.post_ajax = function( serial_data ){
		var post_data = { 
			action     : 'webdev',
			nonce      : webdev.nonce,
			serialized : serial_data,
		};

		$.post( webdev.ajax_url, post_data, app.ajax_response, 'json' )
	};

	app.ajax_response = function( response_data ){
		if( response_data.success ){
			webdev.nonce = response_data.data.nonce;
			alert( response_data.data.script_response );
		} else {
			alert( 'ERROR' );
		}
	};

	$(document).ready( app.init );

	return app;

})( window, document, jQuery );

jQuery( document ).ready( function( jq ) {
   jq( '#buddypress' ).on( 'click', 'a.wirf', function( event ) {
		event.preventDefault();
		var gid   = jq( this ).parent().attr( 'id' ),
			nonce   = jq( this ).attr( 'href' ),
			thelink = jq( this );

		gid = gid.split( '-' );
		gid = gid[1];

		nonce = nonce.split( '?_wpnonce=' );
		nonce = nonce[1].split( '&' );
		nonce = nonce[0];

		// AJAX request
		if ( thelink.hasClass( 'favorite-group' ) || thelink.hasClass( 'unfavorite-group' ) ) {
			//return false;
			if ( thelink.hasClass( 'favorite-group' ) ) {
				var type = 'favorite';
			} else {
				var type = 'unfavorite';
			}
			jq.post( spfavortie_group.ajax_url, {
				url : spfavortie_group.ajax_url,
				action: 'spfavortie_group',
				'gid': gid,
				'type':type,
				'_wpnonce': nonce
			},
			function( response ) {
				if ( type == 'favorite' ) {
				//	thelink.text( 'Unfavorite' );
					thelink.addClass( 'unfavorite-group' ).removeClass( 'favorite-group' );
					thelink.addClass( 'dashicons-star-filled' ).removeClass( 'dashicons-star-empty' );
				} else {
				//	thelink.text( 'Favorite' );
					thelink.addClass( 'favorite-group' ).removeClass( 'unfavorite-group' );
					thelink.addClass( 'dashicons-star-empty' ).removeClass( 'dashicons-star-filled' );
				}
			} );
		}
		return false;
	} );
} );

jQuery( document ).ready( function( flg ) {
   flg( '#buddypress' ).on( 'click', 'a.wir-fav-loc', function( event ) {
		event.preventDefault();
		var gid   = flg( this ).parent().attr( 'id' ),
			nonce   = flg( this ).attr( 'href' ),
			thelink = flg( this );

		gid = gid.split( '-' );
		pid = gid[0];
		gid = gid[1];

		nonce = nonce.split( '?_wpnonce=' );
		nonce = nonce[1].split( '&' );
		nonce = nonce[0];

		// AJAX request
		if ( thelink.hasClass( 'favorite-location' ) || thelink.hasClass( 'unfavorite-location' ) ) {
			//return false;
			if ( thelink.hasClass( 'favorite-location' ) ) {
				var type = 'favorite';
			} else {
				var type = 'unfavorite';
			}
			flg.post( ajaxurl, {
				action: 'wirfav_location',
				'gid': gid,
				'pid': pid,
				'type':type,
				'_wpnonce': nonce
			},
			function( response ) {
				if ( type == 'favorite' ) {
				//	thelink.text( 'Unfavorite' );
					thelink.addClass( 'unfavorite-location' ).removeClass( 'favorite-location' );
					thelink.addClass( 'dashicons-star-filled' ).removeClass( 'dashicons-star-empty' );
				} else {
				//	thelink.text( 'Favorite' );
					thelink.addClass( 'favorite-location' ).removeClass( 'unfavorite-location' );
					thelink.addClass( 'dashicons-star-empty' ).removeClass( 'dashicons-star-filled' );
				}
			} );
		}
		return false;
	} );
} );
