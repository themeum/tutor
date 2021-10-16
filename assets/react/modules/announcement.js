function urlPrams(type, val){

    var url = new URL(window.location.href);
    var search_params = url.searchParams;
    search_params.set(type, val);
    
    url.search = search_params.toString();
    if ( _tutorobject.is_admin ) {
        search_params.set('paged', 1)
    } else {
        search_params.set('current_page', 1);
    }
    url.search = search_params.toString();

    return url.toString();
}

window.jQuery(document).ready($=>{
    const {__} = window.wp.i18n;

    //create announcement
    $(".tutor-announcements-form").on('submit',function(e){
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        var formData = $btn.closest(".tutor-announcements-form").serialize();
        
        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : formData,
            beforeSend: function() {
                $btn.addClass('tutor-updating-message');
            },
            success: function(data) {
                
                if(!data.success) {
                    const {message=__('Something Went Wrong!', 'tutor')} = data.data || {};
                    tutor_toast(__('Error!', 'tutor'), message, 'error');
                    return;
                }
                
                location.reload();         
            },
            complete:function() {
                $btn.removeClass('tutor-updating-message');
            },
            error: function(data){
                tutor_toast(__('Something Went Wrong!', 'tutor'));
            }
        });
    });

    // Delete announcement
    $('.tutor-announcement-delete').click(function(){
        var announcement_id = $(this).data('announcement-id');
        var whichtr = $("#"+$(this).data('target-announcement-row-id'));
        
        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {
                action: 'tutor_announcement_delete',
                announcement_id: announcement_id
            },
            beforeSend: function() {
            
            },
            success: function(data) {
                const {message=__('Something Went Wrong!', 'tutor')} = data.data || {};
                
                if(data.success) {
                    whichtr.remove();
                    tutor_toast('Success!', message, 'success');
                    return;
                } else {
                    tutor_toast('Error!', message, 'error');
                }
            },
            error: function(){
                tutor_toast('Error!', __('Something Went Wrong!', 'tutor'), 'error');
            }
        });
    });

    // Announcement filter
    $('.tutor-announcement-course-sorting').on('change', function(e){
        window.location = urlPrams( 'course-id', $(this).val() );
    });
    $('.tutor-announcement-order-sorting').on('change', function(e){
        window.location = urlPrams( 'order', $(this).val() );
    });
    $('.tutor-announcement-date-sorting').on('change', function(e){
        window.location = urlPrams( 'date', $(this).val() );
    });
    $('.tutor-announcement-search-sorting').on('click', function(e){
        window.location = urlPrams( 'search', $(".tutor-announcement-search-field").val() );
    });
})