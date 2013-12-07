(function($) {
    
    var settings = {
        init: function() {
            var self = this;
            
            self.jqueryValidate(self);

            return false;
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



            var kjhk = $("form#settings-form").validate({
                submitHandler : function(form) {
                    self.serverValidate();
                },
                rules: {
                    'dokan_store_name': {
                        'required': true,
                    },
                    'setting_paypal_email': {
                        email: true,
                    },
                    'setting_category[]': {
                        'required': true,
                    }
                },
                
                messages: {
                    'dokan_store_name': {
                        'required': 'Dokan name required',
                    },

                    setting_paypal_email: {
                        'email': 'Invalid email',
                    },
                    'setting_category[]': {
                        'required': 'Dokan type required',
                    }

                },
            }); 

            return kjhk;

        }
    }

    settings.init();

})(jQuery)