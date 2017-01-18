<?php
/*
Plugin Name: AH About Widget
Plugin URI:  https://wordpress.org/plugins/ah-about-widget/
Description: Zeigt eine "Über mich" Box mit dem Gravatar und der Beschreibung der Autoren-Bio in der Sidebar oder dem Footer an.
Version:     1.0.0
Author:      Andreas Hecht
Author URI:  https://andreas-hecht.com
License:     GPL2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ah_about
Domain Path: /languages
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Registers our Widget.
 */
add_action( 'widgets_init', function(){
	register_widget( 'AH_About_Widget' );
});


/**
 * Loads the Textdomain for the english translation
 */
function ah_about_widget_load_plugin_textdomain() {
    load_plugin_textdomain( 'ah_about', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'ah_about_widget_load_plugin_textdomain' );



class AH_About_Widget extends WP_Widget {

 /**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

/**
 * Register widget with WordPress.
 */
	public function __construct() {

		$this->defaults = array(
			'title'          => '',
			'alignment'	     => 'left',
			'user'           => '',
			'size'           => '45',
			'author_info'    => '',
			'page'           => '',
			'page_link_text' => __( 'Weiter lesen', 'ah_about' ) . ' &raquo;',
		);

		$widget_ops = array(
			'classname'   => 'user-profile',
			'description' => __( 'Zeigt eine kleine "Über mich" Box mit dem Autor-Gravatar in der Sidebar an.', 'ah_about' ),
		);

		$control_ops = array(
			'id_base' => 'user-profile',
			'width'   => 200,
			'height'  => 250,
		);

		$this->WP_Widget( 'user-profile', __( 'AH About Widget', 'ah_about' ), $widget_ops, $control_ops );
        
        
        // Load css only when widget is active
        if ( is_active_widget( false, false, $this->id_base, true ) ) {
            
        // Make function pluggable
        if (! function_exists('ah_about_css')) {
    
        function ah_about_css() {
        
			wp_enqueue_style( 'about-me', plugins_url( '/css/style.css', __FILE__ ) );
            }
        }
    }
add_action( 'wp_enqueue_scripts', 'ah_about_css' );
        
}

/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $before_widget;
        
        echo "<div class='about-widget'>";

			if ( ! empty( $instance['title'] ) )
				echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

			$text = '';

			if ( ! empty( $instance['alignment'] ) )
				$text .= '<span id="about-gravatar" class="align' . esc_attr( $instance['alignment'] ) . '">';

			$text .= get_avatar( $instance['user'], $instance['size'] );

			if( ! empty( $instance['alignment'] ) )
				$text .= '</span>';

				$text .= get_the_author_meta( 'description', $instance['user'] );

			$text .= $instance['page'] ? sprintf( ' <br /><a class="aboutme-link" href="%s">%s</a>', get_page_link( $instance['page'] ), $instance['page_link_text'] ) : '';

			echo wpautop( $text );
        
        echo "</div>";

		echo $after_widget;
	}

	public function form( $instance ) {

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titel', 'ah_about' ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_name( 'user' ); ?>"><?php _e( 'Wähle einen User aus. Die E-Mail Adresse dieses Accounts wird genutzt, um das Gravatar Bild anzuzeigen. Wenn Du keinen Gravatar hast, besorge Dir einen unter: <a href="https://de.gravatar.com/">Gravatar.com</a>. ', 'ah_about' ); ?></label><br /><br />
			<?php wp_dropdown_users( array( 'who' => 'authors', 'name' => $this->get_field_name( 'user' ), 'selected' => $instance['user'] ) ); ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'Gravatar Größe', 'ah_about' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>">
				<?php
				$sizes = array( __( 'Klein', 'ah_about' ) => 45, __( 'Mittel', 'ah_about' ) => 65, __( 'Groß', 'ah_about' ) => 85, __( 'Sehr Groß', 'ah_about' ) => 125 );
				$sizes = apply_filters( 'ah_gravatar_sizes', $sizes );
				foreach ( (array) $sizes as $label => $size ) { ?>
					<option value="<?php echo absint( $size ); ?>" <?php selected( $size, $instance['size'] ); ?>><?php printf( '%s (%spx)', $label, $size ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'alignment' ); ?>"><?php _e( 'Gravatar Ausrichtung', 'ah_about' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'alignment' ); ?>" name="<?php echo $this->get_field_name( 'alignment' ); ?>">
				<option value="none clear"><?php _e( 'Keine', 'ah_about' ); ?></option>
				<option value="left" <?php selected( 'left', $instance['alignment'] ); ?>><?php _e( 'Links', 'ah_about' ); ?></option>
				<option value="right" <?php selected( 'right', $instance['alignment'] ); ?>><?php _e( 'Rechts', 'ah_about' ); ?></option>
			</select>
		</p>
		<h4><?php _e( 'Widget Text / Autor Bio', 'ah_about' ); ?></h4>
		<fieldset>
			<legend><?php _e( 'Den Text über Dich, der in diesem Widget angezeigt wird, sind die biographischen Angaben in Deinem Userprofil. Bitte fülle daher Dein Userprofil aus. (Benutzer => Dein Profil).', 'ah_about' ); ?></legend>
		</fieldset>
		<h4><?php _e( 'Link zu Deiner Über mich Seite', 'ah_about' ); ?></h4>
		<p>
			<label for="<?php echo $this->get_field_name( 'page' ); ?>"><?php _e( 'Wähle Deine "Über mich" Seite aus der Liste unten aus. Diese Seite wird durch das "Über mich" Widget verlinkt. <br />', 'ah_about' ); ?></label><br />
			<?php wp_dropdown_pages( array( 'name' => $this->get_field_name( 'page' ), 'show_option_none' => __( 'Keine', 'ah_about' ), 'selected' => $instance['page'] ) ); ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'page_link_text' ); ?>"><?php _e( 'Der Text für den Link am Ende des "Über mich" Widgets.', 'ah_about' ); ?><br /><br /></label>
			<input type="text" id="<?php echo $this->get_field_id( 'page_link_text' ); ?>" name="<?php echo $this->get_field_name( 'page_link_text' ); ?>" value="<?php echo esc_attr( $instance['page_link_text'] ); ?>" class="widefat" />
		</p>
<?php
	}

 /**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
public function update( $new_instance, $old_instance ) {

		$new_instance['title']                         = strip_tags( $new_instance['title'] );
		$new_instance['bio_text']                  = current_user_can( 'unfiltered_html' ) ? $new_instance['bio_text'] : ah_formatting_kses( $new_instance['bio_text'] );
		$new_instance['page_link_text']      = strip_tags( $new_instance['page_link_text'] );

		return $new_instance;

	}

}