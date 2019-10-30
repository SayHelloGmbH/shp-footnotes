jQuery(function () {

	jQuery("input[name=hide_shp_footnote_after_posts]").click(function () {
		// only show the "Show on Front page" option when the user has decided NOT to hide the footnotes from the bottom of the posts.
		// jQuery("#shp_footnote_on_front").toggle(!this.checked);

		// Disable the "Show on Front page" option when the user has decided NOT to hide the footnotes from the bottom of the posts.
		jQuery("input[name=show_shp_footnote_on_front]").attr('disabled', this.checked)
	});

});