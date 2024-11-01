<?php 
#// BEGIN set display based on selected fields & user permission
class wpul_widget extends WP_Widget {

	function wpul_widget() {
		// Instantiate the parent object
			show_admin_bar(false); // Disable admin bar

		parent::__construct( false, 'WP UserLogin');
	}
        function wpul_user_permissions($args){
                $wp_url = get_settings('siteurl');
                $check = get_option('wpul_settings');
                $welcome = $check['welcome'];
                $vals = explode(',',$args);
                global $current_user, $user_ID, $wp_admin_bar,$wpdb,$post;
                get_currentuserinfo();

                $comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");
                $core = get_option('_site_transient_update_core');
                $plugins = get_option('_site_transient_update_plugins');
                $updates['plugins'] = $plugins->response;
                $updates['core'] = $core->updates['0']->response;
                $plugin_update = count($updates['plugins']);
                $endcollapse = '</div></div>';
                $link[] = '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">'.PHP_EOL;
                $link[] = ($check['dashboard'] == 'CHECKED' ? '<div class="panel panel-primary">'.PHP_EOL.'<div class="panel-heading text-center"><h4 class="panel-title"><a role="button" href="'.admin_url().'">'.__('Dashboard').'</a></h4></div></div>':'').PHP_EOL;
                $link[] = (($comments_waiting > 0) ? '<div class="panel panel-warning"><div class="panel-heading"><h4 class="panel-title text-center"><a role="button" href="'.admin_url('edit-comments.php?comment_status=moderated').'"/">'.pluralize($comments_waiting,__('Comments'),__('Comment')).(' Pending').' <span class="badge badge-important">'.$comments_waiting.'</span></a></h4></div></div>':'').PHP_EOL;
                $link[]= current_user_can('edit_posts') && (is_single() || is_page())?'<div class="panel panel-danger"><div class="panel-heading"><h4 class="panel-title text-center"><a href="'.get_edit_post_link($post->ID).'">Edit This '.ucwords($post->post_type).'</a></h4></div></div>':'';
                
                $postlabel = '<div class="panel panel-info">
                <div class="text-center panel-heading" role="tab" id="posts">
                    <h4 class="panel-title"><a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePost" aria-expanded="true" aria-controls="collapsePost">'.__('Posts').' <b class="caret"></b></a></h4></div>
                <div id="collapsePost" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
              <div class="panel-body">';
                $new = $check['newpost'] == 'CHECKED' && current_user_can('publish_posts') ? '<a href="'.admin_url('post-new.php').'" class="btn btn-default btn-block">'.__('New Post').'</a>':'';
                $edit = $check['editpost'] == 'CHECKED' && current_user_can('edit_posts') ? '<a href="'.admin_url('edit.php').'" class="btn btn-default btn-block ">'.__('Edit Posts').'</a>':'';
                
                $link[] = $new != '' ? $postlabel.$new.$edit.'</div>'.$endcollapse:'' ;

                $themes = '<div class="panel panel-info">
                <div class="text-center panel-heading" role="tab" id="themes">
                <h4 class="panel-title"><a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseAppearance" aria-expanded="true" aria-controls="collapseAppearance">'.__('Appearance').' <b class="caret"></b></a></h4></div>
                <div id="collapseAppearance" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
              <div class="panel-body">';
                $manage =$check['managetheme'] == "CHECKED" && current_user_can('update_themes')? '<a href="'.admin_url('themes.php').'" class="btn btn-default btn-block">'.__('Manage Themes').'</a>':'';
                $installt = $check['installtheme'] == "CHECKED" && current_user_can('install_themes')? '<a href="'.admin_url('theme-install.php').'" class="btn btn-default btn-block ">'.__('Install Themes').'</a>':'';
                $editt = $check['edittheme'] == "CHECKED" && current_user_can('editthemes')? '<a class="btn btn-default btn-block" href="'.admin_url('theme-install.php').'">'.__('Editor').'/a>':'';
                $link[] = $manage != '' ? $themes.$manage.$installt.$editt.'</div>'.$endcollapse :'';
                
                $plugins = '<div class="panel panel-info">
                <div class="text-center panel-heading" role="tab" id="plugins">
                <h4 class="panel-title"><a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePlugins" aria-expanded="true" aria-controls="collapsePlugins">'.__('Plugins').' <b class="caret"></b></a></h4></div>
                <div id="collapsePlugins" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">';
                $update = current_user_can('update_plugins') ? '<a href="'.admin_url('plugins.php').'" class="btn btn-default btn-block">'.__('Manage Plugins').'</a>':'';
                $installp = $check['install_plugins'] == "CHECKED" && current_user_can('install_plugins') ? '<a href="'.admin_url('plugins.php').'" class="btn btn-default btn-block">'.__('Install Plugins').'</a>':'';
                $link[] = $update != '' ? $plugins.$update.$installp.'</div>'.$endcollapse :'';
                        
                $users = '<div class="panel panel-info">
                <div class="text-center panel-heading" role="tab" id="users">
                <h4 class="panel-title"><a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseUsers" aria-expanded="true" aria-controls="collapseUsers">'.__('Users').' <b class="caret"></b></a></h4></div>
                <div id="collapseUsers" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">';
                $editu = $check['users'] == "CHECKED" &&  current_user_can('edit_users')?'<a href="'.admin_url('users.php').'" class="btn btn-default btn-block ">'.__('All Users').'</a>':'';
                $eprofile = $check['profile'] == "CHECKED" &&  is_user_logged_in()?'<a href="'.admin_url('profile.php').'" class="btn btn-warning btn-block ">'.__('Edit Your Profile').'</a>':'';
                $vprofile = '<a href="'.home_url('?author='.$user_ID).'" class="btn btn-success btn-block">'.__('View Your Profile','wp-userlogin').'</a>';
                if($check['logout'] == "CHECKED" && is_user_logged_in()){
                    $logout = $check['redirect_out'] !== ''?'<a class="btn btn-danger btn-block" href="'.wp_logout_url(get_bloginfo('url').'/'.$check['redirect_out']).'">'.__('Logout').'</a></div>':'<a href="'.wp_logout_url($_SERVER['REQUEST_URI']).'" class="btn btn-block btn-danger">'.__('Logout').'</a>';    
                }
                
                $link[] = $users.$editu.$eprofile.$vprofile.$logout.'</div>'.$endcollapse;
                
                $utils = '<div class="panel panel-info">
                <div class="text-center panel-heading" role="tab" id="utils">
                <h4 class="panel-title"><a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseUtils" aria-expanded="true" aria-controls="collapseUtils">'.__('General').' <b class="caret"></b></a></h4></div>
                <div id="collapseUtils" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">';
                $updates = $plugin_update > 0 && current_user_can('update_core') ? '<div class="panel panel-default"><a href="'.admin_url('update-core.php').'"/><span class="btn btn-danger btn-block">'.$plugin_update.__(' Plugin ').pluralize($plugin_update,__('Updates'),__('Update')).__(' Available').'</span></a>':''; 
                $update = $updates['core'] == 'upgrade' && current_user_can('update_core')?'<a href="'.admin_url('update-core.php').'" class="btn btn-danger btn-block">'.__('Core Update Available').'</a>':''; 
                $settings = $check['options'] == "CHECKED" &&  current_user_can('manage_options')?'<a href="'.admin_url('options-general.php').'" class="btn btn-success btn-block">'.__('Settings').'</a>':'';
                $tools = '<a href="'.admin_url('tools.php').'" class="btn btn-block btn-warning">'.__('Your Available Tools').'</a>';
                $link[] = $settings != '' ? $utils.$updates.$update.$settings.$tools.'</div>'.$endcollapse :'';
                
            if($check['welcomecheck'] == "CHECKED"){
                $firstname = !empty($current_user->user_firstname)?$current_user->user_firstname:$current_user->display_name;
                $lastname = !empty($current_user->user_lastname)? $current_user->user_lastname:$current_user->display_name;
                $fullname = !empty($current_user->user_firstname) && !empty($current_user->user_lastname)?$current_user->user_firstname.' '.$current_user->user_lastname:$current_user->display_name;

                $look = array(
                    'user'=>$current_user->user_nicename,
                    'login'=>$current_user->user_login,
                    'email'=>$current_user->user_email,
                    'firstname'=>$firstname,
                    'lastname'=>$lastname,
                    'fullname'=>$fullname,
                    'id'=>$current_user->ID
                );
                $key = '';
                $val = '';
                list($key,$val) = explode('%',$welcome);
                    $head = ($welcome ? $key. $look[$val]:'').PHP_EOL;
                }
                $head = '<span id="welcome">'.$head.'</span>';
            $avatar = $check['avatar'] == "CHECKED"?get_avatar( $current_user->ID, '96', '', $look[$val] ):'';
        preg_match("/src='(.*?)'/i",$avatar,$match);
            $avatar = '<img src="'.$match[1].'" class="img-circle">';
                $head = '<div>'.PHP_EOL.$avatar.'&nbsp;'.$head.PHP_EOL;
                
                $foot = '<div class="panel">'.wpul_optional_links()."</div>$endcollapse";
        $links = implode('',$link);
                return $head.$links.$foot;
        }
	function widget( $args, $instance ) {
		// Widget output
		$check = get_option('wpul_settings');
		//~ print_r($check);	
		if(is_user_logged_in()){
			global $current_user;
			get_currentuserinfo();
		$title =$option['set_log'];	
		
            if ( current_user_can('activate_plugins')){
		for($i=0;$i<10;$i++){
			$options[] =$i;
		}
            }
	    if(current_user_can('edit_posts')){
		$options[] = 2;
		$options[] = 0;
	    }
            if(current_user_can('publish_posts')){
		for($i=3;$i<8;$i++){
			$options[] = $i;
		}
		$options[] .= 0;
	    }
            if(current_user_can('read') ){
                    $options[] = 0;
                    $options[] = 6;
                    $options[] = 7;
            }
	    $options = array_unique($options);
	    $options = implode(',',$options);
	    $options = $this->wpul_user_permissions($options);

		}else{
		$title = $option['set_nonlog'];
		if($option['redirect'] !== ''){
			$redir = get_bloginfo('url').'/'.$option['redirect'];
		}else{
			$redir = $_SERVER['REQUEST_URI'];
		}
                $after_widget = '</div><div class="clearfix"></div>';
			$outargs = array(
        'echo' => true,
        'redirect' => $redir, 
        'form_id' => 'loginform',
        'label_username' => __( 'Username' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in' => __( 'Log In' ),
        'id_username' => 'user_login',
        'id_password' => 'user_pass',
        'id_remember' => 'rememberme',
        'id_submit' => 'wp-submit',
        'remember' => true,
        'value_username' => NULL,
        'value_remember' => false );
			wp_login_form($outargs);
		}
		echo $before_title
		. $title
		. $after_title
		. $options
		.$after_widget;
		
	}



	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		// Output admin widget options form
	}
}
?>