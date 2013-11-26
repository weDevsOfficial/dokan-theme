(function($){

    var variantsHolder = $('#variants-holder');
    var product_gallery_frame;
    var $image_gallery_ids = $('#product_image_gallery');
    var $product_images = $('#product_images_container ul.product_images');

    var Dokan_Editor = {

        /**
         * Constructor function
         */
        init: function() {

            product_type = 'simple';

            $('.product-edit-container').on('click', '._discounted_price', this.showDiscount);
            $('.product-edit-container').on('click', 'a.sale-schedule', this.showDiscountSchedule);
            $('.product-edit-container').on('click', '#_downloadable', this.downloadable);
            $('.product-edit-container').on('change', '#_product_type', this.onChangeProductType);

            // variants
            $('#product-attributes').on('click', '.add-variant-category', this.variants.addCategory);
            $('#variants-holder').on('click', '.box-header .row-remove', this.variants.removeCategory);

            $('#variants-holder').on('click', '.item-action a.row-add', this.variants.addItem);
            $('#variants-holder').on('click', '.item-action a.row-remove', this.variants.removeItem);

            // save attributes
            $('.save_attributes').on('click', this.variants.save);

            // gallery
            $('#dokan-product-images').on('click', 'a.add-product-images', this.gallery.addImages);
            $('#dokan-product-images').on( 'click', 'a.delete', this.gallery.deleteImage);
            this.gallery.sortable();

            $('.product-edit-container').on('click', 'a.dokan-feat-image-btn', this.featuredImage);

        },

        /**
         * Show hide product discount
         */
        showDiscount: function() {
            var self = $(this),
                checked = self.is(':checked'),
                container = $('.special-price-container');

            if (checked) {
                container.removeClass('dokan-hide');
            } else {
                container.addClass('dokan-hide');
            }
        },

        /**
         * Show/hide discount schedule
         */
        showDiscountSchedule: function(e) {
            e.preventDefault();

            $('.sale-schedule-container').slideToggle('fast');
        },

        onChangeProductType: function() {
            var selected = $('#_product_type').val();

            // console.log(selected);

            if ( selected === 'simple' ) {
                product_type = 'simple';
                $('aside.downloadable').removeClass('dokan-hide');
                $('.show_if_variable').addClass('dokan-hide');

            } else {
                // this is a variable type product
                product_type = 'variable';
                $('aside.downloadable').addClass('dokan-hide');
                $('.show_if_variable').removeClass('dokan-hide');
            }
        },

        downloadable: function() {
            if ( $(this).prop('checked') ) {
                $(this).closest('aside').find('.dokan-side-body').removeClass('dokan-hide');
            } else {
                $(this).closest('aside').find('.dokan-side-body').addClass('dokan-hide');
            }
        },

        variants: {
            addCategory: function (e) {
                e.preventDefault();

                var row = $('.inputs-box').length ;
                console.log(row);
                var category = _.template( $('#tmpl-sc-category').html(), { row: row } );
                variantsHolder.append(category).children(':last').hide().fadeIn();
            },

            removeCategory: function (e) {
                e.preventDefault();

                if ( confirm('Sure?') ) {
                    $(this).parents('.inputs-box').fadeOut(function() {
                        $(this).remove();
                    });
                }
            },

            addItem: function (e) {
                e.preventDefault();

                var self = $(this),
                    wrap = self.closest('.inputs-box'),
                    list = self.closest('ul.option-couplet');

                var col = list.find('li').length,
                    row = wrap.data('count');


                var template = _.template( $('#tmpl-sc-category-item').html() );
                self.closest('li').after(template({'row': row, 'col': col}));
            },

            removeItem: function (e) {
                e.preventDefault();

                var options = $(this).parents('ul').find('li');

                // don't remove if only one option is there
                if ( options.length > 1 ) {
                    $(this).parents('li').fadeOut(function() {
                        $(this).remove();
                    });
                }
            },

            save: function() {

                var data = {
                    post_id: 36,
                    data:  $('.woocommerce_attributes').find('input, select, textarea').serialize(),
                    action:  'dokan_save_attributes'
                };

                var this_page = window.location.toString();

                // $('#variants-holder').block({ message: 'saving...' });
                $('#variants-holder').block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });
                $.post(ajaxurl, data, function(resp) {

                    $('#variable_product_options').block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });
                    $('#variable_product_options').load( this_page + ' #variable_product_options_inner', function() {
                        $('#variable_product_options').unblock();
                    } );

                    // fire change events for varaiations
                    $('input.variable_is_downloadable, input.variable_is_virtual').trigger('change');

                    $('#variants-holder').unblock();
                });
            }
        },

        gallery: {

            addImages: function(e) {
                e.preventDefault();

                var attachment_ids = $image_gallery_ids.val();

                if ( product_gallery_frame ) {
                    product_gallery_frame.open();
                    return;
                }

                // Create the media frame.
                product_gallery_frame = wp.media.frames.downloadable_file = wp.media({
                    // Set the title of the modal.
                    title: 'Add Images to Product Gallery',
                    button: {
                        text: 'Add to gallery',
                    },
                    multiple: true
                });

                // When an image is selected, run a callback.
                product_gallery_frame.on( 'select', function() {

                    var selection = product_gallery_frame.state().get('selection');

                    selection.map( function( attachment ) {

                        attachment = attachment.toJSON();

                        if ( attachment.id ) {
                            attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;

                            $product_images.append('\
                                <li class="image" data-attachment_id="' + attachment.id + '">\
                                    <img src="' + attachment.url + '" />\
                                    <ul class="actions">\
                                        <li><a href="#" class="delete" title="Delete image">Delete</a></li>\
                                    </ul>\
                                </li>');
                        }

                    } );

                    $image_gallery_ids.val( attachment_ids );
                });

                product_gallery_frame.open();
            },

            deleteImage: function(e) {
                e.preventDefault();

                $(this).closest('li.image').remove();

                var attachment_ids = '';

                $('#product_images_container ul li.image').css('cursor','default').each(function() {
                    var attachment_id = $(this).attr( 'data-attachment_id' );
                    attachment_ids = attachment_ids + attachment_id + ',';
                });

                $image_gallery_ids.val( attachment_ids );

                return false;
            },

            sortable: function() {
                // Image ordering
                $product_images.sortable({
                    items: 'li.image',
                    cursor: 'move',
                    scrollSensitivity:40,
                    forcePlaceholderSize: true,
                    forceHelperSize: false,
                    helper: 'clone',
                    opacity: 0.65,
                    placeholder: 'dokan-sortable-placeholder',
                    start:function(event,ui){
                        ui.item.css('background-color','#f6f6f6');
                    },
                    stop:function(event,ui){
                        ui.item.removeAttr('style');
                    },
                    update: function(event, ui) {
                        var attachment_ids = '';

                        $('#product_images_container ul li.image').css('cursor','default').each(function() {
                            var attachment_id = jQuery(this).attr( 'data-attachment_id' );
                            attachment_ids = attachment_ids + attachment_id + ',';
                        });

                        $image_gallery_ids.val( attachment_ids );
                    }
                });
            }

        },

        featuredImage: function(e) {
            e.preventDefault();

            var product_featured_frame = false;

            if ( product_featured_frame ) {
                product_featured_frame.open();
                return;
            }

            product_featured_frame = wp.media({
                // Set the title of the modal.
                title: 'Upload featured image',
                button: {
                    text: 'Set featured image',
                }
            });

            product_featured_frame.on('select', function() {
                var selection = product_featured_frame.state().get('selection');

                selection.map( function( attachment ) {
                    attachment = attachment.toJSON();

                    console.log(attachment);

                });
            });

            product_featured_frame.open();
        }
    };

    // On DOM ready
    $(function() {
        Dokan_Editor.init();

        $('#_product_type').trigger('change');
    });

})(jQuery);