<?php  
/* 
Plugin Name: Easy Image Crop 
Plugin URI: http://www.whiteboxstudio.dk/eic
Description: Adds <strong>super intuitive</strong> user interface for <strong>WYSIWYG image cropping</strong>. This image cropping plugin also supports custom image sizes and helps you add, manage and delete custom image sizes.
Version: 1.0
Author: Jens Schr&oslash;der
Author URI: http://www.whiteboxstudio.dk/eic
*/  
?>
<?php

if ( !function_exists('add_action') ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

load_plugin_textdomain('wbs_eic', false, basename( dirname( __FILE__ ) ) . '/languages' );

add_action('wp_enqueue_scripts', 'wbs_eic_enqueue_scripts');
add_action('wp_footer', 'wbs_eic_init_footer');
add_action('wp_ajax_wbs_eic', 'send_ajax_wbs_eic');
add_action('admin_menu', 'wbs_eic_admin_panel');
add_filter('plugin_action_links',  'add_settings_link', 10, 2 );
add_action('admin_init', 'wbs_eic_register_settings');



function wbs_eic_register_settings()
{
	register_setting('wbs_eic_settings_group', 'wbs_eic_cache_flush');
	add_settings_section('wbs_eic_cache_section', __('Cache', 'wbs_eic'), 'wbs_eic_cache_section_text', 'wbs_eic');
	add_settings_field('wbs_eic_cache_settings_field', __('Flush cache when cropping', 'wbs_eic'), 'wbs_eic_cache_field_string', 'wbs_eic', 'wbs_eic_cache_section');
	
	register_setting('wbs_eic_settings_group', 'wbs_eic_border_width');
	add_settings_section('wbs_eic_style_section', __('Style', 'wbs_eic'), 'wbs_eic_style_section_text', 'wbs_eic');
	add_settings_field('wbs_eic_border_width_field', __('Border width', 'wbs_eic'), 'wbs_eic_border_width_field_string', 'wbs_eic', 'wbs_eic_style_section');
	
	register_setting('wbs_eic_settings_group', 'wbs_eic_border_type');
	add_settings_field('wbs_eic_border_type_field', __('Border type', 'wbs_eic'), 'wbs_eic_border_type_field_string', 'wbs_eic', 'wbs_eic_style_section');
	
	register_setting('wbs_eic_settings_group', 'wbs_eic_border_color');
	add_settings_field('wbs_eic_border_color_field', __('Border color', 'wbs_eic'), 'wbs_eic_border_color_field_string', 'wbs_eic', 'wbs_eic_style_section');
	
	register_setting('wbs_eic_settings_group', 'wbs_eic_background_opacity');
	add_settings_field('wbs_eic_background_opacity_field', __('Overlay opacity', 'wbs_eic'), 'wbs_eic_background_opacity_field_string', 'wbs_eic', 'wbs_eic_style_section');
}

function wbs_eic_cache_section_text()
{
	_e('<br/>&nbsp;&nbsp;Enabling \'Flush cache when cropping\' will flush the wordpress cache every time you crop an image', 'wbs_eic');
}

function wbs_eic_cache_field_string()
{
	$options = get_option('wbs_eic_cache_flush');
	if($options['text_string']==true)
	{
		$options['text_string'] = 'checked';
	}
	else
		$options['text_string'] = '';
	echo('<input id="wbs_eic_cache_flush" name="wbs_eic_cache_flush[text_string]" type="checkbox" '.$options['text_string'].' />');
}

function wbs_eic_style_section_text()
{
	_e('<br/>&nbsp;&nbsp;Change the style of the user interface so that it fits with your theme', 'wbs_eic');
}

function wbs_eic_border_width_field_string()
{
	$options = get_option('wbs_eic_border_width', 2);
	echo('<input id="wbs_eic_border_width" name="wbs_eic_border_width[text_string]" type="text" size="4" value="'.$options['text_string'].'" />&nbsp;px');
}

function wbs_eic_border_type_field_string()
{
	$wbs_eic_border_types = array(
		'none'		=>  __('None - Defines no border. This is default', 'wbs_eic'),
		'hidden'	=>  __('Hidden - The same as "none", except in border conflict resolution for table elements', 'wbs_eic'),
		'dotted'	=>  __('Dotted - Defines a dotted border', 'wbs_eic'),
		'dashed'	=>  __('Dashed - Defines a dashed border', 'wbs_eic'),
		'solid'		=>  __('Solid - Defines a solid border', 'wbs_eic'),
		'double'	=>  __('Double - Defines two borders. The width of the two borders are the same as the border-width value', 'wbs_eic'),
		'groove'	=>  __('Groove - Defines a 3D grooved border. The effect depends on the border-color value', 'wbs_eic'),
		'ridge'		=>  __('Ridge - Defines a 3D ridged border. The effect depends on the border-color value', 'wbs_eic'),
		'inset'		=>  __('Inset - a 3D inset border. The effect depends on the border-color value', 'wbs_eic'),
		'outset'	=>  __('Outset - Defines a 3D outset border. The effect depends on the border-color value', 'wbs_eic')
	);
    
	$options = get_option('wbs_eic_border_type', 'dashed');
	echo('<select id="wbs_eic_border_type" name="wbs_eic_border_type[text_string]">');
	foreach($wbs_eic_border_types as $border_type => $border_desc)
	{
		if(strtolower($options['text_string'])==strtolower($border_type))
			echo('<option value="'.$border_type.'" selected>'.$border_desc.'</option>');
		else
			echo('<option value="'.$border_type.'">'.$border_desc.'</option>');
	}
	echo('</select>');
}


function wbs_eic_border_color_field_string()
{
	$options = get_option('wbs_eic_border_color', '#ffffff');
	echo('<input id="wbs_eic_border_color" name="wbs_eic_border_color[text_string]" type="text" size="4" value="'.$options['text_string'].'" />');
}

function wbs_eic_background_opacity_field_string()
{
	$options = get_option('wbs_eic_background_opacity', '0.8');
	echo('<input id="wbs_eic_background_opacity" name="wbs_eic_background_opacity[text_string]" size="4" type="text" value="'.$options['text_string'].'" />');
}

function add_settings_link($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
	
	if ($file == $this_plugin){
		$settings_link = '<a href="options-general.php?page=wbs_eic_settings">'.__("Settings", "photosmash-galleries").'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}

function send_ajax_wbs_eic()
{
	if(isset($_POST['action']) && $_POST['action'] == 'wbs_eic')
	{
		include_once('includes/image.php');
		
		$src_id = $_POST['src_id'];
		$src_x = $_POST['src_x'];
		$src_y = $_POST['src_y'];
		$src_w = $_POST['src_w'];
		$src_h = $_POST['src_h'];
		$dst_w = $_POST['dst_w'];
		$dst_h = $_POST['dst_h'];
		$dst_file = $_POST['dst_file'];
		
		$uploads = wp_upload_dir();
		
		if(get_option('wbs_eic_cache_flush'))
		{
		    wp_cache_flush();
		}
		
		$new_dst_file = wp_crop_image( $src_id, $src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h, false, $uploads['path'].'/'.$dst_file );
		$new_dst_file = apply_filters('wp_create_file_in_uploads', $new_dst_file, $src_id);
	
	
		if($new_dst_file)
		{
			if(is_wp_error($new_dst_file) )
			{
				echo $new_dst_file->get_error_message() . '\r\n' .
					$src_id . '\r\n' .
					$src_x . '\r\n' .
					$src_y . '\r\n' .
					$src_w . '\r\n' .
					$src_h . '\r\n' .
					$dst_w . '\r\n' .
					$dst_h . '\r\n' .
					$dst_file;
				die(-1);
			}
			echo($new_dst_file);
			die();
		}
		else
		{
			die(-1);
		}
	}
	die(-1);
}

function wbs_eic_admin_panel()
{
	add_options_page('EIC', 'Easy Image Crop', 'manage_options', 'wbs_eic_settings', 'add_wbs_eic_settings');
}

function add_wbs_eic_settings()
{
    
    if (!current_user_can('edit_posts'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }


    
?>

<div class="wrap">
	<h2>Easy Image Crop</h2>
	<br class="clear" />
	<div id="poststuff" class="ui-sortable">
		<div class="postbox " >
			<h3><?php echo(_e('Please donate', 'wbs_eic')); ?></h3>
			<div class="inside">
				<p>
					<?php echo(_e('You are more than welcome to make a donation so I can buy my wife some flowers! She LOVES flowers and it is important that she is happy so she will let me spend time developing and supporting this plugin :)', 'wbs_eic')); ?>
				</p>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
					">
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>
		</div>
	</div>
	
	<div id="poststuff" class="ui-sortable">
		<div class="postbox " >
			<h3><?php echo(_e('What it does', 'wbs_eic')); ?></h3>
			<div class="inside">
				<p>
					<?php echo(_e('Easy Image Crop adds a super intuitive interface for WYSIWYG cropping of any image allowing you to use edit in place functionality to crop your any image uploaded to your wordpress site directly in whatever post or page it is shown.', 'wbs_eic')); ?>
				</p>
			</div>
		</div>
	</div>
	<div id="poststuff" class="ui-sortable">
		<div class="postbox " >
			<h3><?php echo(_e('How to do it', 'wbs_eic')); ?></h3>
			<div class="inside">
				<p>
					<?php echo(_e('To use Easy Image Crop you have to be logged in as a wordpress user with permission to edit posts. When viewing a page or post in your browser Easy Image Crop will add a small icon to any image that can be cropped. Simply click the icon and drag the image to position it and drag the bottom right corner of the resize box to resize the image, hold shift to constrain proportions. When done click the crop tool to crop the image.', 'wbs_eic')); ?>
				</p>
			</div>
		</div>
	</div>
	<h3><?php echo(_e('Settings', 'wbs_eic')); ?></h3>
		<p>
		<div id="poststuff" class="ui-sortable">
		<div class="postbox " >
			 <form name="form1" method="post" action="options.php">
				<?php settings_fields('wbs_eic_settings_group'); ?>
				<p>
				<?php do_settings_sections('wbs_eic'); ?>
				</p>
				    <p class="submit">
					<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
				    </p>
			</form>
		</div>
		</div>
		</p>
	<div id="poststuff" class="ui-sortable">
		<div class="postbox " >
			<h3><?php echo(_e('Frequently asked questions', 'wbs_eic')); ?></h3>
			<div class="inside">
				<p>
					<strong><?php echo(_e('Q: Why doesn\'t my image change when I crop it?', 'wbs_eic')); ?></strong>
				</p>
				<p>
					<?php echo(_e('A: Try to enable \'Flush cache when cropping\'. This will flush the internal wordpress cache every time you crop an image', 'wbs_eic')); ?>
				</p>
				<br/>
				<p>
					<strong><?php echo(_e('Q: I\'ve enabled \'Flush cache when cropping\' but my image still hasn\'t changed. Why??', 'wbs_eic')); ?></strong>
				</p>
				<p>
					<?php echo(_e('A: Try to refresh the page after cropping and see if that helps', 'wbs_eic')); ?>
				</p>
				<br/>
				<p>
					<strong><?php echo(_e('Q: I\'ve enabled \'Flush cache when cropping\' and refreshed the page after cropping but my image still hasn\'t changed. Why???', 'wbs_eic')); ?></strong>
				</p>
				<p>
					<?php echo(_e('A: Go get a cup of coffee or something. I\'m sure your freshly cropped image will be on the page if you refresh it when you get back', 'wbs_eic')); ?>
				</p>
				<br/>
				<p>
					<strong><?php echo(_e('Q: I\'ve enabled \'Flush cache when cropping\', waited 10 minutes before I refreshed the page after cropping but my image still hasn\'t changed. Why??????', 'wbs_eic')); ?></strong>
				</p>
				<p>
					<?php echo(_e('A: If only I new! Please post a comment on the <a href="http://www.whiteboxstudio.dk/eic">EIC website</a>. Maybe if I buy my wife some flowers she will let me spend even more time on this plugin :)', 'wbs_eic')); ?>
				</p>

			</div>
		</div>
	</div>
	
   
</div>
<?php
}

function wbs_eic_enqueue_scripts()
{
	if (current_user_can( 'edit_posts'))
	{
		wp_enqueue_style( 'wbs_eic_style', plugins_url('/wbs_eic/css/wbs_eic.0.9.css'));
		wp_deregister_script('jquery');
		wp_enqueue_script('jquery', 'http://code.jquery.com/jquery-latest.min.js');
		wp_enqueue_style( 'wbs_eic_jquery_ui_style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/themes/base/jquery-ui.css');
		wp_enqueue_script( 'wbs_eic_jquery_ui', plugins_url('/wbs_eic/js/jquery-ui.min.js'), 'jquery');
		wp_enqueue_script( 'wbs_eic_jquery', plugins_url('/wbs_eic/js/wbs_eic.0.9.js'));
		$option_border_width = get_option('wbs_eic_border_width', 2);
		$option_border_type = get_option('wbs_eic_border_type', 'dashed');
		$option_border_color = get_option('wbs_eic_border_color', 'white');
		$option_background_opacity = get_option('wbs_eic_background_opacity', '0.8');
		
		$wbs_eic_options_array = array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'border_width' => $option_border_width['text_string'], 
						'border_type' => $option_border_type['text_string'],
						'border_color' => $option_border_color['text_string'],
						'background_opacity' => $option_background_opacity['text_string']
						);

		wp_localize_script( 'wbs_eic_jquery', 'wbs_eic',  $wbs_eic_options_array);
	}	
}

function wbs_eic_init_header()
{

	if (current_user_can( 'edit_posts'))
	{
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo(WP_PLUGIN_URL); ?>/wbs_eic/css/wbs_eic.0.9.css" media="screen" />
		<?php
		

	}
}

function wbs_eic_init_footer()
{
	if (current_user_can( 'edit_posts'))
	{
		global $wp_query;
		$post_id = $wp_query->post->ID;
		$args = array(
			'post_type' => 'attachment',
			'post_per_page' => -1,
			'post_status' => null,
			'post_parent' => $post_id
		);
		     
		$cellCount = 0;
		$attachments = get_posts( $args );
		if ( $attachments ) {
			?>
			<ul id="wbs_eic_attach_list" style="display: none;>
			<?php
			foreach ( $attachments as $attachment )
			{
				$uri = wp_get_attachment_url($attachment->ID);
				$img_file = substr($uri, strrpos($uri, '/') + 1);
				$img_file = substr($img_file, 0, strrpos($img_file, '.'));
				?>
				<li id="<?php echo($img_file); ?>"><?php echo($attachment->ID); ?></li>
				<?php
			}
			?>
			</ul>
			<?php
		}
	}
}
?>