<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class hmg_Table_builder
{
	private $version;

	private $page_slug;

	private $page_hook;

	private $base_url;

	private $db;

	function __construct($_version, $_base_url = false ) {
		$this->load_dependencies();

		$this->version 		= $_version;
		$this->page_slug 	= 'hmg_table_builder';

		$this->db 			= hmg_DB_Table::get_instance();

		add_action( 'admin_menu', array($this, 'add_menu_items') );
		add_action( 'admin_enqueue_scripts', array($this, 'backend_enqueue') );
		add_action( 'admin_init', array($this, 'handle_requests') );
		add_action('plugins_loaded', array($this, 'xml_download'));
		add_action( 'admin_notices', array($this, 'admin_notices') );
		add_shortcode( 'hmg_builded_table', array($this, 'builded_table_callback') );
		add_action( 'init', array($this, 'hmg_table_frontend_scripts') );
		add_action( 'wp_enqueue_scripts', array($this, 'hmg_table_frontend_styles') );

		if(!$_base_url)
			$this->base_url = plugins_url( '', dirname(__FILE__) );
		else
			$this->base_url = $_base_url;
	}

	private function load_dependencies(){
		require_once 'class-hmg-list-table.php';
		require_once 'class-hmg-db-table.php';
		require_once 'class-hmg-table-xml.php';
	}

	public function add_menu_items() {
		$this->page_hook = add_menu_page( __('Table builder', 'hmg-tableplugin'), __('Tables', 'hmg-tableplugin'), 'manage_options', $this->page_slug, array($this, 'print_page'), $this->base_url . "/img/icon.png" );
	}

	public function hmg_table_frontend_scripts() {
		wp_register_script( 'table-builder-front', $this->base_url . '/js/table-builder-front.js', array('jquery'), $this->version, true );
		wp_register_script( 'jquery-stacktable', $this->base_url . '/js/stacktable.js', array('jquery'), '0.1.0', true );
			
	}

	public function hmg_table_frontend_styles() {
		wp_enqueue_style( "hmg-comptable-styles", plugins_url( "css/style.css" , dirname(__FILE__) ), null, $this->version, "all" );			
	}	

	public function backend_enqueue($hook) {
		if( $this->page_hook != $hook )
			return;
		wp_enqueue_style( 'hmg-stylesheet', $this->base_url . '/css/table-builder.css', false, $this->version, 'all' );
		wp_enqueue_script( 'hmg-comptable-script', $this->base_url . '/js/table-builder.js', array('jquery'), $this->version );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script('jquery-effects-bounce');
		if (function_exists('wp_enqueue_media')) {wp_enqueue_media();}

		$hmg_js_strings = array(
			'placeholder' 	=> __('Add content', 'hmg-tableplugin'),
			'resize_error' 	=> __('Please enter valid numbers', 'hmg-tableplugin'),
			'only_one' 	=> __('Please fill only one field', 'hmg-tableplugin'),
			'insert_error_row' 	=> __('Please specify number less than existing rows count', 'hmg-tableplugin'),
			'insert_error_col' 	=> __('Please specify number less than existing cols count', 'hmg-tableplugin'),
			'switch_error' 	=> __('Please enter valid numbers between 1 and', 'hmg-tableplugin')
		);
		wp_localize_script( 'hmg-comptable-script', 'hmg_js_strings', $hmg_js_strings );
	}

	public function print_page() {
	?>
		<div class="wrap">
			<?php
				if(isset($_GET['action']) && $_GET['action'] == 'add'){
					echo sprintf( '<h2>%s <a class="add-new-h2" href="%s">%s</a></h2>', __('Add Table', 'hmg-tableplugin'), admin_url('admin.php?page='.$this->page_slug), __('View All', 'hmg-tableplugin') );
					$this->create_ui();
				}
				elseif(isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['table']) && is_numeric($_GET['table'])){
					echo sprintf( '<h2>%s <a class="add-new-h2" href="%s">%s</a></h2>', __('Edit Table', 'hmg-tableplugin'), admin_url('admin.php?page='.$this->page_slug), __('View All', 'hmg-tableplugin') );
					$table = $this->db->get($_GET['table']);
					if($table)
						$this->create_ui($table);
				}
				else{
					echo sprintf( '<h2>%s <a class="add-new-h2" href="%s">%s</a></h2>', __('Tables', 'hmg-tableplugin'), admin_url('admin.php?page='.$this->page_slug.'&action=add'), __('Add New', 'hmg-tableplugin') );
					$list_table = new hmg_List_Table();
					$list_table->show();
				}
			?>
		</div>
	<?php
	}

	private function create_ui($table = false){
		$table_id 		= $table ? $table['id'] : '';
		$name 			= $table ? $table['name'] : '';
		$rows 				= $table ? $table['rows'] : 4;
		$cols 				= $table ? $table['cols'] : 4;
		$subs 				= $table ? $table['subs'] : '';
		$color				= $table ? $table['color'] : 'default';
		$responsive	= $table ? $table['responsive'] : '';
		$curr_values 	= $table ? $table['tvalues'] : '';
		$col_span = $cols; 
		$sub_array = explode(',', $subs); 
		?>


			<form autocomplete="off" method="POST" class="hmg-form">

		<div class="pro-version-features">
			<h2>Responsive Table $5 Version Features</h2>
			<p>
				<strong><span style="text-decoration:underline;">SEO Optimized Tables</span>, 10 Extra Designs, Dublicate Table, Support</strong>, Export to XML, Image Uploader
			</p>
			<a href="http://plugin-boutique.com/responsive-tables/" target="_blank">Upgrade now for $5</a>
		</div>
				<div class="hmg-sidebar">
				<div class="row">
					<input type="text" class="hmg-comptable-title" placeholder="<?php _e('Give your table a name', 'hmg-tableplugin'); ?>" name="table_name" value="<?php echo esc_attr($name); ?>"  required="required" />
										<?php if($table) submit_button( __('Save Changes', 'hmg-plugin'), 'button-big', 'hmg-save-changes', false ); ?>

				</div>
				<div class="row">
					<div class="shortcode">
						<span>Shortcode: </span><strong>[hmg_builded_table id="<?php echo $table_id; ?>" class=""]</strong> <span>Paste this shortcode where you want the table to appear</span>
					</div>
				<div>
					<div class="row options-panel">
				<input type="hidden" class="hmg-rows" value="<?php echo esc_attr($rows); ?>" name="table_rows" />
				<input type="hidden" class="hmg-cols" value="<?php echo esc_attr($cols); ?>" name="table_cols" />
				<input type="hidden" class="hmg-subs" value="<?php echo esc_attr($subs); ?>" name="table_subs" />
				<div class="hmg-options">
					<div class="hmg-controls">
						<button id="hmg-comptable-resize-btn" type="button" class="first-button button"><?php _e('Add or delete rows & columns', 'hmg-tableplugin') ?></button>
						<select id="hmg-colors" name="table_color" class="hmg-select-color">
							<option value="standard" <?php if($color == 'default') echo 'selected'; ?>><?php _e('Table Design', 'hmg-tableplugin') ;?></option>
							<option value="standard" <?php if($color == 'default') echo 'selected'; ?>><?php _e('Standard', 'hmg-tableplugin') ;?></option>
							<option value="red" <?php if($color == 'red') echo 'selected'; ?>><?php _e('Red', 'hmg-tableplugin') ;?></option>
						</select>
					</div>					
				</div>
				<?php
					if($table) {
						echo '<p class="submit">';
							echo ' ';
							submit_button( __('Save Changes', 'hmg-plugin'), 'primary', 'hmg-save-changes', false );

						echo '</p>';
					} else {
						submit_button( __('Create Table', 'hmg-plugin'), 'primary', 'hmg-create-table', true );
					}
				?>
				</div>
				</div>
				<div class="hmg_comptable_admin_description">
					<div class="hmg_comptable_shortcode_hidden">
						[hmg_builded_table id="<?php echo $table_id; ?>" class="<span id='hmg_comp_shortcode_firsthover'></span><span id='hmg_comp_shortcode_calign'></span>"]
					</div>					
			
				</div>
			</div>
				<table class="hmg-comptable">
					<thead class="hmg-thead">
						<tr>						
							<?php for ($j=1; $j <= $cols; $j++): ?>
								<th><input placeholder="<?php _e('Add content', 'hmg-tableplugin') ?>" type="text" name="table_values[0][<?php echo $j; ?>]" value="<?php echo isset($curr_values[0][$j]) ? esc_attr($curr_values[0][$j]) : ''; ?>" /></th>
							<?php endfor; ?>
						</tr>
					</thead>
					<tbody class="hmg-tbody">
					<?php for ($i=1; $i <= $rows; $i++): ?>
						<?php echo in_array($i, $sub_array) ? '<tr class="subheader">' : '<tr>'; ?>
						<?php for ($j=1; $j <= $cols; $j++): ?>
							<?php echo in_array($i, $sub_array) ? '<td colspan="'.$col_span.'">' : '<td>'; ?>
							<?php if ($j==1) {echo '<span class="num_row_hmg_table">'.$i.'</span>' ;} ;?>
								<textarea placeholder="<?php _e('Add content', 'hmg-tableplugin') ?>" type="text" name="table_values[<?php echo $i; ?>][<?php echo $j; ?>]" ><?php echo isset($curr_values[$i][$j]) ? esc_attr($curr_values[$i][$j]) : ''; ?></textarea>

							</td>
							<?php if(in_array($i, $sub_array)) break; ?>
						<?php endfor; ?>
						</tr>
					<?php endfor; ?>
					</tbody>
				</table>
				<div class="bottom-news">
					<h2>Want to increase your rank on Google?</h2>
					<a target="_blank" href="http://seo-servicen.dk/en/">Check out our SEO Service!</a>
				</div>	
				<div class="clear"></div>							

			</form>

			<?php if(!$table) : ?>
			<form enctype="multipart/form-data" method="POST">
				<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
				<p class="submit">
				<input name="upload_file" type="file" />
					<?php submit_button( __('Import XML', 'hmg-plugin'), 'primary',  'hmg-import-table', false ); echo ' '; ?>
					<?php submit_button( __('Import CSV', 'hmg-plugin'), 'primary',  'hmg-import-csv', false ); echo ' '; ?>
					<select id="hmg-delimiter" name="csv_delimiter" class="hmg-select-delimiter">
						<option value=","><?php _e('"," (comma)', 'hmg-tableplugin') ;?></option>
						<option value=";"><?php _e('";" (semi-colon)', 'hmg-tableplugin') ;?></option>
					</select>
				</p>
			</form>			
			<?php endif; ?>				
			
			<div id="hmg-comptable-resize-dialog" class="hmg-dialog" title="<?php _e('Change Table Size', 'hmg-tableplugin') ?>">
				<div class="hmg-dialog-error"></div>
				<?php _e('Cols', 'hmg-tableplugin') ?>: <input type="text" class="hmg-col-count" />
				<?php _e('Rows', 'hmg-tableplugin') ?>: <input type="text" class="hmg-row-count" />
				<?php _e('Sub-header Rows (e.g.1,3,6)', 'hmg-tableplugin') ?>: <input type="text" class="hmg-sub-array" />
				<button class="button button-primary"><?php _e('Apply', 'hmg-tableplugin') ?></button>
			</div>

			<div id="hmg-row-switcher-dialog" class="hmg-dialog" title="Switch Rows">
				<div class="hmg-dialog-error"></div>
				<?php _e('Row 1', 'hmg-tableplugin') ?>: <input type="text" class="hmg-row1" />
				<?php _e('Row 2', 'hmg-tableplugin') ?>: <input type="text" class="hmg-row2" />
				<button class="button button-primary"><?php _e('Switch', 'hmg-tableplugin') ?></button>
			</div>

			<div id="hmg-col-switcher-dialog" class="hmg-dialog" title="Switch Columns">
				<div class="hmg-dialog-error"></div>
				<?php _e('Col 1', 'hmg-tableplugin') ?>: <input type="text" class="hmg-col1" />
				<?php _e('Col 2', 'hmg-tableplugin') ?>: <input type="text" class="hmg-col2" />
				<button class="button button-primary"><?php _e('Switch', 'hmg-tableplugin') ?></button>
			</div>
			
			<div id="hmg-comptable-addnew-dialog" class="hmg-dialog" title="<?php _e('Add Empty Row/Column', 'hmg-tableplugin') ?>">
				<div class="hmg-dialog-error"></div>
				<?php _e('Add empty col after (number)', 'hmg-tableplugin') ?>: <input type="text" class="hmg-col-after" />
				<?php _e('Add empty row after (number)', 'hmg-tableplugin') ?>: <input type="text" class="hmg-row-after" />
				<button class="button button-primary"><?php _e('Apply', 'hmg-tableplugin') ?></button>
			</div>

			<div id="hmg-comptable-remove-dialog" class="hmg-dialog" title="<?php _e('Delete Row/Column', 'hmg-tableplugin') ?>">
				<div class="hmg-dialog-error"></div>
				<?php _e('Remove col (number)', 'hmg-tableplugin') ?>: <input type="text" class="hmg-col-remove" />
				<?php _e('Remove row (number)', 'hmg-tableplugin') ?>: <input type="text" class="hmg-row-remove" />
				<button class="button button-primary"><?php _e('Apply', 'hmg-tableplugin') ?></button>
			</div>			

		<?php
	}

	private function is_plugin_page() {
		if( !is_admin() || !isset($_GET['page']) || $this->page_slug != $_GET['page'] || (!isset($_GET['action']) && !isset($_GET['action2'])) )
			return false;
		return true;
	}

	public function handle_requests() {
		if( !$this->is_plugin_page() )
			return;

		if(isset($_GET['action2']) && $_GET['action2'] != -1 && $_GET['action'] == -1)
			$_GET['action'] = $_GET['action2'];

		if($_GET['action'] == 'add' && isset($_POST['hmg-create-table'])){
			if (!isset ($_POST['table_respon'])) {$_POST['table_respon'] = '';}
			$result = $this->db->add( $_POST['table_name'], $_POST['table_rows'], $_POST['table_cols'],  $_POST['table_subs'], $_POST['table_color'], $_POST['table_respon'], $_POST['table_values'] );
			if($result){
				$sendback = add_query_arg( array( 'page' => $_GET['page'], 'action' => 'edit', 'table' => $result, 'added' => true ), '' );
				wp_redirect($sendback);
			}
		}

		if($_GET['action'] == 'edit' && isset($_POST['hmg-save-changes']) && isset($_GET['table'])){
			if (!isset ($_POST['table_respon'])) {$_POST['table_respon'] = '';}
			$result = $this->db->update( $_GET['table'], $_POST['table_name'], $_POST['table_rows'], $_POST['table_cols'], $_POST['table_subs'], $_POST['table_color'], $_POST['table_respon'], $_POST['table_values'] );
			$sendback = add_query_arg( array( 'page' => $_GET['page'], 'action' => 'edit', 'table' => $_GET['table'], 'updated' => $result ), '' );
			wp_redirect($sendback);
		}
		
		if($_GET['action'] == 'edit' && isset($_POST['hmg-create-table'])){
			if (!isset ($_POST['table_respon'])) {$_POST['table_respon'] = '';}
			$result = $this->db->add( $_POST['table_name'], $_POST['table_rows'], $_POST['table_cols'],  $_POST['table_subs'], $_POST['table_color'], $_POST['table_respon'], $_POST['table_values'] );
			if($result){
				$sendback = add_query_arg( array( 'page' => $_GET['page'], 'action' => 'edit', 'table' => $result, 'added' => true ), '' );
				wp_redirect($sendback);
			}
		}

 		if($_GET['action'] == 'delete' && isset($_GET['table']) ){
			if(is_array($_GET['table']) || is_numeric($_GET['table'])) {
				$result = $this->db->delete( $_GET['table'] );
				$sendback = add_query_arg( array( 'page' => $_GET['page'], 'deleted' => $result ), '' );
				wp_redirect($sendback);
			}
		} 

		
		if(isset($_POST['hmg-import-table'])) {
			if(is_uploaded_file($_FILES['upload_file']['tmp_name']) && $_FILES['upload_file']['type'] == 'text/xml') {
				$xml = simplexml_load_file($_FILES['upload_file']['tmp_name']);
				$array = xml2array($xml);
			} else {
				exit('Can\'t open file: ' . $_FILES['userfile']['name'] . '. Error: '. $_FILES['upload_file']['error'] .'.');
			}
			$result = $this->db->add($array['name'], $array['rows'], $array['cols'], $array['subs'], $array['color'], $array['responsive'], $array['tvalues'] );
			if($result){
				$sendback = add_query_arg( array( 'page' => $_GET['page'], 'action' => 'edit', 'table' => $result, 'added' => true ), '' );
				wp_redirect($sendback);
			}
		}
	
		if(isset($_POST['hmg-import-csv'])) {
			if(is_uploaded_file($_FILES['upload_file']['tmp_name']) && $_FILES['upload_file']['type'] == 'text/csv' && isset($_POST['csv_delimiter'])) {
				if (($handle = fopen($_FILES['upload_file']['tmp_name'], "r")) !== FALSE) {
					$array =  csv2array( $handle, $_POST['csv_delimiter'] );
				fclose($handle); 
				}
			} else {
				exit('Can\'t open file: ' . $_FILES['userfile']['name'] . '. Error: '. $_FILES['upload_file']['error'] .'.');
			}
			$array['subs'] = '';
			$result = $this->db->add(__('Noname Table', 'hmg-tableplugin'), $array['rows'], $array['cols'], $array['subs'], 'default', '0', $array['tvalues'] );
			if($result){
				$sendback = add_query_arg( array( 'page' => $_GET['page'], 'action' => 'edit', 'table' => $result, 'added' => true ), '' );
				wp_redirect($sendback);
			}
		}
	}

	
	public function admin_notices(){
		if( !$this->is_plugin_page() )
			return;

		$format = '<div class="updated"><p>%s</p></div>';

		if(isset($_GET['added']) && $_GET['added']):
			echo sprintf($format, __('The table has been created successfully!', 'hmg-tableplugin') );
		elseif(isset($_GET['updated']) && $_GET['updated']):
			echo sprintf($format, __('The table has been updated successfully!', 'hmg-tableplugin') );
		elseif(isset($_GET['deleted']) && $_GET['deleted']):
			echo sprintf($format, __('The table has been deleted successfully!', 'hmg-tableplugin') );
		endif;
	}
	
	
	function xml_download() {
		if(isset($_POST['hmg-export-table'])) {
			$result = $this->db->get( $_GET['table'] );
			
			if(!$result)
			return;
		
			$converter = new Array_XML();
			$xmlStr = $converter->convert($result);

			header("Content-type: txt/xml",true,200);
			header("Content-Disposition: attachment; filename=" . $_POST['table_name'] . ".xml" );
			//header('Content-Length: ' . ob_get_length($xmlStr));
			header("Pragma: no-cache");
			header("Expires: 0");
			echo $xmlStr;
			exit();
		}
	}

	function builded_table_callback( $atts ){

		$atts = shortcode_atts( 
			array( 
				'id' => false, 
				'color' => '',
				'class' => ''
			), $atts );

		if(!$atts['id']){
			_e("Please specify the table ID", 'hmg-tableplugin');
			return;
		}
		
		$table = $this->db->get($atts['id']);
		if(!$table)
			return;

	ob_start();
		wp_enqueue_script('table-builder-front');
		wp_enqueue_script('jquery-columnhover');
		?>
			<?php if($table['responsive']) wp_enqueue_script('jquery-stacktable'); ?>
					<?php $change_color = ($atts['color']) ? $atts['color'] : $table['color'] ; ?>
			<div itemscope="" itemtype="http://schema.org/Table" class="hmg-comptable-wrap hmg-thead<?php echo ' hmg-theme-'. $change_color; ?>">
				<table id="hmg-table-<?php echo $atts['id'];?>" class="hmg-comptable<?php echo ' '. $atts['class'].'' ; ?><?php if($table['responsive'] == 1) echo ' hmg-comptable-responsive'; elseif($table['responsive'] == 2) echo ' hmgt-column-stack'; ?>">

					<thead>
						<tr>							
							<?php for ($j=1; $j <= $table['cols']; $j++): ?>
								<?php if ($j==1 && empty($table['tvalues'][0][1])) :?>
									<th itemprop="headline" class="placeholder hmg-placeholder"></th>
								<?php else :?>
									<th itemprop="headline"><?php echo $this->replace_placeholders($table['tvalues'][0][$j]); ?></th>
								<?php endif;?>	
								
							<?php endfor; ?>
						</tr>
					</thead>
					<tbody class="hmg-tbody">
					<?php for($i=1; $i <= $table['rows']; $i++): ?>
					<?php $sub_array = explode(',', $table['subs']);  ?>
						<?php echo in_array($i, $sub_array) ? '<tr class="subheader">' : '<tr>'; ?>
							<?php for ($j=1; $j <= $table['cols']; $j++): ?>
								<?php echo in_array($i, $sub_array) ? '<td colspan="'.$table['cols'].'">' : '<td itemprop="description">'; ?>
								<?php if (!empty ($table['tvalues'][$i][$j])):?>
									<?php $table_cell_echo = $this->replace_placeholders($table['tvalues'][$i][$j]); ?>
									<?php echo do_shortcode($table_cell_echo); ?>
								<?php endif;?>
								</td>
								<?php if(in_array($i, $sub_array)) break; ?>
							<?php endfor; ?>
						</tr>
					<?php endfor; ?>
					</tbody>
				</table>

			</div>
		<?php
		return ob_get_clean();
	}


	public function replace_placeholders($str){
		$values 			= array();
		$values['tick'] 	= '<i class="hmg-table-icon hmg-icon-tick"></i>';
		$values['cross'] 	= '<i class="hmg-table-icon hmg-icon-cross"></i>';
		$values['info'] 	= '<i class="hmg-table-icon hmg-icon-info"></i>';
		$values['warning'] 	= '<i class="hmg-table-icon hmg-icon-warning"></i>';
		$values['heart'] 	= '<i class="hmg-table-icon hmg-icon-heart"></i>';
		$values['lock'] 	= '<i class="hmg-table-icon hmg-icon-lock"></i>';
		$values['star'] 	= '<i class="hmg-table-icon hmg-icon-star"></i>';
		$values['star-empty'] = '<i class="hmg-table-icon hmg-icon-star-empty"></i>';
		$values['col-choice'] = '<span class="badge_div_col"></span>';
		$values['col-choice-image'] = '<span class="badge_div_col badge_div_col_img"></span>';	
		$values['row-choice'] = '<span class="badge_div_row"></span>';
		$values['row-choice-image'] = '<span class="badge_div_row badge_div_col_img"></span>';			

		foreach ($values as $key => $value) {
			$str = str_replace('%'.strtoupper($key).'%', $value, $str);
		}
		return $str;
	}

	public function initialize()
	{
		$this->db->create_table();
	}

	public function rollback()
	{
		$table = hmg_DB_Table::get_instance();
		$table->drop_table();
	}
}