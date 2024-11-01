<?php

/**
 * @package WP Nutrition Facts
 * @link http://www.kilukrumedia.com
 * @copyright Copyright &copy; 2014, Kilukru Media
 * @version: 1.0.2
 */

//error_reporting(E_ALL);
//ini_set('display_errors', 'On');
//ini_set('log_errors', 'On');
//ini_set('error_reporting', E_ALL);


/**
 * Init the Class with MobileESP Class init
 */
class WP_Nutrition_Facts {

	// Set version of element
	public $wp_version;
	public $version_css;
	public $version_js;
	public $minimum_version_functions;
	public $minimum_PHP;
	public $minimum_WP;

	public $label_id;
	public $rda;
	public $nutrional_fields;

	// Filename of log file.
	public $log_file;

	// Flag whether there should be logging.
	public $do_log;

	// Options of the plug-in
	public $options;

	// Set admin notices informations
	public $admin_notices_infos;

	//function WP_Mobilizer() {
	public function __construct() {
		global $wp_version;

		// Current WP version
		$this->wp_version 	= $wp_version;

		// Minimum requirements
		$this->minimum_PHP 	= '5.0';
		$this->minimum_WP 	= '3.6.0';

		// Version of assets files
		$this->version_css 	= '1.0.2';
		$this->version_js 	= '1.0.2';

		// Set admin notices into array mode
		$this->admin_notices_infos 	= array();

		// Stop the plugin if we missed the requirements
		if ( !$this->required_version() || !$this->required_version_php() ){
			return;
		}

		$this->label_id 	= 'wpnf';

		/* RDA SETTINGS */
		$this->rda = array(
			'totalfat' 			=> 65,
			'satfat' 			=> 20,
			'cholesterol' 		=> 300,
			'sodium' 			=> 2400,
			'carbohydrates' 	=> 300,
			'fiber' 			=> 25,
			'protein' 			=> 50,
			'vitamin_a' 		=> 5000,
			'vitamin_c' 		=> 60,
			'calcium' 			=> 1000,
			'iron' 				=> 18,
			'potassium' 		=> 4700,
		);

		/* BASE NUTRIIONAL FIELDS */
		$this->nutrional_fields = array(
			'servingsize' 		=> __('Serving Size','wp_nutrifacts'),
			'servings' 			=> __('Servings','wp_nutrifacts'),
			'calories' 			=> __('Calories','wp_nutrifacts'),
			'totalfat' 			=> __('Total Fat','wp_nutrifacts'),
			'satfat' 			=> __('Saturated Fat','wp_nutrifacts'),
			'transfat' 			=> __('Trans. Fat','wp_nutrifacts'),
			'cholesterol' 		=> __('Cholesterol','wp_nutrifacts'),
			'sodium' 			=> __('Sodium','wp_nutrifacts'),
			'potassium' 		=> __('Potassium','wp_nutrifacts'),
			'carbohydrates' 	=> __('Carbohydrates','wp_nutrifacts'),
			'fiber' 			=> __('Fiber','wp_nutrifacts'),
			'sugars' 			=> __('Sugars','wp_nutrifacts'),
			'protein' 			=> __('Protein','wp_nutrifacts')
		);


		// Hook for init element
		add_action( 'init', 						array( &$this, 'init' 						), 5 );
		//add_action( 'admin_init', 					array( &$this, 'admin_init'					) );
		add_action( 'wp_head', 						array( &$this, 'wp_head'					) );
		//add_action( 'widgets_init', 				array( &$this, 'widgets_init'				) );

		// Hook to save post meta
		add_action( 'save_post', 					array( &$this, 'save_post'					), 1, 2 );

		// Add the script and style files
		add_action('admin_enqueue_scripts', 		array( &$this, 'load_scripts'				) );
		add_action('admin_enqueue_scripts', 		array( &$this, 'load_styles'					) );
		add_action('wp_enqueue_scripts', 			array( &$this, 'load_styles_frontend'		) );

		// Add Shortcode
		add_shortcode( 'wpnf-label', 				array( &$this, 'shortcode_show_label'			) );

		// Add Dashboard Widget
		add_action( 'wp_dashboard_setup', 			array( &$this, 'dashboard_setup'				) );

		// Hook for options elements
		add_action( 'add_meta_boxes', 				array(&$this, 'add_meta_boxes' 		) );

		// Hook for custom post type colum
		add_filter( 'manage_edit-wp-nutrition-facts_columns', 	array(&$this, 'colums_labels' ) );
		add_filter( 'manage_posts_custom_column', 				array(&$this, 'colums_labels_row'), 10, 2 );

		// Hook for Custom Message
		add_filter( 'post_updated_messages', 		array(&$this, 'post_updated_messages' ) );
	}


	/**
	 * Runs after WordPress has finished loading but before any headers are sent
	 */
	public function init() {
		// Load Language files
		if ( !defined( 'WP_PLUGIN_DIR' ) ) {
			load_plugin_textdomain( 'wp_nutrifacts', str_replace( ABSPATH, '', dirname( __FILE__ ) ) );
		} else {
			load_plugin_textdomain('wp_nutrifacts', false, WPNUTRIFACTS_PLUGIN_DIRNAME . '/languages/' );
		}

		$labels = array(
			'name' 					=> __('Nutrition Facts Labels','wp_nutrifacts'),
			'singular_name' 		=> __('Nutrition Facts Labels','wp_nutrifacts'),
			'add_new' 				=> __('Add New','wp_nutrifacts'),
			'add_new_item' 			=> __('Add New Label','wp_nutrifacts'),
			'edit_item'				=> __('Edit Label','wp_nutrifacts'),
			'new_item' 				=> __('New Label','wp_nutrifacts'),
			'all_items' 			=> __('All Labels','wp_nutrifacts'),
			'view_item' 			=> __('View Label','wp_nutrifacts'),
			'search_items' 			=> __('Search Labels','wp_nutrifacts'),
			'not_found' 			=> __('No labels found','wp_nutrifacts'),
			'not_found_in_trash' 	=> __('No labels found in Trash','wp_nutrifacts'), 
			'parent_item_colon' 	=> '',
			'menu_name' 			=> __('Nutrition Facts','wp_nutrifacts')
		);
		
		$args = array(
			'labels' 				=> $labels,
			'public' 				=> false,
			'exclude_from_search' 	=> true,
			'publicly_queryable' 	=> false,
			'show_ui' 				=> true, 
			'show_in_menu' 			=> true, 
			'query_var' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false, 
			'hierarchical' 			=> false,
			'menu_position' 		=> null,
			'menu_icon' 			=> plugins_url('images/wpnutrifacts-logo-mini.png', __FILE__),
			'supports' 				=> array( 'title' )
		);
		register_post_type('wp-nutrition-facts', $args);

	}

	/**
	 * Runs after WordPress has finished loading but before any headers are sent
	 */
	public function admin_init() {
	}


	/**
	 * add_meta_boxes is to add meta boxed for facts elements
	 */
	public function add_meta_boxes() {
	
		add_meta_box( 'wpnutrifacts_create_meta_boxes', __('Nutritional Facts Labels Options','wp_nutrifacts'), array(&$this, 'create_meta_boxes' ), 'wp-nutrition-facts', 'normal', 'default' );
		
		add_meta_box( 'wpnutrifacts_sharing_love_meta_boxes', __('Nutritional Facts','wp_nutrifacts'), array(&$this, 'sharing_love_meta_boxes' ), 'wp-nutrition-facts', 'side', 'default' );
		//add_meta_box( 'wpnutrifacts_ads_meta_boxes', __('Ads','wp_nutrifacts'), array(&$this, 'sharing_love_meta_boxes' ), 'wp-nutrition-facts', 'side', 'default' );
		
	}

	/**
	 * Check if meta value exist and return if exist
	 */
	public function check_meta_value( $label, $array, $to_check = 'empty' ){

		if( $to_check == 'empty' && isset($array[$label]) && isset($array[$label][0]) && ( !empty($array[$label][0]) || $array[$label][0] == '0' ) ){
			return true;
		}else if( $to_check == 'empty' && isset($array[$label]) && isset($array[$label][0]) && empty($array[$label][0]) ){
			return false;
		}

	}

	/**
	 * Get meta value for elements
	 */
	public function get_meta_value( $label, $array, $default = '' ){

		if( $this->check_meta_value($label, $array ) === true ){
			return $array[$label][0];
		}else{
			return $default;
		}

	}

	/**
	 * add_meta_boxes is to add meta boxed for facts elements
	 */
	public function create_meta_boxes() {
		global $post;
		$post_id = $post->ID;
		$meta_values = get_post_meta( $post_id );
		if( defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != WPNUTRIFACTS_DEFAULT_LANGUAGE ){
			$post_id_default_lang = icl_object_id($post_id, 'wp-nutrition-facts', false, WPNUTRIFACTS_DEFAULT_LANGUAGE );
			//$meta_values_lang = get_post_meta( $post_id_default_lang );
		}
		
		$pages = get_posts( array( 'post_type' => 'page', 'numberposts' => -1 ) );
		$posts = get_posts( array( 'numberposts' => -1 ) );
		
		$selected_page_id = isset($meta_values[ $this->label_id . '_pageid']) ? $meta_values[$this->label_id . '_pageid'][0] : 0;

		?>
		<div id="wpnf-section" class="wpnf-section-divider">
			<label for="<?php echo $this->label_id; ?>_pageid">
				<?php _e('Section', 'wp_nutrifacts'); ?>
			</label>
			<select id="<?php echo $this->label_id; ?>_pageid" name="<?php echo $this->label_id; ?>_pageid" style="float: left;">
				<option value=""><?php _e('Select a Page...', 'wp_nutrifacts'); ?></option>
				<optgroup label="<?php _e('Pages'); ?>">
					<?php foreach($pages as $page) { ?>
					<option value="<?php echo $page->ID ?>"<?php if($selected_page_id == $page->ID) echo ' selected="selected"'; ?>><?php echo $page->post_title ?> [id: <?php echo $page->ID ?>]</option>
					<?php } ?>
				</optgroup>
				<optgroup label="<?php _e('Posts', 'wp_nutrifacts'); ?>">
					<?php foreach($posts as $post) { ?>
					<option value="<?php echo $post->ID ?>"<?php if($selected_page_id == $post->ID) echo ' selected="selected"'; ?>><?php echo $post->post_title ?> [id: <?php echo $post->ID ?>]</option>
					<?php } ?>
				</optgroup>

				<?php

				$args = array(
				   'public'   => true,
				   '_builtin' => false
				);

				$output = 'objects'; // names or objects, note names is the default
				$operator = 'and'; // 'and' or 'or'

				$post_types = get_post_types( $args, $output, $operator );
				
				foreach ($post_types as $name => $post_type ) {

					$custom_posts = get_posts( array( 'post_type' => $name, 'numberposts' => -1 ) );
					if( $custom_posts && count($custom_posts) > 0 ){
						echo '<optgroup label="' . $post_type->labels->name . '">';
						foreach( $custom_posts as $post) {
							echo '<option value="' . $post->ID . '"' . ( $selected_page_id == $post->ID ? ' selected="selected"' : '' ) . '>' . $post->post_title . ' [id: ' . $post->ID . ']</option>';
						}
						echo '</optgroup>';
					}
				
				}

				?>
			</select>

			<?php echo $this->show_icon( __('Select a page, post or custom post type to remember where is the nutrition facts.')); ?>

			<div class="wpnf-clear"></div>
		</div>
		<?php

		$html = "";
		$html .= "<div class='wpnf-label' id='wpnf-example'>\n";
		
			$html .= "	<div class='heading'>" . __("Nutrition Facts", 'wp_nutrifacts') . "</div>\n";
			
			$html .= "	<div class='item_row wpnf_cf'>";
				$html .= "	" . __("Per", 'wp_nutrifacts') . " <span id='wpnf_label_servingsize'>" . $this->get_meta_value($this->label_id . '_servingsize', $meta_values) . "</span>";
				$html .= "	(<span id='wpnf_label_servings'>" . $this->get_meta_value($this->label_id . '_servings', $meta_values) . "</span>)";
			$html .= "	</div>\n";
			
			$html .= "	<hr />\n";
			//$html .= "	<div class='amount-per small item_row noborder'></div>\n";
			
			$html .= "	<div class='amount-per small item_row wpnf_cf'>\n";
			$html .= "		<span class='f-left'>" . __("Amount", 'wp_nutrifacts') . "</span>\n";
			$html .= "		<span class='f-right'>% " . __("Daily Value", 'wp_nutrifacts') . "*</span>\n";
			$html .= "	</div>\n";


			$label_id = $this->label_id . '_calories';
			$html .= "	<div class='item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span class='f-left'><strong>" . __("Calories", 'wp_nutrifacts') . "</strong> <span id='wpnf_label_calories'>" . $this->get_meta_value($label_id, $meta_values) . "</span></span>\n";
			//$html .= "		<span class='f-right'>" . __("Calories from Fat", 'wp_nutrifacts') . " <span id='wpnf_label_calories'>" . ( $this->check_meta_value($this->label_id . '_totalfat', $meta_values) ? $this->get_meta_value($this->label_id . '_totalfat', $meta_values) * 9 : '' ) . "</span></span>\n";
			$html .= "	</div>\n";
			

			$label_id = $this->label_id . '_totalfat';
			$html .= "	<div class='item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span class='f-left'><strong>" . __("Total Fat", 'wp_nutrifacts') . "</strong> <span id='wpnf_label_totalfat'>" . $this->get_meta_value($label_id, $meta_values) . "</span><span class='unit'>g</span></span>\n";
			$html .= "		<span class='f-right' id='wpnf_label_totalfat_percent'>" . ( $this->check_meta_value($label_id, $meta_values) ? $this->percentage($meta_values[$label_id][0], $this->rda['totalfat']) . '%' : '' ) . "</span>\n";
			$html .= "	</div>\n";
			

			$label_id = $this->label_id . '_satfat';
			$html .= "	<div class='indent item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span class='f-left'>" . __("Saturated Fat", 'wp_nutrifacts') . " <span id='wpnf_label_satfat'>" . $this->get_meta_value($label_id, $meta_values) . "</span><span class='unit'>g</span></span>\n";
			$html .= "		<span class='f-right' id='wpnf_label_satfat_percent'>" . ( $this->check_meta_value($label_id, $meta_values) ? $this->percentage($meta_values[$label_id][0], $this->rda['satfat']) . '%' : '' ) . "</span>\n";
			$html .= "	</div>\n";
			

			$label_id = $this->label_id . '_transfat';
			$html .= "	<div class='indent item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span>" . __("Trans Fat", 'wp_nutrifacts') . " <span id='wpnf_label_transfat'>" . $this->get_meta_value($label_id, $meta_values) . "</span><span class='unit'>g</span></span>";
			$html .= "	</div>\n";
			

			$label_id = $this->label_id . '_cholesterol';
			$html .= "	<div class='item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span class='f-left'><strong>" . __("Cholesterol", 'wp_nutrifacts') . "</strong> <span id='wpnf_label_cholesterol'>" . $this->get_meta_value($label_id, $meta_values) . "</span><span class='unit'>mg</span></span>\n";
			$html .= "		<span class='f-right' id='wpnf_label_cholesterol_percent'>" . ( $this->check_meta_value($label_id, $meta_values) ? $this->percentage($meta_values[$label_id][0], $this->rda['cholesterol']) . '%' : '' ) . "</span>\n";
			$html .= "	</div>\n";
			

			$label_id = $this->label_id . '_sodium';
			$html .= "	<div class='item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span class='f-left'><strong>" . __("Sodium", 'wp_nutrifacts')."</strong> <span id='wpnf_label_sodium'>" . $this->get_meta_value($label_id, $meta_values) . "</span><span class='unit'>mg</span></span>\n";
			$html .= "		<span class='f-right' id='wpnf_label_sodium_percent'>" . ( $this->check_meta_value($label_id, $meta_values) ? $this->percentage($meta_values[$label_id][0], $this->rda['sodium']) . '%' : '' ) . "</span>\n";
			$html .= "	</div>\n";


			$label_id = $this->label_id . '_potassium';
			$html .= "	<div class='item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span class='f-left'><strong>" . __("Potassium", 'wp_nutrifacts')."</strong> <span id='wpnf_label_potassium'>" . $this->get_meta_value($label_id, $meta_values) . "</span><span class='unit'>mg</span></span>\n";
			$html .= "		<span class='f-right' id='wpnf_label_potassium_percent'>" . ( $this->check_meta_value($label_id, $meta_values) ? $this->percentage($meta_values[$label_id][0], $this->rda['potassium']) . '%' : '' ) . "</span>\n";
			$html .= "	</div>\n";
			

			$label_id = $this->label_id . '_carbohydrates';
			$html .= "	<div class='item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span class='f-left'><strong>" . __("Total Carbohydrate", 'wp_nutrifacts') . "</strong> <span id='wpnf_label_carbohydrates'>" . $this->get_meta_value($label_id, $meta_values) . "</span><span class='unit'>g</span></span>\n";
			$html .= "		<span class='f-right' id='wpnf_label_carbohydrates_percent'>" . ( $this->check_meta_value($label_id, $meta_values) ? $this->percentage($meta_values[$label_id][0], $this->rda['carbohydrates']) . '%' : '' ) . "</span>\n";
			$html .= "	</div>\n";
			

			$label_id = $this->label_id . '_fiber';
			$html .= "	<div class='indent item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span class='f-left'>" . __("Dietary Fiber", 'wp_nutrifacts')." <span id='wpnf_label_fiber'>" . $this->get_meta_value($label_id, $meta_values) . "</span><span class='unit'>g</span></span>\n";
			$html .= "		<span class='f-right' id='wpnf_label_fiber_percent'>" . ( $this->check_meta_value($label_id, $meta_values) ? $this->percentage($meta_values[$label_id][0], $this->rda['fiber']) . '%' : '' ) . "</span>\n";
			$html .= "	</div>\n";
			

			$label_id = $this->label_id . '_sugars';
			$html .= "	<div class='indent item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span>" . __("Sugars", 'wp_nutrifacts') . " <span id='wpnf_label_sugars'>" . $this->get_meta_value($label_id, $meta_values) . "</span><span class='unit'>g</span></span>";
			$html .= "	</div>\n";
			

			$label_id = $this->label_id . '_protein';
			$html .= "	<div class='item_row wpnf_cf" . ( $this->check_meta_value($label_id, $meta_values) ? '' : ' item_row_notactive' ) . "'>\n";
			$html .= "		<span class='f-left'><strong>".__("Protein", 'wp_nutrifacts')."</strong> <span id='wpnf_label_protein'>" . $this->get_meta_value($label_id, $meta_values) . "</span><span class='unit'>g</span></span>\n";
			$html .= "		<span class='f-right' id='wpnf_label_protein_percent'>" . ( $this->check_meta_value($label_id, $meta_values) ? $this->percentage($meta_values[$label_id][0], $this->rda['protein']) . '%' : '' ) . "</span>\n";
			$html .= "	</div>\n";
			

			$html .= "	<hr />\n";
			
			$html .= "	<div class='small wpnf_cf'>\n";
			$html .= "		*" . __("Percent Daily Values are based on a 2,000 calorie diet. Your daily values may be higher or lower depending on your calorie needs.", 'wp_nutrifacts');
			$html .= "	</div>\n";

		$html .= "</div> <!-- end_of // wpnf-label -->\n\n";

		echo $html;

		?>

<div id="wpnf-example-options">
		<?php

		

		foreach( $this->nutrional_fields as $name => $field ) {

			$field_id = $this->label_id . '_' . $name;

			?>
			<div class="wpnf-option">
				<label for="<?php echo $field_id ?>">
					<?php echo $field ?>
				</label>
				<?php

					$attr = '';
					switch ($name) {
						case 'servingsize':
						case 'servings':
							$class = '';
							break;
						default : 
							$class = 'digit';

							if( defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != WPNUTRIFACTS_DEFAULT_LANGUAGE ){
								$attr = ' disabled="disabled"';
							}
							break;
					}

					$input_value = '';
					if(isset($meta_values[$field_id])) {
						$input_value = esc_attr( $meta_values[$field_id][0] );
					}

				?><input class="<?php echo $class; ?>" type="text"<?php echo $attr; ?> style=" float: left; width: 130px;" name="<?php echo $field_id ?>" id="<?php echo $field_id ?>" value="<?php echo $input_value; ?>" />
				
				<?php

					switch ($name) {
						case 'servingsize':
							echo '&nbsp; (' . __('ex. : 1/2 cup', 'wp_nutrifacts') . ')';
							break;

						case 'servings':
							echo '&nbsp; (' . __('ex. : 125 mL', 'wp_nutrifacts') . ')';
							break;

						case 'calories':
							echo '<span class="unit"></span>';
							break;

						case 'cholesterol':
						case 'sodium':
						case 'potassium':
							echo '<span class="unit">mg</span>';
							break;

						default : 
							echo '<span class="unit">g</span>';
							break;
					}

					if( defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != WPNUTRIFACTS_DEFAULT_LANGUAGE ){
						switch ($name) {
							case 'servingsize':
							case 'servings':
								echo '&nbsp; (' . __('ex. : 125 mL', 'wp_nutrifacts') . ')';
								break;

							default : 
								echo $this->show_icon( sprintf(__('Edit value on %s language.', 'wp_nutrifacts'), strtoupper( WPNUTRIFACTS_DEFAULT_LANGUAGE ) ), array(
									'icon' 		=> 'edit',
									'class' 	=> 'icon_edit',
									'href' 		=> admin_url( 'post.php?post=' . $post_id_default_lang . '&action=edit&lang=' . WPNUTRIFACTS_DEFAULT_LANGUAGE . '#wpnf-example-options' ),
								));
								break;
						}
					}

				?>
				<div class="wpnf-clear"></div>
			</div>
			<?php

		}

		?></div><div class="wpnf-clear"></div><?php
	
		?><script type='text/javascript'>/* <![CDATA[ */

var wpnf_rda = {<?php

	echo "\n";
	$i = 0; $i_tot = count($this->rda);
	foreach ($this->rda as $key => $value) {
		echo "	'" . $key . "' : " . $value;
		if( $i < ( $i_tot - 1 ) ){
			echo ",";
		}

		echo "\n";

		$i++;
	}

?>};

var wpnf_nutrional_fields = {<?php

	echo "\n";
	$i = 0; $i_tot = count($this->nutrional_fields);
	foreach ($this->nutrional_fields as $key => $value) {
		echo "	'" . $key . "' : '" . addslashes($value) . "'";
		if( $i < ( $i_tot - 1 ) ){
			echo ",";
		}

		echo "\n";

		$i++;
	}

?>};



/* ]]> */</script><?php


		if( isset($post_id) && $post_id && is_numeric($post_id) && $post_id > 0 ){

			?>
			<div class="wpnf-section-divider"></div><div class="wpnf-clear"></div>

			<div id="wpnf-shortcode">
				
				<h4><?php _e('Shortcode'); ?></h4>

				<input type="text" value="[wpnf-label id=<?php echo $post_id; ?>]" class="shortcode" />

			</div>
			<?php

		}


		
	}

	/**
	 * Action hook is triggered within the <head></head> section of the user's template by the wp_head() function.
	 */
	public function wp_head() {
		if ( is_feed() ) {
			return;
		}

		echo "\n<!-- WP Nutrition Facts " . WPNUTRIFACTS_VERSION . " by Kilukru Media (www.kilukrumedia.com)";
			if( isset($_GET['show_time']) ){
				echo "[" . time() . "] ";
			}
		echo "-->\n";
		echo "<!-- /WP Nutrition Facts -->\n";

	}

	/**
	 * Runs after WordPress has finished loading but before any headers are sent
	 */
	function sharing_love_meta_boxes() {
		if( defined('WPNUTRIFACTS_STOP_SHARING_LOVE') ){ ///   :(   We need some support to give you some nive features, so thanks ;)
			return;
		}
				
		echo '
		<div class="dashboard_widget_block">
			<p><i class="wpnf_icon-mug"></i> Beer == New Awesome Features on all our plugins.<br />Thanks so much for your support.</p>
			<a class="button button-primary" target="_blank" href="http://ch.eckout.com/wpnutritionfacts-donation">Buy me a Beer. <i class="wpnf_icon-heart"></i> You!</a>
			<a class="button button-primary" target="_blank" href="http://ch.eckout.com/wpnutritionfacts-donation-10">or 10$</a>
		</div>
		';
	}


	/**
	 * Runs after WordPress has finished loading but before any headers are sent
	 */
	function dashboard_widget_function() {
		if( defined('WPNUTRIFACTS_DISABLED_DASHBOARD_WIDGET') ){
			return;
		}
		
		//echo '<ul class="ul-disc">';
		//	echo '<li>Facts - [<a href="http://www.kilukrumedia.com" target="_blank">' . __('More infos', 'wp_nutrifacts') . '</a>] (' . __('Free', 'wp_nutrifacts') . ')</li>';
		//echo '</ul>';
		//
		
		echo '
		<div class="dashboard_widget_block">
			<p><i class="wpnf_icon-mug"></i> Beer == New Awesome Features on all our plugins.<br />Thanks so much for your support.</p>
			<a class="button button-primary" target="_blank" href="http://ch.eckout.com/wpnutritionfacts-donation">Buy me a Beer. <i class="wpnf_icon-heart"></i> You!</a>
			<a class="button button-primary" target="_blank" href="http://ch.eckout.com/wpnutritionfacts-donation-10">or 10$</a>
		</div>
		';
	}


	/**
	 * Runs after WordPress has finished loading but before any headers are sent
	 */
	function dashboard_setup() {
		if( defined('WPNUTRIFACTS_DISABLED_DASHBOARD_WIDGET') ){
			return;
		}
		
		wp_add_dashboard_widget('wp_dashboard_widget', '<i class="wpnf_icon-heart"></i> WP Nutrition Facts?', array(&$this, 'dashboard_widget_function') );
	}


	/*
	 * Add Column to WordPress Admin 
	 * Displays the shortcode needed to show label
	 *
	 * 2 Functions
	 */
	 
	function colums_labels( $column ) { 
		$columns = array(
			'cb'       			=> '<input type="checkbox" />',
			'title'    			=> __('Title', 'wp_nutrifacts'),
			'wpnf_shortcode'    => __('Shortcode', 'wp_nutrifacts'),
			'wpnf_page'    		=> __('Section', 'wp_nutrifacts'),
			'date'     			=> __('Date', 'wp_nutrifacts')
		);

		return $columns;
	}


	function colums_labels_row( $column_name, $post_id ) {
	 	if($column_name == "wpnf_shortcode"){
	 		echo "[wpnf-label id={$post_id}]";
	 	}
	 	
	 	if($column_name == "wpnf_page"){
	 		$custom_post = get_post_meta( $post_id, $this->label_id . "_pageid", '' );

	 		if( $custom_post && !empty($custom_post) && is_array($custom_post) && isset($custom_post[0]) && is_numeric($custom_post[0]) ){

	 			$args = array();
				$output = 'objects'; // names or objects, note names is the default
				$operator = 'and'; // 'and' or 'or'
				$post_types = get_post_types( $args, $output, $operator );

	 			$_post = get_post( $custom_post[0] );
	 			
	 			if( $_post && isset($post_types[$_post->post_type]) && isset($post_types[$_post->post_type]->label) ){

		 			echo $post_types[$_post->post_type]->label;
		 			echo ' =&gt; ';

		 			echo '<a href="' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . '">';
		 				echo get_the_title( $custom_post[0] );
		 			echo '</a>';

	 			}

	 		}

	 	}
	 	
	}


	/**
	 * Add Shortcode for WP
	 */
	function shortcode_show_label( $atts ){
		extract(shortcode_atts(array(
			  'id' 					=> false,
		 ), $atts));

		if($id) { return $this->shortcode_generate_label($id, $atts); } else {
			global $post;
		
			$label = get_posts( array( 'post_type' => 'wp-nutrition-facts', 'meta_key' => $this->label_id . '_pageid', 'meta_value' => $post->ID ));
			
			if($label)
			{
				$label = reset($label);
				return $this->shortcode_generate_label( $label->ID, $atts );
			}
		}

		return "";
	}


	/**
	 * Generate Shortcode for WP
	 */
	function shortcode_generate_label( $id, $atts ){
		extract(shortcode_atts(array(
			  'id' 					=> false,
			  'title' 				=> '',
			  'width' 				=> '',

			  'class' 				=> '',

			  'notice' 				=> true,

			  'show_cal_fat' 		=> false,
			  'heading_show' 		=> true,
			  'heading_title' 		=> __("Nutrition Facts", 'wp_nutrifacts'),

			  'servings_show' 		=> true,
			  'servings_title' 		=> '',

		), $atts));

		// Check Language
		switch ( WPNF_ICL_LANGUAGE_CODE ) {
			case 'fr':
				$lang_space = ' ';
				break;
			
			default:
				$lang_space = '';
				break;
		}


		$label = get_post_meta( $id );

		if(!$label) { return false; }
		
		// GET VARIABLES
		foreach( $this->nutrional_fields as $name => $nutrional_field )
		{
			$$name = $label[$this->label_id . '_' . $name][0];	
		}

		// BUILD CALORIES IF WE DONT HAVE ANY
		if($calories == 0) 
		{
			$calories = ( ( $protein + $carbohydrates ) * 4 ) + ($totalfat * 9);
		}
			
		// WIDTH THE LABEL
		$style = '';
		if( !empty($width) && $width != 22) 
		{
			//$style = " style='width: " . $width . "em; font-size: " . ( ( $width / 22 ) * .75 ) . "em;'";
		}
		
		$html = "";
		$html .= "<div class='wpnf-label " . $class . "' id='wpnf-$id' " . ($style ? $style : "") . ">\n";
		
		if( $heading_show === true ){
			$html .= "	<div class='heading'>" . $heading_title . "</div>\n";
		}
		
		if( $servings_show === true ){

			$html .= "	<div class='wpnf_servings item_row wpnf_cf'>";

			if( !empty($servings_title) ){
				$html .= $servings_title;
			}else{
				$html .= "	" . __("Per", 'wp_nutrifacts') . " " . $servingsize;
				if( isset($servings) && !empty($servings) ){
					$html .= "	(" . $servings . ")";
				}
			}

			$html .= "	</div>\n";

			if( empty($servings_title) ){
				$html .= "	<hr />\n";
			}

		}
		
		//$html .= "	<div class='amount-per small item_row noborder'></div>\n";
		
		$html .= "	<div class='amount-per small item_row wpnf_cf'>\n";
		$html .= "		<span class='f-left'>" . __("Amount", 'wp_nutrifacts') . "</span>\n";
		$html .= "		<span class='f-right'>% " . __("Daily Value", 'wp_nutrifacts') . ( $notice === true ? '*' : '' ) . "</span>\n";
		$html .= "	</div>\n";


		if( isset($calories) && !empty($calories) ){

			$html .= "	<div class='item_row wpnf_cf'>\n";
			$html .= "		<span class='f-left'><strong class='wpnf_item_title'>" . __("Calories", 'wp_nutrifacts') . " </strong><span class='wpnf_item_tot'>" . $calories . "</span></span>\n";

			if( $show_cal_fat == 'true' && isset($totalfat) && ( !empty($totalfat) || $totalfat == '0' ) ){
				$html .= "		<span class='f-right'><strong>" . __("Calories from Fat", 'wp_nutrifacts') . " </strong><span class='wpnf_item_tot'>" . ($totalfat * 9) . "</span></span>\n";
			}
			$html .= "	</div>\n";

		}
		

		if( isset($totalfat) && ( !empty($totalfat) || $totalfat == '0' ) ){

			$html .= "	<div class='item_row wpnf_cf'>\n";
			$html .= "		<span class='f-left'><strong class='wpnf_item_title'>" . __("Total Fat", 'wp_nutrifacts') . " </strong><span class='wpnf_item_tot'>" . $totalfat . $lang_space . "g</span></span>\n";
			$html .= "		<span class='f-right'>" . $this->percentage($totalfat, $this->rda['totalfat']) . "%</span>\n";
			$html .= "	</div>\n";
		

			if( isset($satfat) && ( !empty($satfat) || $satfat == '0' ) ){
				$html .= "	<div class='indent item_row wpnf_cf'>\n";
				$html .= "		<span class='f-left'><span class='wpnf_item_title'>" . __("Saturated Fat", 'wp_nutrifacts') . " </span><span class='wpnf_item_tot'>" . $satfat . $lang_space . "g</span></span>\n";
				$html .= "		<span class='f-right'>" . $this->percentage($satfat, $this->rda['satfat']) . "%</span>\n";
				$html .= "	</div>\n";
			}

			if( isset($transfat) && ( !empty($transfat) || $transfat == '0' ) ){
				$html .= "	<div class='indent item_row wpnf_cf'>\n";
				$html .= "		<span><span class='wpnf_item_title'>" . __("Trans Fat", 'wp_nutrifacts') . " </span><span class='wpnf_item_tot'>" . $transfat . $lang_space . "g</span></span>";
				$html .= "	</div>\n";
			}

		}


		if( isset($cholesterol) && ( !empty($cholesterol) || $cholesterol == '0' ) ){
			$html .= "	<div class='item_row wpnf_cf'>\n";
			$html .= "		<span class='f-left'><strong class='wpnf_item_title'>" . __("Cholesterol", 'wp_nutrifacts') . " </strong><span class='wpnf_item_tot'>" . $cholesterol . $lang_space . "mg</span></span>\n";
			$html .= "		<span class='f-right'>" . $this->percentage($cholesterol, $this->rda['cholesterol']) . "%</span>\n";
			$html .= "	</div>\n";
		}

		if( isset($sodium) && ( !empty($sodium) || $sodium == '0' ) ){
			$html .= "	<div class='item_row wpnf_cf'>\n";
			$html .= "		<span class='f-left'><strong class='wpnf_item_title'>" . __("Sodium", 'wp_nutrifacts') . " </strong><span class='wpnf_item_tot'>" . $sodium . $lang_space . "mg</span></span>\n";
			$html .= "		<span class='f-right'>" . $this->percentage($sodium, $this->rda['sodium']) . "%</span>\n";
			$html .= "	</div>\n";
		}

		if( isset($potassium) && ( !empty($potassium) || $potassium == '0' ) ){
			$html .= "	<div class='item_row wpnf_cf'>\n";
			$html .= "		<span class='f-left'><strong class='wpnf_item_title'>" . __("Potassium", 'wp_nutrifacts') . " </strong><span class='wpnf_item_tot'>" . $potassium . $lang_space . "mg</span></span>\n";
			$html .= "		<span class='f-right'>" . $this->percentage($potassium, $this->rda['potassium']) . "%</span>\n";
			$html .= "	</div>\n";
		}

		if( isset($carbohydrates) && ( !empty($carbohydrates) || $carbohydrates == '0' ) ){
			$html .= "	<div class='item_row wpnf_cf'>\n";
			$html .= "		<span class='f-left'><strong class='wpnf_item_title'>" . __("Total Carbohydrate", 'wp_nutrifacts') . " </strong><span class='wpnf_item_tot'>" . $carbohydrates . $lang_space . "g</span></span>\n";
			$html .= "		<span class='f-right'>" . $this->percentage($carbohydrates, $this->rda['carbohydrates']) . "%</span>\n";
			$html .= "	</div>\n";
		

			if( isset($fiber) && ( !empty($fiber) || $fiber == '0' ) ){
				$html .= "	<div class='indent item_row wpnf_cf'>\n";
				$html .= "		<span class='f-left'><span class='wpnf_item_title'>" . __("Dietary Fiber", 'wp_nutrifacts')." </span><span class='wpnf_item_tot'>" . $fiber . $lang_space . "g</span></span>\n";
				$html .= "		<span class='f-right'>" . $this->percentage($fiber, $this->rda['fiber']) . "%</span>\n";
				$html .= "	</div>\n";
			}

			if( isset($sugars) && ( !empty($sugars) || $sugars == '0' ) ){
				$html .= "	<div class='indent item_row wpnf_cf'>\n";
				$html .= "		<span><span class='wpnf_item_title'>" . __("Sugars", 'wp_nutrifacts') . " </span><span class='wpnf_item_tot'>" . $sugars . $lang_space . "g</span></span>";
				$html .= "	</div>\n";
			}

		}

		if( isset($protein) && ( !empty($protein) || $protein == '0' ) ){
			$html .= "	<div class='item_row wpnf_cf'>\n";
			$html .= "		<span class='f-left'><strong class='wpnf_item_title'>".__("Protein", 'wp_nutrifacts')." </strong><span class='wpnf_item_tot'>" . $protein . $lang_space . "g</span></span>\n";
			$html .= "		<span class='f-right'>" . $this->percentage($protein, $this->rda['protein']) . "%</span>\n";
			$html .= "	</div>\n";
		}

		if( $notice === true ){

			$html .= "	<hr />\n";
			
			$html .= "	<div class='small wpnf_cf'>\n";
			$html .= "		*" . __("Percent Daily Values are based on a 2,000 calorie diet. Your daily values may be higher or lower depending on your calorie needs.", 'wp_nutrifacts');
			$html .= "	</div>\n";

		}
	  
		$html .= "</div> <!-- end_of // wpnf-label -->\n\n";

		return $html;
	}


	/**
	 * Temporary inactive
	 */
	function widgets_init()
	{
		//register_widget('WPNUTRIFACTS_Widget');
	}


	/**
	 * Action hook to save post meta
	 */
	public function save_post( $post_id, $post ) {

		if( $_REQUEST['post_type'] == 'wp-nutrition-facts' ){

			//if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'save' ) {
			if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'editpost' && ( ( isset($_REQUEST['save']) && $_REQUEST['save'] != 'new' ) || ( isset($_REQUEST['publish']) && $_REQUEST['publish'] != 'new' ) ) ) {

				foreach( $this->nutrional_fields as $name => $field ) {

					$field_id = $this->label_id . '_' . $name;
					//$old = get_option( $field_id, ( isset($field['std']) ? $field['std'] : null ) );
					$new = isset( $_REQUEST[$field_id] ) ? strip_tags( $_REQUEST[$field_id] ) : null;

					if( isset( $_REQUEST[ $field_id ] ) ) {
						add_post_meta( $post_id, $field_id, $new, true ) || update_post_meta( $post_id, $field_id, $new );
					} else {
						delete_post_meta( $post_id, $field_id );
					}

				}

				if ( isset( $_REQUEST[ $this->label_id . '_pageid' ] ) ) {
					update_post_meta( $post_id, $this->label_id . '_pageid', strip_tags( $_REQUEST[ $this->label_id . '_pageid' ] ) );
				}

				//if( $redirect === true ){
				//	header("Location: plugins.php?page=" . basename($_file_) . "&saved=true");
				//	die;
				//}else{
					$_REQUEST['saved'] = 'true';
					$_GET['saved'] = 'true';
				//}

			} else if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'reset' ) {

			}

		}
	}

	function post_updated_messages( $messages ){
        global $post;

        $post_ID = $post->ID;
        $post_type = get_post_type( $post_ID );

        if( $post_type == 'wp-nutrition-facts' ){

	        $obj = get_post_type_object( $post_type );
	        $singular = $obj->labels->singular_name;

	        /*$messages[$post_type] = array(
	                0 => '', // Unused. Messages start at index 1.
	                1 => sprintf( __( '%s updated. <a href="%s" target="_blank">View %s</a>' ), esc_attr( $singular ), esc_url( get_permalink( $post_ID ) ), strtolower( $singular ) ),
	                2 => __( 'Custom field updated.', 'maxson' ),
	                3 => __( 'Custom field deleted.', 'maxson' ),
	                4 => sprintf( __( '%s updated.', 'maxson' ), esc_attr( $singular ) ),
	                5 => isset( $_GET['revision']) ? sprintf( __('%2$s restored to revision from %1$s', 'maxson' ), wp_post_revision_title( (int) $_GET['revision'], false ), esc_attr( $singular ) ) : false,
	                6 => sprintf( __( '%s published. <a href="%s">View %s</a>'), $singular, esc_url( get_permalink( $post_ID ) ), strtolower( $singular ) ),
	                7 => sprintf( __( '%s saved.', 'maxson' ), esc_attr( $singular ) ),
	                8 => sprintf( __( '%s submitted. <a href="%s" target="_blank">Preview %s</a>'), $singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), strtolower( $singular ) ),
	                9 => sprintf( __( '%s scheduled for: <strong>%s</strong>. <a href="%s" target="_blank">Preview %s</a>' ), $singular, date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), strtolower( $singular ) ),
	                10 => sprintf( __( '%s draft updated. <a href="%s" target="_blank">Preview %s</a>'), $singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), strtolower( $singular ) )
	        );*/

	        $messages[$post_type] = array(
	                0  => '', // Unused. Messages start at index 1.
	                1  => __( 'Label updated.', 'wp_nutrifacts' ),
	                2  => __( 'Label updated.', 'wp_nutrifacts' ),
	                3  => __( 'Label deleted.', 'wp_nutrifacts' ),
	                4  => __( 'Label updated.', 'wp_nutrifacts' ),
	                5  => isset( $_GET['revision']) ? sprintf( __('%2$s restored to revision from %1$s', 'wp_nutrifacts' ), wp_post_revision_title( (int) $_GET['revision'], false ), esc_attr( $singular ) ) : false,
	                6  => __( 'Label published.', 'wp_nutrifacts' ),
	                7  => __( 'Label saved.', 'wp_nutrifacts' ),
	                8  => __( 'Label submitted.'),
	                9  => sprintf( __( '%s scheduled for: <strong>%s</strong>.', 'wp_nutrifacts' ), $singular, date_i18n( __( 'M j, Y @ G:i', 'wp_nutrifacts'), strtotime( $post->post_date ) ) ),
	                10 => __( 'Label draft updated.', 'wp_nutrifacts')
	        );

        }

        return $messages;
	}

	public function show_icon( $text, $atts = array() ){
		extract(shortcode_atts(array(
			  'id' 					=> '',
			  'title' 				=> '',
			  'class' 				=> '',
			  'href' 				=> 'javascript:void(0);',

			  'icon' 				=> 'help',

			  'show_img' 			=> true,
			  'show_img_before' 	=> '',
			  'show_img_after' 		=> '',
			  'show_data' 			=> '[?]',

			  'placement' 			=> 'right',		// top | bottom | left | right | auto. 

			  'echo' 				=> false,

		 ), $atts));

		//<button data-original-title="Tooltip on left" type="button" class="btn btn-default wpnf_tooltip" data-toggle="tooltip" data-placement="right" title="">Tooltip on left</button>

		$html = '';

		$html .= '<a data-original-title="' . $text . '" data-toggle="tooltip" data-placement="' . $placement . '" href="' . $href . '" class="wpnf_tooltip' . ( !empty($class) ? ' ' . $class : '' ) . '"' . ( !empty($title) ? ' title="' . $title . '"' : '' ) . '' . ( !empty($id) ? ' id="' . $id . '"' : '' ) . '>';
			if( $show_img === true ){

				switch ( $icon ) {
					case 'edit':
						$icon_url = 'icons/edit.png';
						$icon_alt = __('Edit', 'wp_nutrifacts');
						break;
					
					default :
					case 'help' :
						$icon_url = 'icons/help.png';
						$icon_alt = __('Help', 'wp_nutrifacts');
						break;
				}

				$html .= $show_img_before;
				$html .= '<img src="' . WPNUTRIFACTS_PLUGIN_IMAGES_URL . $icon_url . '" alt="' . $icon_alt . '" />';
				$html .= $show_img_after;
			}else{
				$html .= $show_data;
			}
		$html .= '</a>';


		if( $echo === true ){
			echo $html;
		}else{
			return $html;
		}

	}


	/**
	 * Load JavaScripts
	 */
	function load_scripts() {
		// Check if they are in admin
		if( is_admin() ){

			// Add numeric validator
			wp_register_script('wpnutrifacts_admin_jquery_numeric', path_join(
				WPNUTRIFACTS_PLUGIN_JS_URL,
				'admin/jquery/jquery.numeric' . ( $this->get_filetime_forfile() ) . '.js'
			), array('jquery'), $this->get_version_number($this->version_js) );
			wp_enqueue_script( 'wpnutrifacts_admin_jquery_numeric' );

			// Add template elements for styling
			wp_register_script('wpnutrifacts_admin_bootstrap_tooltip', path_join(
				WPNUTRIFACTS_PLUGIN_JS_URL,
				'admin/bootstrap/tooltip' . ( $this->get_filetime_forfile() ) . '.js'
			), array('jquery'), $this->get_version_number($this->version_js) );
			wp_enqueue_script( 'wpnutrifacts_admin_bootstrap_tooltip' );

			// Set the common scripts
			wp_register_script('wpnutrifacts_admin_htmlhead_common', path_join(
				WPNUTRIFACTS_PLUGIN_JS_URL,
				'admin/htmlhead_common' . ( $this->get_filetime_forfile() ) . '.js'
			), array('jquery'), $this->get_version_number($this->version_js) );
			wp_enqueue_script( 'wpnutrifacts_admin_htmlhead_common' );

		}

	}


	/**
	 * Load CSS styles
	 */
	function load_styles() {
		// Check if they are in admin
		if( is_admin() ){
			// Set the common style
			wp_register_style( 'wpnutrifacts_admin_common', WPNUTRIFACTS_PLUGIN_CSS_URL .'admin/styles_common' . ( $this->get_filetime_forfile() ) . '.css', false, $this->get_version_number($this->version_css), 'screen' );
			wp_enqueue_style( 'wpnutrifacts_admin_common' );
		}
	}


	/**
	 * Load CSS styles on Frontend
	 */
	function load_styles_frontend() {
		// Check if they not are in admin
		if( !defined('MBLZR_DISABLED_FRONTEND_CSS') && !is_admin() ){
			// Set the common style
			wp_register_style( 'wpnutrifacts_common', WPNUTRIFACTS_PLUGIN_CSS_URL . 'styles' . ( $this->get_filetime_forfile() ) . '.css', false, $this->get_version_number($this->version_css), 'screen' );
			wp_enqueue_style( 'wpnutrifacts_common' );
		}

	}


	/**
	 * Check if the version of WP is compatible with this plugins minimum requirment.
	 */
	function required_version() {
		global $wp_version;

		// Check for WP version installation
		$wp_ok  =  version_compare($wp_version, $this->minimum_WP, '>=');

		if ( ($wp_ok == FALSE) ) {
			$this->admin_notices( sprintf(__('Sorry, WP Nutrition Facts works only under WordPress %s or higher', "wp_mobilizer" ), $this->minimum_WP ), true );
			return false;
		}

		return true;

	}


	/**
	 * Check if the version of PHP of this server is compatible with this plugins minimum requirment.
	 */
	function required_version_php() {
		global $wp_version;

		// Check for PHP version installation
		$wp_ok  =  version_compare(PHP_VERSION, $this->minimum_PHP, '>=');

		if ( ($wp_ok == FALSE) ) {
			$this->admin_notices( sprintf(__('Sorry, WP Nutrition Facts works only under PHP %s or higher', "wp_mobilizer" ), $this->minimum_PHP ), true );

			return false;
		}

		return true;

	}


	/**
	 * Notice admin with some messages
	 */
	function admin_notices( $text, $errormsg = false ) {
		// Add text to admin notice info
		$this->admin_notices_infos[] = mblzr_show_essage($text, $errormsg);

		add_action(
			'admin_notices',
			create_function(
				'',
				'global $wpnutrifacts; if( is_array($wpnutrifacts->admin_notices_infos) && count($wpnutrifacts->admin_notices_infos) > 0 ){foreach($wpnutrifacts->admin_notices_infos as $notice){ echo $notice; } $wpnutrifacts->admin_notices_infos = array(); };'
			)
		);

	}

	
	/**
	* ADD Filetime into file if KM_FILEMTIME_REWRITE constant exist
	* 
	* @param mixed $default
	* @return mixed
	*/
	public function get_filetime_forfile( $default = '' ){
		
		if( !defined('KM_FILEMTIME_REWRITE') || !defined('WPNUTRIFACTS_VERSION_FILETIME') ){
			return $default;
		}
		
		return '-' . WPNUTRIFACTS_VERSION_FILETIME;
		
	}
	

	/**
	* Return null value if KM_FILEMTIME_REWRITE constant exist
	* 
	* @param mixed $default
	*/
	public function get_version_number( $default ){
		
		if( !defined('KM_FILEMTIME_REWRITE') ){
			return $default;
		}
		
		return null;
		
	}

	
	/**
	 * Set log file datas
	 */
	public function log( $message ) {
		if ( $this->do_log ) {
			error_log(date('Y-m-d H:i:s') . " " . $message . "\n", 3, $this->log_file);
		}
	}


	/*
	 * @param integer $contains
	 * @param integer $reference
	 * @return integer
	 */
	function percentage($contains, $reference) 
	{
		return round( $contains / $reference * 100 );
	}


	/**
	 * Check current user is an admin
	 *
	 */
	public function is_admin() {
		return current_user_can('level_8');
	}
	

	/**
	* Check Role for the user
	* 
	* @param mixed $role
	*/
	public function is_role( $role ){
		
		if( $this->current_user == false ){
			return false;
		}
		
		if( @reset($current_user->roles) == $role ){
			return true;
		}else{
			return false;
		}
		
	}
	

	/**
	* Return current ID User
	* 
	* @param mixed $role
	*/
	public function id_user( $role ){
		
		if( $this->current_user == false ){
			return false;
		}else{
			return $this->current_user->ID;
		}
		
	}
	

	/**
	* Get user informations
	* 
	*/
	public function get_user_info(){
		global $current_user;
		
		if( function_exists('get_currentuserinfo') ){
			get_currentuserinfo();
		}
		
		if( isset($current_user) && $current_user ){
			$this->current_user = $current_user;
		}

	}


	/**
	 * Dump menu var
	 */
	function dump_admin_menu() {
		if ( is_admin() ) {
			$this->dump($GLOBALS['menu']);
		}
	}


	/**
	 * Dump var
	 */
	public function dump( $var ) {
		header('Content-Type:text/plain');
		var_dump( $var );
		exit;
	}


}