<?php
/*======================================================
->	RsMembers Login Widget
======================================================*/
class RsMemberssignup extends WP_Widget {
		
	function __construct() {		
		// Instantiate the parent object
		$widget_ops = array( 'classname' => 'features-worp', 'description' => __( "Rs Members Signup" ) );
		parent::__construct('rsmemberssignup', __('Rs Members Signup'), $widget_ops);		
	}
	
	
	function widget( $args, $instance ) {
		extract($args);
		
		global $user_ID, $user_identity; get_currentuserinfo();		
		if (!$user_ID) {
			$path = plugins_url();
			//wp_enqueue_style( 'rs-members-main', $path.'/rs-members/assets/css/main.css', array(), '1.0.1' );		
			$title = apply_filters('widget_title', empty($instance['title']) ? __('Features') : $instance['title'], $instance, $this->id_base);
		
			$title='<span>'.$title.'</span>';				
			echo'<aside class="widget widget_rsmemberssignup" id="'.$widget_id.'">		
					<h2 class="widget-title">'.$title.'</h2>'. $this->signup_process() .'
			</aside>';	
		}
	
	}



	public function signup_process()
	{
		
		$content='';
				
		$rsmembers_payment = get_option( 'rsmembers_payment' );
		$rsmembers_messageoptions  = get_option( 'rsmembers_messageoptions' );
		$rsmembers_settings  = get_option( 'rsmembers_settings' );
		$rsmembers_fields = get_option( 'rsmembers_fieldoptions' );
		
		
		
		$path = get_site_url().'/'.get_page_uri(get_the_ID());
								
				include( $dir .'rsmembers-library2.php');				
				$library_signup = RsMembersLibrary2::get_instance($this);
						
								                
				$content .='<div class="rs_user_registration_worp"><form action="'.$_SERVER['REQUEST_URI'].'" name="form_news_letter_signup" id="form_news_letter_signup" method="post" enctype="multipart/form-data">
					<input type="hidden" name="formprocess" value="active_signup">';
				
				for( $row = 0; $row < count($rsmembers_fields); $row++ ) {			
					if($rsmembers_fields[$row][5]=='on'){
					$content .='<div class="form-signup">
						<div class="left-col">'.$rsmembers_fields[$row][1].'</div>
						<div class="right-col">';							 
							$validation = explode('|',$rsmembers_fields[$row][7]);
							$posttype = ( $_SERVER['REQUEST_METHOD'] == 'POST' and $_POST['formprocess']=='active_signup' ) ? '1' : '0';					
							$content .= $library_signup->formcontrol( $rsmembers_fields[$row][2].'_signup', $rsmembers_fields[$row][3] , $rsmembers_fields[$row][6] ,$rsmembers_fields[$row][6],  $_POST[$rsmembers_fields[$row][2].'_signup'], $validation, $posttype ); 							
							$content .='<div class="clr"></div>
							<div class="r-c-note"></div>
						</div>
						<div class="clr"></div>
					</div>';
					}
				} 
				
				if($rsmembers_settings[8][4]!='0'){
					  $content .='<div class="form-inner15">
						  <div class="left-col">&nbsp;</div>
						  <div class="right-col"><input type="checkbox" name="termscon" id="termscon"> <a href="'. get_page_link($rsmembers_settings[8][4]).'" target="_blank" style="text-decoration:none !important; font-size:14px;">Terms & Condition</a></div>
						  <div class="clr"></div>
					  </div>';
                 }
				
				
				$content .='<div class="form-inner15">
						<div class="left-col">&nbsp;</div>
						<div class="right-col" id="nlloaderdiv"><input type="submit" value="Registration" class="button button-primary" id="nlsubmitbtn" name="nlsubmitbtn"></div>
						<div class="clr"></div>
					</div>
					</form></div>';
                
				if($_SERVER['REQUEST_METHOD'] == 'POST' and !empty($_POST['formprocess']) and $_POST['formprocess']=='active_signup' and $library_signup->get_found_error()==0  ){				
										
						
							
							$systems='';
							$user='';
							for( $row = 0; $row < count($rsmembers_fields); $row++ ) {			
								$value = sanitize_text_field( $_POST[$rsmembers_fields[$row][2].'_signup'] );
								
								if($rsmembers_fields[$row][8]=='s'){						
									$systems .= $rsmembers_fields[$row][2] .','. $value .'|';
								}
								if($rsmembers_fields[$row][8]=='u'){						
									$user .= $rsmembers_fields[$row][2] .','. $value .'|';
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
								$content = $rsmembers_messageoptions[1][1];		
								
							}
							
						
						
					
				}
				
		
		
		
		
		
		
		
		return $content;
		
		
	}	//End Function



	/**
	 * mail send
	 */	
	function mail_send($to, $subject, $message, $headers){
		$sent_message = wp_mail( $to, $subject, $message, $headers );	
		if ( $sent_message ) {
			echo 'Message send to '.$to;
		} else {
			echo 'The message was not sent to '.$to;
		}
	}



	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);		
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0) );
		$title = strip_tags($instance['title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>		
                        
		<?php
	}
}

function RsMemberssignup_Action() {
	register_widget( 'RsMemberssignup' );
}

add_action( 'widgets_init', 'RsMemberssignup_Action' );
?>