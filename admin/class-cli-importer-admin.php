<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.opticommerce.co.uk/
 * @since      1.0.0
 *
 * @package    Cli_Importer
 * @subpackage Cli_Importer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cli_Importer
 * @subpackage Cli_Importer/admin
 * @author     OptiCommerce <faisal.ramzan@musharp.com>
 */
class Cli_Importer_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Create new menu for import products
		add_action( 'admin_menu', array($this, 'opti_import_menu' ));

		// Ajax call to import manually CSV data
		add_action('wp_ajax_csv_ajax_call', array($this, 'import_data_from_manual_csv'));
		add_action('wp_ajax_nopriv_csv_ajax_call', array($this, 'import_data_from_manual_csv'));

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cli_Importer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cli_Importer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cli-importer-admin.css', array(), rand(10, 10000), 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cli_Importer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cli_Importer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cli-importer-admin.js', array( 'jquery' ), rand(10, 10000), false );

		// For JS access
		wp_localize_script( 
			$this->plugin_name, 
			'ajax_object', 
			array( 
				'ajax_url' => site_url() . '/wp-admin/admin-ajax.php',
			)
		);
	}

	/**
	 * Register Importer Page
	 */
	public function opti_import_menu (){
		add_menu_page( 
			__( 'CL Importer', 'woocommerce-importer-mu' ),
			'CL Importer',
			'manage_options',
			'cl-importer',
			array($this, 'import_form'),
			'dashicons-welcome-widgets-menus', 
			90
		);
	}

	/**
	 * Display Form to Import data via files
	 */
	public function import_form(){
		$output = '';
		$output .= '
		<link rel="stylesheet" href="'.plugin_dir_url( __FILE__ ).'css/bootstrap.min.css">
		<script src="'.plugin_dir_url( __FILE__ ).'js/popper.min.js"></script>
		<script src="'.plugin_dir_url( __FILE__ ).'js/bootstrap.min.js"></script>
		<div class="wrap" id="import_form">
			<h1 class="wp-heading-inline">Import CL Products</h1>
			<div id="message" class="notice notice-success is-dismissible">
				<p></p>
			</div>
			<form>
				<div class="custom-file">
					<h6>Upload CSV File</h6>
					<input type="file" class="custom-file-input" id="csvFile" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
					<label class="custom-file-label" for="csvFile">Choose file</label>
				</div>
				
				<div class="images-path">
					<h6>Images Path</h6>
					<input type="text" class="form-control" id="imagesPath" name="imagesPath" value="https://contactlensdemo.optiserver.co.uk/dblutadmir/cl-images/" />
					<small id="imagesfieldHelp" class="form-text text-muted">(e.g.,  https://contactlensdemo.optiserver.co.uk/dblutadmir/cl-images/<span style="background: yellow;">002101.png</span>)</small>
				</div>
			</form> 
			
			<p class="submit">
				<button name="runImport" id="runImport" class="button button-primary" type="submit">
					Import Now
					<div class="spinner-border" role="status"></div>	
				</button>
			</p>
		</div>';

		echo $output;
	}
	
	/**
	 * Import CSV data based on CLID
	 */
	public function import_data_from_manual_csv() {
		global $wpdb;
		$allData = array();
		$imagesPath = filter_input(INPUT_POST, 'imagesPath');
		$count = 1;
		// check if file exist
		if(isset($_FILES['file'])) {
			$file_tmp_name = $_FILES["file"]["tmp_name"];
		} else {
			echo json_encode(array('message' => 'Please select CSV file.', 'status' => 'error')); 
			wp_die();
		}
		
		if (isset($_FILES["file"]) && $_FILES["file"]["size"] > 0) {

			$file = fopen($file_tmp_name, "r");
            // Skipping header row
			fgetcsv($file); 
			while (($data = fgetcsv($file, 2000, ",")) !== FALSE) {
				// CSV Columns Data
				$clid = $data[0];
				$manufacturer = $data[1];
				$brand = $data[2];
				$cl_type = $data[3];
				$wear_duration = $data[4];
				$title = $data[5];
				$product_code = $data[6];
				$pack_size = $data[7];
				$regular_price = $data[8];

				// Check if product already exist or not
				$query = $wpdb->prepare(
					'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = %s AND post_type = \'product\'', $title
				);
				$wpdb->query( $query );
				if ( $wpdb->num_rows ) {
					// Title already exists
					$allData['titles_exist'][] = $title;
					$allData['existing_count'] = count ($allData['titles_exist']);
				} else {
					
					// if CLID not starting with 00
					if (preg_match("~^0\d+$~", $clid)) {
						$clid = $clid;
					}
					else {
						$clid = '00'.$clid;
					}

					if ($regular_price != '0') {

						$db_table = $wpdb->prefix.'cl_verification_data';
						
						$sql_query = "
						SELECT 
						GROUP_CONCAT(DISTINCT(NULLIF (basecurve, ''))) as basecurve, 
						GROUP_CONCAT(DISTINCT(NULLIF (diameter, ''))) as diameter, 
						GROUP_CONCAT(DISTINCT(NULLIF (sphere, ''))) as sphere, 
						GROUP_CONCAT(DISTINCT(NULLIF (cylinder, ''))) as cylinder, 
						GROUP_CONCAT(DISTINCT(NULLIF (axis, ''))) as axis, 
						GROUP_CONCAT(DISTINCT(NULLIF (addition, ''))) as addition, 
						GROUP_CONCAT(DISTINCT(NULLIF (dominance, ''))) as dominance, 
						GROUP_CONCAT(DISTINCT(NULLIF (color, ''))) as color, 
						GROUP_CONCAT(DISTINCT(NULLIF (upccode, ''))) as upccode 
						FROM $db_table WHERE clid = $clid";

						$query_data = $wpdb->get_results( $sql_query, ARRAY_A );
						
						// Order Sphere Start
						$ordered_array_sphere = array();
						$default_sphere = '-20.00, -19.50, -19.00, -18.50, -18.00, -17.50, -17.00, -16.50, -16.00, -15.50, -15.00, -14.50, -14.00, -13.50, -13.00, -12.50, -12.00, -11.50, -11.00, -10.50, -10.00, -9.75, -9.50, -9.25, -9.00, -8.75, -8.50, -8.25, -8.00, -7.75, -7.50, -7.25, -7.00, -6.75, -6.50, -6.25, -6.00, -5.75, -5.50, -5.25, -5.00, -4.75, -4.50, -4.25, -4.00, -3.75, -3.50, -3.25, -3.00, -2.75, -2.50, -2.25, -2.00, -1.75, -1.50, -1.25, -1.00, -0.75, -0.50, -0.25, 0.00, +0.25, +0.50, +0.75, +1.00, +1.25, +1.50, +1.75, +2.00, +2.25, +2.50, +2.75, +3.00, +3.25, +3.50, +3.75, +4.00, +4.25, +4.50, +4.75, +5.00, +5.25, +5.50, +5.75, +6.00, +6.25, +6.50, +7.00, +7.50, +8.00, +8.50, +9.00, +9.50, +10.00, +10.50, +11.00, +11.50, +12.00, +12.50, +13.00, +13.50, +14.00, +14.50, +15.00, +15.50, +16.00, +16.50, +17.00, +17.50, +18.00, +18.50, +19.00, +19.50, +20.00';
						$default_sphere_arr = explode (",", $default_sphere);
						$product_sphere = explode (",", $query_data[0]['sphere']);
						foreach( $default_sphere_arr as $key => $val ){
							if(in_array( $val , $product_sphere )){
								$ordered_array_sphere[] = $val;
							}
						}
						// Order Sphere End
						
						// Order Axis Start
						$ordered_array_axis = array();
						$default_axis = '5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100, 105, 110, 115, 120, 125, 130, 135, 140, 145, 150, 155, 160, 165, 170, 175, 180';
						$default_axis_arr = explode (",", $default_axis);
						$product_axis = explode (",", $query_data[0]['axis']);
						foreach( $default_axis_arr as $key => $val ){
							if(in_array( $val , $product_axis )){
								$ordered_array_axis[] = $val;
							}
						}
						// Order Axis End

						$post_id = wp_insert_post( array(
							'post_author' => 1,
							'post_title' => $title,
							'post_content' => '',
							'post_status' => 'publish',
							'post_type' => "product",
						) );
		
						// Get product image using CLID
						$url = $imagesPath.$clid.'.png';
						$url_is_image = $this->url_is_image($url);
						if ($url_is_image) {
							$url = $imagesPath.$clid.'.png';
						} else {
							$url = plugin_dir_url( dirname( __FILE__ ) ).'admin/images/no_image.png';
						}
						 
						// Upload product image in WordPress media and attach to product
						//$image = $this->upload_image($url, $post_id);
						$image = $this->getImage($url, $post_id);
						
						update_post_meta( $post_id, 'product_code', $product_code );
						update_post_meta( $post_id, 'total_sales', '0' );
						update_post_meta( $post_id, 'lens_per_box', $pack_size );
						update_post_meta( $post_id, '_product_reference', $clid );
						update_post_meta( $post_id, '_sku', $clid );
						
						// Database Columns based on CLID
						$eye_type = array('Left', 'Right');
						wp_set_object_terms( $post_id, $eye_type, 'pa_eye-type', true );
						$color = explode (",", $query_data[0]['color']);
						wp_set_object_terms( $post_id, $color, 'pa_color', true );
						$basecurve = explode (",", $query_data[0]['basecurve']);
						wp_set_object_terms( $post_id, $basecurve, 'pa_base-curve', true );
						$diameter = explode (",", $query_data[0]['diameter']);
						wp_set_object_terms( $post_id, $diameter, 'pa_diameter', true );
						$sphere = $ordered_array_sphere;
						wp_set_object_terms( $post_id, $sphere, 'pa_sphere', true );
						$cylinder = explode (",", $query_data[0]['cylinder']);
						wp_set_object_terms( $post_id, $cylinder, 'pa_cylinder', true );
						$axis = $ordered_array_axis;
						wp_set_object_terms( $post_id, $axis, 'pa_axis', true );
						$addition = explode (",", $query_data[0]['addition']);
						wp_set_object_terms( $post_id, $addition, 'pa_addition', true );
						$dominance = explode (",", $query_data[0]['dominance']);
						wp_set_object_terms( $post_id, $dominance, 'pa_dominance', true );
						
						wp_set_object_terms( $post_id, $manufacturer, 'pa_manufacturer', true );
						wp_set_object_terms( $post_id, $brand, 'pa_brand', true );
						wp_set_object_terms( $post_id, $cl_type, 'pa_cl-type', true );
						wp_set_object_terms( $post_id, $wear_duration, 'pa_wear-duration', true );
						
						// Eye Type (1)
						$attributes_array['eye_type'] = array(
							'label' => 'Eye Type',									
							'name' => 'pa_eye-type',
							'value' => $eye_type,
							'is_visible' => '1',
							'is_variation' => '1',
							'position' => '1',
							'is_taxonomy' => '1'
						);
						// Color (2)
						if (empty($query_data[0]['color']) || is_null($query_data[0]['color'])) {
							$color = '';
							$attributes_array['color'] = array();
						} else {
							if ($query_data[0]['color'] == 'Clear') {
								$attributes_array['color'] = array();
							} else {
								$attributes_array['color'] = array(
									'label' => 'Color',
									'name' => 'pa_color',
									'value' => $color,
									'is_visible' => '1',
									'is_variation' => '1',
									'position' => '2',
									'is_taxonomy' => '1'
								);
							}
						}
						
						// Sphere (3)
						if (empty($query_data[0]['sphere']) || is_null($query_data[0]['sphere'])) {
							$sphere = '';
							$attributes_array['sphere'] = array();
						} else {
							$attributes_array['sphere'] = array(
								'label' => 'Sphere',
								'name' => 'pa_sphere',
								'value' =>  $sphere,
								'is_visible' => '1',
								'is_variation' => '1',
								'position' => '3',
								'is_taxonomy' => '1'
							);
						}
						//Base Curve (4)
						if (empty($query_data[0]['basecurve']) || is_null($query_data[0]['basecurve'])) {
							$basecurve = '';
							$attributes_array['basecurve'] = array();
						} else {
							$attributes_array['basecurve'] = array(
								'label' => 'Base Curve',
								'name' => 'pa_base-curve',
								'value' => $basecurve,
								'is_visible' => '1',
								'is_variation' => '1',
								'position' => '4',
								'is_taxonomy' => '1'
							);
						}
						// Diameter (5)
						if (empty($query_data[0]['diameter']) || is_null($query_data[0]['diameter'])) {
							$diameter = '';
							$attributes_array['diameter'] = array();
						} else {
							$attributes_array['diameter'] = array(
								'label' => 'Diameter',
								'name' => 'pa_diameter',
								'value' => $diameter,
								'is_visible' => '1',
								'is_variation' => '1',
								'position' => '5',
								'is_taxonomy' => '1'
							);
						}
						// Cylinder (6)
						if (empty($query_data[0]['cylinder']) || is_null($query_data[0]['cylinder'])) {
							$cylinder = '';
							$attributes_array['cylinder'] = array();
						} else {
							$attributes_array['cylinder'] = array(
								'label' => 'Cylinder',
								'name' => 'pa_cylinder',
								'value' => $cylinder,
								'is_visible' => '1',
								'is_variation' => '1',
								'position' => '6',
								'is_taxonomy' => '1'
							);
						}
						// Axis (7)
						if (empty($query_data[0]['axis']) || is_null($query_data[0]['axis'])) {
							$axis = '';
							$attributes_array['axis'] = array();
						} else {
							$attributes_array['axis'] = array(
								'label' => 'Axis',
								'name' => 'pa_axis',
								'value' => $axis,
								'is_visible' => '1',
								'is_variation' => '1',
								'position' => '7',
								'is_taxonomy' => '1'
							);
						}
						// Addition (8)
						if (empty($query_data[0]['addition']) || is_null($query_data[0]['addition'])) {
							$addition = '';
							$attributes_array['addition'] = array();
						} else {
							$attributes_array['addition'] = array(
								'label' => 'Addition',
								'name' => 'pa_addition',
								'value' => $addition,
								'is_visible' => '1',
								'is_variation' => '1',
								'position' => '8',
								'is_taxonomy' => '1'
							);
						}
						// Dominance (9)
						if (empty($query_data[0]['dominance']) || is_null($query_data[0]['dominance'])) {
							$dominance = '';
							$attributes_array['dominance'] = array();
						} else {
							$attributes_array['dominance'] = array(
								'label' => 'Dominance',
								'name' => 'pa_dominance',
								'value' => $dominance,
								'is_visible' => '1',
								'is_variation' => '1',
								'position' => '9',
								'is_taxonomy' => '1'
							);
						}
						// Manufacturer (10)
						if (empty($manufacturer) || is_null($manufacturer)) {
							$manufacturer = '';
							$attributes_array['manufacturer'] = array();
						} else {
							$attributes_array['manufacturer'] = array(
								'label' => 'Manufacturer',
								'name' => 'pa_manufacturer',
								'value' => $manufacturer,
								'is_visible' => '1',
								'is_variation' => '0',
								'position' => '10',
								'is_taxonomy' => '1'
							);
						}
						// Brand (11)
						if (empty($brand) || is_null($brand)) {
							$brand = '';
							$attributes_array['brand'] = array();
						} else {
							$attributes_array['brand'] = array(
								'label' => 'Brand',
								'name' => 'pa_brand',
								'value' => $brand,
								'is_visible' => '1',
								'is_variation' => '0',
								'position' => '11',
								'is_taxonomy' => '1'
							);
						}
						// CL Type (12)
						if (empty($cl_type) || is_null($cl_type)) {
							$cl_type = '';
							$attributes_array['cl_type'] = array();
						} else {
							$attributes_array['cl_type'] = array(
								'label' => 'CL Type',
								'name' => 'pa_cl-type',
								'value' => $cl_type,
								'is_visible' => '1',
								'is_variation' => '0',
								'position' => '12',
								'is_taxonomy' => '1'
							);
						}
						// Wear Duration (13)
						if (empty($wear_duration) || is_null($wear_duration)) {
							$wear_duration = '';
							$attributes_array['wear_duration'] = array();
						} else {
							$attributes_array['wear_duration'] = array(
								'label' => 'Wear Duration',
								'name' => 'pa_wear-duration',
								'value' => $wear_duration,
								'is_visible' => '1',
								'is_variation' => '0',
								'position' => '13',
								'is_taxonomy' => '1'
							);
						}
						
						// Set product attributes by ID
						update_post_meta( $post_id, '_product_attributes', $attributes_array );
						// make product variable
						wp_set_object_terms( $post_id, 'variable', 'product_type');
						
						/**
						 * Create contact-lenses category/assign if already exist
						 */
						$term = get_term_by('slug', 'contact-lenses', 'product_cat');
						if ( $term ) {
							wp_set_object_terms($post_id, $term->term_id, 'product_cat');
						} else {
							$category_id = wp_insert_term( 'Contact Lenses', 'product_cat', array(
								'description' => '',
								'parent' => 0,
								'slug' => 'contact-lenses'
							) );
							wp_set_object_terms($post_id, $category_id, 'product_cat');
						}
						
						// Create variations 
						$parent_id = $post_id;
						$variation = array(
							'post_title'   => $title,
							'post_content' => '',
							'post_status'  => 'publish',
							'post_parent'  => $parent_id,
							'post_type'    => 'product_variation'
						);
						$variation_id = wp_insert_post( $variation );
						//WC_Product_Variable::sync( $parent_id );
						update_post_meta( $variation_id, '_regular_price', $regular_price );
						//update_post_meta( $variation_id, '_stock', NULL );
						update_post_meta( $variation_id, '_stock_status', 'instock'); 
						update_post_meta( $post_id, '_stock_status', 'instock');
						update_post_meta( $post_id, '_price', $regular_price );
						update_post_meta( $post_id, '_regular_price', $regular_price );

						// For console to verify data as OBJECT
						$dataToInsert = [
							'clid' => $clid,
							'manufacturer' => $manufacturer,
							'brand' => $brand,
							'cl_type' => $cl_type,
							'wear_duration' => $wear_duration,
							'title' => $title,
							'product_code' => $product_code,
							'regular_price' => $regular_price,
							'color' => $color,
							'basecurve' => $basecurve,
							'diameter' => $diameter,
							'sphere' => $sphere,
							'cylinder' => $cylinder,
							'axis' => $axis,
							'addition' => $addition,
							'dominance' => $dominance,
							'upccode' => $upccode,
							'Image' => $image
						];
						if (!empty($dataToInsert)) {
							$allData['data'][] = $dataToInsert;
							$allData['new_count'] = count ($allData['data']);
						}
					}
				}
				$count++;
				//wp_die('Faisal');
            }
		}
		echo json_encode(
			array(
				'message' => 'CSV file uploaded. '.$allData['new_count'].' new products have been imported successfully.', 
				'status' => 'updated',
				'Data'   => $allData,
			)
		);
		wp_die();
	}
	
	// Get image URL and assign it to product using $post_id
	public function upload_image($url, $post_id) {
		$image = "";
		if($url != "") {
			$file = array();
			$file['name'] = basename($url);
			$file['type'] = 'image/png';
			$file['tmp_name'] = download_url($url);
			$file['size'] = filesize(download_url($url));
			if (is_wp_error($file['tmp_name'])) {
				@unlink($file['tmp_name']);
				echo json_encode(array('Message' => $file['tmp_name']->get_error_messages()));
				wp_die();
			} else {
				$attachmentId = media_handle_sideload($file, $post_id);
				if ( is_wp_error($attachmentId) ) {
					@unlink($file['tmp_name']);
					echo json_encode($attachmentId->get_error_messages());
					wp_die();
				} else {        
					update_post_meta($post_id, '_thumbnail_id', $attachmentId);
					$image = wp_get_attachment_url( $attachmentId );
				}
			}
		}
		return $image;
	}

	public function url_is_image( $url ) {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return FALSE;
		}
		$ext = array( 'jpeg', 'jpg', 'gif', 'png' );
		$info = (array) pathinfo( parse_url( $url, PHP_URL_PATH ) );
		return isset( $info['extension'] )
			&& in_array( strtolower( $info['extension'] ), $ext, TRUE );
	}

	public function getImage($url, $post_id) {
		// Add Featured Image to Products
		$image_url        = $url; // Define the image URL here
		$image_name       = basename($url);
		$upload_dir       = wp_upload_dir(); // Set upload folder
		$image_data       = file_get_contents($image_url); // Get image data
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
		$filename         = basename( $unique_file_name ); // Create image file name
		// Check folder permission and define file location
		if( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}
		// Create the image  file on the server
		file_put_contents( $file, $image_data );
		// Check image file type
		$wp_filetype = wp_check_filetype( $filename, null );
		// Set attachment data
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		// Create the attachment
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
		// Include image.php
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id, $attach_data );
		// And finally assign featured image to post
		set_post_thumbnail( $post_id, $attach_id );
	}
}