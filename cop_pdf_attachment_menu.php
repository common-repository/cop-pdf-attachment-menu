<?php
/*
Plugin Name: AGW: COP PDF Attachment Menu
Description: A shortcode and widget to display a list of attachments to the current post. Defaults to pdf files but allows you select from and output a list of attachments of any mime type.
Version: 0.1.1
Author: Trevor Green	
Author URI: http://www.agreenweb.com/
Plugin URI: http://www.agreenweb.com/wordpress/cop-pdf-attachment-menu/
Text Domain: cop-pdf-attachment-menu
Domain Path: /languages
*/
/*
 * Change list types ol, li, div (nested divs).
 * [pdfmenu list_type="ul"]
 * [pdfmenu list_type="ol"]
 * [pdfmenu list_type="div"]
 * 
 * Set a different class on the list container.
 * [pdfmenu class="differentclass"]
 * 
 * Set a different target.
 * [pdfmenu target="differenttarget"]
 *
 * Get a list of pdfs from a different parent.
 * [pdfmenu post_parent="1"]
 * 
 * Change the number of pdfs to return.
 * 	
 * [pdfmenu numberposts="-1"]  (returns all attachments)
 * 
 * Setting the number of posts to something other than -1 enables paging
 *
 * [pdfmenu numberposts="10"]
 *
 * Setting Paged to false will disable paging and give you only one page with the number of posts requested.
 *
 * [pdfmenu numberposts="10" paged="false"]
 *
 * Setting the offset will get another page of results of the size denoted in number of posts (sample below gets the second page of 10 results)
 *      This can be used if you want to have separate columns of attachments manually formatted.
 *
 * [pdfmenu numberposts="10" paged="false" offset="2"]
 *
 * Set numpages to show only a certain number of attachment pages in the navigation 
 *
 * [pdfmenu numberposts="10" paged="false" numpage="10"] (will only show pages 1 - 10 in the navigation)
 * 
 * Change the post_mime_type to query for a different type or multiple types.
 * [pdfmenu post_mime_type="application/zip"]
 * 
 * Show all mime types
 * [pdfmenu post_mime_type="application/zip"]
 * 
 * Show captions and descriptions with [pdfmenu show_caption=true show_description=true]
 */

// include custom base class derived form WP_Widget

require_once('lib/copbasewidgetclass.php');

$pdfmenu = new cop_pdfmenu;

final class cop_pdfmenu {

	function __construct() {
		
		// Activation Hook
		register_activation_hook(__FILE__, array($this, 'install'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));
		
		add_shortcode( 'pdfmenu', array($this, 'pdfgenerate') );
        
        add_shortcode( 'attachments', array($this, 'generate') );
		
        add_shortcode( 'attachmentmenu', array($this, 'generate') );
		
		add_action( 'parse_request', array($this, 'force_download'), 10);

		add_action( 'widgets_init', create_function('', 'return register_widget("COP_PDF_Attachment_Menu_Widget");'));
	
        add_filter( 'query_vars', array( $this, 'add_query_vars_filter') );
        
	}
    
    public function add_query_vars_filter( $vars ){
        $vars[] = "ap"; // page
        $vars[] = "wi"; // widget instance - not fully implemented 
        $vars[] = "si"; // shortcode instance - not fully implemented 
        return $vars;
    }

	public function install() {
		if (version_compare(PHP_VERSION, '5.2.1', '<')) {
			deactivate_plugins(basename(__FILE__)); // Deactivate ourself
			wp_die("Sorry, COP PDF Attachment Menu requires PHP 5.2.1 or higher. Ask your host how to enable PHP 5 as the default on your servers.");
		}
	}
	public function deactivate(){
		
	}
	public function force_download() {

		if (isset($_GET['did'])) { 
			$a = $_GET['did']; 
			$file = get_attached_file( $a );

			if (file_exists($file) && is_readable($file)) {
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				$mimes = get_allowed_mime_types();
				foreach ($mimes as $type => $mime) {
				    if (strpos($type, $ext) !== false) {
				    	header('Content-type: ' . $mime);  
						header("Content-Disposition: attachment; filename=\"$file\"");	 
						readfile($file); 
						exit;
				    }
		  		}
			}
		}		

		/*
		if (isset($_GET['did'])) { 
			$a = $_GET['did']; 
			$file = get_attached_file( $a );
			if (file_exists($file) && is_readable($file) && preg_match('/\.pdf$/',$file))  { 
				header('Content-type: application/pdf');  
				header("Content-Disposition: attachment; filename=\"$file\"");	 
				readfile($file); 
			} 
		}
		*/
	}

	function generate($atts){
		$args = shortcode_atts( array(
            'id'               => '',
			'title'            => '', 
			//query options
			'post_type'        => 'attachment',
			'post_mime_type'   => 'application/pdf',
			'post_parent'      => get_the_ID(),
			'post_status'      => null,
			'numberposts'      => -1, /* default to showing all attachments */ 
			'paged'            => true,  
			'offset'           => 1, /* default to the 1st page */
			'orderby'          => 'date',
			'order'            => 'ASC',
			'numpages'         => null,
			//output formatting
			'target'           => '_blank',
			'list_type'        => 'ul',
			'class'            => 'cop_pdfmenu',
			'all_types'        => 'true',
			'show_excerpts'    => 'false',
			'show_description' => 'false',
            // set download to all or a specific mime type to enable force downloads.
			'download' => 'false',
		), $atts );
        
        $title =            $args['title'];
        $post_type =        $args['post_type'];
        $post_mime_type  =  $args['post_mime_type'];
        $post_parent =      $args['post_parent'];
        $post_status =      $args['post_status'];
        $numberposts =      $args['numberposts'];
        $paged =            $args['paged'];
        $offset =           $args['offset'];
        $orderby =          $args['orderby'];
        $order =            $args['order'];
        $numpages =         $args['numpages'];
        $target =           $args['target'];
        $list_type =        $args['list_type'];
        $class =            $args['class'];
        $all_types =        $args['all_types'];
        $show_excerpts =    $args['show_excerpts'];
        $show_description = $args['show_description'];
        $download =         $args['download'];
                
		$pagenumber = get_query_var( 'ap' ) ? get_query_var( 'ap' ) : $offset ;                
        
        /* shift the page number down one to match the 0 based pages */ 
		$offset = ($numberposts == -1 ? 0 : ($pagenumber == 1 ? 0 : ($pagenumber-1) * $numberposts) );
        
		/* query once to get a full count of available attachments */ 
		$queryargs = array(
            'post_type'      => $post_type, 
            'numberposts'    => -1, 
            'post_status'    => $post_status, 
            'post_parent'    => $post_parent, 
            'post_mime_type' => $post_mime_type
        );
		if($all_types == 'true' AND isset($queryargs['post_mime_type'])) {
			unset($queryargs['post_mime_type']);
		}
		$attachmentcount = get_posts($queryargs);
		
		$thecount = count($attachmentcount);
		/* query a second time to retrieve the attachment set */
		$queryargs2 = array(
            'post_type'      => $post_type, 
            'numberposts'    => $numberposts, 
            'offset'         => $offset, 
            'orderby'        => $orderby, 
            'order'          => $order, 
            'post_status'    => $post_status,
            'post_parent'    => $post_parent, 
            'post_mime_type' => $post_mime_type
        );
		if($all_types == 'true' AND isset($queryargs2['post_mime_type'])) {
			unset($queryargs2['post_mime_type']);
		}
		$attachments = get_posts($queryargs2);
		
		$listtypes = array(
            'ul' => 'li', 
            'ol' => 'li', 
            'div' => 'div'
        );
		
		$return ="";
		if($title != '') { $return .= '<h2>' . $title . '</h2>'; }
		if(isset($list_type)) {
			$return .= '<' . $list_type;
			if(isset($class)) {
				$return .= ' class="' . $class . '"';	
			}
			$return .= '>';
		}
		if ($attachments) {
			foreach ($attachments as $attachment) {
				$return .= (isset($list_type) ? '<' . $listtypes[$list_type] : '');
				if(isset($args['post_mime_type'])) {
					$return .= ' class="' . $args['post_mime_type'];
				} else {
					$return .= ' class="' . get_post_mime_type( $attachment->ID );
				}
				$return .= '">';
				$return .= '<a href="';                
				if($download == 'all') {
					$return .= '?did=' . $attachment->ID;
				} else {
					if(get_post_mime_type( $attachment->ID ) == $download ) {
						$return .= '?did=' . $attachment->ID;
					} else {
						$return .= wp_get_attachment_url($attachment->ID);
					}
				}
				$return .= '" target="' . $target . '" title="' . get_the_title($attachment->ID) . '">';
				$return .= get_the_title($attachment->ID); 
				$return .= '</a>';
				
				if($show_excerpts == 'true') {
					$return .= '<div class="the_caption">' . $attachment->post_excerpt . '</div>'; 
				}
				if($show_description == 'true') {
					$return .= '<div class="the_description">' . $attachment->post_content . '</div>'; 
				}
				$return .= (isset($list_type) ? '</' . $listtypes[$list_type] . '>' : '');
			}
		}
		if(isset($list_type)) {
			$return .= '</' . $list_type . '>'; 
		}

        $wi = get_query_var( 'wi' );
		/* paging */
		if($numberposts != -1 and $paged == 'true') {
            
			if($numpages == null ? $numpages = ceil($thecount / $numberposts) : $numpages );
			if($numpages > 1) {
				$return .= "<div class='attachment_nav'>";
				for($i = 1;$i <= $numpages;$i++) 
				{
                    global $wp;
                    
                    $current_url = add_query_arg( array(
                                'ap' => $i,
                                //'si' => ( $args['id'] == '' ? 0 : $args['id'] ),
                                //'wi' => ( $wi )
                            ), get_permalink()                                 
                    );
                    
     				$return .= '<a href="' . $current_url . '">' . $i . '</a>';  //Or whatever the link needs to be
     				if($i != $numpages) { $return .= "<span class='sep'>|</span>"; }
				}
				$return .= "</div>";
			}
		}		
		return $return;			
	}
    
    
    /* proxy function for pdf mime type settings */ 
    function pdfgenerate($atts){
		
        $defaults = shortcode_atts( array(
            'id'               => '',
			'title'            => '', 
			//query options
			'post_type'        => 'attachment',
			'post_mime_type'   => 'application/pdf',
			'post_parent'      => get_the_ID(),
			'post_status'      => null,
			'numberposts'      => -1, /* default to showing all attachments */ 
			'paged'            => 'false',  
			'offset'           => 1, /* default to the 1st page */
			'orderby'          => 'date',
			'order'            => 'ASC',
			'numpages'         => null,
			//output formatting
			'target'           => '_blank',
			'list_type'        => 'ul',
			'class'            => 'cop_pdfmenu',
			'all_types'        => 'false',
			'show_excerpts'    => 'false',
			'show_description' => 'false',
			// set download to all or a specific mime type to enable force downloads.
			'download'         => 'false',
		) , $atts );
                
        $result = $this->generate($defaults);
        return $result;
    }
}

class COP_PDF_Attachment_Menu_Widget extends COP_Widget {
    /** constructor */
    function COP_PDF_Attachment_Menu_Widget() {
        parent::WP_Widget(false, $name = 'COP PDF Attachment Menu Widget', true);
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        
        $title = apply_filters('widget_title', $instance['title']);
        
        echo $before_widget; 
            if ( $title ) {
            	echo $before_title . $title . $after_title; } 
        
                /* attempting to protect old installs from not having a default value */ 
                if( !isset( $instance['offset']) ) { $instance['offset'] = 0; }
                if( !isset( $instance['numpages']) ) { $instance['numpages'] = 0; }
                
                $numpages = $instance['numpages'];
                    
                if($instance['post_parent'] == '') {
                	$parent = get_the_ID();
                } else {
                	$parent = $instance['post_parent'];
                }

                // set number of posts to a default of 10 if it is not set in the widget.
                $numberposts = ( isset( $instance['numberposts'] ) ? $instance['numberposts'] : 10 );
                    
                /* retrieve page number from the querystring */
                $pagenumber = get_query_var( 'ap' ) ? get_query_var( 'ap' ) : 0 ;
                        
                /* shift the page number down one to match the 0 based pages */ 
                $offset = ( $numberposts == -1 ? 0 : ( $pagenumber == 1 ? 0 : ( $pagenumber-1 ) * $numberposts ) );

                /* get the count of the attachments */
                $countargs = array(
                	'post_type'      => $instance['post_type'],
                	'numberposts'    => -1,
                	'post_status'    => $instance['post_status'],
                	'post_parent'    => $parent,
                	'post_mime_type' => $instance['post_mime_type'],
                );
                if($instance['all_types'] == 'on' AND isset($countargs['post_mime_type'])) {                    
                    unset($countargs['post_mime_type']);
                }
                $attachmentcount = get_posts($countargs);        
				$thecount = count($attachmentcount);
		
                /* query a second time to retrieve the attachment set */
                $queryargs2 = array(
                    'post_type'      => $instance['post_type'],
                	'numberposts'    => $instance['numberposts'],
                    'offset'         => $offset, 
                    'orderby'        => $instance['orderby'], 
                    'order'          => $instance['order'], 
                    'post_status'    => $instance['post_status'],
                    'post_parent'    => $parent,
                    'post_mime_type' => $instance['post_mime_type']
                );
                if( isset( $instance['all_types'] ) ){
	                if( $instance['all_types'] == true ) {
	                	unset( $queryargs2['post_mime_type'] );
	                }
                } 
                
                $attachments = get_posts($queryargs2);
				
                // output formatting.
				$class      = $instance['class'];
				$list_type  = $instance['list_type'];
				$target     = $instance['target'];
		
				$listtypes = array( 
                    'ul' => 'li', 
                    'ol' => 'li', 
                    'div' => 'div' 
                );
				
				$return = "";
        
				if( isset( $list_type ) ) {
					$return .= '<' . $list_type;
					if( isset( $class ) ) {
						$return .= ' class="' . $class . '"';	
					}
					$return .= '>';
				}
				if ( $attachments ) {
					foreach ( $attachments as $attachment ) {
						$return .= ( isset($list_type) ? '<' . $listtypes[$list_type] : '' );
						if( isset( $args['post_mime_type'] ) ) {
							$return .= ' class="' . $args['post_mime_type'];
						} else {
							$return .= ' class="' . get_post_mime_type( $attachment->ID );
						}
						$return .= '">';	
						$return .= '<a href="';

						if( $instance['forcedownload'] == true ) {
							if( $instance['forcealltypes'] == true ) {
								$return .= '?did=' . $attachment->ID;
							} else {
								if( get_post_mime_type( $attachment->ID ) == $instance['download'] ) {
									$return .= '?did=' . $attachment->ID;
								} else {
									$return .= wp_get_attachment_url( $attachment->ID );
								}
							} 
						} else {
							$return .= wp_get_attachment_url( $attachment->ID );
						}
						$return .= '" target="' . $target . '" title="' . get_the_title( $attachment->ID ) . '">' . get_the_title( $attachment->ID ) . '</a>';
						if( isset( $instance['show_excerpts'] ) ){
							if( $instance['show_excerpts'] == true ) {
								$return .= '<div class="the_caption">' . $attachment->post_excerpt . '</div>'; 
							}
						}
						if( isset( $instance['show_description'] ) ){
							if( $instance['show_description'] == true ) {
								$return .= '<div class="the_description">' . $attachment->post_content . '</div>'; 
							}
						}
						$return .= ( isset($list_type) ? '</' . $listtypes[$list_type] . '>' : '' );
					}
				}
				if( isset($list_type) ) {
					$return .= '</' . $list_type . '>'; 
				}
                
                /* paging */
                if( $instance['numberposts'] != -1 and $instance['paged'] == 'on' ) {
                    
                    if( $numpages == null ? $numpages = ceil($thecount / $instance['numberposts']) : $numpages );
                    if( $numpages > 1 ) {
                        $return .= "<div class='attachment_nav'>";
                        for( $i = 1; $i <= $numpages; $i++ ) 
                        {
                            global $wp;
                            $current_url = add_query_arg( array(
                                'ap' => $i,
                                //'wi' => ltrim( $args['widget_id'], 'cop_pdf_attachment_menu_widget-')
                            ), get_permalink() );

                            $return .= '<a ';
                            if( $i == $pagenumber ) { $return .= 'class="current" '; }
                            $return .= 'href="' . $current_url . '">' . $i . '</a>';  //Or whatever the link needs to be
                            if( $i != $numpages ) { $return .= "<span class='sep'>|</span>"; }
                        }
                        $return .= "</div>";
                    }
                }	
                
        
				echo $return;
            
            echo $after_widget; 
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title']            = strip_tags($new_instance['title']);
		// query options.
		$instance['post_type']        = strip_tags($new_instance['post_type']);
		$instance['post_mime_type']   = strip_tags($new_instance['post_mime_type']);
		$instance['numberposts']      = strip_tags($new_instance['numberposts']);
        $instance['paged']            = strip_tags($new_instance['paged']);
		$instance['post_status']      = strip_tags($new_instance['width']);
		$instance['post_parent']      = strip_tags($new_instance['post_parent']);
		$instance['orderby']          = strip_tags($new_instance['orderby']);
		$instance['order']            = strip_tags($new_instance['order']);
        $instance['numpages']         = strip_tags($new_instance['numpages']);
		//new option to display all types.
		$instance['all_types']        = strip_tags($new_instance['all_types']);
		// output formatting.
		$instance['class']            = strip_tags($new_instance['class']);
		$instance['list_type']        = strip_tags($new_instance['list_type']);
		$instance['target']           = strip_tags($new_instance['target']);
		$instance['show_excerpts']    = strip_tags($new_instance['show_excerpts']);
		$instance['show_description'] = strip_tags($new_instance['show_description']);
		
		$instance['forcedownload']    = strip_tags($new_instance['forcedownload']);
		$instance['forcealltypes']    = strip_tags($new_instance['forcealltypes']);
		$instance['download']         = strip_tags($new_instance['download']);
		//$instance['post_parent']  = $new_instance['show_faces'] ? 'checked="checked"' : '';
		
  	    return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $default = array( 
        	'title'            => '',
			'post_type'        => 'attachment',
			'post_mime_type'   => 'application/pdf',
			'post_status'      => null,
			'post_parent'      => '',
        	'numberposts'      => -1,
            'paged'            => 0,
			'offset'           => 1,
			'orderby'          => 'date',
			'order'            => 'ASC',
            'numpages'         => null,
			'all_types'        => false,		
			'target'           => '_blank',
			'list_type'        => 'ul',
			'class'            => 'cop_pdfmenu',
        	'show_excerpts'    => 0,
			'show_description' => 0,   
			'forcedownload'    => 0,
			'forcealltypes'    => 0,
			'download'         => '', 
        );
		$instance = wp_parse_args( (array) $instance, $default );
 		
 		//$show_faces = $instance['show_faces'] ? 'checked="checked"' : '';
 							
		// query options.
		// http://codex.wordpress.org/Function_Reference/get_post_types
		$this->text(__('Title', 'COP_PDF_Attachment_Menu'), $instance['title'], $this->get_field_id('title'), $this->get_field_name('title'));
		
		$this->select(__('Post Type', 'COP_PDF_Attachment_Menu').' : (post_type)', $instance['post_type'], $this->get_field_id('post_type'), $this->get_field_name('post_type'), get_post_types('','names') );
		$this->select(__('Post Mime Type', 'COP_PDF_Attachment_Menu').' : (post_mime_type)', $instance['post_mime_type'], $this->get_field_id('post_mime_type'), $this->get_field_name('post_mime_type'), get_allowed_mime_types());
		$this->checkbox(__('Display All Types', 'COP_PDF_Attachment_Menu').' : (all_types)', 'all_types', $instance['all_types']);
		$this->text(__('Number of Posts :', 'COP_PDF_Attachment_Menu').' (numberposts)', $instance['numberposts'], $this->get_field_id('numberposts'), $this->get_field_name('numberposts'));
		$this->comment(__('insert -1 to list all attachments.', 'COP_PDF_Attachment_Menu'));
        $this->checkbox(__('Page Results', 'COP_PDF_Attachment_Menu').' : (paged)', 'paged', $instance['paged']);
		$this->text(__('Limit the numer of pages :', 'COP_PDF_Attachment_Menu').' (numpages)', $instance['numpages'], $this->get_field_id('numpages'), $this->get_field_name('numpages'));
        		
		$this->select(__('Order By', 'COP_PDF_Attachment_Menu').' : (orderby)', $instance['orderby'], $this->get_field_id('orderby'), $this->get_field_name('orderby'), array('date','title'));
		$this->select(__('Order', 'COP_PDF_Attachment_Menu').' : (order)', $instance['order'], $this->get_field_id('order'), $this->get_field_name('order'), array('ASC','DESC'));
		
		// output formatting.
		$this->select(__('List Type', 'COP_PDF_Attachment_Menu').' : (list_type)', $instance['list_type'], $this->get_field_id('list_type'), $this->get_field_name('list_type'), array('ul','ol','div'));
		$this->text(__('Class', 'COP_PDF_Attachment_Menu').' : (class)', $instance['class'], $this->get_field_id('class'), $this->get_field_name('class'));
		$this->text(__('Target', 'COP_PDF_Attachment_Menu').' : (target)', $instance['target'], $this->get_field_id('target'), $this->get_field_name('target'));
		
		$this->checkbox(__('Show Excerpts', 'COP_PDF_Attachment_Menu').' : (show_excerpts)', 'show_excerpts', $instance['show_excerpts']);
		$this->checkbox(__('Show Description', 'COP_PDF_Attachment_Menu').' : (show_description)', 'show_description', $instance['show_description']);
		
		$this->text(__('Post Status', 'COP_PDF_Attachment_Menu').' : (post_status)', $instance['post_status'], $this->get_field_id('post_status'), $this->get_field_name('post_status'));
		$this->text(__('Post Parent ID', 'COP_PDF_Attachment_Menu').' : (post_parent)', $instance['post_parent'], $this->get_field_id('post_parent'), $this->get_field_name('post_parent'));
		$this->comment(__('defaults to the current post.', 'COP_PDF_Attachment_Menu'));

		$this->checkbox(__('Force Download', 'COP_PDF_Attachment_Menu'), 'forcedownload', $instance['forcedownload']);
		$this->checkbox(__('For All Types', 'COP_PDF_Attachment_Menu'), 'forcealltypes', $instance['forcealltypes']);
		$this->select(__('For Type', 'COP_PDF_Attachment_Menu').' : (download)', $instance['download'], $this->get_field_id('download'), $this->get_field_name('download'), get_allowed_mime_types() );
		
		echo '<a style="margin: 12px 0px; text-align: center; text-decoration: none; display: block; padding: 6px 12px; background: yellow; color: #333; border: 2px solid black; font-weight: bold;" ';
		echo 'target="_blank" href="http://www.agreenweb.com/wordpress/cop-pdf-attachment-menu/cop-pdf-attachment-menu-donations/">Support &gt;</a>';

		parent::form($instance);
    }	
} // class HP_FB_Like_Widget

$plugin_dir = basename( dirname( __FILE__ ) );
load_plugin_textdomain( 'cop-pdf-attachment-menu', null, $plugin_dir );