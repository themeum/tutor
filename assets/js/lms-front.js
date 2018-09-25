jQuery(document).ready(function($){
    'use strict';

    $(document).on('change', '.lms-course-filter-form', function(e){
        e.preventDefault();
        $(this).closest('form').submit();
    });

    const videoPlayer = {
        track_player : function(){
            var that = this;
            if (typeof Plyr !== 'undefined') {
                const player = new Plyr('#lmsPlayer');

                player.on('ready', function(event){
                    const instance = event.detail.plyr;

                    if (_lmsobject.best_watch_time > 0) {
                        instance.media.currentTime = _lmsobject.best_watch_time;
                    }
                });

                var tempTimeNow = 0;
                var intervalSeconds = 60; //Send to lms backend about video playing time in this interval
                player.on('timeupdate', function(event){
                    const instance = event.detail.plyr;

                    var tempTimeNowInSec = (tempTimeNow / 4); //timeupdate firing 250ms interval
                    if (tempTimeNowInSec >= intervalSeconds){
                        that.sync_time(instance);
                        tempTimeNow = 0;
                    }

                    tempTimeNow++;
                });


                //TODO: fire sync_time() when video end
            }
        },

        sync_time: function(instance){
            /**
             * LMS is sending about video playback information to server.
             *
             */

            var nonce_key = _lmsobject.nonce_key;
            var data = {action: 'sync_video_playback', currentTime : instance.currentTime, post_id : _lmsobject.post_id};
            data[nonce_key] = _lmsobject[nonce_key];

            $.post(_lmsobject.ajaxurl, data);

        },


        init: function(){
            this.track_player();
        }
    };


    /**
     * Fire LMS video
     *
     * @since v.1.0.0
     */
    videoPlayer.init();

});

