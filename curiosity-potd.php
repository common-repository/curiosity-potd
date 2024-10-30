<?php
/*
Plugin Name: Curiosity POTD widget / SOL block
Plugin Script: curiosity-potd.php
Description: NASA Curiosity rover picture of the day widget & current sol static block
Version: 2.1
License: GPL 2.0
Author: Andrea Benzi
Author URI: https://linktr.ee/andreabenzi
*/

class curiosity_potd_widget extends WP_Widget
{
    public function __construct(){
        $widget_options = array( 
            'classname' => 'curiosity_potd',
            'description' => 'NASA Curiosity rover photo of the day widget.',
        );
        parent::__construct( 'curiosity_potd', 'Curiosity POTD', $widget_options );
    }

    public function form( $instance ){
        $defaults = array(
            'title' => 'Curiosity Latest Photo',
            'api_key' => 'DEMO_KEY'
        );
 
        $instance = wp_parse_args( (array) $instance, $defaults ); 
        ?>
 
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">
                Title:
            </label>
            <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'api_key' ); ?>">
                API Key:
            </label>
            <input type="text" id="<?php echo $this->get_field_id( 'api_key' ); ?>" name="<?php echo $this->get_field_name( 'api_key' ); ?>" value="<?php echo $instance['api_key']; ?>" />
        </p>
 
        <?php
    }

 
    public function widget( $args, $instance ){
 
        extract($args);
 
        $title = apply_filters('widget_title', $instance['title'] );
 
        echo $before_widget;
        echo $before_title . $title . $after_title;

        ?>
            <script>
                window.onload = function (){
                    
                    let xhr_manifest = new XMLHttpRequest();
                    xhr_manifest.open('GET','https://api.nasa.gov/mars-photos/api/v1/manifests/curiosity?&api_key=<?php echo $instance['api_key']; ?>');
                    xhr_manifest.send();
                    xhr_manifest.onreadystatechange = function(){
                    	
                    	let obj_manifest = JSON.parse(xhr_manifest.responseText);
                        let sol = obj_manifest.photo_manifest.max_sol;
                        
                        let xhr_curiosity = new XMLHttpRequest();
	                    xhr_curiosity.open('GET','https://api.nasa.gov/mars-photos/api/v1/rovers/curiosity/photos?sol='+sol+'&api_key=<?php echo $instance['api_key']; ?>');
	                    xhr_curiosity.send();
	                    
	                    xhr_curiosity.onreadystatechange = function(){
	                        if(xhr_curiosity.readyState == 4 && xhr_curiosity.status === 200){
	                            
	                            let obj_curiosity = JSON.parse(xhr_curiosity.responseText);
	                            
	                            if (obj_curiosity.photos.length != 0){
	                                
	                                //Create img element
	                                var img_curiosity = document.createElement("img");
	                                var random_camera = Math.floor(Math.random() * obj_curiosity.photos.length);
	                                img_curiosity.src = obj_curiosity.photos[random_camera].img_src;
	                                img_curiosity.style.width = '100%';
	                                document.querySelector('#curiosity-potd').appendChild(img_curiosity);

	                                //Create sol text
	                                var sol = document.createElement("I");
	                                var sol_text = document.createTextNode("Sol: "+obj_curiosity.photos[random_camera].sol);
	                                sol.appendChild(sol_text);                               
	                                document.querySelector('#curiosity-potd').appendChild(sol);

	                                var br = document.createElement("BR");
	                                document.querySelector('#curiosity-potd').appendChild(br);
	                                
	                                //Create camera name text
	                                var sol = document.createElement("SMALL");
	                                var sol_text = document.createTextNode(obj_curiosity.photos[random_camera].camera.full_name);
	                                sol.appendChild(sol_text);                               
	                                document.querySelector('#curiosity-potd').appendChild(sol);

	                            }else{
	                                var error = document.createElement("SMALL");
	                                var error_text = document.createTextNode('Sorry. No picture today.');
	                                error.appendChild(error_text);           
	                                document.querySelector('#curiosity-potd').appendChild(error);
	                            }

	                        }
	                    }
	                    
	                    xhr_curiosity.error = () => {
	                        var error = document.createElement("SMALL");
	                        var error_text = document.createTextNode('Sorry. Error contacting server.');
	                        error.appendChild(error_text);           
	                        document.querySelector('#curiosity-potd').appendChild(error);
	                    }
                    }

                    xhr_manifest.error = () => {
                    	var error = document.createElement("SMALL");
	                    var error_text = document.createTextNode('Sorry. Error contacting server.');
	                    error.appendChild(error_text);           
	                    document.querySelector('#curiosity-potd').appendChild(error);
                    }

                }
            </script>    
            <div id="curiosity-potd"></div>
        
        <?php
        echo $after_widget;
    }

    public function update( $new_instance, $old_instance ){
        $instance = $old_instance;
 
        $instance['title'] = strip_tags( $new_instance['title'] );

        $instance['api_key'] = strip_tags( $new_instance['api_key'] );
 
        return $instance;
    }

 
}

/* widget registration */
function curiosity_potd_register_widgets()
{
    register_widget( 'curiosity_potd_widget' );
}

add_action( 'widgets_init', 'curiosity_potd_register_widgets' );

/* block registration */
function loadSolBlock() {
  wp_enqueue_script(
    'cpotd-block',
    plugin_dir_url(__FILE__) . 'cpotd-block.js',
    array('wp-blocks','wp-editor'),
    true
  );
} 
add_action('enqueue_block_editor_assets', 'loadSolBlock');

/* deactivate new block editor for widget */
function phi_theme_support() {
    remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'phi_theme_support' );
?>