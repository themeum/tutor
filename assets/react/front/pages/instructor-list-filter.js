jQuery(document).ready(function($) {
    /**
     * 
     * Instructor list filter
     * 
     * @since  v.1.8.4
    */
    // Get values on course category selection
    $('.tutor-instructor-filter').each(function() {

        var root = $(this);
        var filter_args = {}; 
        var time_out;

        function run_instructor_filter(name, value, page_number) {

            // Prepare http payload
            var result_container = root.find('.filter-result-container');
            var html_cache = result_container.html();
            var attributes = root.data();
            attributes.current_page = page_number || 1;

            name ? filter_args[name] = value : filter_args = {};
            filter_args.attributes = attributes;
            filter_args.action = 'load_filtered_instructor';
            
            // Show loading icon
            result_container.html('<div style="text-align:center"><img src="'+window._tutorobject.loading_icon_url+'"/></div>');

            $.ajax({
                url: window._tutorobject.ajaxurl,
                data: filter_args,
                type: 'POST',
                success: function(r) {
                    result_container.html(r);
                },
                error: function() {
                    result_container.html(html_cache);
                    tutor_toast('Failed', 'Request Error', 'error');
                }
            })
        }

        root.on('change', '.course-category-filter [type="checkbox"]', function() {

            var values = {};

            $(this).closest('.course-category-filter').find('input:checked').each(function() {
                values[$(this).val()] = $(this).parent().text();
            });

            // Show selected cat list
            var cat_parent = root.find('.selected-cate-list').empty();
            var cat_ids = Object.keys(values);

            cat_ids.forEach(function(value) {
                cat_parent.append('<span>'+values[value]+' <span class="tutor-icon-line-cross" data-cat_id="'+value+'"></span></span>');
            });

            cat_ids.length ? cat_parent.append('<span data-cat_id="0">Clear All</span>') : 0;

            run_instructor_filter($(this).attr('name'), cat_ids);
        })
        .on('click', '.tutor-instructor-ratings i', function(e) {
            const rating = e.target.dataset.value;
            run_instructor_filter('rating_filter', rating);
        })
        .on('change', "#tutor-instructor-relevant-sort", function(e) {
            const short_by = e.target.value;
            run_instructor_filter('short_by', short_by);
        })
        .on('click', '.selected-cate-list [data-cat_id]', function() {

            var id = $(this).data('cat_id');
            var inputs = root.find('.mobile-filter-popup [type="checkbox"]');
            id ? inputs = inputs.filter('[value="'+id+'"]') : 0;
            
            inputs.prop('checked', false).trigger('change');
        })
        .on('input', '.filter-pc [name="keyword"]', function() {
            // Get values on search keyword change
            
            var val = $(this).val();

            time_out ? window.clearTimeout(time_out) : 0;

            time_out = window.setTimeout(function() {

                run_instructor_filter('keyword', val);
                time_out = null;

            }, 500);
        })
        .on('click', '[data-page_number]', function(e) {

            // On pagination click
            e.preventDefault();
            
            run_instructor_filter(null, null, $(this).data( 'page_number' ) );

        }).on('click', '.clear-instructor-filter', function() {
           
            // Clear filter
            var root = $(this).closest('.tutor-instructor-filter');
            
            root.find('input[type="checkbox"]').prop('checked', false);

            root.find('[name="keyword"]').val('');
            const stars = document.querySelectorAll(".tutor-instructor-ratings i");
            //remove star selection
            for (let star of stars) {
                if (star.classList.contains('active')) {
                    star.classList.remove('active');
                }
                if (star.classList.contains('ttr-star-full-filled')) {
                    star.classList.remove('ttr-star-full-filled');
                    star.classList.add('ttr-star-line-filled');
                }
            }
            rating_range.innerHTML = ``;
            
            run_instructor_filter();
        })
        .on('click', '.mobile-filter-container i', function () {
            // Open mobile screen filter
            $(this).parent().next().addClass('is-opened');
        })
        .on('click', '.mobile-filter-popup button', function() {
            
            $('.mobile-filter-popup [type="checkbox"]').trigger('change');
            
            // Close mobile screen filter
            $(this).closest('.mobile-filter-popup').removeClass('is-opened');

        }).on('input', '.filter-mobile [name="keyword"]', function() {

            // Sync keyword with two screen
            
            root.find('.filter-pc [name="keyword"]').val($(this).val()).trigger('input');

        }).on('change', '.mobile-filter-popup [type="checkbox"]', function(e) {

            if(e.originalEvent) {
                return;
            }

            // Sync category with two screen
            var name = $(this).attr('name');
            var val = $(this).val();
            var checked = $(this).prop('checked');

            root.find('.course-category-filter [name="'+name+'"]').filter('[value="'+val+'"]').prop('checked', checked).trigger('change');
        
        }).on('mousedown touchstart', '.expand-instructor-filter', function(e) {
            
            var window_height = $(window).height();
            var el = root.find('.mobile-filter-popup>div');
            var el_top = window_height-el.height();
            var plus = ((e.originalEvent.touches || [])[0] || e).clientY - el_top;

            root.on('mousemove touchmove', function(e){

                var y = ((e.originalEvent.touches || [])[0] || e).clientY;

                var height = (window_height-y)+plus;
                
                (height>200 && height<=window_height) ? el.css('height', height+'px') : 0;
            });
        
        }).on('mouseup touchend', function(){

            root.off('mousemove touchmove');
        })
        .on('click', '.mobile-filter-popup>div', function(e) {
            e.stopImmediatePropagation();
        }).on('click', '.mobile-filter-popup', function(e) {
            $(this).removeClass('is-opened');
        }).on('click', '.tutor-instructor-category-show-more > .text-medium-caption', function(e) {
            //show more @since v2.0.0
            let term_id = e.target.parentNode.dataset.id;
            console.log(e.target.tagName)
            console.log(term_id)
            $.ajax({
                url: window._tutorobject.ajaxurl,
                type: 'POST',
                data: {action: 'show_more', term_id: term_id},
                beforeSend: function() {
                    $(".tutor-show-more-loading").html(`<img src='${window._tutorobject.loading_icon_url}'>`);
                },
                success: function(response) {
                    console.log(response)
                    if (response.success && response.data.categories.length) {
                        $(".tutor-instructor-category-show-more").css("display", "block");
                        for (let res of response.data.categories) {
                            const wrapper = $(".tutor-instructor-categories-wrapper .course-category-filter");
                          
                            $(".tutor-instructor-category-show-more .text-medium-caption").attr('data-id', res.term_id);
                            wrapper.append(
                                `<div class="tutor-form-check tutor-mb-25">
                                    <input
                                        id="item-a"
                                        type="checkbox"
                                        class="tutor-form-check-input tutor-form-check-square"
                                        name="category"
                                        value="${res.term_id}"/>
                                    <label for="item-a">
                                        ${res.name}
                                    </label>
                                </div>
                                `
                            );
                        }
                    }
                    if (false === response.data.show_more) {
                        $(".tutor-instructor-category-show-more").css("display", "none");
                        if (document.querySelector(".course-category-filter").classList.contains('tutor-show-more-blur')) {
                            document.querySelector(".course-category-filter").classList.remove("tutor-show-more-blur");
                        }
                    }
                },
                complete: function() {
                    $(".tutor-show-more-loading").html(``);
                },
                error: function(err) {
                    alert(err)
                }
            }); 
        })
    });

    /**
     * Show start active as per click
     * 
     * @since v2.0.0
     */
    const stars = document.querySelectorAll(".tutor-instructor-ratings i");
    const rating_range = document.querySelector(".tutor-instructor-rating-filter"); 
    for(let star of stars) {
        star.onclick = (e) => {
            //remove active if has
            for (let star of stars) {
                if (star.classList.contains('active')) {
                    star.classList.remove('active');
                }
                if (star.classList.contains('ttr-star-full-filled')) {
                    star.classList.remove('ttr-star-full-filled');
                    star.classList.add('ttr-star-line-filled');
                }
            }
            //show stars active as click
            const length = e.target.dataset.value;
            for (let i = 0; i < length; i++) {
                stars[i].classList.add('active');
                stars[i].classList.remove('ttr-star-line-filled');
                stars[i].classList.add('ttr-star-full-filled');
            }
            rating_range.innerHTML = `0.0 - ${length}.0`;
        }
    }
});