<?php

  /**
   * Add a widget for plugin drop in for the themes etc
   *
   * @since    0.0.2
   */
  class WRSS_Widget extends WP_Widget {

  	// Main constructor
  	public function __construct() {
  		parent::__construct(
  			'wrss_widget',
  			__( 'Women\'s Refuge Shielded Site Widget', 'WRSS' ),
  			array(
  				'customize_selective_refresh' => true,
  				'classname' => 'security' // This gives us a hatched shield default logo in admin tool.
  			)
  		);
  	}

  	// The widget form (for the backend )
  	public function form( $instance ) {

  		// Set widget defaults
  		$defaults = WRSS_default_args();

  		// Parse current settings with defaults
  		extract( wp_parse_args( ( array ) $instance, $defaults ) );

  		echo '<p>';
  		echo '	<label for="'.esc_attr( $this->get_field_id( 'icon_size' ) ).'">'.__('Size','WRSS').'</label>'."\n";
  		echo '	<select class="widefat" id="'.esc_attr( $this->get_field_id( 'icon_size' ) ).'" name= "'.esc_attr( $this->get_field_name( 'icon_size' ) ).'">'."\n";
  		echo '		<option value="large"'.( $icon_size == 'large'?' selected="selected"':'').'>'.__('Large', 'WRSS').'</option>'."\n";
  		echo '		<option value="small"'.( $icon_size == 'small'?' selected="selected"':'').'>'.__('Small', 'WRSS').'</option>'."\n";
  		echo '		<option value="custom"'.( $icon_size == 'custom'?' selected="selected"':'').'>'.__('Button', 'WRSS').'</option>'."\n";
  		echo '	</select>'."\n";
  		echo '</p>';

  		echo '<p>';
  		echo '	<label for="'.esc_attr( $this->get_field_id( 'modal_id' ) ).'">'.__('Modal ID','WRSS').'</label>'."\n";
  		echo '	<input class="widefat" id="'.esc_attr( $this->get_field_id( 'modal_id' ) ).'" name="'.esc_attr( $this->get_field_name( 'modal_id' ) ).'" type="text" value="'.esc_attr( $modal_id ).'" />'."\n";
  		echo '</p>';

  		echo '<p>';
  		echo '	<label for="'.esc_attr( $this->get_field_id( 'element_id' ) ).'">'.__('Element ID','WRSS').'</label>'."\n";
  		echo '	<input class="widefat" id="'.esc_attr( $this->get_field_id( 'element_id' ) ).'" name="'.esc_attr( $this->get_field_name( 'element_id' ) ).'" type="text" value="'.esc_attr( $element_id ).'" />'."\n";
  		echo '</p>';

  	}

  	// Update widget settings
  	public function update( $new_instance, $old_instance ) {
  		$instance = $old_instance;

  		$defaults = WRSS_default_args();

  		foreach( $defaults as $name => $val ){
  			$instance[ $name ] = isset( $new_instance[$name] ) ? sanitize_text_field( $new_instance[$name] ) : $val;
  		}

  		return $instance;
  	}

  	// Display the widget
  	public function widget( $args, $instance ) {
  		// WordPress core before_widget hook (always include )
  		echo $args['before_widget'];

  		$instance = wp_parse_args( $instance, WRSS_default_args() );

  		echo do_shortcode( '[womens_refuge_shield icon_size="'.$instance['icon_size'].'" modal_id="'.$instance['modal_id'].'" element_id="'.$instance['element_id'].'"]');

  		// WordPress core after_widget hook (always include )
  		echo $args['after_widget'];
  	}

  }


  /**
   * Register the widget so we can use it in the editors
   *
   * @since    0.0.2
   */
  function register_wrss_widget() {
  	register_widget( 'WRSS_Widget' );
  }
  add_action( 'widgets_init', 'register_wrss_widget' );


  /**
   * Tiny CSS file - purely to apply the shield logo to the Widget in the
   * editor tool.
   *
   * @since    0.0.2
   */
  function wrss_enqueue_admin_style( $hook ) {
  	wp_register_style( 'wrss_admin_css', WRSS_URL . '/css/admin.css', false, WRSS_VERSION );
    wp_enqueue_style( 'wrss_admin_css' );
  }
  add_action( 'admin_enqueue_scripts', 'wrss_enqueue_admin_style' );
