<?php

/**
 * Abstract class for defining custom post types.
 *
 **/
abstract class CustomPostType{
	public
		$name           = 'custom_post_type',
		$plural_name    = 'Custom Posts',
		$singular_name  = 'Custom Post',
		$add_new_item   = 'Add New Custom Post',
		$edit_item      = 'Edit Custom Post',
		$new_item       = 'New Custom Post',
		$public         = True,  # I dunno...leave it true
		$use_title      = True,  # Title field
		$use_editor     = True,  # WYSIWYG editor, post content field
		$use_revisions  = True,  # Revisions on post content and titles
		$use_thumbnails = False, # Featured images
		$use_order      = False, # Wordpress built-in order meta data
		$use_metabox    = False, # Enable if you have custom fields to display in admin
		$use_shortcode  = False, # Auto generate a shortcode for the post type
		                         # (see also objectsToHTML and toHTML methods)
		$taxonomies     = array('post_tag'),
		$built_in       = False,

		# Optional default ordering for generic shortcode if not specified by user.
		$default_orderby = null,
		$default_order   = null;


	/**
	 * Wrapper for get_posts function, that predefines post_type for this
	 * custom post type.  Any options valid in get_posts can be passed as an
	 * option array.  Returns an array of objects.
	 **/
	public function get_objects($options=array()){

		$defaults = array(
			'numberposts'   => -1,
			'orderby'       => 'title',
			'order'         => 'ASC',
			'post_type'     => $this->options('name'),
		);
		$options = array_merge($defaults, $options);
		$objects = get_posts($options);
		return $objects;
	}


	/**
	 * Similar to get_objects, but returns array of key values mapping post
	 * title to id if available, otherwise it defaults to id=>id.
	 **/
	public function get_objects_as_options($options=array()){
		$objects = $this->get_objects($options);
		$opt     = array();
		foreach($objects as $o){
			switch(True){
				case $this->options('use_title'):
					$opt[$o->post_title] = $o->ID;
					break;
				default:
					$opt[$o->ID] = $o->ID;
					break;
			}
		}
		return $opt;
	}


	/**
	 * Return the instances values defined by $key.
	 **/
	public function options($key){
		$vars = get_object_vars($this);
		return $vars[$key];
	}


	/**
	 * Additional fields on a custom post type may be defined by overriding this
	 * method on an descendant object.
	 **/
	public function fields(){
		return array();
	}


	/**
	 * Using instance variables defined, returns an array defining what this
	 * custom post type supports.
	 **/
	public function supports(){
		#Default support array
		$supports = array();
		if ($this->options('use_title')){
			$supports[] = 'title';
		}
		if ($this->options('use_order')){
			$supports[] = 'page-attributes';
		}
		if ($this->options('use_thumbnails')){
			$supports[] = 'thumbnail';
		}
		if ($this->options('use_editor')){
			$supports[] = 'editor';
		}
		if ($this->options('use_revisions')){
			$supports[] = 'revisions';
		}
		return $supports;
	}


	/**
	 * Creates labels array, defining names for admin panel.
	 **/
	public function labels(){
		return array(
			'name'          => __($this->options('plural_name')),
			'singular_name' => __($this->options('singular_name')),
			'add_new_item'  => __($this->options('add_new_item')),
			'edit_item'     => __($this->options('edit_item')),
			'new_item'      => __($this->options('new_item')),
		);
	}


	/**
	 * Creates metabox array for custom post type. Override method in
	 * descendants to add or modify metaboxes.
	 **/
	public function metabox(){
		if ($this->options('use_metabox')){
			return array(
				'id'       => $this->options('name').'_metabox',
				'title'    => __($this->options('singular_name').' Fields'),
				'page'     => $this->options('name'),
				'context'  => 'normal',
				'priority' => 'high',
				'fields'   => $this->fields(),
			);
		}
		return null;
	}


	/**
	 * Registers metaboxes defined for custom post type.
	 **/
	public function register_metaboxes(){
		if ($this->options('use_metabox')){
			$metabox = $this->metabox();
			add_meta_box(
				$metabox['id'],
				$metabox['title'],
				'show_meta_boxes',
				$metabox['page'],
				$metabox['context'],
				$metabox['priority']
			);
		}
	}


	/**
	 * Registers the custom post type and any other ancillary actions that are
	 * required for the post to function properly.
	 **/
	public function register(){
		$registration = array(
			'labels'     => $this->labels(),
			'supports'   => $this->supports(),
			'public'     => $this->options('public'),
			'taxonomies' => $this->options('taxonomies'),
			'_builtin'   => $this->options('built_in')
		);

		if ($this->options('use_order')){
			$registration = array_merge($registration, array('hierarchical' => True,));
		}

		register_post_type($this->options('name'), $registration);

		if ($this->options('use_shortcode')){
			add_shortcode($this->options('name').'-list', array($this, 'shortcode'));
		}
	}


	/**
	 * Shortcode for this custom post type.  Can be overridden for descendants.
	 * Defaults to just outputting a list of objects outputted as defined by
	 * toHTML method.
	 **/
	public function shortcode($attr){
		$default = array(
			'type' => $this->options('name'),
		);
		if (is_array($attr)){
			$attr = array_merge($default, $attr);
		}else{
			$attr = $default;
		}
		return sc_object_list($attr);
	}


	/**
	 * Handles output for a list of objects, can be overridden for descendants.
	 * If you want to override how a list of objects are outputted, override
	 * this, if you just want to override how a single object is outputted, see
	 * the toHTML method.
	 **/
	public function objectsToHTML($objects, $css_classes){
		if (count($objects) < 1){ return '';}

		$class = get_custom_post_type($objects[0]->post_type);
		$class = new $class;

		ob_start();
		?>
		<ul class="<?php if($css_classes):?><?=$css_classes?><?php else:?><?=$class->options('name')?>-list<?php endif;?>">
			<?php foreach($objects as $o):?>
			<li>
				<?=$class->toHTML($o)?>
			</li>
			<?php endforeach;?>
		</ul>
		<?php
		$html = ob_get_clean();
		return $html;
	}


	/**
	 * Outputs this item in HTML.  Can be overridden for descendants.
	 **/
	public function toHTML($object){
		$html = '<a href="'.get_permalink($object->ID).'">'.$object->post_title.'</a>';
		return $html;
	}
}

class ContactInformation extends CustomPostType {
	public
		$name           = 'contact_information',
		$plural_name    = 'Contact Information',
		$singular_name  = 'Contact Information',
		$add_new_item   = 'Add New Contact Information',
		$edit_item      = 'Edit Roam Contact Information',
		$new_item       = 'New Roam Contact Information',
		$public         = True,
		$use_editor     = False,
		$use_thumbnails = False,
		$use_order      = True,
		$use_title      = True,
		$use_metabox    = True;

	public function fields(){
		$prefix = $this->options('name').'_';
		return array(
			array(
				'name' => 'Value',
				'desc' => 'Phone Number, URL, etc. Accepts HTML.',
				'id'   => $prefix.'value',
				'type' => 'text'
			),
		);
	}
}

class Alert extends CustomPostType {
	public
		$name           = 'alert',
		$plural_name    = 'Alerts',
		$singular_name  = 'Alert',
		$add_new_item   = 'Add New Alert',
		$edit_item      = 'Edit Alert',
		$new_item       = 'New Alert',
		$public         = True,
		$use_editor     = True,
		$use_thumbnails = False,
		$use_order      = False,
		$use_title      = True,
		$use_metabox    = True,
		$taxonomies     = array();

	public function fields(){
		$prefix = $this->options('name').'_';
		return array(
			array(
				'name' => 'Alert Text',
				'desc' =>
					'The text for the alert that is displayed on www.ucf.edu.
					When automatic alert fetching is enabled, this text is
					supplied by Roam Secure.
					<br />
					Word count is determined by the Alert Text Length value
					in Theme Options.
					<br />
					<strong>This field must have some content for the alert to
					appear on www.ucf.edu. Leaving it blank will disable the alert.
					</strong>',
				'id'   => $prefix.'short',
				'type' => 'textarea'
			),
			array(
				'name' => 'Alert Call-to-Action Text',
				'desc' =>
					'Call-to-action displayed under the alert text that encourages users to click the alert.',
				'id'   => $prefix.'cta',
				'type' => 'text',
				'std' => 'More Information',
			),
			array(
				'name' => 'Alert URL',
				'desc' =>
					'Where users should be directed when this alert is clicked.
					<br>
					If this value is left blank, users will be directed to the UCF Alert homepage.',
				'id'   => $prefix.'url',
				'type' => 'text'
			),
			array(
				'name' => 'Type of Alert',
				'desc' =>
					'Specify what type of alert this is. Determines the type
					of icon that appears for the alert on www.ucf.edu.',
				'id'   => $prefix.'alert_type',
				'type' => 'select',
				'default' => 'General Notification',
				'options' => array(
					'Weather Emergency' 	=> 'weather',
					'Police Action' 		=> 'police',
					'Update' 				=> 'update',
					'Message'               => 'message'
				),
			),
			array(
				'name' => 'Alert Expiration',
				'desc' =>
					'The amount of time, in hours, before the alert is removed
					from the main alert feed and from UCF.edu.<br/>
					Default is one hour.',
				'id'   => $prefix.'expiration',
				'type' => 'text',
				'std' => '1',
			),
		);
	}
}

?>
