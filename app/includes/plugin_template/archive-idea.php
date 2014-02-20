<?php
/**
 * WP Ideas Archive Template
 *
 * @package rtPanel
 *
 * @since rtPanelChild 2.0
 */
get_header();

$content_class = apply_filters('rtwiki_content_class', 'large-8 small-12 columns');
?>

<div id="primary" class="content-area <?php echo $content_class ?>">
	<div id="content" class="site-content" role="main">
<?php
include RTWPIDEAS_PATH . 'app/includes/plugin_template/loop-common.php';
?>

	</div><!-- #content -->
</div><
get_sidebar();
get_footer();
