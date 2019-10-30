<?php
/**
 * Admin screen functionality for Footnotes.
 *
 * @package shp-footnotes
 */

$footnoteOptions = get_option('shp_footnotes_options');

if (isset($_POST['shp_footnote_hidden'])) {
	// Check the nonce for the Reading Time.
	check_admin_referer('shp_footnotes_settings_nonce');

	if ('Y' === $_POST['shp_footnote_hidden']) :
		// Form data sent.
		$hide_shp_footnote_after_posts = isset($_POST['hide_shp_footnote_after_posts']) ? true : false;
		$show_shp_footnote_on_front    = isset($_POST['show_shp_footnote_on_front']) ? true : false;

		$updateOptions = array(
			'hide_shp_footnote_after_posts' => $hide_shp_footnote_after_posts,
			'show_shp_footnote_on_front'    => $show_shp_footnote_on_front,
		);

		update_option('shp_footnotes_options', $updateOptions);
		?>
		<div class="updated">
			<p><strong><?php esc_html_e('Options saved.', 'shp-footnotes'); ?></strong></p>
		</div>
		<?php
	endif;
} else {
	// Normal page display.
	$hide_shp_footnote_after_posts = isset($footnoteOptions['hide_shp_footnote_after_posts']) ? $footnoteOptions['hide_shp_footnote_after_posts'] : false;
	$show_shp_footnote_on_front    = isset($footnoteOptions['show_shp_footnote_on_front']) ? $footnoteOptions['show_shp_footnote_on_front'] : false;
}
?>

<div class="wrap">
	<?php echo '<h2>' . esc_html__('Footnotes Settings', 'shp-footnotes') . '</h2>'; ?>

	<form name="shp_footnotes_form" method="post">
		<input type="hidden" name="shp_footnote_hidden" value="Y">
		<?php wp_nonce_field('shp_footnotes_settings_nonce'); ?>
		<?php echo '<h4>' . esc_html__('Footnotes Settings', 'shp-footnotes') . '</h4>'; ?>

		<p><?php esc_html_e('Hide Footnotes after post content: ', 'shp-footnotes'); ?><input type="checkbox" name="hide_shp_footnote_after_posts" <?php checked($hide_shp_footnote_after_posts); ?> size="20"></p>

		<p id="shp_footnote_on_front"><?php esc_html_e('Show Footnotes on Front Page: ', 'shp-footnotes'); ?><input type="checkbox" name="show_shp_footnote_on_front" <?php checked($show_shp_footnote_on_front); ?> size="20"></p>

		<p class="submit">
		<input type="submit" name="Submit" value="<?php esc_attr_e('Update Options', 'shp_footnotes_trdom'); ?>" />
		</p>
	</form>

	<div class="shp-footnotes-shortcode-hint">
		<p><?php echo esc_html__('Shortcode: [note]Insert your note here.[/note]', 'shp-footnotes'); ?></p>
	</div>

</div>
