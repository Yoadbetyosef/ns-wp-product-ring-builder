<?php
 
class Ring_Builder_Setting {
	
	private $options;
	private $shortname = 'gcpb';

 	/**
	* Instance
	* @access private
	* @static
	*
	*/
	private static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		
		$this->gcpb_field_initialize();

		// add_action( 'init', [ $this, 'init' ] );
		add_action( 'admin_menu', [ $this, 'gcpb_setting_page' ], 11 );
		add_action('admin_notices', [ $this,'gcpb_admin_notice']);
		add_action( 'admin_enqueue_scripts', [ $this, 'gcpb_admin_script' ] );
		// add_menu_page( $this->plugin_name, 'Plugin Name', 'administrator', $this->plugin_name, array( $this, 'displayPluginAdminDashboard' ), plugin_dir_url( __FILE__ ) . 'img/logo.png', 26 );
		add_action( 'admin_post_gcpb_process_setting', [ $this,'gcpb_setting_form_action'] );

 	}

	public function gcpb_admin_script() {
		$screen = get_current_screen();
 
		if ( $screen->id !== 'toplevel_page_gcpb-setting') return;

		wp_enqueue_style( 'gcpb-admin', plugins_url('assets/admin/css/gcpb-admin.css', dirname(dirname(__FILE__))), array(), '1.1.2');

		// wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0-rc.0');

		//Add the Select2 JavaScript file
		// wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', 'jquery', '4.1.0-rc.0');
		// wp_enqueue_script( 'minicolors-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-minicolors/2.2.4/jquery.minicolors.min.js', 'jquery', '4.1.0-rc.0');

 
		wp_enqueue_script( 'jquery-ui-draggable' );
		// wp_enqueue_script( 'gcpb-repeater', plugins_url('assets/admin/js/jquery.repeater.js', dirname(dirname(__FILE__))), array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'gcpb-admin', plugins_url('assets/admin/js/gcpb-admin.js', dirname(dirname(__FILE__))), array( 'jquery' ), '2.1.0', true );
		
		wp_localize_script( 'gcpb-admin', 'gcpb_ajax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		));
	}

	public function gcpb_admin_notice() {
  
		$screen = get_current_screen();
 
		if ( $screen->id !== 'toplevel_page_gcpb-setting') return;

		if ( isset( $_GET['saved'] ) && $_GET['saved'] ) {

			//if settings updated successfully 

			if ( 'true' === $_GET['saved'] ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php _e('Setting updated successfully', 'textdomain') ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-warning is-dismissible">
					<p><?php _e('Error occur, please try again', 'textdomain') ?></p>
				</div>
			<?php endif;
		}
	}

	/**
	* Add options page
	*/
	public function gcpb_setting_page() {
		add_menu_page(
 			'GCP Builder',
			'GCP Builder',
			'manage_options',
			'gcpb-setting',
			[ $this, 'gcpb_setting_admin_page' ],
			'',
			100
		);
		add_submenu_page('gcpb-setting', __('GCP Builder', 'otw-woo-ring-builder-td'), __('GCP Builder', 'otw-woo-ring-builder-td'), 'manage_options', 'gcpb-setting', array($this,'gcpb_setting_admin_page') );
	}

	public function gcpb_field_initialize() {

		$this->options = array( 
			array(
				'name' => 'Products Per Page',
				'type' => 'number',
				'id' => $this->shortname.'_products_per_page',
			),
			array(
				'name' => 'Filter Attributes',
				'desc' => 'Set filters for the products. ',
				'id' => $this->shortname.'_filter_attributes',
				'type' => 'repeater',
			),
			array(
				'name' => 'Styles',
				'type' => 'sub-section',
			),
			array(
				'name' => 'Accent Color',
				'desc' => 'Set the color of the accent variable ',
				'id' => $this->shortname.'_accent_color',
				'type' => 'color',
			),
			array(
				'name' => 'Border Radius',
				'id' => $this->shortname.'_border_radius',
				'type' => 'radio',
				'desc' => 'Which side would you like your sidebar?',
				'options' => array('square' => 'Square ', 'rounded' => 'Rounded'),
			),
			array(
				'name' => 'Product Fields',
				'type' => 'sub-section',
			),
			array(
				'name' => 'Product Page',
				'type' => 'select',
				'id' => $this->shortname.'_product_page',
				'options' => $this->gcpb_post_object('page'),
			),
			array(
				'name' => 'Product Archive',
				'type' => 'select',
				'id' => $this->shortname.'_product_archive',
				'options' => $this->gcpb_post_object('page'),
			),
			array(
				'name' => 'Short Description Title',
				'type' => 'text',
				'id' => $this->shortname.'_short_desc_toggle_title',
			),
			array(
				'name' => 'Shipping Details Toggle Title',
				'type' => 'text',
				'id' => $this->shortname.'_shipping_details_toggle_title',
			),
			array(
				'name' => 'Shipping Details Text',
				'type' => 'textarea',
				'id' => $this->shortname.'_shipping_details_text',
			),
			array(
				'name' => 'Returns Toggle Title',
				'type' => 'text',
				'id' => $this->shortname.'_returns_toggle_title',
			),
			array(
				'name' => 'Returns Text',
				'type' => 'textarea',
				'id' => $this->shortname.'_returns_text',
			),
			array(
				'name' => 'No product found shortcode',
				'type' => 'text',
				'id' => $this->shortname.'_no_product_found_shortocde',
			),
			array(
				'name' => 'Default Product Gallery Image',
				'type' => 'file',
				'id' => $this->shortname.'_default_product_image',
			),
		);


	}

	/**
	 * Options page callback
	 */
	public function gcpb_setting_admin_page() {
		// $this->options = get_option( 'hyros_api_data' );
		
		prb_get_template( 'admin/gcpb-setting-form.php', array('options' => $this->options) ); 
	}

	public function gcpb_form_fields($field_data) {
		$output = '';

		switch ($field_data['type']) {
			case 'sub-section':
				$value = get_option($field_data['id']);

				$output = '<tr valign="top">
					<th scope="row"><h3>'.$field_data['name'].'</h3><th>
					<td></td>
				</tr>';
				break;

			case 'text':
				$value = get_option($field_data['id']);
				$output = '<tr valign="top">
					<th scope="row">'.$field_data['name'].'</th>
					<td><input type="text" name="gcpb_option['.$field_data['id'].']" value="'.$value.'" /></td>
				</tr>';
 				break;

			case 'number':
				$value = get_option($field_data['id']);

				$output = '<tr valign="top">
					<th scope="row">'.$field_data['name'].'</th>
					<td><input type="number" name="gcpb_option['.$field_data['id'].']" value="'.$value.'" /></td>
				</tr>';
				break;

			case 'color':
				$value = get_option($field_data['id']);

				$output = '<tr valign="top">
					<th scope="row">'.$field_data['name'].'</th>
					<td><input type="color" name="gcpb_option['.$field_data['id'].']" value="'.$value.'" /></td>
				</tr>';
				break;

			case 'textarea':
				$value = get_option($field_data['id']);

				$output = '<tr valign="top">
					<th scope="row">'.$field_data['name'].'</th>
					<td><textarea name="gcpb_option['.$field_data['id'].']" cols="50" rows="6" class="regular-text">'.$value.'</textarea></td>
				</tr>';
				break;

			case 'radio':
				$value = get_option($field_data['id']);

				$output = '<tr valign="top">
					<th scope="row">'.$field_data['name'].'</th>
					<td>';
					foreach ( $field_data['options'] as $val => $label ) {
						$checked = ($value == $val ) ?'checked="checked"':'';
 						$output .= '<label for=""><input type="radio" name="gcpb_option['.$field_data['id'].']" value="'.$val.'" '.$checked.' />'.$label.'</label>';
					}
					$output .= '</td>
				</tr>';
				break;

			case 'select':
					$value = get_option($field_data['id']);
	
					$output = '<tr valign="top">
						<th scope="row">'.$field_data['name'].'</th>
						<td>
						<select id="'.$field_data['id'].'" name="gcpb_option['.$field_data['id'].']">';
							foreach ( $field_data['options'] as $val => $label ) {
								$selected = ($value == $val ) ?'selected="selected"':'';
								$output .= '<option value="' . $val . '" ' . $selected . '>' . $label . '</option>';
							}
						$output .= '</select>
						</td>
					</tr>';
					break;

			case 'file':
				$image_id = get_option($field_data['id']);

				$output = '<tr valign="top" class="gcpb-field-media">
					<th scope="row">'.$field_data['name'].'</th>
					<td>';
					if( $image = wp_get_attachment_image_url( $image_id, 'medium' ) ) :  
						$output .='<a href="#" class="gcpb-upload">
							<img src="'.esc_url( $image ).'" />
						</a>
						<a href="#" class="gcpb-remove-media">Remove image</a>
						<input type="hidden" name="gcpb_option['.$field_data['id'].']" value="'.absint( $image_id ).'">';
					else : 
						$output .='<a href="#" class="button gcpb-upload">Upload image</a>
						<a href="#" class="gcpb-remove-media" style="display:none">Remove image</a>
						<input type="hidden" name="gcpb_option['.$field_data['id'].']" value="">';
					endif; 
				$output .='</td>
				</tr>';
				break;
 
			case 'repeater':
				$repeater_values = get_option($field_data['id']);
				$attributes = wc_get_attribute_taxonomies();
				$parent_term = array();
				$sub_terms = array();

				if( !empty($attributes) ) {
					foreach( $attributes as $attribute ) {
						$parent_term[$attribute->attribute_name] = $attribute->attribute_label;
						$sub_terms[$attribute->attribute_name]['sub_terms'] = get_terms( array('taxonomy' => 'pa_' . $attribute->attribute_name, 'fields' => 'all' ) );
						$sub_terms[$attribute->attribute_name]['label'] = $attribute->attribute_label;
					}
				}
				 

									/* parent clone */
									$cloner = '';
									$cloner .= '<div id="parent-clone">
											<div data-repeater-item="repeater" class="gcpb-parent">
													<div class="gcpb-repeater-parent">
														<div class="gcpb-field gcpb-field-filter"><label>Main Filter</label>';
															if( !empty($parent_term) ) {
																$cloner	.= '<select data-repeater="select2" name="main-attribute" data-name="main-attribute" class="js-gcpb-select2 gcpb-repeater-field">';
																foreach( $parent_term as $pk => $pterm ) {
																	$cloner	.= '<option value="'.$pk.'">'.$pterm.'</option>';
																}
																$cloner	.= '</select>';
															}
											$cloner	.='</div>
														<div class="gcpb-field">';
															$cloner .= '<label>Is Filter Product ? </label><input type="checkbox" name="is_filter" data-name="is_filter" value="yes" class="gcpb-repeater-field" />
														</div>
														<div class="gcpb-field">';
															$cloner .= '<label>Show In Loop ? </label><input type="checkbox" name="gcpb_loop_visibility" data-name="gcpb_loop_visibility" value="yes" class="gcpb-repeater-field" />
														</div>
														<div class="gcpb-field">';
															$cloner	.= '<input data-repeater-delete type="button" value="Delete"/>
														</div>
													</div>';
														
										$cloner	.='<div class="gcpb-inner-repeater">';
											$cloner	.='<div data-repeater-name="gcpb-sub-attributes" class="drag gcpb-child repeater-block">';
												$j = 0;
												$cloner	.='<div class="gcpb-repeater-item" data-repeater-item="sub-repeater" data-repeater-name="gcpb-sub-attributes">
														<div class="gcpb-repeater-child">
															<div class="gcpb-field gcpb-field-term">';
															
															if( !empty($sub_terms) ) {
																
																$cloner	.= '<select data-repeater="select2" name="sub-attribute" data-name="sub-attribute" class="gcpb-repeater-field">';
																foreach( $sub_terms as $sub_term ) {
																	if(!empty($sub_term['sub_terms'])) {
																		$cloner	.='<optgroup label="'.$sub_term['label'].'">';
																		foreach( $sub_term['sub_terms'] as $s_term) {

																			$cloner	.='<option value="'.$s_term->term_id.'">'.$sub_term['label'].'->'.$s_term->name.'</option>';
																		}
																		$cloner	.='</optgroup>';
																	}
																}
																$cloner	.= '</select>';
															}
														$cloner	.='</div>
 														<div class="gcpb-field gcpb-field-icon">
															<a href="#" class="button gcpb-upload">';
															
															$style = 'style="display:none"';
															$image_id = '';
															$cloner	.='Upload image';
															$cloner	.='</a>

															<a href="#" class="gcpb-remove" '.$style.'>Remove image</a>
															<input type="hidden" name="gcpb_attribute_icon" data-name="gcpb_attribute_icon" class="gcpb-repeater-field" value="">
														</div>
													<div class="gcpb-field gcpb-field-action">
														<input data-repeater-delete type="button" value="Delete"/>
													</div>
													</div>
												</div>';
 												$cloner	.= '<input  data-repeater-create type="button" class="js-gcpb-inner-add-row" value="Add"/>
												 </div>
												</div>';
 
										$cloner	.='</div>
										</div>';
 
									/* child clone */
									$child_cloner = '';
									 $child_cloner	.='<div id="child-clone">
									 <div class="gcpb-repeater-item" data-repeater-item="sub-repeater" data-repeater-name="gcpb-sub-attributes">
									 <div class="gcpb-repeater-child">
										 <div class="gcpb-field gcpb-field-term">';
										 if( !empty($sub_terms) ) {
											 
											$child_cloner .= '<select data-repeater="select2" name="sub-attribute" data-name="sub-attribute" class="gcpb-repeater-field">';
											 foreach( $sub_terms as $sub_term ) {
												 if(!empty($sub_term['sub_terms'])) {
													$child_cloner .='<optgroup label="'.$sub_term['label'].'">';
													 foreach( $sub_term['sub_terms'] as $s_term) {

														$child_cloner .='<option value="'.$s_term->term_id.'">'.$sub_term['label'].'->'.$s_term->name.'</option>';
													 }
													 $child_cloner .='</optgroup>';
												 }
											 }
											 $child_cloner .= '</select>';
										 }
										$child_cloner.='</div>

										<div class="gcpb-field gcpb-field-icon">
											<a href="#" class="button gcpb-upload">';
											
											$style = 'style="display:none"';
											$image_id = '';
											$child_cloner	.='Upload image';
											$child_cloner	.='</a>

											<a href="#" class="gcpb-remove" '.$style.'>Remove image</a>
											<input type="hidden" name="gcpb_attribute_icon" data-name="gcpb_attribute_icon" class="gcpb-repeater-field" value="">
										</div>
										<div class="gcpb-field gcpb-field-action">
											<input data-repeater-delete type="button" value="Delete"/>
										</div>
									 </div>
								 </div>
								</div>';

				$output = '
				<tr valign="top">
					<th scope="row">'.$field_data['name'].'</th>
						<td>
						<div class="gcpb-repeater-wrap">
								<div class="gcpb-repeater">
									<div data-repeater-name="gcpb_option['.$field_data['id'].']" class="drag gcpb-parent repeater-main">';
									if(!empty($repeater_values)) {
										$i=0;
										foreach( $repeater_values as $repeater_item ) {
									
								$output .= '<div data-repeater-item="repeater" class="gcpb-parent">
											<div class="gcpb-repeater-parent">
												<div class="gcpb-field gcpb-field-filter"><label>Main Filter</label>';
													if( !empty($parent_term) ) {
														$output .= '<select data-repeater="select2" name="main-attribute" data-name="main-attribute" class="js-gcpb-select2 gcpb-repeater-field">';
														foreach( $parent_term as $pk => $pterm ) {
															$selected = ($repeater_item['main-attribute'] == $pk ) ?'selected="selected"':'';
															$output .= '<option value="'.$pk.'" '.$selected.'>'.$pterm.'</option>';
														}
														$output .= '</select>';
													}
									$output .='</div>
												<div class="gcpb-field">';
 													$checked = (isset($repeater_item['is_filter']) && $repeater_item['is_filter'] == 'yes' ) ?'checked="checked"':'';
													$output .= '<label>Is Filter Product ? </label><input type="checkbox" name="is_filter" '.$checked.' data-name="is_filter" value="yes" class="gcpb-repeater-field" />
												</div>
												<div class="gcpb-field">';
													$loop_visibility = (isset($repeater_item['gcpb_loop_visibility']) && $repeater_item['gcpb_loop_visibility'] == 'yes' ) ?'checked="checked"':'';
													$output .= '<label>Show In Loop ? </label><input type="checkbox" name="gcpb_loop_visibility" '.$loop_visibility.' data-name="gcpb_loop_visibility" value="yes" class="gcpb-repeater-field" />
												</div>
												<div class="gcpb-field">';
													$output .= '<input data-repeater-delete type="button" value="Delete"/>
												</div>
											</div>';
												
										$output .='<div class="gcpb-inner-repeater">';
										
										if(isset($repeater_item['gcpb-sub-attributes']) && !empty($repeater_item['gcpb-sub-attributes'])) {
											$output .='<div data-repeater-name="gcpb-sub-attributes" class="drag gcpb-child repeater-block">';
												$j = 0;
												foreach( $repeater_item['gcpb-sub-attributes'] as $sub_attributes ) {

														$output .='<div class="gcpb-repeater-item" data-repeater-item="sub-repeater" data-repeater-name="gcpb-sub-attributes">
															<div class="gcpb-repeater-child">
																<div class="gcpb-field gcpb-field-term">';
																if( !empty($sub_terms) ) {
																	
																	$output .= '<select data-repeater="select2" name="sub-attribute" data-name="sub-attribute" class="js-gcpb-select2 gcpb-repeater-field">';
																	foreach( $sub_terms as $sub_term ) {
																		if(!empty($sub_term['sub_terms'])) {
																			$output .='<optgroup label="'.$sub_term['label'].'">';
																			foreach( $sub_term['sub_terms'] as $s_term) {

																				$selected = ($sub_attributes['sub-attribute'] == $s_term->term_id ) ?'selected="selected"':'';
																				$output .='<option value="'.$s_term->term_id.'" '.$selected.'>'.$sub_term['label'].'->'.$s_term->name.'</option>';
																			}
																			$output .='</optgroup>';
																		}
																	}
																	$output .= '</select>';
																}
															$output .='</div>

															<div class="gcpb-field gcpb-field-icon">
																<a href="#" class="button gcpb-upload">';
																if(!empty($sub_attributes['gcpb_attribute_icon'])) {
																	$style = 'style="display:block"';
																	$image_id = $sub_attributes['gcpb_attribute_icon'];
																	$output .= '<img src="'.wp_get_attachment_url($sub_attributes['gcpb_attribute_icon']).'" />';
																} else {
																	$style = 'style="display:none"';
																	$image_id = '';
																	$output .='Upload image';
																}
																$output .='</a>
																<a href="#" class="gcpb-remove" '.$style.'>Remove image</a>
																<input type="hidden" name="gcpb_attribute_icon" data-name="gcpb_attribute_icon" class="gcpb-repeater-field" value="'.$image_id.'">
															</div>
															<div class="gcpb-field gcpb-field-action">
																<input data-repeater-delete type="button" value="Delete"/>
															</div>
															</div>
														</div>';
														$j++;
												}

												$output .= '<input  data-repeater-create type="button" class="js-gcpb-inner-add-row" value="Add"/>
												</div>
												</div>';
										}

									$output .='</div>
									';
									// data-repeater-item
									$i++;
								}
							}
								$output .='</div>
								<input  data-repeater-create class="js-gcpb-add-row" type="button" value="Add"/>
							</div>';

							$output .= '<div class="gcpb-field-cloner" style="display: none;">';
								$output .=$cloner;
								$output .= $child_cloner;
							$output .= '</div>
						</div>
					</td>
				</tr>';
									

 
				break;
 
			default:
				# code...
				break;
		}

		echo $output;
	}

	function gcpb_setting_form_action() {

		// echo '<pre>'; print_r($_POST); echo '</pre>';
		// die();
		if(isset($_REQUEST['gcpb_option']) && !empty($_REQUEST['gcpb_option'])) {

			foreach ($this->options as $value) {

                if( isset( $_REQUEST['gcpb_option'][$value['id']] ) && !empty($_REQUEST['gcpb_option'][$value['id']]) ) {
                    update_option( $value['id'], $_REQUEST['gcpb_option'][$value['id']]  );
                } else {
                    delete_option( $value['id'] );
                }
            }
		}
		
		header("Location: admin.php?page=gcpb-setting&saved=true");
		// process your form here
	}
		

	function gcpb_post_object() {
		$pages = get_pages(); 
		$page_object = array();

        foreach ( $pages as $page ) {
			$page_object[$page->ID] = $page->post_title;
		}

		return $page_object;
	}
	 
}
 
Ring_Builder_Setting::instance(); ?>