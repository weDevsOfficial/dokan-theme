<?php
/**
* Dokan customizer
*/
class Dokan_Customizer {
    
    function __construct() {
        # code...
        add_action( 'customize_register', array($this, 'dokan_customizer') );
        add_action( 'wp_footer', array($this, 'dokan_customizer_css') );
        add_action( 'customize_preview_init', array($this, 'dokan_customizer_live_preview') );
    }

    function dokan_customizer( $wp_customize ) {
        //var_dump( get_stylesheet_directory_uri().'/assets/images/logo.png' ); die();
        // More to come...
        //---------------------------logo---------------------------

        $wp_customize->add_section( 'theme_logo_section' , array(
            'title'       => __( 'Theme Logo', 'dokan' ),
            'priority'    => 9,
            'description' => 'Upload your logo to replace the default Logo (dimension : 180 X 50)',
        ) );

        $wp_customize->add_setting( 'theme_logo' ,
            array(
            'default' => get_stylesheet_directory_uri().'/assets/images/logo.png',
            'capability' => 'edit_theme_options',
            'type' => 'option',
            )
        );

        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'theme_logo_image', array(
            'label'    => __( 'Upload Logo', 'theme_logo' ),
            'section'  => 'theme_logo_section',
            'settings' => 'theme_logo',
        ) ) );

        //--------------------------link color------------------------------
        
        $wp_customize->add_setting(
            'link_color',
            array(
                'default'     => '#428BCA',
                'transport'   => 'postMessage'
            )
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'link_color',
                array(
                    'label'      => __( 'Link Color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'link_color',
                    'priority'   => 20
                )
            )
        );


        //--------------------------link hover color------------------------------
        
        $wp_customize->add_setting(
            'link_hover_color',
            array(
                'default'     => '#2A6496',
                'transport'   => 'postMessage'
            )
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'link_hover_color',
                array(
                    'label'      => __( 'Link hover Color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'link_hover_color',
                    'priority'   => 25
                )
            )
        );


        //---------------------------Header Background color--------------------------------
        
        $wp_customize->add_setting( 
            'header_bg_color' , 
            array(
                'default'     => '#fff',
                'transport'   => 'postMessage',
            ) 
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'header_bg_color',
                array(
                    'label'      => __( 'Header Background color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'header_bg_color',
                    'priority'   => 30
                )
            )
        );


        //--------------------------nav backgroung color------------------------------
       
        $wp_customize->add_setting(
            'nav_bg_color',
            array(
                'default'     => '#fff',
                'transport'   => 'postMessage'
            )
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'nav_bg_color',
                array(
                    'label'      => __( 'Navigation Background Color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'nav_bg_color',
                    'priority'   => 33
                )
            )
        );



        //--------------------------nav color------------------------------
       
        $wp_customize->add_setting(
            'nav_color',
            array(
                'default'     => '#777777',
                'transport'   => 'postMessage'
            )
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'nav_color',
                array(
                    'label'      => __( 'Navigation Color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'nav_color',
                    'priority'   => 35
                )
            )
        );


        //--------------------------nav hover color------------------------------
       
        $wp_customize->add_setting(
            'nav_hover_color',
            array(
                'default'     => '#333',
                'transport'   => 'postMessage'
            )
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'nav_hover_color',
                array(
                    'label'      => __( 'Navigation hover Color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'nav_hover_color',
                    'priority'   => 40
                )
            )
        );


        //---------------------------Footer Background color--------------------------------
        
        $wp_customize->add_setting( 
            'footer_bg_color' , 
            array(
                'default'     => '#393939',
                'transport'   => 'postMessage',
            ) 
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'footer_bg_color',
                array(
                    'label'      => __( 'Footer Background color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'footer_bg_color',
                    'priority'   => 50
                )
            )
        );
        
        
        //---------------------------footer text color--------------------------------
        
        $wp_customize->add_setting( 
            'footer_text_color' , 
            array(
                'default'     => '#E8E8E8',
                'transport'   => 'postMessage',
            ) 
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'footer_text_color',
                array(
                    'label'      => __( 'Footer text color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'footer_text_color',
                    'priority'   => 55
                )
            )
        );

        //---------------------------widget header color--------------------------------
        
        $wp_customize->add_setting( 
            'widget_header_color' , 
            array(
                'default'     => '#E8E8E8',
                'transport'   => 'postMessage',
            ) 
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'widget_header_color',
                array(
                    'label'      => __( 'Widget header color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'widget_header_color',
                    'priority'   => 60
                )
            )
        );


        //---------------------------widget text color--------------------------------
        
        $wp_customize->add_setting( 
            'widget_text_color' , 
            array(
                'default'     => '#AFAFAF',
                'transport'   => 'postMessage',
            ) 
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'widget_text_color',
                array(
                    'label'      => __( 'Widget link color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'widget_text_color',
                    'priority'   => 65
                )
            )
        );


        //---------------------------widget text hover color--------------------------------
        
        $wp_customize->add_setting( 
            'widget_text_hover_color' , 
            array(
                'default'     => '#E8E8E8',
                'transport'   => 'postMessage',
            ) 
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'widget_text_hover_color',
                array(
                    'label'      => __( 'Widget link hover color', 'dokan' ),
                    'section'    => 'colors',
                    'settings'   => 'widget_text_hover_color',
                    'priority'   => 70
                )
            )
        );


    }

    //-------------------------------------customize css---------------------------
    function dokan_customizer_css() {
        // echo(get_theme_mod( 'theme_logo' ));
        ?>
        <style type="text/css">

            <?php if ( get_theme_mod( 'theme_logo' ) ) : ?>
            .site-header h1.site-title a { background: url("<?php echo esc_url( get_theme_mod( 'theme_logo' ) ); ?>") no-repeat scroll 0 0 rgba(0, 0, 0, 0);}*/
            <?php endif; ?>
            .site-header { background-color: <?php echo get_theme_mod( 'header_bg_color' ); ?> ; }
            .navbar-default{ background-color: <?php echo get_theme_mod( 'nav_bg_color' ); ?>; }
            .navbar-default .navbar-nav > li > a{ color: <?php echo get_theme_mod( 'nav_color' ); ?>; }
            .navbar-default .navbar-nav > li > a:hover { color: <?php echo get_theme_mod( 'nav_hover_color' ); ?>; }
            a { color: <?php echo get_theme_mod( 'link_color' ); ?>; }
            a:hover { color: <?php echo get_theme_mod( 'link_hover_color' ); ?>; }
            .site-footer .widget h3{ color: <?php echo get_theme_mod( 'widget_header_color' ); ?>; }
            .site-footer .widget ul li a{ color: <?php echo get_theme_mod( 'widget_text_color' ); ?>; }
            .site-footer .widget ul li a:hover{ color: <?php echo get_theme_mod( 'widget_text_hover_color' ); ?>; }
            .site-footer { color: <?php echo get_theme_mod( 'footer_text_color' ); ?>; }
            .site-footer { background-color: <?php echo get_theme_mod( 'footer_bg_color' ); ?> ; }
        </style>
        <?php
    }
    

//--------------live preview---------------------------
    function dokan_customizer_live_preview() {
        wp_enqueue_script(
            'dokan-theme-customizer',
            get_template_directory_uri() . '/assets/js/theme-customizer.js',
            array( 'jquery', 'customize-preview' ),
            '0.3.0',
            true
        );
    }

}

new Dokan_Customizer();