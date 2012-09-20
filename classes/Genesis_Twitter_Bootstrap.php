<?php

/**
 * Customizes genesis to make us of Twitter Bootstrap
 */
class Genesis_Twitter_Bootstrap {

	protected $config = array(
		'container_class'  => 'container',
		'load_assets'      => true,
		'remove_header'    => false,
		'main_nav'         => array(
			'filter'       => true,
			'classes'	   => '',
			'brand'        => '',
			'responsive'   => true
		)
	);

	public function __construct($config = array()) {
		if ( is_admin() ) return;

		$this->config = array_replace_recursive($this->config, $config);

		remove_action('genesis_doctype', 'genesis_do_doctype');
		add_action('genesis_doctype', array($this, 'html5_doctype') );

		add_action('wp_footer', array($this, 'output_js_components') );

		remove_action('genesis_after_endwhile', 'genesis_posts_nav');
		add_action('genesis_after_endwhile', array($this, 'posts_nav') );

		add_action('genesis_before', array($this, 'open_container_class') );
		add_action('genesis_after', array($this, 'close_container_class') );

		remove_action( 'genesis_header', 'genesis_do_header' );

		if ( true == $this->config['remove_header'] ) {
			unregister_sidebar('header-right');
		} else {
			add_action('genesis_header', array($this, 'genesis_do_header') );
		}

		remove_action('genesis_header', 'genesis_header_markup_open', 5);
		add_action('genesis_header', array($this, 'header_markup_open'), 5 );

		if ( true == $this->config['main_nav']['filter'] ) {
			add_filter('genesis_do_nav', array($this, 'bootstrap_do_nav'), 10, 3);
		}

		if ( true == $this->config['load_assets'] ) {
			wp_enqueue_script('gtb-bootstrap', $this->url() . '/assets/js/bootstrap.min.js', array('jquery') );
			wp_enqueue_style('gtb-bootstrap', $this->url() . '/assets/css/bootstrap.min.css' );
			wp_enqueue_style('gtb-bootstrap-respsonive', $this->url() . '/assets/css/bootstrap-responsive.min.css' );
		}

	}

	public function get_config() {
		return $this->config;
	}

	/**
	 * Builds the url to the Genesis_Twitter_Bootstrap.php file
	 */
	public function url() {
		$url = explode('/wp-content', dirname(__FILE__) );
		$url = content_url() . $url[1];
		return apply_filters('genesis_twitter_bootstrap_url', $url);
	}

	/**
	 * Outputs the html5 doctype
	 */
	public function html5_doctype() {
		$doctype = '<!DOCTYPE html>
		<html dir="' . get_bloginfo("text_direction") . '" lang="' . get_bloginfo("language") . '">
		<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="' . get_bloginfo( "html_type" ) . ' charset=' . get_bloginfo( "charset" ) . '" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		';
		$doctype = preg_replace('/\t/', '', $doctype);
		echo $doctype;
	}

	/**
	 * Outputs script that enables various bootstrap components
	 */
	public function output_js_components() {
		?>
		<script>
		jQuery(document).ready(function($) {

			// == Begin unrecommended overrides ==
			var content_sidebar_wrap = $('#content-sidebar-wrap');

			content_sidebar_wrap.addClass('row');
			content_sidebar_wrap.find('#content').addClass('span8');
			content_sidebar_wrap.find('#sidebar').addClass('span4');
			// == End unrecommended overrides ==


			$('.dropdown-toggle').dropdown();
			$('.collapse').collapse();

			<?php if ( genesis_get_option('posts_nav') == 'numeric' ) { ?>

			// change class of pagination container
			var content = $('#content');
			content.find('.navigation').removeClass('navigation').addClass('pagination');

			<?php } ?>
		});
		</script>
		<?php
	}

	public function open_container_class() {
		echo '<div class="', $this->config['container_class'], '">';
	}

	public function close_container_class() {
		echo '</div><!-- .', $this->config['container_class'], '-->';
	}

	public function header_markup_open() {
		echo '<div id="header" class="row">';
		genesis_structural_wrap( 'header' );
	}

	function genesis_do_header() {

		echo '<div id="title-area" class="span8">';
		do_action( 'genesis_site_title' );
		do_action( 'genesis_site_description' );
		echo '</div><!-- end #title-area -->';

		if ( is_active_sidebar( 'header-right' ) || has_action( 'genesis_header_right' ) ) {
			echo '<div class="widget-area span4">';
			do_action( 'genesis_header_right' );
			dynamic_sidebar( 'header-right' );
			echo '</div><!-- end .widget-area -->';
		}

	}

	/**
	 * Outputs markup for the bootstrap nav
	 */
	public function bootstrap_do_nav($nav_output, $nav, $args) {
		$args = array(
			'theme_location'  => 'primary',
			'container'       => false,
			'menu_class'      => 'nav',
			'echo'            => 0,
			'walker'          => new Twitter_Navbar_Walker
		);

		$nav = wp_nav_menu($args);

		$nav_output = '<div class="navbar ' . $this->config['main_nav']['classes'] . '"><div class="navbar-inner">';

		if ( true == $this->config['main_nav']['responsive'] ) {
			$nav_output .= '
			<div class="container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
			</a>';
		}

		if ( ! empty($this->config['main_nav']['brand']) ) {
			$nav_output .= '<a class="brand" href="' . site_url() . '">' . $this->config['main_nav']['brand'] . '</a>';
		}

		$nav_output .= ( true == $this->config['main_nav']['responsive'] ) ? '<div class="nav-collapse">' . $nav . '</div><!-- .nav-collapse --></div><!-- .container -->' : $nav;

		$nav_output .= '</div><!-- .navbar-inner --></div><!-- .navbar -->';

		return $nav_output;
	}

	public function posts_nav() {
		( 'numeric' == genesis_get_option('posts_nav') ) ? genesis_numeric_posts_nav() : $this->pagination_older_newer();
	}

	public function pagination_older_newer() {

		$older_link = get_next_posts_link( apply_filters( 'genesis_older_link_text', g_ent( '&larr; ' ) . __( 'Older', 'genesis' ) ) );
		$newer_link = get_previous_posts_link( apply_filters( 'genesis_newer_link_text', __( 'Newer', 'genesis' ) . g_ent( ' &rarr;' ) ) );

		if ( genesis_get_option('posts_nav') == 'prev-next' ) {
			$older_link = get_previous_posts_link( apply_filters( 'genesis_prev_link_text', g_ent( '&laquo; ' ) . __( 'Previous Page', 'genesis' ) ) );
			$newer_link = get_next_posts_link( apply_filters( 'genesis_next_link_text', __( 'Next Page', 'genesis' ) . g_ent( ' &raquo;' ) ) );
		}

		$older = $older_link ? '<li class="previous">' . $older_link . '</li>' : '';
		$newer = $newer_link ? '<li class="next">' . $newer_link . '</li>' : '';

		$nav = '<ul class="pager">' . $older . $newer . '</ul><!-- .pager -->';

		if ( $older || $newer ) {
			echo $nav;
		}
	}
}