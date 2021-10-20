window.jQuery(document).ready($ => {

    /**
     * Profile Photo and Cover Photo editor
     * 
     * @since  v.1.7.5
    */
     var PhotoEditor=function(photo_editor){

        this.dialogue_box = photo_editor.find('#tutor_photo_dialogue_box');

        this.open_dialogue_box = function(name){
            this.dialogue_box.attr('name', name);
            this.dialogue_box.trigger('click');
        }

        this.validate_image = function(file){
            return true;
        }

        this.upload_selected_image = function(name, file){
            if(!file || !this.validate_image(file)){
                return;
            }

            var nonce = tutor_get_nonce_data(true);

            var context = this;
            context.toggle_loader(name, true);

            // Prepare payload to upload
            var form_data = new FormData();
            form_data.append('action', 'tutor_user_photo_upload');
            form_data.append('photo_type', name);
            form_data.append('photo_file', file, file.name);
            form_data.append(nonce.key, nonce.value);
            
            $.ajax({
                url:window._tutorobject.ajaxurl,
                data:form_data,
                type:'POST',
                processData: false,
                contentType: false,
                error:context.error_alert,
                complete:function(){
                    context.toggle_loader(name, false);
                }
            })
        }

        this.accept_upload_image=function(context, e){
            var file = e.currentTarget.files[0] || null;
            context.update_preview(e.currentTarget.name, file);
            context.upload_selected_image(e.currentTarget.name, file);
            $(e.currentTarget).val('');
        }

        this.delete_image=function(name){
            var context = this;
            context.toggle_loader(name, true);
            
            $.ajax({
                url:window._tutorobject.ajaxurl,
                data:{action:'tutor_user_photo_remove', photo_type:name},
                type:'POST',
                error:context.error_alert,
                complete:function(){
                    context.toggle_loader(name, false);
                }
            });
        }

        this.update_preview=function(name, file){
            var renderer = photo_editor.find(name=='cover_photo' ? '#tutor_cover_area' : '#tutor_profile_area');

            if(!file){
                renderer.css('background-image', 'url('+renderer.data('fallback')+')');
                this.delete_image(name);
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                renderer.css('background-image', 'url('+e.target.result+')');
            }
            
            reader.readAsDataURL(file); 
        }

        this.toggle_profile_pic_action=function(show){
            var method = show===undefined ? 'toggleClass' : (show ? 'addClass' : 'removeClass');
            photo_editor[method]('pop-up-opened');
        }

        this.error_alert=function(){
            alert('Something Went Wrong.');
        }

        this.toggle_loader = function(name, show){
            photo_editor.find('#tutor_photo_meta_area .loader-area').css('display', (show ? 'block' : 'none'));
        }

        this.initialize = function(){
            var context = this;

            this.dialogue_box.change(function(e){context.accept_upload_image(context, e)});

            photo_editor.find('#tutor_profile_area .tutor_overlay, #tutor_pp_option>div:last-child').click(function(){context.toggle_profile_pic_action()});

            // Upload new
            photo_editor.find('.tutor_cover_uploader').click(function(){context.open_dialogue_box('cover_photo')});
            photo_editor.find('.tutor_pp_uploader').click(function(){context.open_dialogue_box('profile_photo')});

            // Delete existing
            photo_editor.find('.tutor_cover_deleter').click(function(){context.update_preview('cover_photo', null)});
            photo_editor.find('.tutor_pp_deleter').click(function(){context.update_preview('profile_photo', null)});
        }
    }

    var photo_editor = $('#tutor_profile_cover_photo_editor');
    photo_editor.length>0 ? new PhotoEditor(photo_editor).initialize() : 0;
});