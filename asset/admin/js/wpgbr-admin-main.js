let adminWpgbr = (function($, window, undefined) {
	let adminWpgbr = {};
	$( document ).ready(
		function() {
			$( '.wpgbr-nav-tab-wrapper > a' ).on(
				'click',
				function(e) {
					e.preventDefault();
					if ( $( this ).hasClass( 'tab1' ) ) {
						$( this ).addClass( 'nav-tab-active' );
						$( '#wpgbr-general' ).show();
						$( '#wpgbr-google' ).hide();
						$( '.tab2' ).removeClass( 'nav-tab-active' );
					} else {
						$( this ).addClass( 'nav-tab-active' );
						$( '#wpgbr-general' ).hide();
						$( '#wpgbr-google' ).show();
						$( '.tab1' ).removeClass( 'nav-tab-active' );
					}
				}
			);

			$( '#connect_google' ).on(
				'click',
				function(e) {
					e.preventDefault();
					let feedbackElem = $('.admin-feedback');
					feedbackElem.text('').addClass('hide');
					$.ajax(
						{
							type : "POST",
							dataType : "json",
							cache: false,

							url : wpgbr_ajax_object.ajax_url,
							data : {
								action: "google_connect",
							},//data,
							success: function(response) {
								console.log( response );
								feedbackElem.text(response.msg).removeClass('hide');
							},
							error: function(response) {
								console.log( response );
							}
						}
					);
				}
			);
		}
	);

	return adminWpgbr;
})( jQuery, window );

