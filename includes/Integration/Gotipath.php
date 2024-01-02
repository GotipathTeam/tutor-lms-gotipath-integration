<?php
/**
 * Override Tutor default & integrate Gotipath
 *
 * @package TutorLMSGotipathIntegration\Integration
 * @since v1.0.0
 */

namespace Tutor\GotipathIntegration\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add action & filter to override Tutor default
 * & incorporate Gotipath
 *
 * @version  1.0.0
 * @package  TutorLMSGotipathIntegration\Integration
 * @category Integration
 * @author   Gotipath <support@gotipath.com>
 */
class Gotipath {

	/**
	 * Register action & filter hooks
	 *
	 * @since v1.0.0
	 */
	public function __construct() {
		add_filter( 'tutor_preferred_video_sources', __CLASS__ . '::filter_preferred_sources' );
		add_filter( 'tutor_single_lesson_video', __CLASS__ . '::filter_lesson_video', 10, 3 );
		add_filter( 'tutor_course/single/video', __CLASS__ . '::filter_course_video' );
		add_action( 'tutor_after_video_meta_box_item', __CLASS__ . '::meta_box_item', 10, 2 );
		add_filter( 'should_tutor_load_template', __CLASS__ . '::filter_template_load', 99, 2 );
		add_action( 'tutor_after_video_source_icon', __CLASS__ . '::video_source_icon' );
	}

	/**
	 * Filter tutor default video sources
	 *
	 * @since v1.0.0
	 *
	 * @param array $video_source default video sources.
	 *
	 * @return array
	 */
	public static function filter_preferred_sources( array $video_source ): array {
		$video_source['gotipath'] = array(
			'title' => __( 'Gotipath', 'tutor-lms-gotipath-integration' ),
			'icon'  => 'gotipath',
		);

		return $video_source;
	}

	/**
	 * Filter single lesson video on the course content
	 * (aka spotlight) section
	 *
	 * @since v1.0.0
	 *
	 * @param string $content  tutor's default lesson content.
	 *
	 * @return string
	 */
	public static function filter_lesson_video( $content ) {
		$gotipath_video_id = self::is_gotipath_video_source();
		if ( false !== $gotipath_video_id ) {
			$content = self::get_embed_video( $gotipath_video_id );
		}
		return $content;
	}

	/**
	 * Filter course intro video if source if gotipath net
	 *
	 * @since v1.0.0
	 *
	 * @param string $content course intro video content.
	 *
	 * @return string
	 */
	public static function filter_course_video( $content ) {
		$gotipath_video_id = self::is_gotipath_video_source();
		if ( false !== $gotipath_video_id ) {
			$content = self::get_embed_video( $gotipath_video_id );
		}
		return $content;
	}

	/**
	 * Add gotipath net source field on the meta box
	 *
	 * @since v1.0.0
	 *
	 * @param string $style display style.
	 * @param object $post  post object.
	 *
	 * @return void
	 */
	public static function meta_box_item( $style, $post ):void {
		$video           = maybe_unserialize( get_post_meta( $post->ID, '_video', true ) );
		$video_source    = tutor_utils()->avalue_dot( 'source', $video, 'gotipath' );
		$gotipath_source = tutor_utils()->avalue_dot( 'source_gotipath', $video );
		?>
		<div class="tutor-mt-16 video-metabox-source-item video_source_wrap_gotipath tutor-dashed-uploader" style="<?php echo esc_attr( $style ); ?>">
			<input class="tutor-form-control" type="text" name="video[source_gotipath]" value="<?php echo esc_attr( $gotipath_source ); ?>" placeholder="<?php esc_html_e( 'Place Your Gotipath Videos\'s Iframe URL Here', 'tutor-lms-gotipath-integration' ); ?>">
		</div>
		<script>
			// Don't show input field if video source is not gotipath net.
			var gotipath = document.querySelector('.video_source_wrap_gotipath');
			var videoSource = document.querySelector('.tutor_lesson_video_source.no-tutor-dropdown');
			var icon = document.querySelector('i[data-for=gotipath]');
			if (videoSource) {
				if (videoSource.value != 'gotipath') {
					gotipath.style = 'display:none;'
				}
				
				if (videoSource.value == 'gotipath') {
					icon.style = 'display:block;';
				} else {
					icon.style.display = 'display:none;';
				}

				videoSource.onchange = (e) => {
					console.log(e.target.value);
					if (e.target.value == 'gotipath') {
						icon.style = 'display:block;';
					} else {
						icon.style = 'display:none;';
						console.log('none');
					}
				}
			}
		</script>
		<?php
	}

	/**
	 * If video source is gotipath net then let not
	 * load the template from tutor
	 *
	 * @since v1.0.0
	 *
	 * @param boolean $should_load should load template.
	 * @param string  $template  template name.
	 *
	 * @return bool
	 */
	public static function filter_template_load( bool $should_load, string $template ):bool {
		if ( false !== self::is_gotipath_video_source() && 'single.video.gotipath' === $template ) {
			$should_load = false;
		}
		return $should_load;
	}

	/**
	 * Check video source is gotipath
	 *
	 * @since v1.0.0
	 *
	 * @return mixed  video source if exists otherwise false
	 */
	public static function is_gotipath_video_source() {
		$video_info = tutor_utils()->get_video_info();
		$response   = false;
		if ( $video_info ) {
			$gotipath_video_id = tutor_utils()->array_get( 'source_gotipath', $video_info );
			$video_source   = $video_info->source;
			if ( 'gotipath' === $video_source && '' !== $gotipath_video_id ) {
				$response = $gotipath_video_id;
			}
		}
		return $response;
	}

	/**
	 * Get embedded gotipath net video
	 *
	 * @since v1.0.0
	 *
	 * @param string $gotipath_video_id video id for embedding.
	 *
	 * @return string video content
	 */
	private static function get_embed_video( $gotipath_video_id ):string {
		ob_start();
		?>
		<div class="tutor-video-player">
			<div style="position: relative; padding-top: 56.25%;">
				<iframe src="<?php echo esc_attr( $gotipath_video_id ); ?>" loading="lazy" style="border: none; position: absolute; top: 0; height: 100%; width: 100%;" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" allowfullscreen="true"></iframe>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Video source icon that will be visible on the
	 * video source dropdown
	 *
	 * @since v1.0.0
	 *
	 * @return void
	 */
	public static function video_source_icon() {
		echo '<i class="tutor-icon-video-camera-o" data-for="gotipath"></i>';
	}
}
