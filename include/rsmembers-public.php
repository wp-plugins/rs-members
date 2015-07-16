<?php
/**
 * RS-members is wordpress most powerful membership plugin many many features are include there.
 *
 * @link       http://www.themexpo.net
 *
 * @package    rs-members
 */
class RsMembersPublic
{
	private static $_instance = NULL;
	private $plugin = NULL;
		
	private function __construct($plugin){
		
		$this->plugin = $plugin;		
		add_shortcode('rsmembers-registration', array(&$this, 'shortcode_rsmembers'));	
		add_shortcode('rsmembers-login', array(&$this, 'shortcode_rsmemberslogin'));	
		add_shortcode('rsmembers-contentrestriction', array(&$this, 'shortcode_restriccontent'));	
		
	}

	/**
	 * Returns the singleton instance for this class
	 * @param Object $plugin The parent plugin's instance
	 * @return Object The single instance to the SlugPublic class
	 */
	public static function get_instance($plugin){
		if (NULL === self::$_instance)
			self::$_instance = new self($plugin);
		return (self::$_instance);
	}
	
	/**
	 * Shortcode callback
	 * Member registration
	 */
	public function shortcode_rsmembers($atts = array(), $content = ''){	
		
		$rsmembers_payment = get_option( 'rsmembers_payment' );
		$rsmembers_messageoptions  = get_option( 'rsmembers_messageoptions' );
		$rsmembers_settings  = get_option( 'rsmembers_settings' );
		$rsmembers_fields = get_option( 'rsmembers_fieldoptions' );
		
		
		$path = get_site_url().'/'.get_page_uri(get_the_ID());				
				
				global $user_ID, $user_identity; get_currentuserinfo();		
				if (!$user_ID) {
												
					?>				                
					<div class="rs_user_registration_worp">
                    <form action="<?php echo $_SERVER['REQUEST_URI']?>" name="rs_user_registration" id="rs_user_registration" method="post" enctype="multipart/form-data" >
						<input type="hidden" name="formprocess" value="active">
					<?php
					for( $row = 0; $row < count($rsmembers_fields); $row++ ) {			
						if($rsmembers_fields[$row][5]=='on'){
						?>			
						<div class="form-inner15">
							<div class="left-col"><?php echo $rsmembers_fields[$row][1]; ?></div>
							<div class="right-col">
								<?php 
								$validation = explode('|',$rsmembers_fields[$row][7]);
								$posttype = ( $_SERVER['REQUEST_METHOD'] == 'POST' and $_POST['formprocess']=='active' ) ? '1' : '0';					
								echo $this->plugin->library->formcontrol($rsmembers_fields[$row][2], $rsmembers_fields[$row][2], $rsmembers_fields[$row][3] , $rsmembers_fields[$row][6] ,$rsmembers_fields[$row][6],  $_POST[$rsmembers_fields[$row][2]], $validation, $posttype ); ?>
								
								<div class="clr"></div>
								<div class="r-c-note"></div>
							</div>
							<div class="clr"></div>
						</div>            
						<?php
						}
					} 
					?>
						
                    <?php if($rsmembers_settings[8][4]>0){ ?>
                        <div class="form-inner15">
							<div class="left-col">&nbsp;</div>
							<div class="right-col"><input type="checkbox" name="termscon" id="termscon"> <a href="<?php echo get_page_link($rsmembers_settings[8][4]); ?>" target="_blank" style="text-decoration:none !important; font-size:14px;">Terms & Condition</a></div>
							<div class="clr"></div>
						</div>
                    <?php } ?>
                        
                        <div class="form-inner15">
							<div class="left-col">&nbsp;</div>
							<div class="right-col" id="nlloaderdiv"><input type="submit" value="Registration" class="button button-primary" id="nlsubmitbtn" name="nlsubmitbtn"></div>
							<div class="clr"></div>
						</div>
						</form>
					</div> 
					
					<?php					
					if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['formprocess']) && $_POST['formprocess']=='active' and $this->plugin->library->get_found_error()==0  ){				
												
						
						
							$systems='';
							$user='';
							for( $row = 0; $row < count($rsmembers_fields); $row++ ) {			
								if($rsmembers_fields[$row][8]=='s'){						
									$systems .= $rsmembers_fields[$row][2] .','. $_POST[$rsmembers_fields[$row][2]] .'|';
								}
								if($rsmembers_fields[$row][8]=='u'){						
									$user .= $rsmembers_fields[$row][2] .','. $_POST[$rsmembers_fields[$row][2]] .'|';
								}
							} 
							$custom = $systems . '/#/#' . $user;	
							
							$customex = explode('/#/#', $custom);
							$systems = $customex[0];
							$user = $customex[1];
												
							$fieldsval  = array();
							$fields = explode('|', $systems);					
							foreach( $fields as $field ) {
								$options = explode( ',', $field );
								$fieldsval["$options[0]"] = $options[1];
							}							
							$userdata = array(
								'user_login'    =>   esc_attr($fieldsval['user_login']),
								'user_email'    =>   esc_attr($fieldsval['user_email']),
								'user_pass'     =>   esc_attr($fieldsval['user_pass']),
								'user_url'      =>   esc_attr($fieldsval['website']),
								'first_name'    =>   esc_attr($fieldsval['first_name']),
								'last_name'     =>   esc_attr($fieldsval['last_name']),
								'nickname'      =>   esc_attr($fieldsval['nickname']),
								'description'   =>   esc_attr($fieldsval['description']),
								'role'     		=>   esc_attr(get_option('default_role')),
								'user_registered' => date('Y-m-d H:i:s'),			
							);
							$user_id = wp_insert_user( $userdata );
							
							if(!empty($user_id)){						
								echo'<style>.rs_user_registration_worp{display:none;}</style>';
								
								$users = explode('|', $user);					
								foreach( $users as $usr ) {
									$options = explode( ',', $usr );
									update_user_meta( $user_id, $options[0], $options[1] );
								}
								
								if($rsmembers_settings[1][4]=='on')
									update_usermeta( $user_id, 'rsmembers_status', 'Deactivate' );
								else
									update_usermeta( $user_id, 'rsmembers_status', 'Active' );
								
								if($rsmembers_settings[3][4]>0)
									update_usermeta( $user_id, 'rsmembers_expiredate', date('Y-m-d', strtotime("+".$rsmembers_settings[3][4]." days")) );
								else
									update_usermeta( $user_id, 'rsmembers_expiredate', '' );								
								
								update_usermeta( $user_id, 'rsmembers_actype', 'Free' );
								
								$headers = 'From: User Registration <'.  get_option( 'admin_email' ) . ">\r\n";
								$headers .= 'MIME-Version: 1.0' . "\r\n";
								$headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
																
								$subject = 'User Registration';
								$message = 'Thank you for registration. <br><br> Your mail address: '.$fieldsval['user_email'].' User Id: '.$fieldsval['user_login'].' Password: '.$fieldsval['user_pass'] . '';
								$to = $fieldsval['user_email'];						
								$sent_message = wp_mail( $to, $subject, $message, $headers );
																
								if($rsmembers_settings[0][4]=='on'){						
									$subject = 'User registration admin notification';
									$message = 'One user complete registration. <br><br> Mail address: '.$fieldsval['user_email'].' User Id: '.$fieldsval['user_login']. '';
									$to = get_option( 'admin_email' );						
									$sent_message = wp_mail( $to, $subject, $message, $headers );						
								}								
								echo $rsmembers_messageoptions[1][1];		
								
							}							
						
						
						
									
					}
				
				} else {
	
					$content ='<div class="sidebox">
						<h3>Welcome, '.$user_identity.'</h3>
						<div class="usericon">';
							global $userdata; get_currentuserinfo(); $content .= get_avatar($userdata->ID, 60); 
						$content .='</div>
						<div class="userinfo">
							<p>You&rsquo;re logged in as <strong>'.$user_identity.'</strong></p>
							<p>
								<a href="'.wp_logout_url('index.php').'">Log out</a> | ';
								if (current_user_can('manage_options')) { 
									$content .='<a href="' . admin_url() . '" target="_blank">' . __('Dashboard') . '</a>'; 
								} else { 
									$content .='<a href="' . admin_url() . 'profile.php" target="_blank">' . __('Profile') . '</a>'; 
								}
				
							$content .='</p>
						</div>
					</div>';
					echo $content;
				
				}
		
		
		
	
		
	}	//End Function
	



	/**
	 * Shortcode callback
	 * member login
	 */
	public function shortcode_rsmemberslogin($atts = array(), $content = '')
	{
		$content='';	
		
		global $user_ID, $user_identity; get_currentuserinfo();	
		
		if (!$user_ID) { 			
		$ajax_nonce = wp_create_nonce("rs-security-nonce");			
			
			$redirect_to = empty($_GET)? $_SERVER["REQUEST_URI"]."?reset=true" : $_SERVER["REQUEST_URI"]."&reset=true";
		
			$content .='<div class="sidebox"><h3>Login to your account</h3><div class="rs-widget-login-div" >
						<form method="post" action="'. wp_login_url().'" class="wp-user-form">
						<p><label for="user_login1">Username:</label>
						<input id="user_login1" type="text" name="log" required="required" /></p>
						<p><label for="user_pass1">Password:</label>
						<input id="user_pass1" type="password" name="pwd" required="required" /></p>					
						<p><input id="rememberme-1" type="checkbox" name="rememberme" value="forever" />
						<label for="rememberme-1" >Remember me</label></p>
						
						'.do_action('login_form').'
						<p><input type="submit" name="user-submit" value="Login" /></p>
						<p>
						<input type="hidden" name="action" value="login">
						<input type="hidden" name="wp-submit" value="yes">
						<input type="hidden" name="redirect_to" value="'. $_SERVER['REQUEST_URI'].'" />
						<input type="hidden" class="force_ssl_login" value="<?php echo json_encode(force_ssl_login()); ?>"/>
						<input type="hidden" name="security" value="<?php echo $ajax_nonce?>"/>
						</p>
						</form>
						<a class="rs-flipping-link" href="#lost-pass" >Lost your password?</a>
						
			</div>        
			<div class="rs-widget-lost_pass-div" style="display:none;">
				
						<form method="post" action="'. add_query_arg( 'action' , 'lostpassword', wp_login_url() ) .'">
						<p><label for="lost_user_login1">Enter your username or email: </label>
						<input type="text" name="user_login" value="" size="20" id="lost_user_login1" /></p>
						'.do_action('login_form', 'resetpass') .'
						<p><input type="submit" name="user-submit" value="Reset my password" /></p>
						<p>
						<input type="hidden" name="action" value="lostpassword">
						<input type="hidden" name="wp-submit" value="yes">
						<input type="hidden" name="redirect_to" value="'. $redirect_to .'" />
						<input type="hidden" name="security" value="'. $ajax_nonce.'"/>
						<p>
						</form>
						<a class="rs-flipping-link" href="#rs-login">Back to login</a>
			</div>';
        
        } else {
	
			$content .='<div class="sidebox">
				<h3>Welcome, '.$user_identity.'</h3>
				<div class="usericon">';
					global $userdata; get_currentuserinfo(); $content .= get_avatar($userdata->ID, 60); 
				$content .='</div>
				<div class="userinfo">
					<p>You&rsquo;re logged in as <strong>'.$user_identity.'</strong></p>
					<p>
						<a href="'.wp_logout_url('index.php').'">Log out</a> | ';
						if (current_user_can('manage_options')) { 
							$content .='<a href="' . admin_url() . '" target="_blank">' . __('Dashboard') . '</a>'; 
						} else { 
							$content .='<a href="' . admin_url() . 'profile.php" target="_blank">' . __('Profile') . '</a>'; 
						}
		
					$content .='</p>
				</div>
			</div>';
			echo $content;
	
		} 				
		$content .='<style>.sidebox{max-width:300px;}</style>';		
		
		return $content;
		
	}	//End Function

	/**
	 * Shortcode callback
	 * Content restriction
	 */
	public function shortcode_restriccontent($atts = array(), $content = ''){
			
		global $user_ID, $user_identity; get_currentuserinfo();
		$rsmembers_settings = get_option( 'rsmembers_settings' ); 
		if (!$user_ID and $rsmembers_settings[7][4] == 'on' ) {					
			return '<strong style="color:#F00;">Only logined user can visible this section.</strong>';		
		}else{
			return $content;
		}	
		
	}	//End Function
	
	
	
	
	
	
	

}	//End Class

// EOF