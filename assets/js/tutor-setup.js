jQuery(document).ready(function($){
    'use strict';


    if ($('.tutor-splash-video').length) {

        console.log('GG');

		// Define the controls
		var plyr_options = {
	        autoplay: true,
			clickToPlay: true,
			controls: []
	    };

		var players =  plyr.setup(plyr_options);

		players[0].on('pause', function(event) {
		  console.log("test");
		});

    }
    


});