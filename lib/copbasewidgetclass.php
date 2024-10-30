<?php

/**
 *  widget utilily class 
 */

class COP_Widget extends WP_Widget {
	
	function text($label, $value, $field_id, $field_name){
		echo "\r\n".'<p><label for="'.$field_id.'">'.__($label).' <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.esc_attr( $value ).'" /></label></p>';
	}
	function select($label, $value, $field_id, $field_name, $options){
		echo "\r\n".'<p><label for="'.$field_id.'">'.__($label).'</label> <select class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.esc_attr($value).'">';
		foreach ($options as $option) {
			echo '<option value="' . $option . '"';
			echo (($option == $value) ? ' selected' : '');
			echo '>' . $option . '</option>';
		}
		echo '</select></label></p>';
	}
	function checkbox($label, $property, $value){		
		$ischecked = $value ? 'checked="checked"' : '';
		$field_id = $this->get_field_id($property);
		$field_name = $this->get_field_name($property);
		echo "\r\n".'<p><input type="checkbox" class="checkbox" id="'.$field_id.'" name="'.$field_name . '" ' . $ischecked . ' />&nbsp;<label for="'.$field_id.'">'.__($label).' </label></p>';
	}
	function comment($text) {
		echo "\r\n".'<p';
		echo ' style="color: #303030; font-size: 11px; font-family: georgia; padding: 6px 10px; background-color: #EDF7FF; border-bottom: 1px solid #303030; margin-top:-10px;"'; 
		echo '>' . $text . '</p>';	
	}
	function form($instance) {
		echo '<div style="float: left; padding-right: 7px;">provided by: </div>'. "\r\n";
		echo '<img src="' . WP_PLUGIN_URL . '/cop-pdf-attachment-menu/images/agreenweb.png" style="float: left;"/>';
		echo "\r\n".'<a href="http://www.agreenweb.com/" target="_blank" style="padding-left:8px; text-decoration: none;">agreenweb</a>';
	}
	
}