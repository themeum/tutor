jQuery(document).ready(function($){
    'use strict';

    $(document).on('change', '.lms-course-filter-form', function(e){
        e.preventDefault();
        $(this).closest('form').submit();
    });

    const videoPlayer = {
        nonce_key : _lmsobject.nonce_key,
        track_player : function(){
            var that = this;
            if (typeof Plyr !== 'undefined') {
                const player = new Plyr('#lmsPlayer');

                player.on('ready', function(event){
                    const instance = event.detail.plyr;
                    if (_lmsobject.best_watch_time > 0) {
                        instance.media.currentTime = _lmsobject.best_watch_time;
                    }
                    that.sync_time(instance);
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

                player.on('ended', function(event){
                    const instance = event.detail.plyr;

                    var data = {is_ended:true};
                    that.sync_time(instance, data)
                });
            }
        },
        sync_time: function(instance, options){
            /**
             * LMS is sending about video playback information to server.
             */
            var data = {action: 'sync_video_playback', currentTime : instance.currentTime, duration:instance.duration,  post_id : _lmsobject.post_id};
            data[this.nonce_key] = _lmsobject[this.nonce_key];

            var data_send = data;

            if(options){
                data_send = Object.assign(data, options);
            }
            $.post(_lmsobject.ajaxurl, data_send);
        },
        init: function(){
            this.track_player();
        }
    };

    /**
     * Fire LMS video
     * @since v.1.0.0
     */
    videoPlayer.init();
});

