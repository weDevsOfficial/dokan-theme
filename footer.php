<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package _bootstraps
 * @package _bootstraps - 2013 1.0
 */
?>
</div><!-- .row -->
</div><!-- .container -->
</div><!-- #main .site-main -->

<footer id="colophon" class="site-footer" role="contentinfo">
    <div class="container">

        <div class="row footer-widget-area">
            <div class="col-md-3">
                <aside class="widget">
                    <h3 class="widget-title">Company</h3>

                    <ul class="list-unstyled">
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Grunteed Period</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </aside>
            </div>

            <div class="col-md-3">
                <aside class="widget">
                    <h3 class="widget-title">Company</h3>

                    <ul class="list-unstyled">
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Grunteed Period</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </aside>
            </div>

            <div class="col-md-3">
                <aside class="widget">
                    <h3 class="widget-title">Company</h3>

                    <ul class="list-unstyled">
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Grunteed Period</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </aside>
            </div>

            <div class="col-md-3">
                <aside class="widget footer-subscribe">
                    <h3 class="widget-title">Newsletter Subscription</h3>

                    <form action="post">
                        <ul class="subscribe-from">
                            <li>
                                <input type="email" name="email" value="" placeholder="Please enter your Email" class="form-control">
                            </li>
                            <li>
                                <input type="submit" value="Subscribe" class="btn">
                            </li>
                        </ul>
                    </form>

                    <?php $url = get_template_directory_uri() . '/assets/images/footer'; ?>
                    <ul class="list-inline footer-social">
                        <li><a href="#" target="_blank"><img src="<?php echo $url; ?>/f-facebook.png" alt="Facebook"></a></li>
                        <li><a href="#" target="_blank"><img src="<?php echo $url; ?>/f-gplus.png" alt="G+"></a></li>
                        <li><a href="#" target="_blank"><img src="<?php echo $url; ?>/f-twitter.png" alt="Twitter"></a></li>
                    </ul>
                </aside>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="footer-copy">
                    <div class="col-md-6 site-info">

                        &copy; 2008-2013 <a href="http://tareq.wedevs.com">Tareq Hasan</a>. All rights are reserved.
                        Powered by <a href="http://wordpress.org/" target="_blank" title="A Semantic Personal Publishing Platform" rel="generator">WordPress</a>.
                    </div><!-- .site-info -->

                    <div class="col-md-6 pull-right footer-getway">
                        <ul>
                            <li>
                                <img src="<?php echo $url; ?>/visa.png" alt="VISA">
                                <img src="<?php echo $url; ?>/master-card.png" alt="MASTER CARD">
                                <img src="<?php echo $url; ?>/web-money.png" alt="WEB Money">
                                <img src="<?php echo $url; ?>/2co.png" alt="2CO">
                                <img src="<?php echo $url; ?>/paypal.png" alt="Paypal">
                                <img src="<?php echo $url; ?>/google-checkout.png" alt="Google">
                                <img src="<?php echo $url; ?>/switch.png" alt="Switch">
                                <img src="<?php echo $url; ?>/dinners.png" alt="Dinners">
                                <img src="<?php echo $url; ?>/moneybookers.png" alt="Money Bookers">
                                <img src="<?php echo $url; ?>/stripe.png" alt="Stripe">
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div><!-- .row -->
    </div><!-- .container -->
</footer><!-- #colophon .site-footer -->
</div><!-- #page .hfeed .site -->

<?php wp_footer(); ?>

</body>
</html>