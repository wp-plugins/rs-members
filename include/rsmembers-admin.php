<?php
/**
 * RS-members is wordpress most powerful membership plugin many many features are include there.
 *
 * @link       http://www.themexpo.net
 *
 * @package    rs-members
 */
class RsMembersAdmin
{
	private static $_instance = NULL;

	private $_plugin = NULL;					// reference to main plugin instance
	private $_options = NULL;					// reference to options array
	private $_settings = NULL;					// the SpectrOMSettings instance
	
	
	private function __construct($plugin){
		
		$this->_plugin = $plugin;
		add_action('admin_enqueue_scripts', array(&$this, 'register_scripts'));

		add_action('admin_init', array(&$this, 'admin_init'));		
		add_action('admin_menu', array(&$this, 'admin_menu'));
		
				
	}
	
	/**
	 * Return a Singleton instance of the class
	 * @return object Returns the instance of the class
	 */
	public static function get_instance($plugin){
		if (NULL === self::$_instance)
			self::$_instance = new self($plugin);
		return (self::$_instance);
	}

	
	/**
	 * Callback for 'admin_init' action
	 */
	public function admin_init(){
		// init process for button control
		if ( get_user_option('rich_editing') == 'true') {
			$rsmembers_settings = get_option( 'rsmembers_settings' ); 
			if ($rsmembers_settings[7][4] == 'on' ) {					
				add_filter('mce_external_plugins',array(&$this, 'rsmembers_call_editor') );
				add_filter('mce_buttons', array(&$this, 'resticontent_add_button'), 0);
			}
		}
	}
	
	/**
	 * @return editor button
	 */
	function resticontent_add_button($buttons){
		array_push($buttons,"rsmember_editor_button");
		return $buttons;
	}
	function rsmembers_call_editor($plugin_array){
		$url = $this->_plugin->get_assets_url('js/editor_plugin.js') ;
		$plugin_array['rsmember_editor_button'] = $url;
		return $plugin_array;
	}
	
	
	/**
	 * Sets up the admin menu
	 */
	public function admin_menu(){
		
		add_menu_page( rsmembers::PLUGIN_NAME , rsmembers::PLUGIN_NAME , 'manage_options',  'rsmembers_settings', array(&$this, 'settings_page'), 'dashicons-universal-access' , 73 );
				

	}


	/**
	 * Output the settings page
	 */
	public function settings_page(){
		
		echo '<div class="wrap">', PHP_EOL;
		echo '<h2>'. rsmembers::PLUGIN_NAME . '</h2>', PHP_EOL;
		
		$this->public_html();
		
		echo '</div>', PHP_EOL;
	}
	
	/**
	 * Registers the scripts and styles used by the admin code
	 */
	public function register_scripts(){
				
		
		wp_enqueue_style( 'tab-component', $this->_plugin->get_assets_url('css/component.css'), array(), rsmembers::PLUGIN_VERSION );
		wp_enqueue_style( 'notify', $this->_plugin->get_assets_url('css/notify.css'), array(), rsmembers::PLUGIN_VERSION );
		wp_enqueue_style( 'tokenize', $this->_plugin->get_assets_url('css/selectivity-full.min.css'), array(), rsmembers::PLUGIN_VERSION );
		wp_enqueue_style( 'invmodal', $this->_plugin->get_assets_url('css/invmodal.css'), array(), rsmembers::PLUGIN_VERSION );
				
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-sortable' );				
		wp_enqueue_script( 'tab_script', $this->_plugin->get_assets_url('js/cbpFWTabs.js') );
		wp_enqueue_script( 'notify', $this->_plugin->get_assets_url('js/jquery-notify.js') );
		wp_enqueue_script( 'tokenize', $this->_plugin->get_assets_url('js/selectivity-full.min.js') );
		wp_enqueue_script( 'jquery.invmodal', $this->_plugin->get_assets_url('js/jquery.invmodal.js') );
			
	}
	

	public function public_html(){
		?>
		<div id="tabs" class="inv-tabs">
				<nav>
					<ul>
						<li><a href="#section-1" class="icon-settings"><span>Settings</span></a></li>
						<li><a href="#section-2" class="icon-field"><span>Required Field</span></a></li>
						<li><a href="#section-3" class="icon-message"><span>Required Message</span></a></li>
						<li><a href="#section-4" class="icon-letter"><span>News Letter</span></a></li>
						<li><a href="#section-5" class="icon-paypal"><span>Payment Getway</span></a></li>
					</ul>
				</nav>
				<div class="inv-tabs-content">
					<section id="section-1">
						<div class="innersection">						
							<div class="inner_col1">	
                            	<h3 class="title">Settings</h3>
								<?php $this->plugin_settings();?>
                            </div>
                            <div class="inner_col2">	
                            	
                                <div class="inner_col_2">
                                    <h3 class="title">RS-Members News :</h3>
                                    <ul>
                                    <li>Version : <?php echo rsmembers::PLUGIN_VERSION;?></li>
                                    <li><a href="http://www.themexpo.net/wpplugins/rs-members/users-guide" target="_blank">Complete User Guide</a></li>
                                    <li><a href="http://www.themexpo.net/forum/discussion/wpplugins/rs-members" target="_blank">Our Support Forum</a></li>
                                    </ul>
                            	</div>
                                <div class="inner_col_2">
                                    <h3 class="title">Upcoming free features :</h3>
                                    <ul>
                                    <li>* Woocommerce, bbpress, buddypress integrate.</li>
                                    <li>* Social Media Connect (facebook/twitter/Linkdin).</li>
                                    <li>* Custom CSS feature.</li>
                                    <li>* Manage Member Role.</li>
                                    </ul>
                            	</div>                                                            
                            	<div class="inner_col_2">
                                    <h3 class="title">Upcoming paid features :</h3>
                                    <ul>
                                    <li>* Paypal payment getway.</li>
                                    <li>* 2 checkouts.</li>
                                    <li>* Authorize.net </li>
                                    <li>* Mailchamp, Aweber, Campian Monitor.</li>
                                    <li>* Coupon Code Configuration.</li>
                                    </ul>
                            	</div> 
                            
                            </div>
                            <div class="clr"></div>
                        </div>
					</section>
					<section id="section-2">
						<div class="innersection">						
                             <div class="inner_section">   
                                <h3 class="title">Required Field</h3>
                                <?php $this->required_field();?>
                            </div>    					
                        </div>
						<div class="innersection">						
							<div class="inner_section">
								<?php $this->required_field_new();?>
                            </div>
                        </div>						
					</section>
					<section id="section-3">
                        <div class="innersection">						
							<div class="inner_section">
                            	<h3 class="title">Required Message</h3>
								<?php $this->required_message();?>
                            </div>
                        </div>
					</section>
					<section id="section-4">
						<div class="innersection">		
                            <div class="inner_section">
                            	<h3 class="title">News Letter</h3>
								<?php $this->news_letter();?>   
                            </div>                        
                    	</div>    
					</section>
					<section id="section-5">
						<div class="innersection">		
                        	<div class="inner_section">
                                <div style="font-size:28px !important; line-height:40px; display:block; margin-bottom:20px;">1. Paypal payment getway.</div>
                                <div style="font-size:28px !important; line-height:40px; display:block; margin-bottom:20px;">2. 2 checkouts.</div>
                                <div style="font-size:28px !important; line-height:40px; display:block; margin-bottom:20px;">3. Authorize.net.</div>
                            </div>    
                        </div>
					</section>
				</div><!-- /content -->
			</div>			
			<script>new CBPFWTabs(document.getElementById("tabs"));</script>
			
		<?php		
	} // End Function
	
	
/*====================================================================
	Settings section
====================================================================*/	
	private function plugin_settings(){
		
		$rsmembers_settings  = get_option( 'rsmembers_settings' );		
		$rsmembers_settings_arr = array(
			__( "Notify email at [ <strong>" . get_option( 'admin_email' )."</strong> ] for each new registration.", 'rsmembers' ),
			__( "Holds new registrations for admin approval.", 'rsmembers' ),
			__( "Set the account free No/Yes", 'rsmembers' ),
			__( "Enter trial period days", 'rsmembers' ),
			__( "", 'rsmembers' ),
			__( "", 'rsmembers' ),
			__( "", 'rsmembers' ),
		);
		?>		
        <?php $this->_plugin->rsmembers_ajaxpost('rsmembers_settings',"psloaderdiv","psloadingdiv", $this->_plugin->get_assets_url('images/loading2.gif'),"pssubmitbtn","psform_acction"); ?>
		<script type="text/javascript">
        function psform_acction(msg){
            
        }           
        </script>                
        <form action="<?php echo $_SERVER['REQUEST_URI']?>" name="rsmembers_settings" id="rsmembers_settings" method="post" enctype="multipart/form-data">
                <input type="hidden" name="caseselect" value="plugin_settings">
                <?php				
				for( $row = 0; $row < count( $rsmembers_settings )-1; $row++ ) { 
					if($rsmembers_settings[$row][2]=='faccount'){
						?>
                        <div class="form-inner15" style="display:none">
                        	<input type="hidden" name="<?php echo $rsmembers_settings[$row][2];?>" id="<?php echo $rsmembers_settings[$row][2];?>" value="on">
                        </div>
						<?php
					}else if($rsmembers_settings[$row][2]=='captcha'){
						?>
                        <div class="form-inner15" style="display:none">
                        	<input type="hidden" name="<?php echo $rsmembers_settings[$row][2];?>" id="<?php echo $rsmembers_settings[$row][2];?>" value="">
                        </div>
						<?php
					}else{
						?>
                        <div class="form-inner15">
                            <div class="left-col"><?php echo $rsmembers_settings[$row][1]; ?></div>
                            <div class="right-col">
                                <?php echo $this->_plugin->library->formcontrol( $rsmembers_settings[$row][2], $rsmembers_settings[$row][2], $rsmembers_settings[$row][3], $rsmembers_settings[$row][4], '', '', '', 0 );?>
                                <div class="clr"></div>
                                <div class="r-c-note"><?php echo $rsmembers_settings_arr[$row];?></div>
                            </div>
                            <div class="clr"></div>
                        </div>
						<?php 
					}
				} 
				?>
                
                
                <div class="form-inner15">
                    <div class="left-col">Terms & Condition Page</div>
                    <div class="right-col">
                        <?php
						 $args = array(
							'authors'      => '',
							'child_of'     => 0,
							'date_format'  => get_option('date_format'),
							'depth'        => 0,
							'echo'         => 1,
							'exclude'      => '',
							'include'      => '',
							'link_after'   => '',
							'link_before'  => '',
							'post_type'    => 'page',
							'post_status'  => 'publish',
							'show_date'    => '',
							'sort_column'  => 'post_title',
							'sort_order'   => '',
							'title_li'     => __('Pages')
						); 
						$pages = get_pages( $args ); 
						?>
						<select name="termcondi" id="termcondi" class="select-control">
                    	<option value="">Select Page</option>
						<?php						
						foreach ($pages as $page) {
							?>
                        	<option <?php if($rsmembers_settings[8][4]==$page->ID) echo'selected';?>  value="<?php echo $page->ID;?>"><?php echo $page->post_title;?></option>
							<?php
                        }
                        ?>
                	</select>                       
                        
                        <div class="clr"></div>
                        <div class="r-c-note">Select Term & Condition page.</div>
                    </div>
                    <div class="clr"></div>
                </div>
                
                <div class="form-inner15">
                    <div class="left-col">CSV Download</div>
                    <div class="right-col">
                        <a class="csvdownload" href="javascript:void(0)" dataurl="<?php //echo plugins_url();?><?php echo $_SERVER['REQUEST_URI']?>">Download</a>
                        <div class="clr"></div>
                        <div class="r-c-note">Click [ Download ] to backup your data.</div>
                    </div>
                    <div class="clr"></div>
                </div>
                
                <div class="form-inner15">
                    <div class="left-col">&nbsp;</div>
                    <div class="right-col" id="psloaderdiv"><input type="submit" value="Save Changes" class="button button-primary" id="pssubmitbtn" name="pssubmitbtn"></div>
                    <div class="clr"></div>
                </div>
        </form>        			
		<?php
	} // End Function	


/*====================================================================
	News letter section
====================================================================*/
	private function news_letter(){
		?>
         <?php $this->_plugin->rsmembers_ajaxpost('form_news_letter',"nlloaderdiv","nlloadingdiv", $this->_plugin->get_assets_url('images/loading2.gif'),"nlsubmitbtn","nlform_acction"); ?>
		<script type="text/javascript">
        function nlform_acction(msg){
            
        }           
        </script>                
        <form action="<?php echo $_SERVER['REQUEST_URI']?>" name="form_news_letter" id="form_news_letter" method="post" enctype="multipart/form-data">
            <input type="hidden" name="caseselect" value="news_letter">   
            <div class="form-inner15">
                <div class="left-col">To</div>
                <div class="right-col">
                    <script type="text/javascript">
                    jQuery(document).ready(function() {	 
                        jQuery('#multiple-select').selectivity();
                    });
                    </script>
                    <select id="multiple-select" class="selectivity-input" data-placeholder="Type to search user" name="traditional[]" multiple>
                    	<?php
						$blogusers = get_users( 'orderby=role' );
						foreach ( $blogusers as $user ) {
							?>
                        	<option value="<?php echo esc_html( $user->user_email );?>"><?php echo esc_html( $user->user_email );?></option>
							<?php
                        }
                        ?>
                	</select>
                    <div class="clr"></div>
                    <div class="r-c-note"></div>
                </div>
                <div class="clr"></div>
            </div>
            <div class="form-inner15">
                <div class="left-col">Subject</div>
                <div class="right-col">
                    <input id="subject" name="subject" type="text" class="text-control">
                    <div class="clr"></div>
                    <div class="r-c-note"></div>
                </div>
                <div class="clr"></div>
            </div>
            <div class="form-inner15">
                <div class="left-col">Message</div>
                <div class="right-col">
                    <textarea id="message" name="message" rows="6" class="textarea-control"  style=""></textarea>
                    <div class="clr"></div>
                    <div class="r-c-note"></div>
                </div>
                <div class="clr"></div>
            </div>
            <div class="form-inner15">
                <div class="left-col">&nbsp;</div>
                <div class="right-col" id="nlloaderdiv"><input type="submit" value="Send News letter" class="button button-primary" id="nlsubmitbtn" name="nlsubmitbtn"></div>
                <div class="clr"></div>
            </div>
		</form>    
		<?php	
	}
	

/*====================================================================
	Field list section
====================================================================*/
	private function required_field(){		
		$rsmembers_fields = get_option( 'rsmembers_fieldoptions' );
		?>
		<?php $this->_plugin->rsmembers_ajaxpost('updatefieldform',"ffloaderdiv","ffloadingdiv", $this->_plugin->get_assets_url('images/loading2.gif'),"ffsubmitbtn","ffform_acction"); ?>
		<script type="text/javascript">
        function ffform_acction(msg){
           
        }          
        </script>       
        <form name="updatefieldform" id="updatefieldform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>" enctype="multipart/form-data">			
			<div>
				<table class="widefat" id="image_sort" style="table-layout:inherit;">
					<thead><tr class="head" style="background-color:#e2e2e1;">
						<th scope="col"><?php _e( 'Field Name', 'rsmembers' ); ?></th>
                        <th scope="col"><?php _e( 'Option Name', 'rsmembers' ); ?></th>
                        <th scope="col"><?php _e( 'Field Type',  'rsmembers' ); ?></th>
                        <th scope="col"><?php _e( 'Action',  'rsmembers' ); ?></th>
                        <th scope="col"><?php _e( 'On registration area?',    'rsmembers' ); ?></th>
					</tr></thead>
                    <tbody id="requiredfieldnew">
				<?php				
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
				} ?>
                </tbody>
				</table>
                </div> 
                <p style="width:98%; margin:0px 1%;" class="submit" id="ffloaderdiv"><input type="submit" value="<?php _e( 'Update Fields', 'rsmembers' ); ?> &raquo;" class="button button-primary" id="ffsubmitbtn" name="ffsubmitbtn"></p><br /> 
			</form>	
           
			<script type="text/javascript">
           	jQuery(document).ready(function(){
                jQuery('table#image_sort tbody').sortable({
                    axis: 'y',
                    update: function (event, ui) {
                        var post_url = '<?php echo $_SERVER['REQUEST_URI']?>';            
                        // POST to server using jQuery.post or jQuery.ajax
                        jQuery.ajax({
                            data: jQuery("#updatefieldform").serialize(),
                            type: 'POST',
                            url: post_url				
                        });
                    }
                });	
            });
            function setfieldvilue(fieldcheckbox,fieldrequired){
                if(document.getElementById(fieldcheckbox).checked) {
                    jQuery("#"+fieldrequired).val('on');
                } else {
                    jQuery("#"+fieldrequired).val('');
                }
            }
            </script>
            <script type="text/javascript">
			
			function editfields(fieldsid){
				jQuery('#thumb0').html('');
				ajax_state('<?php echo $_SERVER['REQUEST_URI']?>&type=editfields&fieldsid='+ fieldsid ,"thumb0");				
				jQuery('a.poplight').trigger("click");
			}	
			function deletefields(fieldsid){
				jQuery('#thumb0').html('');
				delcon=confirm('Are you want to delete ??');
				if(delcon){						
					ajax_state('<?php echo $_SERVER['REQUEST_URI']?>&type=deletefields&fieldsid='+ fieldsid ,"requiredfieldnew");
				}
			}										
			</script>
            
			<a href="#thumb0" class="poplight"></a>
			<div id="thumb0" class="popup_block" style="height:450px;">
				  <!--<div class="btn_close"></div>-->
				  <div class="thumb-text"></div>
				  <div class="clr"></div>    
			 </div>
		<?php 
	} // End function
	

/*====================================================================
	New Field section
====================================================================*/	
	private function required_field_new(){		
		
		$this->_plugin->rsmembers_ajaxpost('newfieldform',"nffloaderdiv","nffloadingdiv", $this->_plugin->get_assets_url('images/loading2.gif'),"nffsubmitbtn","ffform_acction"); ?>
		<script type="text/javascript">
        function ffform_acction(msg){
           	if(msg=='Added successfully'){									
				jQuery('#FieldName').val('');				
				ajax_state('<?php echo $_SERVER['REQUEST_URI']?>&caseselect=field_list_form',"requiredfieldnew");	
			}
        }          
        </script>
        
        <form name="newfieldform" id="newfieldform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
			<input type="hidden" name="caseselect" value="field_new_form">	
			<input type="hidden" value="" id="field_name_action" name="field_name_action">	
                <h3 class="title">New Fields Info</h3><br>
                
                <div class="form-inner15">
                    <div class="left-col">Field Name</div>
                    <div class="right-col">
                        <input id="FieldName" name="FieldName" type="text" class="text-control">
                        <div class="clr"></div>
                        <div class="r-c-note"></div>
                    </div>
                    <div class="clr"></div>
                </div>
                <div class="form-inner15">
                    <div class="left-col">Field Type</div>
                    <div class="right-col">
                        <select name="FieldType" id="FieldType" class="select-control">
                        	<option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                        	<option value="password">Password</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="select">Drop Down</option>
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
                        <textarea name="selectval" id="selectval" class="text-control" rows="5"></textarea>
                        <div class="clr"></div>
                        <div class="r-c-note"> Options should be Option Name,option_value| <br><strong>Ex:</strong> <---- Select One ---->,|Position One,1|Position Two,2|Position Three,3|Position Four,4</div>
                    </div>
                    <div class="clr"></div>
                </div>
                                
                <h3 class="title">Field Validation Rules</h3><br>
                <div class="form-inner15">
                    <div class="left-col">Required</div>
                    <div class="right-col">
                        <input name="required" id="required" type="checkbox" class="cmn-toggle cmn-toggle-round"/>
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
                            <option value="numeric">Numeric Value</option>
                        	<option value="email">Email</option>
                            <option value="date">Date</option>
                            <option value="website">Website</option>
                        </select>
                        <div class="clr"></div>
                        <div class="r-c-note"></div>
                    </div>
                    <div class="clr"></div>
                </div>
                
                <div class="form-inner15">
                    <div class="left-col">Maximum length</div>
                    <div class="right-col">
                        <input id="maxlen" name="maxlen" type="text" class="text-control">
                        <div class="clr"></div>
                        <div class="r-c-note"></div>
                    </div>
                    <div class="clr"></div>
                </div>
                
                <div class="form-inner15">
                    <div class="left-col">Minimum length</div>
                    <div class="right-col">
                        <input id="minlen" name="minlen" type="text" class="text-control">
                        <div class="clr"></div>
                        <div class="r-c-note"></div>
                    </div>
                    <div class="clr"></div>
                </div>
                
                
                <div class="form-inner15">
                    <div class="left-col">&nbsp;</div>
                    <div class="right-col" id="nffloaderdiv">
                        <input type="submit" value="<?php _e( 'New Fields', 'rsmembers' ); ?> &raquo;" class="button button-primary" id="nffsubmitbtn" name="nffsubmitbtn">
                        <div class="clr"></div>
                        <div class="r-c-note"></div>
                    </div>
                    <div class="clr"></div>
                </div>
		</form>	
            
		<?php 
	} // End function

/*====================================================================
	Required Message section
====================================================================*/	
	private function required_message(){
		$rsmembers_messageoptions  = get_option( 'rsmembers_messageoptions' );
		$this->_plugin->rsmembers_ajaxpost('rm_form',"rmloaderdiv","rmloadingdiv", $this->_plugin->get_assets_url('images/loading2.gif'),"rmsubmitbtn","rmform_acction"); ?>
		<script type="text/javascript">
        function rmform_acction(msg){
            
        }           
        </script>
        <form name="rm_form" id="rm_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>" enctype="multipart/form-data">
		<input type="hidden" name="caseselect" value="required_message">
		<?php
		for( $row = 0; $row < count( $rsmembers_messageoptions ); $row++ ) { ?>
			<div class="form-inner15">
				<div class="left-col"><?php echo $rsmembers_messageoptions[$row][0];?></div>
				<div class="right-col">
					<textarea name="<?php echo "rmessage_".$row; ?>" id="" rows="3" class="textarea-control"><?php echo stripslashes( $rsmembers_messageoptions[$row][1] ); ?></textarea>
					<div class="clr"></div>
					<div class="r-c-note"></div>
				</div>
				<div class="clr"></div>
			</div>
		<?php } 
		?>
        	<div class="form-inner15">
				<div class="left-col">&nbsp;</div>
				<div class="right-col">
					<p class="submit" id="rmloaderdiv"><input type="submit" value="Save Changes" class="button button-primary" id="rmsubmitbtn" name="rmsubmitbtn"></p>
					<div class="clr"></div>
					<div class="r-c-note"></div>
				</div>
				<div class="clr"></div>
			</div>        
        </form>     
		<?php
	} // End function
		





	
	



	
}	//End Class

// EOF