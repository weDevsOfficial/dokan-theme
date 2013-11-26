<?php
/**
* The template for displaying Author Archive pages.
*
* @package WordPress
* @subpackage Twenty_Ten
* @since Twenty Ten 1.0
*/

get_header(); ?>

  <div class="container">
    <div class="row">
      <div class="span12">


          <?php
          global $author;

          if(have_posts()) the_post();
          if(get_the_author_meta('description')):
          ?>

          <h2><?php echo get_the_author_meta('meta_key'); ?></h2>
          <hr>
          <br/>

                <div class="main-box">
                    <div class="row-fluid">

                    <div class="span9">
                    <div id="author-description">
                    <h4>Informació general</h4>

                        <?php echo get_the_author_meta('meta_key'); ?><br>

                    </div>
                    </div>



                    <div class="span3">
                    <div id="author-description">

                        <?php echo wp_get_attachment_image( get_the_author_meta( 'meta_key' ), 'thumbnail' ); ?>

                    </div>
                    </div>

                    <div class="row-fluid">
                    <div class="span12">
                    <div id="entry-author-description">
                    <br><hr>
                    <p><?php echo get_the_author_meta('description'); ?></p><br>
                    </div>
                    </div>
                    </div>


                </div> <!-- row-fluid-->
                </div> <!-- main-box -->

                <br>
                <hr>

            <?php endif;
            rewind_posts();
            ?>



      </div> <!-- span12 -->
    </div>
  </div>


<?php get_footer(); ?>