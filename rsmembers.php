<?php
/*
	Plugin Name: RS Members
	Plugin URI: http://wordpress.org/plugins/rs-members/
	Description: RS-members is wordpress most powerful membership plugin many many features are include there.
	Version: 1.0.1
	Author: themexpo
	Author URI: http://www.themexpo.net/
	License: GPL3+
	Text Domain: rs-members
	*/
	
	/*
    Copyright Automattic and many other contributors.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 1 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.
*/

class RsMembers{
	
	private static $_instance = NULL;

	const PLUGIN_NAME = 'RS Members';	// plugin's full name
	const PLUGIN_VERSION = '1.0.1';				// plugin version
	const PLUGIN_SLUG = 'rs-members';			// plugin slug name
	const PLUGIN_DOMAIN = 'rsmembers';			// the text domain used by the plugin
	//const OPTION_NAME = 'rsmembers_options';

	private $dir_plugin = NULL;			// the directory where the plugin code is installed
	private $dir_include = NULL;		// the directory where the plugin include files are located
	private $dir_assets = NULL;			// the directory where the plugin assets are located
	private $url_assets = NULL;			// the URL to the plugin assets directory
	
	public $library = NULL;				// the URL to the plugin assets directory
		
	private function __construct(){
		$this->dir_plugin = dirname(__FILE__) . DIRECTORY_SEPARATOR;
		$this->dir_include = $this->dir_plugin . 'include' . DIRECTORY_SEPARATOR;
		$this->dir_assets = $this->dir_plugin . 'assets' . DIRECTORY_SEPARATOR;
		$this->url_assets = plugin_dir_url(__FILE__) . 'assets/';
		
		register_activation_hook(__FILE__, array(&$this, 'install'));
		register_deactivation_hook(__FILE__, array(&$this, 'uninstall'));
		
		$rsmembers_settings  = get_option( 'rsmembers_settings' );		
		( ! defined( 'modreg' ) ) ? define( 'modreg',      $rsmembers_settings[1][4]  ) : '';
		( ! defined( 'postrestrice' ) ) ? define( 'postrestrice',      $rsmembers_settings[5][4]  ) : '';
		( ! defined( 'pagerestrice' ) ) ? define( 'pagerestrice',      $rsmembers_settings[6][4]  ) : '';
				
		$this->load('rsmembers-library.php');
	 	$this->library = RsMembersLibrary::get_instance($this);
		
				
		$this->load('rsmembers-widget-login.php');
		$this->load('rsmembers-widget-signup.php');
				
		
		
		if (is_admin()) {
			$this->wpuser_save();
			
			$this->load('rsmembers-admin.php');
			RsMembersAdmin::get_instance($this);
		} else {
			$this->load('rsmembers-public.php');
			RsMembersPublic::get_instance($this);
		}
		
		// Enable automatic updates for plugins
		add_filter('auto_update_plugin', '__return_true');

		add_action('plugins_loaded', array(&$this, 'load_textdomain' ));
		add_action('init', array(&$this, 'rsmember_register_css' ));	
	}

	
	// register our form css
	function rsmember_register_css() {
		wp_enqueue_style( rsmembers::PLUGIN_SLUG.'-main', $this->get_assets_url('css/main.css'), array(), rsmembers::PLUGIN_VERSION );
		wp_enqueue_script( rsmembers::PLUGIN_SLUG.'-rs-login-widget', $this->get_assets_url('js/rs-login-widget.js'), array(), rsmembers::PLUGIN_VERSION, TRUE );		
	}
	
		
	/**
	 * Return a Singleton instance of the class
	 * @return object Returns the instance of the class
	 */
	public static function get_instance(){
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}
	
	/**
	 * Loads a specific class name
	 * @param string $file The name of the class file to load
	 */
	public function load_class($file){
		$this->load('classes' . DIRECTORY_SEPARATOR . $file);
	}
	public function load($file){
		include_once($this->dir_include . $file);
	}
	
	/**
	 * Plugin activation callback. Called once when the plugin is installed.
	 */
	public function install(){
		$this->load('rsmembers-install.php');
		RsMembersInstall::install();
	}

	/**
	 * Plugin deactivation callback. Called once when the plugin is uninstalled.
	 */
	public function uninstall()
	{
		$this->load('rsmembers-uninstall.php');
		RsMembersUninstall::uninstall();
	}
	
	
	/**
	 * Returns one of the plugin's directories.
	 * @param string $dir The plugin subdirectory name
	 * @return string The directory with a trailing slash
	 */
	public function get_directory($dir = NULL)
	{
		$dir = $this->dir_plugin . (NULL === $dir ? '' : $dir . DIRECTORY_SEPARATOR);
		return ($dir);
	}

	/**
	 * Returns a URL to the plugin's assets directory
	 * @param string $asset The directory name nad file name of the asset
	 * @return string The URL referencing the plugin's asset
	 */
	public function get_assets_url($asset = NULL)
	{
		$url = $this->url_assets;
		if (NULL !== $asset)
			$url .= $asset;
		return ($url);
	}
	
	/**
	 * Returns a URL to the plugin's include directory
	 */
	public function get_include_url($includeurl = NULL)
	{
		$url = plugin_dir_url(__FILE__).'include/';
		if (NULL !== $includeurl)
			$url .= $includeurl;
		return ($url);
	}

	/**
	 * Loads the plugin's textdomain
	 */
	public function load_textdomain(){		
		load_plugin_textdomain(
			'rsmembers',					// the text domain (see Plugin Headers)
			FALSE,								// deprecated parameter
			$this->get_directory('language'));
	}

	
	/**
	 * Email send
	 */	
	function mail_send($to, $subject, $message, $headers){
		$sent_message = wp_mail( $to, $subject, $message, $headers );	
		if ( $sent_message ) {
			echo 'Message send to '.$to;
		} else {
			echo 'The message was not sent to '.$to;
		}
	}
	
	
	/**
	 * Saved the plugin's data
	 */
	public function wpuser_save(){
				
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['caseselect']) && $_POST['caseselect']=='news_letter'   ){
						
				require( ABSPATH . WPINC . '/pluggable.php' );
							
				$headers = 'From: User Registration '.  get_option( 'admin_email' ) . "\r\n";
				$headers .= 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
							
				$subject = sanitize_text_field( $_POST['subject'] );
				$message = sanitize_text_field( $_POST['message'] );
				$traditional = sanitize_text_field( $_POST['traditional'] );			
				for($i=0;$i<sizeof($traditional);$i++){
					$to = $traditional[$i];	
					$to = sanitize_email($to);							
					$this->mail_send($to, $subject, $message, $headers);	
				}
				die();
		}
		
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['caseselect']) && $_POST['caseselect']=='plugin_settings'   ){
						
				$rsmembers_settings = get_option( 'rsmembers_settings' );
				$value = '';
				for( $row = 0; $row < count( $rsmembers_settings)-1; $row++ ) {				
					$value = sanitize_text_field( $_POST[$rsmembers_settings[$row][2]] );				
					$set = array( $rsmembers_settings[$row][0],$rsmembers_settings[$row][1],$rsmembers_settings[$row][2],$rsmembers_settings[$row][3],$value  );
					$rsmembers_newsettings[$row] = $set;
				}			
				$set1 = array( $row+1, 'Terms & Condition Page','termcondi','text',$_POST["termcondi"]);
				$rsmembers_newsettings[$row] = $set1;
										
				update_option( 'rsmembers_settings', $rsmembers_newsettings );
				echo 'Settings updated';
				die();
			
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['caseselect']) && $_POST['caseselect']=='required_message'   ){
			
				$rsmembers_messageoptions = get_option( 'rsmembers_messageoptions' );				
				for( $row = 0; $row < count( $rsmembers_messageoptions); $row++ ) {				
					$value = sanitize_text_field( $_POST["rmessage_".$row] );
					$set = array( $rsmembers_messageoptions[$row][0], $value );
					$rsmembers_newmessageoptions[$row] = $set;
				}		
				update_option( 'rsmembers_messageoptions', $rsmembers_newmessageoptions );
				echo 'Required message updated';
				die();
				
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['caseselect']) && $_POST['caseselect']=='field_form'   ){
			$rsmembers_fieldoptions = get_option( 'rsmembers_fieldoptions' );
			update_option( 'rsmembers_fieldoptions', '' );
			
			for( $row = 0; $row < count( $rsmembers_fieldoptions); $row++ ) {
				$value1 = sanitize_text_field( $_POST['fieldtitle'][$row] );
				$value2 = sanitize_text_field( $_POST['fieldname'][$row] );
				$value3 = sanitize_text_field( $_POST['fieldtype'][$row] );
				$value4 = sanitize_text_field( $_POST['fieldaction'][$row] );
				$value5 = sanitize_text_field( $_POST['fieldrequired'][$row] );
				$value6 = sanitize_text_field( $_POST['fieldselectval'][$row] );
				$value7 = sanitize_text_field( $_POST['fieldvalidation'][$row] );
				$value8 = sanitize_text_field( $_POST['fieldsystemtype'][$row] );
								
				$set = array( $row+1 , $value1, $value2, $value3, $value4, $value5, $value6, $value7, $value8);
				$rsmembers_newfieldoptions[$row] = $set;
				
			}
			update_option( 'rsmembers_fieldoptions', $rsmembers_newfieldoptions );
			echo 'Required Field updated';
			die();
		}
		
		
		
		if($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['type']) && $_GET['type']=='editfields' ){	
				
			$rsmembers_fieldoptions = get_option( 'rsmembers_fieldoptions' );
			$fieldsid = $_GET['fieldsid'];
							
			for( $row = 0; $row < count( $rsmembers_fieldoptions); $row++ ) {				
				if($row==$fieldsid){
				
				?>		
				<div style="height:410px; overflow:auto; margin:20px;">
                		 
				<form name="updateformfield" id="updateformfield" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>" enctype="multipart/form-data">
					<input type="hidden" name="fieldposition" value="<?php echo $fieldsid; ?>">
					<input type="hidden" name="caseselect" value="field_update_form">	
					<input type="hidden" value="<?php echo $rsmembers_fieldoptions[$row][4]; ?>" id="field_name_action" name="field_name_action">
					<input type="hidden" value="<?php echo $rsmembers_fieldoptions[$row][8]; ?>" id="field_system_type" name="field_system_type">
                    <input type="hidden" value="<?php echo $rsmembers_fieldoptions[$row][5]; ?>" id="onregarea" name="onregarea">
						
						<h3 class="title">Edit Fields Info</h3><br>                
						<div class="form-inner15">
							<div class="left-col">Field Name</div>
							<div class="right-col">
								<input id="FieldName" name="FieldName" type="text" value="<?php echo $rsmembers_fieldoptions[$row][1]; ?>" class="text-control">
								<div class="clr"></div>
								<div class="r-c-note"></div>
							</div>
							<div class="clr"></div>
						</div>
						<div class="form-inner15">
							<div class="left-col">Field Type</div>
							<div class="right-col">
								<select name="FieldType" id="FieldType" class="select-control">
									<option value="text" <?php if($rsmembers_fieldoptions[$row][3]=='text') echo'selected';?>>Text</option>
									<option value="textarea" <?php if($rsmembers_fieldoptions[$row][3]=='textarea') echo'selected';?>>Textarea</option>
									<option value="password" <?php if($rsmembers_fieldoptions[$row][3]=='password') echo'selected';?>>Password</option>
									<option value="checkbox" <?php if($rsmembers_fieldoptions[$row][3]=='checkbox') echo'selected';?>>Checkbox</option>
									<option value="select" <?php if($rsmembers_fieldoptions[$row][3]=='select') echo'selected';?>>Drop Down</option>
								</select>
								<div class="clr"></div>
								<div class="r-c-note"></div>
							</div>
							<div class="clr"></div>
						</div>
						
						<h3 class="title">Additional information for dropdown fields</h3><br>
						<div class="form-inner15">
							<div class="left-col">Only for dropdown values:</div>
							<div class="right-col">
								<textarea name="selectval" id="selectval" class="text-control" rows="5"><?php echo $fieldvalue = ( $rsmembers_fieldoptions[$row][3]=='select' ) ?  $rsmembers_fieldoptions[$row][6] : '';?></textarea>
								<div class="clr"></div>
								<div class="r-c-note"> Options should be Option Name,option_value| <br><strong>Ex:</strong> <---- Select One ---->,|Position One,1|Position Two,2|Position Three,3|Position Four,4</div>
							</div>
							<div class="clr"></div>
						</div>
						<?php $validationrule = $rsmembers_fieldoptions[$row][7];				
						if(strpos($validationrule ,'maxlen')>0){
							$maxlen = substr( $validationrule, strpos($validationrule ,'maxlen')+7 , strlen($validationrule));
							if(strpos($maxlen ,'|')>0){
								$maxlen = substr( $maxlen, 0 , strpos($maxlen ,'|'));
							}
						}				
						if(strpos($validationrule ,'minlen')>0){
							$minlen = substr( $validationrule, strpos($validationrule ,'minlen')+7 , strlen($validationrule));
							if(strpos($minlen ,'|')>0){
								$minlen = substr( $minlen, 0 , strpos($minlen ,'|'));
							}
						}
						?>                
						<h3 class="title">Field Validation Rules</h3><br>
						<div class="form-inner15">
							<div class="left-col">Required</div>
							<div class="right-col">
								<input name="required" id="required" type="checkbox" <?php if(strpos($validationrule ,'required')==0 and !empty($validationrule)) echo'checked="checked"';?> class="cmn-toggle cmn-toggle-round" />
								<label for="required"></label>
								<div class="clr"></div>
								<div class="r-c-note"></div>
							</div>
							<div class="clr"></div>
						</div>                
						
                        <div class="form-inner15">
                            <div class="left-col">Validation Type</div>
                            <div class="right-col">
                                <select name="CustomValidation" id="CustomValidation" class="select-control">
                                    <option value="">Select Type</option>
                                    <option value="numeric" <?php if(strpos($validationrule ,'numeric')>0 and !empty($validationrule)) echo'selected';?>>Numeric Value</option>
                                    <option value="email" <?php if(strpos($validationrule ,'email')>0 and !empty($validationrule)) echo'selected';?>>Email</option>
                                    <option value="date" <?php if(strpos($validationrule ,'date')>0 and !empty($validationrule)) echo'selected';?>>Date</option>
                                    <option value="website" <?php if(strpos($validationrule ,'website')>0 and !empty($validationrule)) echo'selected';?>>Website</option>
                                </select>
                                <div class="clr"></div>
                                <div class="r-c-note"></div>
                            </div>
                            <div class="clr"></div>
                        </div>
                                               
						<div class="form-inner15">
							<div class="left-col">Maximum length</div>
							<div class="right-col">
								<input id="maxlen" name="maxlen" type="text" value="<?php echo $maxlen;?>" class="text-control">
								<div class="clr"></div>
								<div class="r-c-note"></div>
							</div>
							<div class="clr"></div>
						</div>
						
						<div class="form-inner15">
							<div class="left-col">Minimum length</div>
							<div class="right-col">
								<input id="minlen" name="minlen" type="text" value="<?php echo $minlen;?>" class="text-control">
								<div class="clr"></div>
								<div class="r-c-note"></div>
							</div>
							<div class="clr"></div>
						</div>
						
						<div class="form-inner15">
							<div class="left-col">&nbsp;</div>
							<div class="right-col" id="uffloaderdiv">
                                <input type="submit" value="<?php _e( 'Update Fields', 'rsmembers' ); ?> &raquo;" class="button button-primary" id="uffsubmitbtn" name="uffsubmitbtn">
								<div class="clr"></div>
								<div class="r-c-note"></div>
							</div>
							<div class="clr"></div>
						</div>
				</form>
		
					<div class="clr"></div>    
			   </div>          
				<?php
				
				}		
			}
			die();	
		}
		
		
		
		
		if($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['type']) && $_GET['type']=='deletefields'   ){	
			
			$rsmembers_fieldoptions = get_option( 'rsmembers_fieldoptions' );
			$fieldsid = $_GET['fieldsid'];
							
			for( $row = 0; $row < count( $rsmembers_fieldoptions); $row++ ) {				
				if($row<$fieldsid){	
					$set = array( $row,$rsmembers_fieldoptions[$row][1],$rsmembers_fieldoptions[$row][2],$rsmembers_fieldoptions[$row][3],$rsmembers_fieldoptions[$row][4],$rsmembers_fieldoptions[$row][5],$rsmembers_fieldoptions[$row][6],$rsmembers_fieldoptions[$row][7] ,$rsmembers_fieldoptions[$row][8] );
					$rsmembers_newfieldoptions[$row] = $set;
				}else if($row==$fieldsid){
				
				}else{
					$set = array( $row-1,$rsmembers_fieldoptions[$row][1],$rsmembers_fieldoptions[$row][2],$rsmembers_fieldoptions[$row][3],$rsmembers_fieldoptions[$row][4],$rsmembers_fieldoptions[$row][5],$rsmembers_fieldoptions[$row][6],$rsmembers_fieldoptions[$row][7] ,$rsmembers_fieldoptions[$row][8] );
					$rsmembers_newfieldoptions[$row-1] = $set;		
				}		
			}
			
			update_option( 'rsmembers_fieldoptions', $rsmembers_newfieldoptions );
			
			$rsmembers_fields = get_option( 'rsmembers_fieldoptions' );		
								
				$class = '';
				for( $row = 0; $row < count($rsmembers_fields); $row++ ) {
					$class = ( $class == 'alternate' ) ? '' : 'alternate'; ?>
					<tr id="list_item_<?php echo $row; ?>" class="<?php //echo $class; ?>" valign="top" style="cursor:move; border-bottom:1px solid #666 !important;" >						
                    <input type="hidden" name="caseselect" value="field_form">    
                        <input type="hidden" name="fieldposition" id="fieldposition<?php echo $row; ?>">
                        <input type="hidden" name="fieldtitle[]" id="fieldtitle<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][1]; ?>">
                        <input type="hidden" name="fieldname[]" id="fieldname<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][2]; ?>">
                        <input type="hidden" name="fieldtype[]" id="fieldtype<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][3]; ?>">
                        <input type="hidden" name="fieldselectval[]" id="fieldselectval<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][6]; ?>">
                        <input type="hidden" name="fieldvalidation[]" id="fieldvalidation<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][7]; ?>">
                        <input type="hidden" name="fieldsystemtype[]" id="fieldsystemtype<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][8]; ?>">                      
                        <td width="20%" style="border-bottom:1px solid #e1e1e1;"><?php 
							_e( $rsmembers_fields[$row][1], 'rsmembers' );
							if( $rsmembers_fields[$row][4] == 'no' ){ ?><font color="red">*</font><?php }
							?>
						</td>
                        <td width="20%" style="border-bottom:1px solid #e1e1e1;"><?php echo $rsmembers_fields[$row][2]; ?></td>
                        <td width="20%" style="border-bottom:1px solid #e1e1e1;"><?php echo $rsmembers_fields[$row][3]; ?></td>
                        <?php if( $rsmembers_fields[$row][4]!='no') { ?>
                            <td width="20%" style="border-bottom:1px solid #e1e1e1;">
                            <a onclick="editfields(<?php echo $row; ?>);" href="javascript:void(0)"><?php _e( 'Edit', 'rsmembers' ); ?></a>
                            <?php if($rsmembers_fields[$row][8] == 'u'){?>
                             / 
                            <a onclick="deletefields(<?php echo $row; ?>);" href="javascript:void(0)"><?php _e( 'Delete', 'rsmembers' ); ?></a>
                            <?php }?>
                            <input type="hidden" name="fieldaction[]" id="fieldaction<?php echo $row; ?>" value=""></td>						
						<?php } else { ?>
                            <td width="20%" style="border-bottom:1px solid #e1e1e1;">-<input type="hidden" name="fieldaction[]" id="fieldaction<?php echo $row; ?>" value="no"></td>
                        <?php } ?>
                         <?php if( $rsmembers_fields[$row][4]!='no') {?>
                            <td width="20%" style="border-bottom:1px solid #e1e1e1;"><?php
								$selected = ( $rsmembers_fields[$row][5] == 'on' ) ? 'checked="checked"' : '';	
								?>								
                                <input class="cmn-toggle cmn-toggle-round" type="checkbox" name="fieldcheckbox" id="fieldcheckbox<?php echo $row; ?>" <?php echo $selected; ?> onClick="setfieldvilue('fieldcheckbox<?php echo $row; ?>','fieldrequired<?php echo $row; ?>')">
                                <label for="fieldcheckbox<?php echo $row; ?>"></label>
                                
                                <input type="hidden" name="fieldrequired[]" id="fieldrequired<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][5]; ?>">                                								
								</td>
                        <?php } else { ?>
                            <td width="20%" style="border-bottom:1px solid #e1e1e1;">-<input type="hidden" name="fieldrequired[]" id="fieldrequired<?php echo $row; ?>" value="on"></td>                           
                        <?php } ?>
                        
					</tr><?php
				} 
		die();
		}		
		
		
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['caseselect']) && $_POST['caseselect']=='field_update_form'   ){
		
				$rsmembers_fieldoptions = get_option( 'rsmembers_fieldoptions' );
				$fieldsid = sanitize_text_field( $_POST['fieldposition'] );
								
				for( $row = 0; $row < count( $rsmembers_fieldoptions); $row++ ) {				
					if($row<$fieldsid){	
						$set = array( $row,$rsmembers_fieldoptions[$row][1],$rsmembers_fieldoptions[$row][2],$rsmembers_fieldoptions[$row][3],$rsmembers_fieldoptions[$row][4],$rsmembers_fieldoptions[$row][5],$rsmembers_fieldoptions[$row][6],$rsmembers_fieldoptions[$row][7] ,$rsmembers_fieldoptions[$row][8] );
						$rsmembers_newfieldoptions[$row] = $set;
					}else if($row==$fieldsid){
										
						$fieldvalue='';
						$FieldName = sanitize_text_field( $_POST["FieldName"] );			
						$OptionName= strtolower( preg_replace("![^a-z0-9]+!i", "_", $FieldName) );
						$FieldType = sanitize_text_field( $_POST["FieldType"] );
						$field_name_action = sanitize_text_field( $_POST["field_name_action"] );
						$field_system_type = sanitize_text_field( $_POST["field_system_type"] );
						$onregarea = sanitize_text_field( $_POST["onregarea"] );		
						$ondefault = '';
						$textval = '';
						$selectval = sanitize_text_field( $_POST["selectval"] );
						
						if($ondefault!='on') $textval='';		
						$fieldvalue =  ( $FieldType=='select' ) ? $selectval : $textval;
						
						$validation='';
						$required = sanitize_text_field( $_POST["required"] );
						if($required=='on') $validation .='required';
						
						$CustomValidation = sanitize_text_field( $_POST["CustomValidation"] );
			  			if(!empty($CustomValidation)) $validation .='|'.$CustomValidation;
						
						
						$maxlen = sanitize_text_field( $_POST["maxlen"] );
						if(!empty($maxlen)) $validation .='|maxlen:'.$maxlen;
						$minlen = sanitize_text_field( $_POST["minlen"] );
						if(!empty($minlen)) $validation .='|minlen:'.$minlen;
						
						
						$validationrule='';
						if(strpos($validation ,'equired')==0 and !empty($validation))
							$validationrule = 'required|' . $validation;	
						else
							$validationrule = $validation;
						
						if($field_system_type=='u')				
							$set = array( $row,$FieldName,$OptionName,$FieldType,$field_name_action,$onregarea,$fieldvalue,$validationrule,$field_system_type );
						else						
							$set = array( $row,$FieldName,$rsmembers_fieldoptions[$row][2],$FieldType,$field_name_action,$onregarea,$fieldvalue,$validationrule,$field_system_type );
						$rsmembers_newfieldoptions[$row] = $set;
						
						
					}else{
						$set = array( $row,$rsmembers_fieldoptions[$row][1],$rsmembers_fieldoptions[$row][2],$rsmembers_fieldoptions[$row][3],$rsmembers_fieldoptions[$row][4],$rsmembers_fieldoptions[$row][5],$rsmembers_fieldoptions[$row][6],$rsmembers_fieldoptions[$row][7] ,$rsmembers_fieldoptions[$row][8] );
						$rsmembers_newfieldoptions[$row] = $set;		
					}		
				}		
				update_option( 'rsmembers_fieldoptions', $rsmembers_newfieldoptions );
				
		}
	
	
		/*====================================================================
			New field information added
		====================================================================*/
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['caseselect']) && $_POST['caseselect']=='field_new_form'   ){
				
				$error='';
				$fieldvalue='';
				$FieldName = sanitize_text_field( $_POST["FieldName"] );			
				$OptionName= strtolower( preg_replace("![^a-z0-9]+!i", "_", $FieldName) );
				$FieldType = sanitize_text_field( $_POST["FieldType"] );
				
				if(empty($FieldName)) $error='Field Name Required !';
				else if(empty($FieldType)) $error='Field Type Required !';
				
				if($error!=''){
					echo $error; 
					exit;	
				}else{
				
					  $rsmembers_fieldoptions = get_option( 'rsmembers_fieldoptions' );
					  
					  for( $row = 0; $row < count( $rsmembers_fieldoptions); $row++ ) {				
						  $set = array( $rsmembers_fieldoptions[$row][0],$rsmembers_fieldoptions[$row][1],$rsmembers_fieldoptions[$row][2],$rsmembers_fieldoptions[$row][3],$rsmembers_fieldoptions[$row][4],$rsmembers_fieldoptions[$row][5],$rsmembers_fieldoptions[$row][6],$rsmembers_fieldoptions[$row][7] ,$rsmembers_fieldoptions[$row][8] );
						  $rsmembers_newfieldoptions[$row] = $set;
					  }
								  
					  
					  $field_name_action = sanitize_text_field( $_POST["field_name_action"] );
					  $onregarea = ''; 	
					  $ondefault = '';
					  $textval = '';
					  $selectval = sanitize_text_field( $_POST["selectval"] );
					  if($selectval == '<---- Select One ---->,|Position One,1|Position Two,2|Position Three,3|Position Four,4')  $selectval=''; 
					  
					  
					  
					  if($ondefault!='on') $textval='';		
					  $fieldvalue =  ( $FieldType=='select' ) ? $selectval : $textval;
					  
					  $validation='';
					  $required = sanitize_text_field( $_POST["required"] );
					  if($required=='on') $validation .='required';		
					  
					  $CustomValidation = sanitize_text_field( $_POST["CustomValidation"] );
					  if(!empty($CustomValidation)) $validation .='|'.$CustomValidation;
					  
					  
					  $maxlen = sanitize_text_field( $_POST["maxlen"] );
					  if(!empty($maxlen)) $validation .='|maxlen:'.$maxlen;
					  $minlen = sanitize_text_field( $_POST["minlen"] );
					  if(!empty($minlen)) $validation .='|minlen:'.$minlen;
					  
					  
					  $validationrule='';
					  if(strpos($validation ,'equired')==0 and !empty($validation))
						  $validationrule = 'required|' . $validation;	
					  else
						  $validationrule = $validation;
									  
					  $setopt = array( $row,$FieldName,$OptionName,$FieldType,$field_name_action,$onregarea,$fieldvalue,$validationrule,'u' );
					  $rsmembers_newfieldoptions[$row] = $setopt;
						  
					  update_option( 'rsmembers_fieldoptions', $rsmembers_newfieldoptions );
					  
					  echo'Added successfully';
							
				}
				// End code	
				die();
		}
		
		
		/*====================================================================
			Field List
		====================================================================*/
		
		
		if($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['caseselect']) && $_GET['caseselect']=='field_list_form'   ){
							
				$rsmembers_fields = get_option( 'rsmembers_fieldoptions' );		
						
				$class = '';
				for( $row = 0; $row < count($rsmembers_fields); $row++ ) {
					$class = ( $class == 'alternate' ) ? '' : 'alternate'; ?>
					<tr id="list_item_<?php echo $row; ?>" class="<?php //echo $class; ?>" valign="top" style="cursor:move; border-bottom:1px solid #666 !important;" >						
					<input type="hidden" name="caseselect" value="field_form">    
						<input type="hidden" name="fieldposition" id="fieldposition<?php echo $row; ?>">
						<input type="hidden" name="fieldtitle[]" id="fieldtitle<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][1]; ?>">
						<input type="hidden" name="fieldname[]" id="fieldname<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][2]; ?>">
						<input type="hidden" name="fieldtype[]" id="fieldtype<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][3]; ?>">
						<input type="hidden" name="fieldselectval[]" id="fieldselectval<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][6]; ?>">
						<input type="hidden" name="fieldvalidation[]" id="fieldvalidation<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][7]; ?>">
						<input type="hidden" name="fieldsystemtype[]" id="fieldsystemtype<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][8]; ?>">                      
						<td width="20%" style="border-bottom:1px solid #e1e1e1;"><?php 
							_e( $rsmembers_fields[$row][1], 'rsmembers' );
							if( $rsmembers_fields[$row][4] == 'no' ){ ?><font color="red">*</font><?php }
							?>
						</td>
						<td width="20%" style="border-bottom:1px solid #e1e1e1;"><?php echo $rsmembers_fields[$row][2]; ?></td>
						<td width="20%" style="border-bottom:1px solid #e1e1e1;"><?php echo $rsmembers_fields[$row][3]; ?></td>
						<?php if( $rsmembers_fields[$row][4]!='no') { ?>
							<td width="20%" style="border-bottom:1px solid #e1e1e1;">
							<a onclick="editfields(<?php echo $row; ?>);" href="javascript:void(0)"><?php _e( 'Edit', 'rsmembers' ); ?></a>
							<?php if($rsmembers_fields[$row][8] == 'u'){?>
							 / 
							<a onclick="deletefields(<?php echo $row; ?>);" href="javascript:void(0)"><?php _e( 'Delete', 'rsmembers' ); ?></a>
							<?php }?>
							<input type="hidden" name="fieldaction[]" id="fieldaction<?php echo $row; ?>" value=""></td>						
						<?php } else { ?>
							<td width="20%" style="border-bottom:1px solid #e1e1e1;">-<input type="hidden" name="fieldaction[]" id="fieldaction<?php echo $row; ?>" value="no"></td>
						<?php } ?>
						 <?php if( $rsmembers_fields[$row][4]!='no') {?>
							<td width="20%" style="border-bottom:1px solid #e1e1e1;"><?php
								$selected = ( $rsmembers_fields[$row][5] == 'on' ) ? 'checked="checked"' : '';	
								?>								
								<input class="cmn-toggle cmn-toggle-round" type="checkbox" name="fieldcheckbox" id="fieldcheckbox<?php echo $row; ?>" <?php echo $selected; ?> onClick="setfieldvilue('fieldcheckbox<?php echo $row; ?>','fieldrequired<?php echo $row; ?>')">
								<label for="fieldcheckbox<?php echo $row; ?>"></label>
								
								<input type="hidden" name="fieldrequired[]" id="fieldrequired<?php echo $row; ?>" value="<?php echo $rsmembers_fields[$row][5]; ?>">                                								
								</td>
						<?php } else { ?>
							<td width="20%" style="border-bottom:1px solid #e1e1e1;">-<input type="hidden" name="fieldrequired[]" id="fieldrequired<?php echo $row; ?>" value="on"></td>                           
						<?php } ?>
						
					</tr><?php
				} 	
				// End code	
				die();
		}
		
		
		
		/*====================================================================
			Csv download
		====================================================================*/
		
		if($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['caseselect']) && $_GET['caseselect']=='csvdownload'   ){
			
				$args = array(
					'fields' => 'all_with_meta'
				);
				$users = get_users(  );
					
				if ( ! $users ) {
					$referer = add_query_arg( 'error', 'empty', wp_get_referer() );
					wp_redirect( $referer );
					exit;
				}
			
				$filename = 'users.' . date( 'Y-m-d-H-i-s' ) . '.csv';
				
				header( 'Content-Description: File Transfer' );
				header( 'Content-Disposition: attachment; filename=' . $filename );
				header( 'Content-Type: text/csv; charset=' . get_option( 'blog_charset' ), true );
			
				$exclude_data = apply_filters( 'exclude_data', array() );
				
				global $wpdb;
			
				$user_data = array(
					'ID', 'user_login', 'user_pass',
					'user_nicename', 'user_email', 'user_url',
					'user_registered', 'user_activation_key', 'user_status',
					'display_name'
				);
				$user_meta_datas = $wpdb->get_results( "SELECT distinct(meta_key) FROM $wpdb->usermeta" );
				$user_meta_datas = wp_list_pluck( $user_meta_datas, 'meta_key' );
				$fields = array_merge( $user_data, $user_meta_datas );
			
				$headers = array();
				foreach ( $fields as $key => $field ) {
					if ( in_array( $field, $exclude_data ) )
						unset( $fields[$key] );
					else
						$headers[] = '"' . strtolower( $field ) . '"';
				}
				echo implode( ',', $headers ) . "\n";
			
				foreach ( $users as $user ) {
					$data = array();
					foreach ( $fields as $field ) {
						$value = isset( $user->{$field} ) ? $user->{$field} : '';
						$value = is_array( $value ) ? serialize( $value ) : $value;
						$data[] = '"' . str_replace( '"', '""', $value ) . '"';
					}
					echo implode( ',', $data ) . "\n";
				}
			
				// End code	
				die();
		}
	
	
	
	
	
	} // End function
	
		
	function rsmembers_ajaxpost($form_name,$loader_div,$loading_div,$loading_image,$submit_button,$form_redirect_js_function){		
		?>
			<style type="text/css">.loading{position:absolute; left:-32px; top:2px; visibility:hidden;}</style>
			<script type="text/javascript">	
            jQuery(function(){
                jQuery("#<?php echo $form_name;?> #<?php echo $loader_div;?>").append('<div id="<?php echo $loading_div;?>" class="loading"><img src="<?php echo $loading_image;?>" alt="loader" align="left" /></div>');
                jQuery("#<?php echo $form_name;?> #<?php echo $loader_div;?>").css({position:'relative'});                        
                jQuery('#<?php echo $form_name;?>').submit(function(e){
                    e.preventDefault();						
                    var form = jQuery(this);
                    var post_url = form.attr('action');
                    var formData = new FormData(jQuery(this)[0]);
					      
                    jQuery.ajax({
                        type: 'POST',
                        url: post_url, 
                        data: formData,
                        async: false,
                        cache: false,
                        contentType: false,
                        processData: false,
                        beforeSend:function(){             
                            jQuery("#<?php echo $form_name;?> #<?php echo $submit_button;?>").attr("disabled", 'false');
                            jQuery("#<?php echo $form_name;?> #<?php echo $loading_div;?>").css({visibility:'visible'});
                        },
                        success: function(msg){
                            <?php echo $form_redirect_js_function.'(msg);';?>
							jQuery.notify({
								inline: true,
								html: '<p>'+msg+'<p>'
							}, 2500);
							setTimeout(function(){
								jQuery("#<?php echo $form_name;?> #<?php echo $submit_button;?>").attr("disabled", false);
								jQuery("#<?php echo $form_name;?> #<?php echo $loading_div;?>").css({visibility:'hidden'});					
							},2500);
                                               
                        },
                        error: function(){
                            
                        },
                        complete: function(){
                            
                        }
                    });
                });
            });
            </script>
		<?php	
	} // End function
	
	function ajaxpost_showvalue($form_name,$loader_div,$loading_div,$loading_image,$submit_button,$container){
		
		?>
			<style type="text/css">.loading{position:absolute; left:-32px; top:2px; visibility:hidden;}</style>
			<script type="text/javascript">	
            jQuery(function(){
                jQuery("#<?php echo $form_name;?> #<?php echo $loader_div;?>").append('<div id="<?php echo $loading_div;?>" class="loading"><img src="<?php echo $loading_image;?>" alt="loader" align="left" /></div>');
                jQuery("#<?php echo $form_name;?> #<?php echo $loader_div;?>").css({position:'relative'});                        
                jQuery('#<?php echo $form_name;?>').submit(function(e){
                    e.preventDefault();						
                    var form = jQuery(this);
                    var post_url = form.attr('action');
                    var formData = new FormData(jQuery(this)[0]);
                            
                    jQuery.ajax({
                        type: 'POST',
                        url: post_url, 
                        data: formData,
                        async: false,
                        cache: false,
                        contentType: false,
                        processData: false,
                        beforeSend:function(){             
                            jQuery("#<?php echo $form_name;?> #<?php echo $submit_button;?>").attr("disabled", 'false');
                            jQuery("#<?php echo $form_name;?> #<?php echo $loading_div;?>").css({visibility:'visible'});
                        },
                        success: function(msg){                           
							jQuery("#<?php echo $container;?>").html(msg);							
							setTimeout(function(){
								jQuery("#<?php echo $form_name;?> #<?php echo $submit_button;?>").attr("disabled", false);
								jQuery("#<?php echo $form_name;?> #<?php echo $loading_div;?>").css({visibility:'hidden'});					
							},2500);
                                               
                        },
                        error: function(){
                            
                        },
                        complete: function(){
                            
                        }
                    });
                });
            });
            </script>
		<?php	
	}


} // End Class


if (!defined('ABSPATH')) { header('Status: 403 Forbidden');  header('HTTP/1.1 403 Forbidden'); die('Forbidden'); }
RsMembers::get_instance();
// EOF


/*====================================================================
	Extended Login
====================================================================*/
	add_filter( 'wp_authenticate_user', 'rsmembers_extended_login', 10, 2 );
	
	function rsmembers_extended_login( $user, $password ){ // $user, 
				
		$rsmembers_status = get_user_status( $user->ID );
		$user_expiredate = get_user_expire_date( $user->ID );
		
		if ( empty( $rsmembers_status ) ) {
			// the user does not have a status so let's assume the user is good to go
			return $user;
		}
		
		if($user->ID!='1'){		
			
			if(!$user  || $rsmembers_status != 'Active'){
				//User note found, or no value entered or doesn't match stored value - don't proceed.
				remove_action('authenticate', 'wp_authenticate_username_password', 20); 
		
				//Create an error to return to user
				return $user = new WP_Error( 'denied', __("<strong>ERROR</strong>: User [<strong>$user->display_name</strong>] is deactivated. Please contact to administrator for activation.") );
				
			}else if(!$user  || $user_expiredate < date('Y-m-d') ){
				//User note found, or no value entered or doesn't match stored value - don't proceed.
				remove_action('authenticate', 'wp_authenticate_username_password', 20); 
		
				//Create an error to return to user
				return $user = new WP_Error( 'denied', __("<strong>ERROR</strong>:  Free account of <strong>$user->display_name</strong> already expired in [$user_expiredate]. Please contact to administrator for activation.") );
				
			}else 
				return $user;
										
		}else 
			return $user;
		
	}
	
	/**
	 * Get the status of a user.
	 *
	 * @param int $user_id
	 * @return string the status of the user
	 */
	function get_user_status( $user_id ) {
		$user_status = get_user_meta($user_id, 'rsmembers_status', true);

		if ( empty( $user_status ) ) {
			$user_status = 'Active';
		}

		return $user_status;
	}

	/**
	 * Get the expire date of a user.
	 *
	 * @param int $user_id
	 * @return string the status of the user
	 */
	function get_user_expire_date( $user_id ) {
		$user_expiredate = get_user_meta($user_id, 'rsmembers_expiredate', true);

		if ( empty( $user_expiredate ) ) {
			$user_expiredate = date('Y-m-d', strtotime("+15 days"));
		}

		return $user_expiredate;
	}

	
/*====================================================================
	Add Column to user list
====================================================================*/

	function rsmembers_add_user_columns( $column ) {
		$column['rsmembers_status'] = 'User Status';
		$column['rsmembers_actype'] = 'Account Type';
		return $column;
	}
	add_filter( 'manage_users_columns', 'rsmembers_add_user_columns' );
	
	function new_modify_user_table_row( $val, $column_name, $user_id ) {
		$user = get_userdata( $user_id );
		switch ($column_name) {
			case 'rsmembers_status' :
				return get_the_author_meta( 'rsmembers_status', $user_id );
				break;
			case 'rsmembers_actype' :
				return get_the_author_meta( 'rsmembers_actype', $user_id );
				break;
			default:
		}
		return $return;
	}
	add_filter( 'manage_users_custom_column', 'new_modify_user_table_row', 10, 3 );


/*====================================================================
	Add field to user edit panel
====================================================================*/

add_action( 'personal_options_update', 'rsmembers_status_fields');
add_action( 'edit_user_profile_update', 'rsmembers_status_fields');

function rsmembers_status_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
    return false;

	update_usermeta( $user_id, 'rsmembers_status', $_POST['rsmembers_status'] );
	update_usermeta( $user_id, 'rsmembers_actype', $_POST['rsmembers_actype'] );
}


add_action( 'show_user_profile', 'rsmembers_edit_status_fields');
add_action( 'edit_user_profile', 'rsmembers_edit_status_fields');

function rsmembers_edit_status_fields ($user) {
?>
<style>
#rsmembers_status { width: 15em;}
#rsmembers_actype { width: 15em;}
</style>
<h3>RS Members Additional Field</h3>
<table class="form-table">
    <tr>
        <th><label for="dropdown">User Status</label></th>
        <td>
            <?php
            //get dropdown saved value
            $selected = get_the_author_meta('rsmembers_status', $user->ID);
            ?>
            <select name="rsmembers_status" id="rsmembers_status">
                <option value="Active" <?php echo ($selected == "Active")?  'selected="selected"' : ''; ?>>Active</option>
                <option value="Deactivate" <?php echo ($selected == "Deactivate")?  'selected="selected"' : ''; ?>>Deactivate</option>              
            </select>
            <span class="description">Select the above</span>
        </td>
    </tr>
    <tr>
        <th><label for="dropdown">User Account Type</label></th>
        <td>
            <?php
            //get dropdown saved value
            $selected = get_the_author_meta('rsmembers_actype', $user->ID);
            ?>
            <select name="rsmembers_actype" id="rsmembers_actype">
                <option value="Free" <?php echo ($selected == "Free")?  'selected="selected"' : ''; ?>>Free</option>
                <option value="Paid" <?php echo ($selected == "Paid")?  'selected="selected"' : ''; ?>>Paid</option>              
            </select>
            <span class="description">Select the above</span>
        </td>
    </tr>
   
    
        
</table>
<?php
}



/*====================================================================
	User Active / Inactive section
====================================================================*/


	add_action( 'admin_footer-users.php', 'rsmembers_bulk_user_action' );
	add_action( 'load-users.php', 'rsmembers_load_user_status' );
	add_action( 'rsmembers_user_action_active', 'rsmembers_set_user_action_active' );
	if( modreg == 'on' ) {
		add_filter( 'user_row_actions', 'wpmem_insert_activate_link1', 10, 2 );
	}


	/**
	 * Function to add activate to the bulk dropdown list
	 */
	function rsmembers_bulk_user_action(){
	 ?>
		<script type="text/javascript">
		  jQuery(document).ready(function() {
		<?php if( modreg == 'on' ) { ?>
			jQuery('<option>').val('activebulk').text('<?php _e( 'Active' )?>').appendTo("select[name='action']");
		<?php } ?>			
		<?php if( modreg == 'on' ) { ?>
			jQuery('<option>').val('activebulk').text('<?php _e( 'Active' )?>').appendTo("select[name='action2']");
		<?php } ?>			
		  });
		</script>
		<?php
	}


	/**
	 * Function to add activate link to the user row action
	 *
	 * @param  array $actions
	 * @param  $user_object
	 * @return array $actions
	 */
	function wpmem_insert_activate_link1( $actions, $user_object ) {
		if( current_user_can( 'edit_users', $user_object->ID ) ) {
		
			if($user_object->ID!='1'){		
				$var = get_user_meta( $user_object->ID, 'rsmembers_status', true );			
				if( $var != 'Active' ) {
					$url = "users.php?action=active-single&amp;user=$user_object->ID";
					$url = wp_nonce_url( $url, 'activate-user' );
					$actions['activate'] = '<a href="' . $url . '">Active</a>';
				}else{
					$url = "users.php?action=inactive-single&amp;user=$user_object->ID";
					$url = wp_nonce_url( $url, 'activate-user' );
					$actions['activate'] = '<a href="' . $url . '">Deactivate</a>';		
				}
			}
			
		}
		return $actions;
	}


/**
 * Function to handle bulk actions at page load
 *
 * @uses WP_Users_List_Table
 */
function rsmembers_load_user_status()
{
	$wp_list_table = _get_list_table( 'WP_Users_List_Table' );
	$action = $wp_list_table->current_action();
	$sendback = '';
	
	switch( $action ) {
		
	case 'activebulk':
		
		/** validate nonce */
		check_admin_referer( 'bulk-users' );
		
		/** get the users */
		$users = $_REQUEST['users'];
		
		/** update the users */
		$x = 0;
		foreach( $users as $user ) {
			
			// check to see if the user is already activated, if not, activate
			if(  get_user_meta( $user, 'rsmembers_status', 'Deactivate' ) =='Deactivate' and $user!='1' ) {
				rsmembers_user_status_active( $user );
				$x++;
			}
		}
		
		/** set the return message */
		$sendback = add_query_arg( array('userstatus' => $x . ' users activated' ), $sendback );
		
		break;
		
	case 'active-single':
		
		/** validate nonce */
		check_admin_referer( 'activate-user' );
		
		/** get the users */
		$users = $_REQUEST['user'];
		
		/** set the user activated, if not, activate */
		rsmembers_user_status_active( $users );
			
		/** get the user data */
		$user_info = get_userdata( $users );

		/** set the return message */
		$sendback = add_query_arg( array('userstatus' => "$user_info->user_login activated" ), $sendback );
				
		break;
	
	case 'inactive-single':
		
		/** validate nonce */
		check_admin_referer( 'activate-user' );
		
		/** get the users */
		$users = $_REQUEST['user'];
		
		/** set the user inactivated, if not, inactive */			
		rsmembers_user_status_inactive( $users );
		
		/** get the user data */
		$user_info = get_userdata( $users );
  
		/** set the return message */
		$sendback = add_query_arg( array('userstatus' => "$user_info->user_login deactivate" ), $sendback );
		
		break;		
	
	case 'show':
		
		add_action( 'pre_user_query', 'wpmem_a_pre_user_query' );
		return;
		break;
		
	case 'export':

		/*$users  = ( isset( $_REQUEST['users'] ) ) ? $_REQUEST['users'] : false;
		include_once( WPMEM_PATH . 'admin/user-export.php' );
		wpmem_export_users( array( 'export'=>'selected' ), $users );
		return;*/
		break;
		
	default:
		return;
		break;

	}

	/** if we did not return already, we need to wp_redirect */
	wp_redirect( $sendback );
	exit();

}


/**
 * Activates a user
 *
 * If registration is moderated, sets the activated flag 
 * in the usermeta. Flag prevents login when modreg
 * is true (active). Function is fired from bulk user edit or
 * user profile update.
 *
 * @param int  $user_id
 * @param bool $chk_pass
 * @uses $wpdb WordPress Database object
 */
function rsmembers_user_status_active( $user_id )
{
		
	// set the active flag in usermeta
	update_user_meta( $user_id, 'rsmembers_status', 'Active' );
	
	/**
	 * Fires after the user activation process is complete.
	 *
	 * @param int $user_id The user's ID.
	 */
	do_action( 'rsmembers_user_action_active', $user_id );
	
	return;
}


/**
 * Deactivates a user
 *
 * Reverses the active flag from the activation process
 * preventing login when registration is moderated.
 *
 * @param int $user_id
 */
function rsmembers_user_status_inactive( $user_id ) {
	update_user_meta( $user_id, 'rsmembers_status', 'Deactivate' );
}

/**
 * Use rsmembers_set_user_action_active to set the user_status field to Active using rsmembers_set_action_active.
 *
 * @uses  set_user_status
 * @param $user_id
 */
function rsmembers_set_user_action_active( $user_id ) {
	rsmembers_set_action_active( $user_id, 'Active' );
	return;
}


/**
 * Updates the user_status value in the wp_users table
 *
 * @param $user_id
 * @param $status
 */
function rsmembers_set_action_active( $user_id, $status ) {
	update_user_meta( $user_id, 'rsmembers_status', $status );	
	return;
}



/*====================================================================
	Post Restriction
====================================================================*/
if( postrestrice == 'on' ) {

	function rsmembers_post_restriction_markup($object)
	{
		wp_nonce_field(basename(__FILE__), "rsmembers-post-restriction-nonce"); 
		?>
			<div>
				<span style="width:100%; padding:10px 0px; display:block;">Post is not blocked by default.</span>            
				<?php
					$checkbox_value = get_post_meta($object->ID, "rsmembers-post-restriction", true); 
					if($checkbox_value == ""){
						?>
							<input name="rsmembers-post-restriction" type="checkbox" value="true"> <label for="rsmembers-post-restriction">Block the post</label>
						<?php
					}else if($checkbox_value == "true"){
						?>  
							<input name="rsmembers-post-restriction" type="checkbox" value="true" checked> <label for="rsmembers-post-restriction">Post is blocked</label>
						<?php
					}
				?>            
			</div>
		<?php  
	}
	 
	function rsmembers_post_restriction(){
		add_meta_box("rsmembers-post-restriction", "Post Restriction", "rsmembers_post_restriction_markup", "post", "side", "high", null);
	}
	 
	add_action("add_meta_boxes", "rsmembers_post_restriction");
	
	
	function save_rsmembers_post_restriction($post_id, $post, $update){
		if (!isset($_POST["rsmembers-post-restriction-nonce"]) || !wp_verify_nonce($_POST["rsmembers-post-restriction-nonce"], basename(__FILE__)))
			return $post_id;
	 
		if(!current_user_can("edit_post", $post_id))
			return $post_id;
	 
		if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
			return $post_id;
	 
		$slug = "post";
		if($slug != $post->post_type)
			return $post_id;
		 
		$meta_box_checkbox_value = "";
	 
		if(isset($_POST["rsmembers-post-restriction"])){
			$meta_box_checkbox_value = $_POST["rsmembers-post-restriction"];
		}   
		update_post_meta($post_id, "rsmembers-post-restriction", $meta_box_checkbox_value);
	}
	 
	add_action("save_post", "save_rsmembers_post_restriction", 10, 3);

}
/*====================================================================
	Page Restriction
====================================================================*/
if( pagerestrice == 'on' ) {
	
	function rsmembers_page_restriction_markup($object)
	{
		wp_nonce_field(basename(__FILE__), "rsmembers-page-restriction-nonce"); 
		?>
			<div>
				<span style="width:100%; padding:10px 0px; display:block;">Page is not blocked by default.</span>            
				<?php
					$checkbox_value = get_post_meta($object->ID, "rsmembers-page-restriction", true); 
					if($checkbox_value == ""){
						?>
							<input name="rsmembers-page-restriction" type="checkbox" value="true"> <label for="rsmembers-page-restriction">Block the page</label>
						<?php
					}else if($checkbox_value == "true"){
						?>  
							<input name="rsmembers-page-restriction" type="checkbox" value="true" checked> <label for="rsmembers-page-restriction">Page is blocked</label>
						<?php
					}
				?>            
			</div>
		<?php  
	}
	 
	function rsmembers_page_restriction(){
		add_meta_box("rsmembers-page-restriction", "Page Restriction", "rsmembers_page_restriction_markup", "page", "side", "high", null);
	}
	 
	add_action("add_meta_boxes", "rsmembers_page_restriction");
	
	
	function save_rsmembers_page_restriction($page_id, $page, $update){
		if (!isset($_POST["rsmembers-page-restriction-nonce"]) || !wp_verify_nonce($_POST["rsmembers-page-restriction-nonce"], basename(__FILE__)))
			return $page_id;
	 
		if(!current_user_can("edit_post", $page_id))
			return $page_id;
	 
		if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
			return $page_id;
	 
		$slug = "page";    
		 
		$meta_box_checkbox_value = "";
	 
		if(isset($_POST["rsmembers-page-restriction"])){
			$meta_box_checkbox_value = $_POST["rsmembers-page-restriction"];
		}   
		update_post_meta($page_id, "rsmembers-page-restriction", $meta_box_checkbox_value);
	}
	 
	add_action("save_post", "save_rsmembers_page_restriction", 10, 3);
}

/*====================================================================
	Restricted page and post show section
====================================================================*/
function rsmembers_filter_the_content( $content ) {
    
	$rsmembers_messageoptions  = get_option( 'rsmembers_messageoptions' );
	$rsmembers_settings  = get_option( 'rsmembers_settings' );
	$custom_content='';
	
	global $user_ID, $user_identity; get_currentuserinfo();		
		
	switch(get_post_type()){
	
		case 'post':
			if($rsmembers_settings[5][4]=='on'){
				$checkbox_value = get_post_meta(get_the_ID(), "rsmembers-post-restriction", true); 
				if(!$user_ID and $checkbox_value == "true" )
					$custom_content .= '<div style="color:#F00; padding-bottom:50px;">'.$rsmembers_messageoptions[3][1].'</div><br><br><br><br>[rsmembers-login]';
				else	
					$custom_content .= $content;			
			}else{
				$custom_content .= $content;
			}		
			break;		
		case 'page':
			if($rsmembers_settings[6][4]=='on'){
				$checkbox_value = get_post_meta(get_the_ID(), "rsmembers-page-restriction", true); 
				if(!$user_ID and $checkbox_value == "true" )
					$custom_content .= '<div style="color:#F00; padding-bottom:50px;">'.$rsmembers_messageoptions[4][1].'</div>[rsmembers-login]';
				else	
					$custom_content .= $content;
			}else{
				$custom_content .= $content;
			}		
			break;
	}	
    return $custom_content;
}
add_filter( 'the_content', 'rsmembers_filter_the_content' );
















