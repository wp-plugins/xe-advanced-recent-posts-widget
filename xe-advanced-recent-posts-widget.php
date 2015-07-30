<?php
/**
* Plugin Name: XE Advanced Recent Posts Widget
* Plugin URI: http://xieno.com
* Description: XE Advanced Recent Posts Widget
* Version: 1.0.0
* Author:  Xieno Devloper Team
* Author URI: http://xieno.com
* License: GPL2
*/

/*  Copyright 2014  Xieno Devloper Team (email: support @ xieno.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/



// register XE_Advanced_Recent_Posts_Widget widget
function register_xe_recent_posts_widget() {
    register_widget( 'XE_Advanced_Recent_Posts_Widget' );
}
add_action( 'widgets_init', 'register_xe_recent_posts_widget' );



function xe_recent_post_stylesheet() {
	wp_register_style('xerecentpost_stylesheet', plugins_url('css/xe-advanced-recent-posts-widget.css', __FILE__) );
	wp_enqueue_style('xerecentpost_stylesheet');
} 

add_action('wp_enqueue_scripts', 'xe_recent_post_stylesheet');

function xerecentpost_wp_admin_style() {
	
		wp_enqueue_script( 'recent_post_js',plugins_url( '/js/xe-advanced-recent-posts-widget.js' , __FILE__ )  );
	
		wp_register_style('xerecentpost_wp_admin_css', plugins_url('css/xerecentpost-wp-admin.css', __FILE__) );
		wp_enqueue_style( 'xerecentpost_wp_admin_css' );
}

add_action( 'admin_enqueue_scripts', 'xerecentpost_wp_admin_style' );


/**
 * Adds XE_Advanced_Recent_Posts_Widget widget.
 */
class XE_Advanced_Recent_Posts_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'xe-advanced-recent-posts_widget', // Base ID
			__( 'XE Advanced Recent Posts Widget', 'xerecentpost' ), // Name
			array( 'description' => __( 'Advanced Recent Posts Widget', 'xerecentpost' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		 ?>
		<ul class="xe-advanced-recent-posts-widget">
		<?php 
			
			$noOfPost=	!empty($instance['no_post']) ?$instance['no_post'] : 5 ;
		
			$args = array(
			  'type'    => 'post',
			  'orderby' => 'name',
			  'order' => 'ASC'
			  );
		    $cat_arr=array();
			$cat_id='';
			$categories = get_categories($args);
			
			foreach ( $categories as $cat ) {
				
				if ( isset($instance['cat-'. $cat->cat_ID]) ) {
					$cat_arr[]=$cat->cat_ID;
				}
				unset($cat);
			}
		
		if(!empty($cat_arr) )
		{
			$cat_id=implode(",",$cat_arr);
		}
		
		$args = array(
			'posts_per_page'   => $noOfPost,
			'offset'           => 0,
			'category'         => $cat_id,
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'post',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);
		
		$myposts = get_posts( $args );
		
		foreach ( $myposts as $post ) {
			
			 setup_postdata( $post ); 
		

		?>
        	<li class="<?php echo $instance['imgalign']; ?>">

                 <?php
					if($instance['dispthumb'] == 1) {
					
						$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail_size' );
						$url = $thumb['0']; 
						$width=$instance['width'] !="" ? $instance['width'] : 50;
						$height=$instance['height'] != "" ?$instance['height'] : 50 ;
		
						if($url != "" ) {
					?>
                    <a href="<?php echo get_permalink( $post->ID ); ?>"><img src="<?php echo $url; ?>" height="<?php echo $height;?>" width="<?php echo $width;?>"></a>
                    
                    <?php } else { ?>
					<a href="<?php echo get_permalink( $post->ID ); ?>"><img src="<?php echo plugins_url();?>/xe-advanced-recent-posts-widget/images/imagenotfound.png" ></a>					
					<?php } }?>
                
                <div class="widget-posts-descr">
                
                	<h4><a href="<?php echo get_permalink( $post->ID ); ?>" rel="bookmark" class="xearpw-title"><?php echo $post->post_title; ?></a></h4>
                    
                    <?php if($instance['showpostdate'] == 1 ) { ?>
                  		  <time class="xearpw-date" datetime="<?php echo $post-post_date_gmt; ?>"><?php echo mysql2date("M d Y", $post->post_date); ?></time>
                    <?php } ?>
                    <?php 
						if($instance['postexect'] == 1) {
							echo ($instance['ext_length'] !="") ? substr( get_the_excerpt() ,0,$instance['ext_length']) :  get_the_excerpt();
						}
					?> 
                    
                   
                    
                </div>
			</li>
		<?php } 
		wp_reset_postdata();?> 
        </ul>		
		<?php	
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 * @see WP_Widget::form()
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'XE Advanced Recent Posts Widget', 'text_domain' );
		$no_post = ! empty( $instance['no_post'] ) ? $instance['no_post'] :'5';
		$ext_length = ! empty( $instance['ext_length'] ) ? $instance['ext_length'] :'50';
		$dispthumb = ! empty( $instance['dispthumb'] ) ? $instance['dispthumb'] :1;
		$postexect = ! empty( $instance['postexect'] ) ? $instance['postexect'] :1;
		$shwdte = ! empty( $instance['showpostdate'] ) ? $instance['showpostdate'] :1;
		$width=! empty( $instance['width'] ) ? $instance['width'] :'60';
		$height=! empty( $instance['height'] ) ? $instance['height'] :'60';
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
        <p>
        	<label for="<?php echo $this->get_field_name( 'no_post' ); ?>"><?php _e( 'Number of posts display:' ); ?></label>
            <input type="text" class="small-input" name="<?php echo $this->get_field_name( 'no_post' ); ?>" id="<?php echo $this->get_field_id( 'no_post' ); ?>" size="3" value="<?php echo esc_attr($no_post) ?>" />
        </p>
        <p>
        	<label><?php _e( 'Select Category' ); ?></label>
            <?php
			$args = array(
			  'type'    => 'post',
			  'orderby' => 'name',
			  'order' => 'ASC'
			  );
			$categories = get_categories($args);
			
			?></p>
          <div class="xe-cat-list" <?php if(count($categories) > 5 ) { echo 'style="height: 150px;"'; }?>>  
      <?php foreach ( $categories as $cat ) {
        $instance['cat-'. $cat->cat_ID] = isset($instance['cat-'. $cat->cat_ID]) ? $instance['cat-'. $cat->cat_ID] : false;   
    ?>
        <p><input class="checkbox" type="checkbox" <?php checked($instance['cat-'. $cat->cat_ID], true) ?> id="<?php echo $this->get_field_id('cat-'. $cat->cat_ID); ?>" name="<?php echo $this->get_field_name('cat-'. $cat->cat_ID); ?>" />
        <label for="<?php echo $this->get_field_id('cat-'. $cat->cat_ID); ?>"><?php echo $cat->cat_name ?></label></p>
      <?php
        unset($cat);
        } 
      ?>
        </div>
        
        <p><label for="checkbox"><input type="checkbox" <?php checked($shwdte, true) ?> name="<?php echo $this->get_field_name( 'showpostdate' ); ?>" id="<?php echo $this->get_field_name( 'showpostdate' ); ?>"> <?php _e( 'Show post date?' ); ?></label></p>
        
        
        <p><label for="checkbox2"><input onClick="ExcertShowHide(this);" type="checkbox" <?php checked($postexect, true) ?> name="<?php echo $this->get_field_name( 'postexect' ); ?>" id="<?php echo $this->get_field_name( 'postexect' ); ?>" > <?php _e( 'Show post Excerpt?' ); ?> </label></p>
       
        <p class="extp" <?php if($postexect !=1){ ?> style="display:none;" <?php } ?>>
        	<label for="<?php echo $this->get_field_name( 'ext_length' ); ?>"><?php _e( 'Excerpt Length'); ?></label>
            <input type="text" class="small-input"  value="<?php echo esc_attr($ext_length) ?>" name="<?php echo $this->get_field_name( 'ext_length' ); ?>" id="<?php echo $this->get_field_name( 'ext_length' ); ?>">
        </p>
        <p><label for=""><input onClick="ThumbShowHide(this);" type="checkbox" <?php checked($dispthumb, true) ?> name="<?php echo $this->get_field_name( 'dispthumb' ); ?>" id="<?php echo $this->get_field_name( 'dispthumb' ); ?>" >  <?php _e( 'Display Thumbnails?' ); ?>:</label></p>
        
        <div class="thumbszdiv" <?php if($dispthumb!=1){ ?> style="display:none;" <?php } ?> >
        
        <p>
            <label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width' ); ?>:</label>
            
            <input class="small-input" type="number" id="<?php echo $this->get_field_id( 'width' ); ?>"  name="<?php echo $this->get_field_name( 'width' ); ?>" step="1" min="0" value="<?php echo esc_attr($width); ?>">
            
            <label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height' ); ?>:</label>
            
            <input class="small-input" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" value="<?php echo esc_attr($height); ?>" type="number" step="1" min="0">
        </p>
       
        
        <p>
            <label>Align:</label>
            <select class="small-input" name="<?php echo $this->get_field_name( 'imgalign' ); ?>" id="<?php echo $this->get_field_name( 'imgalign' ); ?>">
                <option value="left" <?php if( $instance['imgalign'] =="left") { ?> selected="selected" <?php } ?> >Left</option>
                <option value="right" <?php if( $instance['imgalign'] =="right") { ?> selected="selected" <?php } ?> >Right</option>
            </select>
        </p>
         
         </div>
         
		<?php 
}

	/**
	 * Sanitize widget form values as they are saved.
	 * @see WP_Widget::update()
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$args = array(
			  'type'    => 'post',
			  'orderby' => 'name',
			  'order' => 'ASC'
			  );
			$categories = get_categories($args);
			
			foreach ( $categories as $cat ) {
				
				if ( isset($new_instance['cat-'. $cat->cat_ID]) ) {
					$instance['cat-'. $cat->cat_ID] = 1;
				} else if ( isset($instance['cat-'. $cat->cat_ID]) ){
					unset($instance['cat-'. $cat->cat_ID]);
				}
				unset($cat);
			}
		
		$instance['showpostdate']= isset($new_instance['showpostdate'])?1:0;
		$instance['postexect']= isset($new_instance['postexect'])?1:0;
		$instance['ext_length'] = ( ! empty( $new_instance['ext_length'] ) ) ? strip_tags( $new_instance['ext_length'] ) : '';
		$instance['dispthumb']= isset($new_instance['dispthumb'])?1:0;
		$instance['width']= $new_instance['width'];
		$instance['height']= $new_instance['height'];
		$instance['imgalign'] = ( ! empty( $new_instance['imgalign'] ) ) ? strip_tags( $new_instance['imgalign'] ) : '';		
		
		return $instance;
	}

} // class XE_Advanced_Recent_Posts_Widget
?>