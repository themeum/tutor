document.addEventListener('DOMContentLoaded', () => {
    var $ = window.jQuery;
    
    /**
     * Withdraw Form Tab/Toggle
     *
     * @since v.1.1.2
     */

     $('.tutor-dashboard-setting-withdraw input[name="tutor_selected_withdraw_method"]').on('change', function (e) {
        var $that = $(this);
        var form = $that.closest('form');
        form.find('.withdraw-method-form').hide();
        form.find('.withdraw-method-form').hide().filter('[data-withdraw-form="'+$that.val()+'"]').show();
    });
});
    

