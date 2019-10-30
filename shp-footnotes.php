<?php
/**
 * Plugin Name: Footnotes
 * Plugin URI: https://github.com/sayhellogmbh/shp-footnotes
 * Description: Easily add footnotes to your posts with a simple shortcode.
 * Version: 2.0.0
 * Author: Say Hello GmbH
 * Author URI: https://sayhello.ch
 * License: GPL2
 *
 * @package shp-footnotes
 */

/*
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace SayHello\Plugin;

class ShpFootnotes
{

	/**
	 * Add label option using add_option if it does not already exist.
	 *
	 * @var array $footnotes An array for the footnotes to be stored.
	 */
	public $footnotes     = array();
	public $footnoteCount = 0;
	public $prevPost;
	public $footnoteOptions;

	private $footnoteSettings;

	/**
	 * Constructing the initial plugin options, shortcodes, and hooks.
	 */
	public function __construct()
	{
		$this->footnoteSettings = array(
			'footnoteLabel'                  => __('Footnotes', 'shp-footnotes'),
			'useLabel'                       => false,
			'hide_shp_footnote_after_posts' => false,
			'show_shp_footnote_on_front'    => false,
		);

		add_option('shp_footnotes_options', $this->footnoteSettings);
		add_shortcode('note', array( $this, 'shp_footnote_shortcode' ));
		add_shortcode('notiz', array( $this, 'shp_footnote_shortcode' ));
		add_filter('the_content', array( $this, 'shp_footnote_after_content' ), 20);
		add_filter('the_content', array( $this, 'shp_footnote_reset' ), 999);
		add_action('wp_enqueue_scripts', array( $this, 'register_qtip_scripts' ));
		add_action('admin_menu', array( $this, 'shp_footnotes_admin_actions' ));
		add_action('admin_enqueue_scripts', array( $this, 'shp_footnotes_admin_scripts' ));
		add_action('plugins_loaded', array($this, 'shp_footnotes_load_plugin_textdomain'));

		$this->footnoteOptions = get_option('shp_footnotes_options');
	}

	public function shp_footnotes_load_plugin_textdomain()
	{
		load_plugin_textdomain('shp-footnotes', false, basename(dirname(__FILE__)));
	}

	/**
	 * Registering the scripts and styles used by jQuery qTip.
	 */
	public function register_qtip_scripts()
	{
		wp_register_script('imagesloaded', plugins_url('/assets/qtip/imagesloaded.pkgd.min.js', __FILE__), array(), '3.1.8', true);
		wp_register_script('qtip', plugins_url('/assets/qtip/jquery.qtip.min.js', __FILE__), array( 'jquery', 'imagesloaded' ), '3.0.3', true);
		wp_register_script('qtipcall', plugins_url('/assets/qtip/jquery.qtipcall.js', __FILE__), array( 'jquery', 'qtip' ), '1.1.0', true);
		wp_register_style('qtipstyles', plugins_url('/assets/qtip/jquery.qtip.min.css', __FILE__), array(), '3.0.3', false);
		wp_register_style('shpfootnotescss', plugins_url('/assets/shp-footnotes.css', __FILE__), array(), '1.1.0', false);
	}

	/**
	 * Create the SHP Footnotes shortcode.
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $content Content within the shortcode.
	 */
	public function shp_footnote_shortcode($atts, $content = null)
	{
		if (isset($this->footnoteOptions['show_shp_footnote_on_front']) && $this->footnoteOptions['show_shp_footnote_on_front']) {
			$shpefn_show_on_front = is_front_page();
		} else {
			$shpefn_show_on_front = false;
		}

		wp_enqueue_style('qtipstyles');
		wp_enqueue_style('shpfootnotescss');
		wp_enqueue_script('imagesloaded');
		wp_enqueue_script('qtip');
		wp_enqueue_script('qtipcall');
		wp_enqueue_style('dashicons');

		$atts = shortcode_atts(
			array(
				// Future home of shortcode atts.
			),
			$atts
		);

		$post_id = get_the_ID();

		$content = do_shortcode($content);

		$count = $this->footnoteCount;

		// Increment the counter.
		$count++;

		// Set the footnoteCount (This whole process needs reworked).
		$this->footnoteCount = $count;

		$this->shp_footnote_content($content);

		if (( is_singular() || $shpefn_show_on_front ) && is_main_query()) {
			$footnoteLink = '#shp-footnote-bottom-' . $this->footnoteCount . '-' . $post_id;
		} else {
			$footnoteLink = get_permalink(get_the_ID()) . '#shp-footnote-bottom-' . $this->footnoteCount . '-' . $post_id;
		}

		$footnoteContent = "<span id='shp-footnote-" . esc_attr($this->footnoteCount) . '-' . $post_id . "' class='shp-footnote-margin-adjust'></span><span class='shp-footnote'><a href='" . esc_url($footnoteLink) . "' title='" . htmlspecialchars($content, ENT_QUOTES) . "'><sup>" . esc_html($this->footnoteCount) . "</sup></a></span>";

		return $footnoteContent;
	}

	/**
	 * The content of a particular footnote.
	 *
	 * @param string $content The content from a particular call of the shortcode.
	 */
	public function shp_footnote_content($content)
	{
		$this->footnotes[ $this->footnoteCount ] = $content;

		return $this->footnotes;
	}

	/**
	 * Display the list of footnotes after the post content.
	 *
	 * @param string $content The content of the current post.
	 */
	public function shp_footnote_after_content($content)
	{
		if (isset($this->footnoteOptions['hide_shp_footnote_after_posts']) && $this->footnoteOptions['hide_shp_footnote_after_posts']) {
			return $content;
		}

		if (isset($this->footnoteOptions['show_shp_footnote_on_front']) && $this->footnoteOptions['show_shp_footnote_on_front']) {
			$shpefn_show_on_front = is_front_page();
		} else {
			$shpefn_show_on_front = false;
		}

		if (( is_singular() || $shpefn_show_on_front ) && is_main_query()) {
			$footnotesInsert = $this->footnotes;

			$footnoteCopy = '';
			$shpefn_output = '';

			$useLabel = isset($this->footnoteOptions['useLabel']) ? $this->footnoteOptions['useLabel'] : false;
			$efLabel  = isset($this->footnoteOptions['footnoteLabel']) ? $this->footnoteOptions['footnoteLabel'] : __('Footnotes', 'shp-footnotes');

			$post_id = get_the_ID();

			foreach ($footnotesInsert as $count => $footnote) {
				$footnoteCopy .= '<li class="shp-footnote-single"><span id="shp-footnote-bottom-' .esc_attr($count) . '-' . $post_id . '" class="shp-footnote-margin-adjust"></span>' . wp_kses_post($footnote) . '<a class="shp-footnote-to-top" href="' . esc_url('#shp-footnote-' . $count . '-' . $post_id) . '"></a></li>';
			}
			if (! empty($footnotesInsert)) {
				if (true === $useLabel) {
					$footnote_label = '<div class="shp-footnote-title"><h4>' . esc_html($efLabel) . '</h4></div>';
					// Filter for editing footnote label markup and output.
					$footnote_label = apply_filters('shpefn_footnote_label', $footnote_label, $efLabel);

					$shpefn_output .= $footnote_label;
				}

				$footnote_content = '';

				// Add filter before footnote list.
				$footnote_content  = apply_filters('before_footnote', $footnote_content);
				$footnote_content .= '<ol class="shp-footnotes-wrapper">' . $footnoteCopy . '</ol>';

				// Add filter after footnote list.
				$footnote_content = apply_filters('after_footnote', $footnote_content);

				$shpefn_output .= $footnote_content;

				// Add filters for the entire footnote list output.
				$shpefn_output = apply_filters('shpefn_footnote_list_output', $shpefn_output);

				$content .= do_shortcode($shpefn_output);
			}
		}

		return $content;
	}

	/**
	 * Reset the footnote count and footnote array each time the_content has been run.
	 *
	 * @param string $content The content of the post from the_content filter.
	 */
	public function shp_footnote_reset($content)
	{
		$this->footnoteCount = 0;

		$this->footnotes = array();

		return $content;
	}

	/**
	 * Include the shp-footnotes-admin.php file.
	 */
	public function shp_footnotes_admin()
	{
		include 'shp-footnotes-admin.php';
	}

	/**
	 * Function to add options page for SHP Footnote Settings.
	 */
	public function shp_footnotes_admin_actions()
	{
		add_options_page(
			__('Footnotes Settings', 'shp-footnotes'),
			__('Footnotes', 'shp-footnotes'),
			'manage_options',
			'shp-footnotes-settings',
			array( $this, 'shp_footnotes_admin' )
		);
	}

	/**
	 * Function for enqueuring SHP Footnotes admin scripts.
	 */
	public function shp_footnotes_admin_scripts()
	{
		wp_enqueue_style('shp-footnotes-admin-styles', plugins_url('/assets/shp-footnotes-admin.css', __FILE__), '', '1.0.13');
		wp_enqueue_script('shp-footnotes-admin-scripts', plugins_url('/assets/js/shp-footnotes-admin.js', __FILE__), array( 'jquery' ), '1.0.1', true);
	}
}

$shpFootnotes = new ShpFootnotes();
