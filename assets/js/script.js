jQuery(function($) {
    // $('button.single_add_to_cart_button').removeClass('button').addClass('btn btn-danger');
    // $('a.button').removeClass('button').addClass('btn btn-danger');

    $('ul.dropdown-menu li.dropdown').hover(function() {
        $(this).addClass('open');
    }, function() {
        $(this).removeClass('open');
    });

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $('.tips').tooltip();

    // set dashboard menu height
    var dashboardMenu = $('ul.dokan-dashboard-menu'),
        contentArea = $('#content article');

    if ( contentArea.height() > dashboardMenu.height() ) {
        dashboardMenu.css({ height: contentArea.height() });
    }

    // cat drop stack, disable parent anchors if has children
    if ( $(window).width() < 767) {
        $('#cat-drop-stack li.has-children').on('click', '> a', function(e) {
            e.preventDefault();

            $(this).siblings('.sub-category').slideToggle('fast');
        });
    } else {
        $('#cat-drop-stack li.has-children > .sub-category').each(function(index, el) {
            var sub_cat = $(el);
            var length = sub_cat.find('.sub-block').length;

            if ( length == 3 ) {
                sub_cat.css('width', '260%');
            } else if ( length > 3) {
                sub_cat.css('width', '340%');
            }
        });
    }

    // tiny helper function to add breakpoints
    function getGridSize() {
        return (window.innerWidth < 600) ? 2 : (window.innerWidth < 900) ? 3 : 4;
    }

    $('.product-sliders').flexslider({
        animation: "slide",
        animationLoop: false,
        itemWidth: 190,
        itemMargin: 10,
        controlNav: false,
        minItems: getGridSize(),
        maxItems: getGridSize()
    });

    function showTooltip(x, y, contents) {
        jQuery('<div class="chart-tooltip">' + contents + '</div>').css({
            top: y - 16,
            left: x + 20
        }).appendTo("body").fadeIn(200);
    }

    var prev_data_index = null;
    var prev_series_index = null;

    jQuery(".chart-placeholder").bind("plothover", function(event, pos, item) {
        if (item) {
            if (prev_data_index != item.dataIndex || prev_series_index != item.seriesIndex) {
                prev_data_index = item.dataIndex;
                prev_series_index = item.seriesIndex;

                jQuery(".chart-tooltip").remove();

                if (item.series.points.show || item.series.enable_tooltip) {

                    var y = item.series.data[item.dataIndex][1];

                    tooltip_content = '';

                    if (item.series.prepend_label)
                        tooltip_content = tooltip_content + item.series.label + ": ";

                    if (item.series.prepend_tooltip)
                        tooltip_content = tooltip_content + item.series.prepend_tooltip;

                    tooltip_content = tooltip_content + y;

                    if (item.series.append_tooltip)
                        tooltip_content = tooltip_content + item.series.append_tooltip;

                    if (item.series.pie.show) {

                        showTooltip(pos.pageX, pos.pageY, tooltip_content);

                    } else {

                        showTooltip(item.pageX, item.pageY, tooltip_content);

                    }

                }
            }
        } else {
            jQuery(".chart-tooltip").remove();
            prev_data_index = null;
        }
    });

    $('body').on('added_to_cart', function(event, data) {
        $('i.fa-shopping-cart').removeClass('fa-spin');

        $('.dokan-cart-amount-top > .amount').fadeOut( 'fast', function(){
            $('.dokan-cart-amount-top > .amount').html(data.amount).fadeIn('fast');
        });
    });

    $('body').on('adding_to_cart', function(e, button) {
        $(button).children('i').addClass('fa-spin');
    });

});

// Dokan Register

jQuery(function($) {
    $('.user-role input[type=radio]').on('change', function() {
        var value = $(this).val();

        if ( value === 'seller') {
            $('.show_if_seller').slideDown();
        } else {
            $('.show_if_seller').slideUp();
        }
    });

    $('#company-name').on('focusout', function() {
        var value = $(this).val().toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        $('#seller-url').val(value);
        $('#url-alart').text( value );
        $('#seller-url').focus();
    });

    $('#seller-url').keydown(function(e) {
        var text = $(this).val();

        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 109, 110, 173, 189, 190]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                return;
        }

        if ((e.shiftKey || (e.keyCode < 65 || e.keyCode > 90) && (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105) ) {
            e.preventDefault();
        }
    });

    $('#seller-url').keyup(function(e) {
        $('#url-alart').text( $(this).val() );
    });

    $('#shop-phone').keydown(function(e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 107, 109, 110, 187, 189, 190]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }

        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    $('#seller-url').on('focusout', function() {
        var self = $(this),
        data = {
            action : 'shop_url',
            url_slug : self.val(),
            _nonce : dokan.nonce,
        };

        if ( self.val() === '' ) {
            return;
        }

        var row = self.closest('.form-row');
        row.block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

        $.post( dokan.ajaxurl, data, function(resp) {

            if ( resp == 0){
                $('#url-alart').removeClass('text-success').addClass('text-danger');
                $('#url-alart-mgs').removeClass('text-success').addClass('text-danger').text(dokan.seller.notAvailable);
            } else {
                $('#url-alart').removeClass('text-danger').addClass('text-success');
                $('#url-alart-mgs').removeClass('text-danger').addClass('text-success').text(dokan.seller.available);
            }

            row.unblock();

        } );

    });
});

//dokan settings

(function($) {

    $.validator.setDefaults({ ignore: ":hidden" });

    var validatorError = function(error, element) {
        var form_group = $(element).closest('.form-group');
        form_group.addClass('has-error').append(error);
    };

    var validatorSuccess = function(label, element) {
        $(element).closest('.form-group').removeClass('has-error');
    };

    var Dokan_Settings = {
        init: function() {
            var self = this;

            //image upload
            $('a.dokan-banner-drag').on('click', this.imageUpload);
            $('a.dokan-remove-banner-image').on('click', this.removeBanner);

            $('a.dokan-gravatar-drag').on('click', this.gragatarImageUpload);
            $('a.dokan-remove-gravatar-image').on('click', this.removeGravatar);

            this.validateForm(self);

            return false;
        },


        imageUpload: function() {
            var file_frame,
                self = $(this);

            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: jQuery( this ).data( 'uploader_title' ),
                button: {
                    text: jQuery( this ).data( 'uploader_button_text' )
                },
                multiple: false
            });

            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();

                var wrap = self.closest('.dokan-banner');
                wrap.find('input.dokan-file-field').val(attachment.id);
                wrap.find('img.dokan-banner-img').attr('src', attachment.url);
                $('.image-wrap', wrap).removeClass('dokan-hide');

                $('.button-area').addClass('dokan-hide');
            });

            // Finally, open the modal
            file_frame.open();

        },
        gragatarImageUpload: function() {
            var file_frame,
                self = $(this);

            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: jQuery( this ).data( 'uploader_title' ),
                button: {
                    text: jQuery( this ).data( 'uploader_button_text' )
                },
                multiple: false
            });

            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();

                var wrap = self.closest('.dokan-gravatar');
                wrap.find('input.dokan-file-field').val(attachment.id);
                wrap.find('img.dokan-gravatar-img').attr('src', attachment.url);
                $('.gravatar-wrap', wrap).removeClass('dokan-hide');
                $('.gravatar-button-area').addClass('dokan-hide');
            });

            // Finally, open the modal
            file_frame.open();

        },

        submitSettings: function() {

            var self = $( "form#settings-form" ),
                form_data = self.serialize() + '&action=dokan_settings';


            self.find('.ajax_prev').append('<span class="dokan-loading"> </span>');
            $.post(dokan.ajaxurl, form_data, function(resp) {

               self.find('span.dokan-loading').remove();
                $('html,body').animate({scrollTop:100});

                if ( resp.success ) {

                    $('.dokan-ajax-response').html( $('<div/>', {
                        'class': 'alert alert-success',
                        'html': '<p>' + resp.data + '</p>'
                    }) );

                } else {

                    $('.dokan-ajax-response').html( $('<div/>', {
                        'class': 'alert alert-danger',
                        'html': '<p>' + resp.data + '</p>'
                    }) );
                }
            });
        },

        validateForm: function(self) {

            $("form#settings-form").validate({
                //errorLabelContainer: '#errors'
                submitHandler: function(form) {
                    self.submitSettings();
                },
                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: validatorError,
                success: validatorSuccess
            });

        },

        removeBanner: function(e) {
            e.preventDefault();

            var self = $(this);
            var wrap = self.closest('.image-wrap');
            var instruction = wrap.siblings('.button-area');

            wrap.find('input.dokan-file-field').val('0');
            wrap.addClass('dokan-hide');
            instruction.removeClass('dokan-hide');
        },

        removeGravatar: function(e) {
            e.preventDefault();

            var self = $(this);
            var wrap = self.closest('.gravatar-wrap');
            var instruction = wrap.siblings('.gravatar-button-area');

            wrap.find('input.dokan-file-field').val('0');
            wrap.addClass('dokan-hide');
            instruction.removeClass('dokan-hide');
        },
    };

    var Dokan_Withdraw = {

        init: function() {
            var self = this;

            this.withdrawValidate(self);
        },

        withdrawValidate: function(self) {
            $('form.withdraw').validate({
                //errorLabelContainer: '#errors'

                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: validatorError,
                success: validatorSuccess
            })
        }
    };

    var Dokan_Coupons = {
        init: function() {
            var self = this;
            this.couponsValidation(self);
        },

        couponsValidation: function(self) {
            $("form.coupons").validate({
                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: validatorError,
                success: validatorSuccess
            });
        }
    };

    var Dokan_Seller = {
        init: function() {
            this.validate(this);
        },

        validate: function(self) {
            // e.preventDefault();

            $('form#dokan-form-contact-seller').validate({
                errorPlacement: validatorError,
                success: validatorSuccess,
                submitHandler: function(form) {

                    $(form).block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                    var form_data = $(form).serialize();
                    $.post(dokan.ajaxurl, form_data, function(resp) {
                        $(form).unblock();

                        if ( typeof resp.data !== 'undefined' ) {
                            $(form).find('.ajax-response').html(resp.data);
                        }

                        $(form).find('input[type=text], input[type=email], textarea').val('').removeClass('valid');
                    });
                }
            });
        }
    };

    var Dokan_Add_Seller = {
        init: function() {
            this.validate(this);
        },

        validate: function(self) {
            // e.preventDefault();

            $('form#register').validate({
                errorPlacement: validatorError,
                success: validatorSuccess,
                submitHandler: function(form) {
                    form.submit();
                }
            });
        }
    };

    $(function() {
        Dokan_Settings.init();
        Dokan_Withdraw.init();
        Dokan_Coupons.init();
        Dokan_Seller.init();
        Dokan_Add_Seller.init();
    });

})(jQuery);
