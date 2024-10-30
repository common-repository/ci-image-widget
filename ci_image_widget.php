<?php
	/**
	 * Plugin Name: The Image Widget
	 * Plugin URI: https://wordpress.org/plugins/ci-image-widget/
	 * Description: A simple image widget brought to you by the folks over at CSSIgniter.com
	 * Version: 1.0.1
	 * Author: The CSSIgniter Team
	 * Author URI: http://www.cssigniter.com
	 * License: GPL2
	 * Text Domain: ci-image-widget
	 */

	add_action( 'widgets_init', 'ci_image_widget_init' );
	function ci_image_widget_init() {
		register_widget( 'CI_Image_Widget' );
	}

	class CI_Image_Widget extends WP_Widget {

		protected $defaults = array(
			'title'       => '',
			'link_url'    => '',
			'link_target' => '',
			'image_id'    => '',
			'size'        => 'large'
		);

		public function __construct() {
			load_plugin_textdomain( 'ci-image-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			$widget_details = array(
				'classname'   => 'CI_Image_Widget',
				'description' => __( 'Upload and add an image in any of your widgetized sidebars.', 'ci-image-widget' )
			);

			parent::__construct( 'ci_image_widget', __( 'The Image Widget', 'ci-image-widget' ), $widget_details );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts')  );
			add_action( 'in_plugin_update_message-'. plugin_basename( __FILE__ ), array( $this, 'update_message'), 10, 2 );
		}

		public function admin_scripts() {
			global $pagenow;

			if ( in_array( $pagenow, array( 'widgets.php', 'customize.php', 'post.php' ) ) ) {
				wp_enqueue_media();
				wp_enqueue_script( 'ci-image-widget-admin-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/admin-scripts.js', array( 'jquery' ) );
				wp_enqueue_style( 'ci-image-widget-admin-styles', plugin_dir_url( __FILE__ ) . 'assets/css/admin-styles.css' );
			}
		}

		private function sanitize_image_size( $value ) {
			$valid_values = array(
				'full',
				'thumbnail',
				'medium',
				'large',
				'post-thumbnail'
			);

			if( ! in_array( $value, $valid_values ) ) {
				$value = 'large';
			}

			return $value;
		}

		public function register_scripts() {
			wp_register_script( 'ci-image-widget-magnific-popup', plugins_url( 'assets/js/jquery.magnific-popup.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_register_script( 'ci-image-widget-scripts', plugins_url( 'assets/js/scripts.js', __FILE__ ), array( 'ci-image-widget-magnific-popup' ), '1.0', true );
			wp_register_style( 'ci-image-widget-popup', plugins_url( 'assets/css/popup.css', __FILE__ ) );
			wp_enqueue_script( 'ci-image-widget-scripts' );
			wp_enqueue_style( 'ci-image-widget-popup' );
		}

		public function update_message( $plugin_data, $r ) {
			if ( ! empty( $r->upgrade_notice ) ) {
				printf( '<p style="margin: 3px 0 0 0; border-top: 1px solid #ddd; padding-top: 3px">%s</p>', $r->upgrade_notice );
			}
		}

		public function widget( $args, $instance ) {

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$link_target = $instance['link_target'] == 1 ? 'target="_blank"' : '';
			$image_size  = $instance['size'];
			$image_info  = wp_prepare_attachment_for_js( $instance['image_id'] );
			$image_src   = wp_get_attachment_image_src( intval( $instance['image_id'] ), $image_size );
			$link_url    = $instance['link_url'] ? $instance['link_url'] : $image_info['url'];
			$lightbox    = $instance['link_url'] ? '' : 'class="ci-image-widget-popup"';

			echo $args['before_widget'];
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			}

			if( ! empty( $image_src ) ) :
				?>

				<a href="<?php echo esc_url( $link_url ) ?>" <?php echo $link_target; ?> <?php echo $lightbox; ?>>
					<img src="<?php echo esc_url( $image_src[0] ); ?>" alt="<?php echo esc_attr( $image_info['alt'] ); ?>">
				</a>

				<?php
			endif;

			echo $args['after_widget'];
		}

		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title']       = sanitize_text_field( $new_instance['title'] );
			$instance['link_url']    = esc_url_raw( $new_instance['link_url'] );
			$instance['link_target'] = (int) isset( $new_instance['link_target'] );
			$instance['image_id']    = intval( $new_instance['image_id'] );
			$instance['size']        = $this->sanitize_image_size( $new_instance['size'] );

			return $instance;
		}

		public function form( $instance ) {

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title       = $instance['title'];
			$link_url    = $instance['link_url'];
			$link_target = $instance['link_target'];
			$image_id    = $instance['image_id'];
			$size        = $instance['size'];

			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
					<?php _e( 'Title:', 'ci-image-widget' ); ?>
				</label>

				<input
					type="text"
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
					value="<?php echo esc_attr( $title ); ?>"/>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'link_url' ) ); ?>">
					<?php _e( 'Link URL - If left empty the image will pop up in a lightbox', 'ci-image-widget' ); ?>
				</label>

				<input
					type="text"
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'link_url' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'link_url' ) ); ?>"
					value="<?php echo esc_url( $link_url ); ?>"/>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'link_target' ) ); ?>">
					<input
						type="checkbox"
						id="<?php echo esc_attr( $this->get_field_id( 'link_target' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'link_target' ) ); ?>"
						value="1" <?php checked( $link_target, 1 ); ?> />
					<?php _e( 'Open link in new tab', 'ci-image-widget' ); ?>
				</label>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'image' ) ); ?>">
					<?php _e( 'Image:', 'ci-image-widget' ); ?>
				</label>
				<div class="ci-image-widget-thumb-container">
					<?php $thumb_src = wp_get_attachment_image_src( intval( $image_id ), 'thumbnail' ); ?>
					<?php if( ! empty( $thumb_src ) ) : ?>
						<img src="<?php echo esc_url( $thumb_src[0] ); ?>" class="ci-image-widget-thumb" />
					<?php endif; ?>
				</div>

				<input
					type="hidden"
					class="ci-image-widget-media-id"
					name="<?php echo esc_attr( $this->get_field_name( 'image_id' ) ); ?>"
					id="<?php echo esc_attr( $this->get_field_id( 'image_id' ) ); ?>"
					value="<?php echo intval( $image_id ); ?>"
					/>

				<input
					id="<?php echo esc_attr( $this->get_field_id( 'image' ) ); ?>_button"
					class="ci-image-widget-upload-button button"
					type="button"
					value="<?php echo esc_attr__( 'Select Image', 'ci-image-widget' ); ?>"/>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>">
					<?php _e( 'Image size:', 'ci-image-widget' ); ?>
				</label>
				<select class="ci-image-widget-size-select" name="<?php echo esc_attr( $this->get_field_name( 'size' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>">
					<option value="large" <?php selected( $size, 'large' ); ?>><?php strip_tags( _ex( 'Large', 'Image size', 'ci-image-widget' ) ); ?></option>
					<option value="thumbnail" <?php selected( $size, 'thumbnail' ); ?>><?php strip_tags( _ex( 'Thumbnail', 'Image size', 'ci-image-widget' ) ); ?></option>
					<option value="medium" <?php selected( $size, 'medium' ); ?>><?php strip_tags( _ex( 'Medium', 'Image size', 'ci-image-widget' ) ); ?></option>
					<option value="full" <?php selected( $size, 'full' ); ?>><?php strip_tags( _ex( 'Full', 'Image size', 'ci-image-widget' ) ); ?></option>
					<?php if( get_theme_support( 'post-thumbnails' ) ) : ?>
						<option value="post-thumbnail" <?php selected( $size, 'post-thumbnail' ); ?>><?php strip_tags( _ex( 'Post Thumbnail', 'Image size', 'ci-image-widget' ) ); ?></option>
					<?php endif; ?>
				</select>
			</p>
			<?php
		}
	} //CI_Image_Widget
