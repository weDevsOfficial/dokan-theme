<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
    </header><!-- .entry-header -->

    <div class="entry-meta">
        <?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>

            <i class="icon-calendar"></i>
            <?php dokan_posted_on(); ?>
            <span class="sep"> | </span>

            <?php
            /* translators: used between list items, there is a space after the comma */
            $categories_list = get_the_category_list( __( ', ', 'dokan' ) );
            if ( $categories_list ) :
                ?>
                <span class="cat-links">
                    <i class="icon-folder-open"></i>
                    <?php printf( __( '%1$s', 'dokan' ), $categories_list ); ?>
                </span>
            <?php endif; // End if categories ?>

            <?php if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
                <span class="sep"> | </span>
                <span class="comments-link"><i class="icon-comment-alt"></i> <?php comments_popup_link( __( 'Leave a comment', 'dokan' ), __( '1 Comment', 'dokan' ), __( '% Comments', 'dokan' ) ); ?></span>
            <?php endif; ?>

            <?php edit_post_link( __( 'Edit', 'dokan' ), '<span class="sep"> | </span><span class="edit-link"><i class="icon-edit"></i> ', '</span>' ); ?>

        <?php endif; // End if 'post' == get_post_type() ?>
    </div><!-- .entry-meta -->

    <div class="entry-content">
        <?php the_content(); ?>
        <?php wp_link_pages( array('before' => '<div class="page-links">' . __( 'Pages:', 'dokan' ), 'after' => '</div>') ); ?>
    </div><!-- .entry-content -->

    <footer class="entry-meta">
        <?php
        /* translators: used between list items, there is a space after the comma */
        $tag_list = get_the_tag_list( '', __( ', ', 'dokan' ) );

        if ( '' != $tag_list ) {
            printf( '<span class="tags"><i class="icon-tags"></i> Tagged: %s</span>', $tag_list );
        }
        ?>
    </footer><!-- .entry-meta -->
</article><!-- #post-<?php the_ID(); ?> -->
