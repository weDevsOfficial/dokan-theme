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

    // set dashboard menu height
    // $('.dokan-dash-sidebar ul.dokan-dashboard-menu').css({
    //     'height': $('#main').height()
    // });

});

//dokan settings

(function($) {
    
    var settings = {
        init: function() {
            self = this;

            //image upload
            $('a.dokan-banner-drag').on('click', this.imageUpload);
            
            self.jqueryValidate(self);

            return false;
        },


        imageUpload: function() {
            var file_frame,
                click = $(this);     

            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: jQuery( this ).data( 'uploader_title' ),
                button: {
                    text: jQuery( this ).data( 'uploader_button_text' ),
                },
                multiple: false // Set to true to allow multiple files to be selected
            });

            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();

                // Do something with attachment.id and/or attachment.url here
                console.log(attachment);
                click.closest('div').find('input.dokan-file-field').val(attachment.id);
                click.closest('div').find('img.dokan-banner-img').attr('src', attachment.url);
                $('.dokan-banner-drag').hide();
            });

            // Finally, open the modal
            file_frame.open();

        },

        serverValidate: function() {
   
            var self = $( "form#settings-form" ),
                form_data = self.serialize() + '&action=dokan_settings';

            
            self.find('.ajax_prev').append('<span class="dokan-loading"> </span>');
          
            $.post(dokan.ajaxurl, form_data, function(resp) {

               self.find('span.dokan-loading').remove();
                $('html,body').animate({scrollTop:100});

                if(resp.success) {
                    console.log(resp.data['success_save']);
                    var alert = $('.alert-success');
                        prev_alert = $('.alert-danger');

                    prev_alert.hide().children('strong').children('p').remove();
                    alert.children('strong').children('p').remove();
                    alert.show().children('strong').append('<p>'+resp.data['success_save']+'</p>');

                }

                if(resp.success === false) {
                    var alert = $('.alert-danger');
                    
                    $('.alert-success').hide().children('strong').children('p').remove();
                    alert.children('strong').children('p').remove();

                    if(resp.data['noce_verify'] ) {
                        
                        alert.show().children('strong').append('<p>'+resp.data['noce_verify']+'</p>');
                    }

                    if( resp.data['error_message'] ) {

                        var error_all= '';

                        $.each(resp.data['error_message'], function( key, message) {
                            error_all += '<p>'+message[0]+'</p>';
                        } );

                        alert.show().children('strong').append(error_all);
                    }
                }
            }); 
        },

        jqueryValidate: function(self) {



            $("form#settings-form").validate({
                //errorLabelContainer: '#errors'
                submitHandler: function(form) {
                    self.serverValidate();
                },
                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: function(error, element) {
        
                    var form_group = $(element).closest('.form-group');
                    form_group.addClass('has-error').append(error);
                },

                success: function(label, element) {
                    $(element).closest('.form-group').removeClass('has-error');
                }
            }); 

        }
    }

    settings.init();

})(jQuery);

//withdraw
(function($) {
    var withdraw = {
        
        init: function() {
            var self = this;

            this.withdrawValidate(self);
        },

        withdrawValidate: function(self) {
            $('form.withdraw').validate({
                //errorLabelContainer: '#errors'
                
                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: function(error, element) {
        
                    var form_group = $(element).closest('.form-group');
                    form_group.addClass('has-error').append(error);
                },

                success: function(label, element) {
                    $(element).closest('.form-group').removeClass('has-error');
                }
            })
        }

        
    }

    withdraw.init();
})(jQuery);

//coupons
(function($){
    var coupons = {
        init: function() {
            var self = this;
            this.couponsValidation(self); 
        },

        couponsValidation: function(self) {
            $("form.coupons").validate({
                //errorLabelContainer: '#errors'
                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: function(error, element) {
        
                    var form_group = $(element).closest('.form-group');
                    form_group.addClass('has-error').append(error);
                },

                success: function(label, element) {
                    $(element).closest('.form-group').removeClass('has-error');
                }
            }); 
        }


    }

    coupons.init();
})(jQuery)