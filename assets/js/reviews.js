;(function($){

    var Dokan_Comments = {

        init: function() {
            $('#dokan-comments-table').on('click', '.dokan-cmt-action', this.setCommentStatus);
            $('#dokan-comments-table').on('click', 'button.dokan-cmt-close-form', this.closeForm);
            $('#dokan-comments-table').on('click', 'button.dokan-cmt-submit-form', this.submitForm);
            $('#dokan-comments-table').on('click', '.dokan-cmt-edit', this.populateForm);
            $('.dokan-check-all').on('click', this.toggleCheckbox);
        },

        toggleCheckbox: function() {
            $(".dokan-check-col").prop('checked', $(this).prop('checked'));
        },

        setCommentStatus: function(e) {
            e.preventDefault();

            var self = $(this),
                comment_id = self.data('comment_id'),
                comment_status = self.data('cmt_status'),
				page_status = self.data('page_status'),
				post_type = self.data('post_type'),
				curr_page = self.data('curr_page'),
                tr = self.closest('tr'),
                data = {
                    'action': 'wpuf_comment_status',
                    'comment_id': comment_id,
                    'comment_status': comment_status,
					'page_status': page_status,
					'post_type': post_type,
					'curr_page': curr_page,
					'nonce': dokan.nonce
                };


            $.post(dokan.ajaxurl, data, function(resp){

                if(page_status === 1) {
                    if ( comment_status === 1 || comment_status === 0) {
                        tr.fadeOut(function() {
                            tr.replaceWith(resp.data['content']).fadeIn();
                        });

                    } else {
                        tr.fadeOut(function() {
                            $(this).remove();
                        });
                    }
                } else {
                    tr.fadeOut(function() {
                        $(this).remove();
                    });
                }

                if(resp.data['pending'] == null) resp.data['pending'] = 0;
                if(resp.data['spam'] == null) resp.data['spam'] = 0;
				if(resp.data['trash'] == null) resp.data['trash'] = 0;

                $('.comments-menu-pending').text(resp.data['pending']);
                $('.comments-menu-spam').text(resp.data['spam']);
				$('.comments-menu-trash').text(resp.data['trash']);
            });
        },

        populateForm: function(e) {
            e.preventDefault();

            var tr = $(this).closest('tr');

            // toggle the edit area
            if ( tr.next().hasClass('dokan-comment-edit-row')) {
                tr.next().remove();
                return;
            }

            var table_form = $('#dokan-edit-comment-row').html(),
                data = {
                    'author': tr.find('.dokan-cmt-hid-author').text(),
                    'email': tr.find('.dokan-cmt-hid-email').text(),
                    'url': tr.find('.dokan-cmt-hid-url').text(),
                    'body': tr.find('.dokan-cmt-hid-body').text(),
                    'id': tr.find('.dokan-cmt-hid-id').text(),
                    'status': tr.find('.dokan-cmt-hid-status').text(),
                };


            tr.after( _.template(table_form, data) );
        },

        closeForm: function(e) {
            e.preventDefault();

            $(this).closest('tr.dokan-comment-edit-row').remove();
        },

        submitForm: function(e) {
            e.preventDefault();

            var self = $(this),
                parent = self.closest('tr.dokan-comment-edit-row'),
                data = {
                    'action': 'dokan_update_comment',
                    'comment_id': parent.find('input.dokan-cmt-id').val(),
                    'content': parent.find('textarea.dokan-cmt-body').val(),
                    'author': parent.find('input.dokan-cmt-author').val(),
                    'email': parent.find('input.dokan-cmt-author-email').val(),
                    'url': parent.find('input.dokan-cmt-author-url').val(),
                    'status': parent.find('input.dokan-cmt-status').val(),
					'nonce': dokan.nonce,
					'post_type' : parent.find('input.dokan-cmt-post-type').val(),
                };

            $.post(dokan.ajaxurl, data, function(res) {
                if ( res.success === true) {
                    parent.prev().replaceWith(res.data);
                    parent.remove();
                } else {
                    alert( res.data );
                }
            });
        }
    };

    $(function(){

        Dokan_Comments.init();
    });

})(jQuery);