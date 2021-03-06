<?php
/**
 * Bimbler Users
 *
 * @package   Bimbler_Timeline
 * @author    Paul Perkins <paul@paulperkins.net>
 * @license   GPL-2.0+
 * @link      http://www.paulperkins.net
 * @copyright 2014 Paul Perkins
 */

/**
 * Include dependencies necessary... (none at present)
 *
 */

/**
 * Bimbler Timeline
 *
 * @package Bimbler_Timeline
 * @author  Paul Perkins <paul@paulperkins.net>
 */
class Bimbler_Timeline {

        /*--------------------------------------------*
         * Constructor
         *--------------------------------------------*/

        /**
         * Instance of this class.
         *
         * @since    1.0.0
         *
         * @var      object
         */
        protected static $instance = null;

        /**
         * Return an instance of this class.
         *
         * @since     1.0.0
         *
         * @return    object    A single instance of this class.
         */
        public static function get_instance() {

                // If the single instance hasn't been set, set it now.
                if ( null == self::$instance ) {
                        self::$instance = new self;
                } // end if

                return self::$instance;

        } // end get_instance

        /**
         * Initializes the plugin by setting localization, admin styles, and content filters.
         */
        private function __construct() {

        	add_action ('wp_enqueue_scripts', array ($this, 'enqueue_bootstrap_scripts'));
        	
        	add_shortcode( 'bimbler_timeline', array ($this, 'show_timeline'));
        	        	        	         	
		} // End constructor.
		
		private $script = '<script type="text/javascript">
var responsiveHelper;
var breakpointDefinition = {
    tablet: 1024,
    phone : 480
};
var tableContainer;

	jQuery(document).ready(function($)
	{
		tableContainer = $("#table-1");
		
		var thing = tableContainer.dataTable({
			"sPaginationType": "bootstrap",
			"aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
			"bStateSave": true,

		    // Responsive Settings
		    bAutoWidth     : false,
		    fnPreDrawCallback: function () {
		        // Initialize the responsive datatables helper once.
		        if (!responsiveHelper) {
		            responsiveHelper = new ResponsiveDatatablesHelper(tableContainer, breakpointDefinition);
		        }
		    },
		    fnRowCallback  : function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
		        responsiveHelper.createExpandIcon(nRow);
		    },
		    fnDrawCallback : function (oSettings) {
		        responsiveHelper.respond();
		    }
		});
				
		thing.columnFilter({
			"sPlaceHolder" : "head:after"
		});
				
		$(".dataTables_wrapper select").select2({
			minimumResultsForSearch: -1
		});
				
	});
				
				
</script>';
		
	private $script_bot = '	<script src="/wp-content/plugins/bimbler-users/assets/js/jquery.dataTables.min.js"></script>
	<script src="/wp-content/plugins/bimbler-users/assets/js/datatables/TableTools.min.js"></script>
	<script src="/wp-content/plugins/bimbler-users/assets/js/dataTables.bootstrap.js"></script>
	<script src="/wp-content/plugins/bimbler-users/assets/js/datatables/jquery.dataTables.columnFilter.js"></script>
	<script src="/wp-content/plugins/bimbler-users/assets/js/datatables/lodash.min.js"></script>
	<script src="/wp-content/plugins/bimbler-users/assets/js/datatables/responsive/js/datatables.responsive.js"></script>';
		
	function enqueue_bootstrap_scripts () {
		wp_register_style( 'style-datatables', plugins_url('data-tables.css', __FILE__) );
		wp_enqueue_style( 'style-datatables' );
		
		wp_register_style( 'style-entypo', plugins_url('entypo.css', __FILE__) );
		wp_enqueue_style( 'style-entypo' );
	}
	
	function render_timeline ($days) {
		$time_str = 'g:ia';
		$date_str = 'j M Y';
		$timestamp_str = 'Y-m-d\TH:i';
		
		$rsvp = Bimbler_RSVP::get_instance ();
		
		$user_activity = $rsvp->get_timeline_activity($days);
		
		$left = false;
		
		//var_dump ($user_rsvps);
		
		$content = '';
		
		foreach ($user_activity as $activity)
		{
			$begin = '';
			$skip = 0;
			
			if ('rsvp' == $activity->type)
			{	
				$post = get_post ($activity->post_id);
				$title = $post->post_title;
				$time = $activity->time;
				$link = tribe_get_event_link ($post);
				$rsvp = $activity->other1;

				$user = get_userdata($activity->user_id);
				$avatar = get_avatar ($user->ID, 250);//, null, null, $user_info->user_login);
				
				$icon = 'fa fa-check-square-o';
					
				if ('Y' == $rsvp) {
					$colour = 'bg-success';
		
					$text = $user->first_name . ' RSVPd Yes to <a href="' . $link . '">' . $title . '</a>.';
				} else {
					$colour = 'bg-danger';
		
					$text = $user->first_name . ' RSVPd No to <a href="' . $link . '">' . $title . '</a>.';
				}
			}
			
			if ('attended' == $activity->type)
			{
				$post = get_post ($activity->post_id);
				$title = $post->post_title;
				$time = $activity->time;
				$link = tribe_get_event_link ($post);
				$user = get_userdata($activity->user_id);
				
				$icon = 'fa fa-bicycle';
					
				$colour = 'bg-success';
				 
				$text = $user->first_name . ' attended <a href="' . $link . '">' . $title . '</a>.';
			}
		
			if ('comment' == $activity->type)
			{
				$post = get_post ($activity->post_id);
				$title = $post->post_title;
				$time = $activity->time;
				$comment_id = $activity->other1;
				//$comment_object = get_comment ($comment_id);
				$link = esc_url(get_comment_link($comment_id));

				$user = get_userdata($activity->user_id);
				$avatar = get_avatar ($user->ID, 250);//, null, null, $user_info->user_login);
				
				$icon = 'fa fa-comments-o';
				$colour = 'bg-success';
		
				$str=explode(' ',get_comment_excerpt($comment_id));
				$comment_excerpt=implode(' ',array_slice($str,0,11));
				if(count($str) > 11 && substr($comment_excerpt,-1)!='.') $comment_excerpt.='...';
		
				$text = $user->first_name . ' said &quot;' .  $comment_excerpt . '&quot; about <a href="' . $link . '">' . $title . '</a>.';
			}
		
			if ('photo' == $activity->type)
			{
				$time = $activity->time;

				$user = get_userdata($activity->user_id);
				$avatar = get_avatar ($user->ID, 250);//, null, null, $user_info->user_login);
				
				$icon = 'fa fa-camera';
				$colour = 'bg-success';
					
				$text = $user->first_name . ' uploaded a photo.';
		
				$src = '';
				$thumb = '';
		
				if ('/' != $activity->other1[0]) {
					$src .= '/';
					$thumb .= '/';
				}
		
				$src .= $activity->other1 . '/' . $activity->other2;
				$thumb .= $activity->other1 . '/thumbs/thumbs_' . $activity->other2;
		
				$text .= '<div class="row">';
				$text .= '<div id="ngg-image-0" class="ngg-gallery-thumbnail-box">';
				$text .= '<div class="ngg-gallery-thumbnail">' . PHP_EOL;
				$text .= '<a href="'. $src . '"' . PHP_EOL;
				$text .= 'data-src="' . $src . '"' . PHP_EOL;
				$text .= 'data-thumbnail="' . $thumb . '"' . PHP_EOL;
				//$text .= 'data-fancybox-group="27452b167807389dcf163ec7c4e03497"' . PHP_EOL; // New
				$text .= 'class="ngg-fancybox" ' . PHP_EOL;
				$text .= 'class="fancybox" ' . PHP_EOL;
				$text .= 'rel="27452b167807389dcf163ec7c4e03497"' . PHP_EOL;
				$text .= '>';
				$text .= '<img class="pull-rightx" src="' . $thumb . '" border=0></img>';
				$text .= '</a></div></div>';
				$text .= '</div>';
			}
				
			if ('joined' == $activity->type)
			{
				$time = $activity->time;
		
				$user = get_userdata($activity->user_id);
				$avatar = get_avatar ($user->ID, 250);//, null, null, $user_info->user_login);
				
				$icon = 'fa fa-smile-o';
				$colour = 'bg-success';
		
				$text = $user->first_name . ' joined.';
		
				$begin = ' begin';
			}
			
			if ('order' == $activity->type) {
				if (current_user_can( 'manage_options'))
				{
					$time = $activity->time;
			
					$user = get_userdata($activity->user_id);
					$avatar = get_avatar ($user->ID, 250);//, null, null, $user_info->user_login);
						
					$icon = 'fa fa-dollar';
					$colour = 'bg-success';
			
					$text = $user->first_name . ' placed an order.';
			
					$begin = '';
				}
				else
				{
					$skip = 1;
				}
			}
				
			if ('login' == $activity->type) {
				if (current_user_can( 'manage_options'))
				{
	
					$time = $activity->time;
					
					$user = get_userdata($activity->user_id);
					$avatar = get_avatar ($user->ID, 250);//, null, null, $user_info->user_login);
					
					$icon = 'fa fa-keyboard-o';
					$colour = 'bg-success';
						
					$text = $user->first_name . ' logged in.';
						
					$begin = '';
				}
				else 
				{
					$skip = 1;
				}
			}		
	
			if (!$skip) {

				$content .= '<article class="timeline-entry';
				if (true == $left) { 
					$content .= ' left-aligned'; 
				}
	
				$content .= $begin . '">';
			  						
			  	$content .= '					<div class="timeline-entry-inner">';
			  	$content .= '						<time class="timeline-time" datetime="' .  date ($timestamp_str, strtotime ($time)) . '"><span>' . date ($time_str, strtotime($time)) . '</span> <span>' .  date ($date_str, strtotime($time)) . '</span></time>';
			  							
			  	$content .= '						<div class="timeline-icon ' . $colour .'">';
			  	$content .= '							<i class="' . $icon . '"></i>';
			  	$content .= '						</div>';
			  							
			  	$content .= '						<div class="timeline-label group">';
			  	
			  	//$content .= '					<div class="tab-item-avatar">';
			  	//$content .= '						<a href="/profile/' . $user->user_nicename . '/" title="View ' . $user->user_nicename . '\'s profile">' . $avatar . '</a>';
			  	$content .= '							<span>' . $text . '</span>';
			  	//$content .= '					</div>';
			  			  	
			  	//$content .= '<div class="tab-item-inner group" style="vertical-align: baseline;">';
			  	//$content .= '							<span >' . $text . '</span>';
			  	//$content .= ' </div>';
			  	
			  	$content .= '						</div>';
			  	$content .= '					</div>';
			  						
			  	$content .= '				</article>';
	
			  	if (true == $left) { 
	 				$left = false; 
	 			} else {
	 				$left = true; 
	 			}
			}
 		}

 		return $content;
	}
	
	/*
	 * 
	 *
	 */
	function show_timeline($atts) {
		
		$a = shortcode_atts (array (
								'past' 	=> 7,
							), $atts);
		
		if (!isset ($a)) {
			error_log ('show_timeline called with no interval set.');
			return;
		}
		
		$days = $a['past'];
		
		$content = '';
		//$content .= '<p>Start date: past ' . $days . ' days</p>';
		
		
		
		$content .= '<div class="timeline-centered scrollable" data-height="250">';
		
		$content .= $this->render_timeline ($days);
				
		$content .= '		</div> <!-- timeline-centred -->';
		
		return $content;
	}
					
} // End class
