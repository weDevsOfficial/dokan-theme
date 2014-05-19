<?php

/**
 * Category walker for generating doakn store category
 */
class Dokan_Store_Category_Walker extends Walker {

    function __construct( $seller_id ) {
        $this->store_url = dokan_get_store_url ( $seller_id );
    }

    var $tree_type = 'category';
    var $db_fields = array('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );

        if ( $depth == 0 ) {
            $output .= $indent . '<div class="sub-category">' . "\n";
        } else {
            $output .= "$indent<ul class='children'>\n";
        }
    }

    function end_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );

        if ( $depth == 0 ) {
            $output .= "$indent</div> <!-- .sub-category -->\n";
        } else {
            $output .= "$indent</ul>\n";
        }
    }

    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        extract( $args );
        $indent = str_repeat( "\t", $depth );

        if ( $depth == 1 ) {
            $output .= $indent . '<div class="sub-block">' . "\n\t" .'<h3><a href="' . $this->store_url . 'section/' . $category->term_id . '">' . $category->name . '</a></h3>' . "\n";
        } else {
            $caret = $args['has_children'] ? ' <span class="caret"></span>' : '';
            $class_name = $args['has_children'] ? ' class="has-children"' : '';
            $output .= $indent . '<li' . $class_name . '><a href="' . $this->store_url . 'section/' . $category->term_id . '">' . $category->name . $caret . '</a>';
        }
    }

    function end_el( &$output, $category, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );

        if ( $depth == 1 ) {
            $output .= "$indent</div><!-- .sub-block -->\n";
        } else {
            $output .= "$indent</li>\n";
        }
    }
}
