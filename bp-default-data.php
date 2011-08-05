<?php
/**
 * Plugin Name: BuddyPress Default Data
 * Plugin URI:  http://ovirium.com
 * Description: Plugin will create lots of users, groups, topics, activity items - useful for testing purpose.
 * Author:      slaFFik
 * Version:     0.3.5
 * Author URI:  http://cosydale.com
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

define('BPDD_VERSION', '0.3.5');

add_action('bp_init', 'bpdd_init');
function bpdd_init(){
	add_action( bp_core_admin_hook(), 'bpdd_admin_page', 99 );
}

function bpdd_admin_page(){
	if ( !is_super_admin() )
		return false;
		
	$page = add_submenu_page( 'bp-general-settings', __( 'Default Data', 'bpdd' ), __( 'Default Data', 'bpdd' ), 'manage_options', 'bpdd-setup', 'bpdd_admin_page_content'  );
	add_action( "admin_print_styles-$page", 'bp_core_add_admin_menu_styles' );
}

function bpdd_admin_page_content(){
?>
	<div class="wrap">

		<?php screen_icon( 'buddypress' ); ?>
		<style>
		ul.items{margin:20px}
		ul li.users{border-bottom: 1px solid #EEEEEE;margin: 0 0 10px;padding: 5px 0}
		ul li.users ul,ul li.groups ul{margin:5px 0 0 20px}
		#message ul.results li{list-style:disc;margin-left:25px}
		</style>
		<h2><?php _e('BuddyPress Default Data', 'bpdd') ?> <sup>v<?php echo BPDD_VERSION ?></sup></h2>

		<?php if ( isset( $_POST['bpdd-admin-submit'] ) ) :
			// default values
			$users = false; 
			$profile = false; 
			$messages = false; 
			$activity = false; 
			$friends = false; 
			$groups = false; 
			$forums = false; 
			$g_activity = false; 
			?>

			<?php
			if(isset($_POST['bpdd-admin-clear']) && !empty($_POST['bpdd-admin-clear'])){
				
			}
			
			// Import users
			if(isset($_POST['bpdd']['import-users'])){
				$users = bpdd_import_users();
				$imported['users'] = count($users) . ' new users';
				if(isset($_POST['bpdd']['import-profile'])){
					$profile = bpdd_import_users_profile($users);
					$imported['profile'] = count($profile) .' profile entries';
				}
				if(isset($_POST['bpdd']['import-messages'])){
					$messages = bpdd_import_users_messages($users);
					$imported['messages'] = count($messages) .' private messages';
				}
				if(isset($_POST['bpdd']['import-activity'])){
					$activity = bpdd_import_users_activity($users);
					$imported['activity'] = count($activity) .' activity items';
				}
				if(isset($_POST['bpdd']['import-friends'])){
					$friends = bpdd_import_users_friends($users);
					$imported['friends'] = count($friends) .' friends connections';
				}
			}
			// Import groups
			if(isset($_POST['bpdd']['import-groups'])){
				$groups = bpdd_import_groups($users);
				$imported['groups'] = count($groups) .' new groups';
				if(isset($_POST['bpdd']['import-g-members'])){
					$g_members = bpdd_import_groups_forums($groups);
					$imported['g_members'] = count($g_members) .' groups members (1 user can be in several groups)';
				}
				if(isset($_POST['bpdd']['import-forums'])){
					$forums = bpdd_import_groups_forums($groups);
					$imported['forums'] = count($forums) .' groups forum topics';
				}
				if(isset($_POST['bpdd']['import-g-activity'])){
					$g_activity = bpdd_import_groups_activity($groups);
					$imported['g_activity'] = count($g_activity) .' groups activity items';
				}
				
			}

			?>
			<div id="message" class="updated fade">
				<p><?php 
					_e( 'Data was successfully imported', 'bpdd' );
					if (count($imported)>0){
						echo ':<ul class="results"><li>';
						echo implode('</li><li>', $imported);
						echo '</li></ul>';
					} ?>
				</p>
			</div>

		<?php endif; ?>

		<form action="" method="post" id="bpdd-admin-form">
			<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('input#import-profile, input#import-friends, input#import-activity, input#import-messages').click(function(){
					if (jQuery(this).attr('checked') == 'checked')
						jQuery('input#import-users').attr('checked', 'checked');
				});
				jQuery('input#import-users').click(function(){
					if (jQuery(this).attr('checked') != 'checked')
						jQuery('input#import-profile, input#import-friends, input#import-activity, input#import-messages').removeAttr('checked');
				});
				
				jQuery('input#import-forums, input#import-g-members, input#import-g-activity').click(function(){
					if (jQuery(this).attr('checked') == 'checked')
						jQuery('input#import-groups').attr('checked', 'checked');
				});
				jQuery('input#import-groups').click(function(){
					if (jQuery(this).attr('checked') != 'checked')
						jQuery('input#import-forums, input#import-g-members, input#import-g-activity').removeAttr('checked');
				});
				
				jQuery("input#bpdd-admin-clear").click( function() {
					if ( confirm( 'Are you sure you want to delete all users (except one with ID=1), groups, messages activities, forum topics etc?' ) ) 
						return true; 
					else
						return false; 
				});
			});
			</script>
			
			<p><?php _e('Please do not import users twice as this will cause lots of errors (believe me).', 'bpdd'); ?></p>
			
			<ul class="items">
				<li class="users">
					<label for="import-users">
						<input type="checkbox" name="bpdd[import-users]" id="import-users" value="1" /> &nbsp;
						<?php _e( 'Do you want to import users?', 'bpdd' ) ?>
					</label>
					<ul>
						<?php if ( bp_is_active( 'xprofile' ) ) : ?>
							<li>
								<label for="import-profile">
									<input type="checkbox" disabled name="bpdd[import-profile]" id="import-profile" value="1" /> &nbsp;
									<?php _e( 'Do you want to import users profile data (profile groups and fields will be created)?', 'bpdd' ) ?>
								</label>
							</li>
						<?php endif; ?>

						<?php if ( bp_is_active( 'friends' ) ) : ?>
							<li>
								<label for="import-friends">
									<input type="checkbox" disabled name="bpdd[import-friends]" id="import-friends" value="1" /> &nbsp;
									<?php _e( 'Do you want to create some friend connections between imported users?', 'bpdd' ) ?>
								</label>
							</li>
						<?php endif; ?>

						<?php if ( bp_is_active( 'activity' ) ) : ?>
							<li>
								<label for="import-activity">
									<input type="checkbox" disabled name="bpdd[import-activity]" id="import-activity" value="1" /> &nbsp;
									<?php _e( 'Do you want to import activity posts for users?', 'bpdd' ) ?>
								</label>
							</li>
						<?php endif; ?>
						
						<?php if ( bp_is_active( 'messages' ) ) : ?>
							<li>
								<label for="import-messages">
									<input type="checkbox" name="bpdd[import-messages]" id="import-messages" value="1" /> &nbsp;
									<?php _e( 'Do you want to import private messages between users?', 'bpdd' ) ?>
								</label>
							</li>
						<?php endif; ?>
					</ul>
				</li>
				<?php if ( bp_is_active( 'groups' ) ) : ?>
				<li class="groups">
					<label for="import-groups">
						<input type="checkbox" name="bpdd[import-groups]" id="import-groups" value="1" /> &nbsp;
						<?php _e( 'Do you want to import groups?', 'bpdd' ) ?>
					</label>
					<ul>
						<li>
							<label for="import-g-members">
								<input type="checkbox"disabled  name="bpdd[import-g-members]" id="import-g-members" value="1" /> &nbsp;
								<?php _e( 'Do you want to import group members?', 'bpdd' ) ?>
							</label>
						</li>
						<?php if ( bp_is_active( 'groups' ) && bp_is_active( 'activity' ) ) : ?>
							<li>
								<label for="import-g-activity">
									<input type="checkbox" disabled name="bpdd[import-g-activity]" id="import-g-activity" value="1" /> &nbsp;
									<?php _e( 'Do you want to import group activity posts?', 'bpdd' ) ?>
								</label>
							</li>
						<?php endif; ?>
						<?php if ( bp_is_active( 'groups' ) && bp_is_active( 'forums' ) ) : ?>
							<li>
								<label for="import-forums">
									<input type="checkbox" disabled name="bpdd[import-forums]" id="import-forums" value="1" /> &nbsp;
									<?php _e( 'Do you want to import group forums, topics and posts?', 'bpdd' ) ?>
								</label>
							</li>
						<?php endif; ?>
					</ul>
				</li>
				<?php endif; ?>
			</ul>					

			<p class="submit">
				<input class="button-primary" type="submit" name="bpdd-admin-submit" id="bpdd-admin-submit" value="<?php _e( 'Import Selected Data', 'bpdd' ); ?>" />
				<input class="button-primary" type="submit" name="bpdd-admin-clear" id="bpdd-admin-clear" value="<?php _e( 'Clear BuddyPress Data', 'bpdd' ); ?>" />
			</p>

			<p><?php _e('Many thanks to <a href="http://imdb.com" target="_blank">IMDB.com</a> for movies titles (groups names), <a href="http://en.wikipedia.org" target="_blank">Wikipedia.org</a> (users names) and <a href="http://en.wikipedia.org/wiki/Lorem_ipsum" target="_blank">Lorem Ipsum</a> (activity and forum posts).', 'bpdd') ?></p>
			
			<?php wp_nonce_field( 'bpdd-admin' );	?>

		</form>

	</div>

<?php
}

/*
 *	Importer engine
 */
function bpdd_import_users(){
	$users_data = array();
	$users = array();

	require (dirname(__FILE__) . '/data/users.php');
	
	foreach($users_data as $user){
		$cur = wp_insert_user( array(
			'user_login' => $user['login'], 
			'user_pass' => $user['pass'], 
			'display_name' => $user['display_name'],
			'user_email' => $user['email']
		));
		bp_update_user_meta( $cur, 'last_activity', bpdd_get_random_date(5) );
		bp_update_user_meta( $cur, 'notification_messages_new_message', 'no' );
		$users[]->ID = $cur;
	}

	return $users;
}

function bpdd_import_users_profile(){
	return true;
}

function bpdd_import_users_messages($users = false){
	$messages = array();

	require (dirname(__FILE__) . '/data/messages.php');	

	// first level messages
	for($i = 0; $i < 100; $i++){
		$messages[] = messages_new_message(array(
					'sender_id' => bpdd_get_random_users_ids(1, 'string'),
					'recipients' => bpdd_get_random_users_ids(1, 'array'),
					'subject' => $messages_subjects[array_rand($messages_subjects)],
					'content' => $messages_content[array_rand($messages_content)],
					'date_sent' => bpdd_get_random_date(15)
				));
	}
	
	return $messages;
}
function bpdd_import_users_activity(){
	return true;
}
function bpdd_import_users_friends(){
	return true;
}
function bpdd_import_groups($users = false){
	$groups = array();
	$group_ids = array();
	if (empty($users))
		$users = get_users();
	require (dirname(__FILE__) . '/data/groups.php');
	foreach($groups as $group){
		$cur = groups_create_group(array(
				'creator_id' => $users[array_rand($users)]->ID,
				'name' => $group['name'],
				'description' => $group['description'],
				'slug' => groups_check_slug( sanitize_title( esc_attr( $group['name'] ) ) ),
				'status' => $group['status'],
				'date_created' => bpdd_get_random_date(30,5),
				'enable_forum' => $group['enable_forum']
		));
		groups_update_groupmeta( $cur, 'total_member_count', 1 );
		groups_update_groupmeta( $cur, 'last_activity', bpdd_get_random_date(10) );
		groups_new_group_forum( $cur, $group['name'], $group['description'] );
		$group_ids[] = $cur;
	}
	return $group_ids;
}
function bpdd_import_groups_activity(){
	return true;
}
function bpdd_import_groups_forums(){
	return true;
}

/*
 *	Helpers
 */
function bpdd_get_random_users_ids($count = 1, $output = 'array'){
	$was = array();
	$users = array();
	$users = get_users();
	$all = count($users);
		
	for( $i = 0; $i < $count; $i++ ){
		$cur = array_rand($users);
		if(in_array($cur, $was))
			$cur = array_rand($users);
		$random[] = $users[$cur]->ID;
		$was[] = $cur;
	}

	if($output == 'array'){
		return $random;
	}elseif($output == 'string'){
		return implode(',', $random);
	}
}

function bpdd_get_random_date($days_from = 30, $days_to = 0){
	//  1 day in seconds is 86400
	$from = $days_from * 86400;
	
	// $days_from should always be less than $days_to
	if ($days_to > $days_from)
		$days_to = $days_from - 1;
	$to = $days_to * 86400;
	
	$date_from = time() - $from;
	$date_to = time() - $to;
	return date( 'Y-m-d H:i:s', rand($date_from, $date_to));
}
