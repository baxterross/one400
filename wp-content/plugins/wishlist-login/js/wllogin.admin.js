jQuery(function($) {
	//initialize flowplayers
	$f('div.flowplayer',
		{
			src: "http://wishlist-products.s3.amazonaws.com/videos/flowplayer.commercial-full.swf",
			wmode: "opaque"
		},
		{
			key: "#$e340057b90b6a72a92e",
			clip: {
				autoPlay: true,
				autoBuffering: true
			}
	});
});

jQuery(document).ready(function($){
     if( typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function' ){
		 var options = {
			defaultColor: '#262626',
			palettes: [
				'#000000',
				'#387edc',
				'#00b2cb',
				'#9ad80b',
				'#199200',
				'#fc7f01',
				'#8b2bb1',
				'#d910d8',
				'#c90000',
				'#efec00'
			]
		 };
		 
		 jQuery( '.wllogin_colorpicker' ).wpColorPicker(options);
    } else {
        jQuery( '.wllogin_colorpicker_trigger' ).farbtastic( '.wllogin_colorpicker' );
    }
});
