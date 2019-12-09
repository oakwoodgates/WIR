window.wir_flist_ajax = ( function( window, document, wir_flist_jq ){
	var app = {};

	app.cache = function(){
		app.$ajax_form = wir_flist_jq( 'a.wir_flist' );
	};

	app.init = function(){
		app.cache();
		app.$ajax_form.on( 'click', app.form_handler );
	};

	app.form_handler = function( evt ){
		evt.preventDefault();

		var gid   	= jq( this ).parent().attr( 'id' ),
			l_nonce = jq( this ).attr( 'href' ),
			thelink = jq( this );
			app.$thelink = thelink;

			gid = gid.split( '-' );
			gid = gid[1];

			l_nonce = l_nonce.split( '?_wpnonce=' );
			l_nonce = l_nonce[1].split( '&' );
			l_nonce = l_nonce[0];

		if ( thelink.hasClass( 'favorite-list' ) || thelink.hasClass( 'unfavorite-list' ) ) {
			if ( thelink.hasClass( 'favorite-list' ) ) {
				var type = 'favorite';
				console.log('favorite');
			} else {
				var type = 'unfavorite';
				console.log('unfavorite');
			}
		
		} else {
			return false;
		}
		var something = app.$ajax_form.serialize();
		var serialized_data = {
			'gid' : gid,
			'l_nonce' : l_nonce,
			'type' : type,
		};

		app.post_ajax( serialized_data );
	};

	app.post_ajax = function( serial_data ){
		var post_data = { 
			action     : 'wir_flist_hook',
			nonce      : wir_obj.nonce,
			serialized : serial_data,
		};

		console.log(JSON.stringify(post_data));
		console.log(serial_data);

		wir_flist_jq.post( wir_obj.ajax_url, post_data, app.ajax_response, 'json' )
	};

	app.ajax_response = function( response_data ){
		if( response_data.success ){
			console.log(wir_obj.nonce);
			console.log(JSON.stringify(response_data.data));

			if ( response_data.data.type == 'favorite' ) {
				app.$thelink.addClass( 'unfavorite-list' ).removeClass( 'favorite-list' );
				app.$thelink.addClass( 'dashicons-star-filled' ).removeClass( 'dashicons-star-empty' );
			} else {
				app.$thelink.addClass( 'favorite-list' ).removeClass( 'unfavorite-list' );
				app.$thelink.addClass( 'dashicons-star-empty' ).removeClass( 'dashicons-star-filled' );
			}

			wir_obj.nonce = response_data.data.nonce;
			console.log('SUCCESS');
		//	alert( response_data.data.script_response );
		} else {
			console.log('FAILED');
		//	alert( 'ERROR' );
		}
	};

	wir_flist_jq(document).ready( app.init );

	return app;

})( window, document, jQuery );

window.wir_floc_ajax = ( function( window, document, wir_floc_jq ){
	var app = {};

	app.cache = function(){
		app.$ajax_form = wir_floc_jq( 'a.wir_floc' );
	};

	app.init = function(){
		app.cache();
		app.$ajax_form.on( 'click', app.form_handler );
	};

	app.form_handler = function( evt ){
		evt.preventDefault();

		var gid   	= jq( this ).parent().attr( 'id' ),
			l_nonce = jq( this ).attr( 'href' ),
			thelink = jq( this );
			app.$thelink = thelink;

			gid = gid.split( '-' );
			pid = gid[0];
			gid = gid[1];

			l_nonce = l_nonce.split( '?_wpnonce=' );
			l_nonce = l_nonce[1].split( '&' );
			l_nonce = l_nonce[0];

		if ( thelink.hasClass( 'favorite-location' ) || thelink.hasClass( 'unfavorite-location' ) ) {
			if ( thelink.hasClass( 'favorite-location' ) ) {
				var type = 'favorite';
				console.log('favorite');
			} else {
				var type = 'unfavorite';
				console.log('unfavorite');
			}
		
		} else {
			return false;
		}
		var something = app.$ajax_form.serialize();
		var serialized_data = {
			'gid' : gid,
			'l_nonce' : l_nonce,
			'type' : type,
			'pid' : pid,
		};

		app.post_ajax( serialized_data );
	};

	app.post_ajax = function( serial_data ){
		var post_data = { 
			action     : 'wir_floc_hook',
			nonce      : wir_obj.nonce,
			serialized : serial_data,
		};

		console.log(JSON.stringify(post_data));
		console.log(serial_data);

		wir_floc_jq.post( wir_obj.ajax_url, post_data, app.ajax_response, 'json' )
	};

	app.ajax_response = function( response_data ){
		if( response_data.success ){
			console.log(wir_obj.nonce);
			console.log(JSON.stringify(response_data.data));

			if ( response_data.data.type == 'favorite' ) {
				app.$thelink.addClass( 'unfavorite-location' ).removeClass( 'favorite-location' );
				app.$thelink.addClass( 'dashicons-star-filled' ).removeClass( 'dashicons-star-empty' );
			} else {
				app.$thelink.addClass( 'favorite-location' ).removeClass( 'unfavorite-location' );
				app.$thelink.addClass( 'dashicons-star-empty' ).removeClass( 'dashicons-star-filled' );
			}

			wir_obj.nonce = response_data.data.nonce;
			console.log('SUCCESS');
		//	alert( response_data.data.script_response );
		} else {
			console.log('FAILED');
		//	alert( 'ERROR' );
		}
	};

	wir_floc_jq(document).ready( app.init );

	return app;

})( window, document, jQuery );

window.wir_clist_ajax = ( function( window, document, wir_clist_jq ){
	var app = {};

	app.cache = function(){
		app.$ajax_form = wir_clist_jq( 'a.wir_clist' );
	};

	app.init = function(){
		app.cache();
		app.$ajax_form.on( 'click', app.form_handler );
	};

	app.form_handler = function( evt ){
		evt.preventDefault();

		var gid   	= jq( this ).parent().attr( 'id' ),
			l_nonce = jq( this ).attr( 'href' ),
			thelink = jq( this );
			app.$thelink = thelink;

			gid = gid.split( '-' );
			gid = gid[1];

			l_nonce = l_nonce.split( '?_wpnonce=' );
			l_nonce = l_nonce[1].split( '&' );
			l_nonce = l_nonce[0];

		var something = app.$ajax_form.serialize();
		var serialized_data = {
			'gid' : gid,
			'l_nonce' : l_nonce,
		};

		app.post_ajax( serialized_data );
	};

	app.post_ajax = function( serial_data ){
		var post_data = { 
			action     : 'wir_clst_hook',
			nonce      : wir_obj.nonce,
			serialized : serial_data,
		};

		console.log('post_ajax');
		console.log(JSON.stringify(post_data));

		wir_clist_jq.post( wir_obj.ajax_url, post_data, app.ajax_response, 'json' )
	};

	app.ajax_response = function( response_data ){
		if( response_data.success ){
			console.log('ajax_response');
			console.log(wir_obj.nonce);
			console.log(JSON.stringify(response_data.data));
			top.location.replace(response_data.data.redirect);
			wir_obj.nonce = response_data.data.nonce;
			console.log('SUCCESS');
		//	alert( response_data.data.script_response );
		} else {
			console.log('FAILED');
		//	alert( 'ERROR' );
		}
	};

	wir_clist_jq(document).ready( app.init );

	return app;

})( window, document, jQuery );

window.wir_floc_ajax = ( function( window, document, wir_floc_jq ){
	var app = {};

	app.cache = function(){
		app.$ajax_form = wir_floc_jq( 'a.wir_floc' );
	};

	app.init = function(){
		app.cache();
		app.$ajax_form.on( 'click', app.form_handler );
	};

	app.form_handler = function( evt ){
		evt.preventDefault();

		var gid   	= jq( this ).parent().attr( 'id' ),
			l_nonce = jq( this ).attr( 'href' ),
			thelink = jq( this );
			app.$thelink = thelink;

			gid = gid.split( '-' );
			pid = gid[0];
			gid = gid[1];

			l_nonce = l_nonce.split( '?_wpnonce=' );
			l_nonce = l_nonce[1].split( '&' );
			l_nonce = l_nonce[0];

		if ( thelink.hasClass( 'favorite-location' ) || thelink.hasClass( 'unfavorite-location' ) ) {
			if ( thelink.hasClass( 'favorite-location' ) ) {
				var type = 'favorite';
				console.log('favorite');
			} else {
				var type = 'unfavorite';
				console.log('unfavorite');
			}
		
		} else {
			return false;
		}
		var something = app.$ajax_form.serialize();
		var serialized_data = {
			'gid' : gid,
			'l_nonce' : l_nonce,
			'type' : type,
			'pid' : pid,
		};

		app.post_ajax( serialized_data );
	};

	app.post_ajax = function( serial_data ){
		var post_data = { 
			action     : 'wir_floc_hook',
			nonce      : wir_obj.nonce,
			serialized : serial_data,
		};

		console.log(JSON.stringify(post_data));
		console.log(serial_data);

		wir_floc_jq.post( wir_obj.ajax_url, post_data, app.ajax_response, 'json' )
	};

	app.ajax_response = function( response_data ){
		if( response_data.success ){
			console.log(wir_obj.nonce);
			console.log(JSON.stringify(response_data.data));
			wir_obj.nonce = response_data.data.nonce;
			console.log('SUCCESS');
		} else {
			console.log('FAILED');
		}
	};

	wir_floc_jq(document).ready( app.init );

	return app;

})( window, document, jQuery );

window.wir_cloc_ajax = ( function( window, document, wir_cloc_jq ){
	var app = {};

	app.cache = function(){
		app.$ajax_form = wir_cloc_jq( 'a.wir_cloc' );
	};

	app.init = function(){
		app.cache();
		app.$ajax_form.on( 'click', app.form_handler );
	};

	app.form_handler = function( evt ){
		evt.preventDefault();

		var gid   	= jq( this ).parent().attr( 'id' ),
			l_nonce = jq( this ).attr( 'href' ),
			thelink = jq( this );
			app.$thelink = thelink;

			gid = gid.split( '-' );
			pid = gid[0];
			gid = gid[1];

			l_nonce = l_nonce.split( '?_wpnonce=' );
			l_nonce = l_nonce[1].split( '&' );
			l_nonce = l_nonce[0];

		var something = app.$ajax_form.serialize();
		var serialized_data = {
			'gid' : gid,
			'l_nonce' : l_nonce,
			'pid' : pid,
			'something' : something,
		};
		wir_cloc_jq('#yes_id').attr('value', pid);

		app.post_ajax( serialized_data );
	};

	app.post_ajax = function( serial_data ){
		var post_data = { 
			action     : 'wir_cloc_hook',
			nonce      : wir_obj.nonce,
			serialized : serial_data,
		};
		console.log('grapes');

		console.log(JSON.stringify(post_data));
		console.log(serial_data);

		wir_cloc_jq.post( wir_obj.ajax_url, post_data, app.ajax_response, 'json' )
	};

	app.ajax_response = function( response_data ){
		if( response_data.success ){
			console.log('SUCCESS START');

			console.log(wir_obj.nonce);
			console.log(JSON.stringify(response_data.data));
			wir_obj.nonce = response_data.data.nonce;
			console.log('SUCCESS');
		} else {
			console.log('FAILED');
		}
	};

	wir_cloc_jq(document).ready( app.init );

	return app;

})( window, document, jQuery );

window.webdev_ajax = ( function( window, document, wd ){
	var app = {};

	app.cache = function(){
		app.$ajax_form = wd( '#wir_clone_locaton' );
	};

	app.init = function(){
		app.cache();
		app.$ajax_form.on( 'submit', app.form_handler );

	};

	app.form_handler = function( evt ){
		evt.preventDefault();
		var something = app.$ajax_form.serialize();
			gid = wd('#wir_group_select').val();
			pid = wd('#yes_id').val();
		var serialized_data = {
			'gid' : gid,
			'pid' : pid,
			'something' : something,
		};
		app.post_ajax( serialized_data );
	};

	app.post_ajax = function( serial_data ){
		var post_data = { 
			action     : 'webdev',
			nonce      : wir_obj.nonce,
			serialized : serial_data,
		};
		console.log('post this');
		console.log(JSON.stringify(post_data));

		wd.post( wir_obj.ajax_url, post_data, app.ajax_response, 'json' )
	};

	app.ajax_response = function( response_data ){

		if( response_data.success ){
			wir_obj.nonce = response_data.data.nonce;
			console.log('Response');
			console.log(JSON.stringify(response_data.data));
			top.location.replace(response_data.data.redirect);
		//	alert( response_data.data.script_response );
		} else {
		//	alert( 'ERROR' );
		}
	};


	wd(document).ready( app.init );

	return app;

})( window, document, jQuery );