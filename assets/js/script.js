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