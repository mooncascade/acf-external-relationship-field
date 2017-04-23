<?php

namespace WordPress\ACF\Fields;

class ExternalRelationship extends \acf_field {

	const NAME = 'external_relationship';
	const TEXT_DOMAIN = 'acf-external-relationship';

	public function __construct() {
		$this->name = self::NAME;
		$this->label = __ ( 'External Relationship', self::TEXT_DOMAIN );
		$this->category = 'relational';
		$this->defaults = array (
				'types' => array (),
				'tags' => array (),
				'min' => 0,
				'max' => 0,
				'filters' => array (
						'search',
						'types',
						'tags'
				),
				'elements' => array (),
				'return_format' => 'object'
		);

		$this->l10n = array (
				'min' => __ ( "Minimum values reached ( {min} values )", 'acf' ),
				'max' => __ ( "Maximum values reached ( {max} values )", 'acf' ),
				'loading' => __ ( 'Loading', 'acf' ),
				'empty' => __ ( 'No matches found', 'acf' )
		);

		add_action ( sprintf ( 'wp_ajax_acf/fields/%s/query', self::NAME ), array (
				$this,
				'ajax_query'
		) );
		add_action ( sprintf ( 'wp_ajax_nopriv_acf/%s/query', self::NAME ), array (
				$this,
				'ajax_query'
		) );

		add_action ( 'admin_enqueue_scripts', array (
				$this,
				'admin_enqueue_scripts'
		), 11, 0 );

		parent::__construct ();
	}

	function admin_enqueue_scripts() {
		wp_enqueue_script ( 'external_relationship', plugin_dir_url ( __FILE__ ) . '../../js/plugin.js', array (
				'jquery',
				'underscore',
				'acf-pro-input',
				'acf-input'
		) );
	}

	function ajax_query() {
		if (! acf_verify_ajax ())
			die ();

		$response = $this->get_ajax_query ( $_POST );

		acf_send_ajax_results ( $response );
	}

	function get_grouped_results($args, $field, $post_id) {
		$args = array_merge ( array (
				'posts_per_page' => - 1,
				'paged' => 0,
				'types' => 'post',
				'orderby' => 'title',
				'order' => 'ASC'
		), $args );
			
		// Filters
		$args = apply_filters ( sprintf ( 'acf/fields/%s/query',
				self::NAME ), $args, $field, $post_id );

		$args = apply_filters ( sprintf ( 'acf/fields/%s/query/name=%s',
				self::NAME, isset($field ['_name']) ? $field ['_name'] : $field ['name'] ), $args, $field, $post_id );

		$args = apply_filters ( sprintf ( 'acf/fields/%s/query/key=%s',
				self::NAME, $field ['key'] ), $args, $field, $post_id );

		// Get
		return $this->get_entities ( $args, $field );
	}
		
	function get_entities($args, $field) {
		$result = array ();

		$result = apply_filters ( sprintf ( 'acf/fields/%s/fetch',
				self::NAME ), $result, $args );

		$result = apply_filters ( sprintf ( 'acf/fields/%s/fetch/name=%s',
				self::NAME, isset($field ['_name']) ? $field ['_name'] : $field ['name'] ), $result, $args );

		$result = apply_filters ( sprintf ( 'acf/fields/%s/fetch/key=%s',
				self::NAME, $field ['key'] ), $result, $args );

		return $result;
	}

	function get_types($field) {
		$types = array ();

		$types = apply_filters ( sprintf ( 'acf/fields/%s/query_types',
				self::NAME ), $types, $field );

		return $types;
	}

	function get_tags($field) {
		$tags = array ();

		$tags = apply_filters ( sprintf ( 'acf/fields/%s/query_tags',
				self::NAME ), $tags, $field );

		return $tags;
	}

	function get_title($entity, $field, $post_id = 0, $is_search = 0) {
		// Get id
		if (! $post_id)
			$post_id = acf_get_form_data ( 'post_id' );

		// Default
		$title = 'Untitled';
			
		// Filters
		$title = apply_filters ( sprintf ( 'acf/fields/%s/result',
				self::NAME ), $title, $entity, $field, $post_id, $is_search );

		$title = apply_filters ( sprintf ( 'acf/fields/%s/result/name=%s',
				self::NAME, isset($field ['_name']) ? $field ['_name'] : $field ['name'] ), 
				$title, $entity, $field, $post_id, $is_search );

		$title = apply_filters ( sprintf ( 'acf/fields/%s/result/key=%s',
				self::NAME, $field ['key'] ), $title, $entity, $field, $post_id, $is_search );

		return $title;
	}

	function get_post_result($id, $text) {
		// Vars
		$result = array (
				'id' => $id,
				'text' => $text
		);

		// Return
		return $result;
	}

	function get_ajax_query($options = array()) {
		$options = array_merge ( array (
				'post_id' => 0,
				's' => '',
				'field_key' => '',
				'paged' => 1,
				'types' => '',
				'tags' => ''
		), $options );

		$field = acf_get_field ( $options ['field_key'] );
		if (! $field)
			return false;

		$results = array ();
		$args = array ();
		$s = false;
		$is_search = false;

		$args ['posts_per_page'] = 20;
		$args ['paged'] = $options ['paged'];

		if ($options ['s'] !== '') {
			// Strip slashes (search may be integer)
			$s = wp_unslash ( strval ( $options ['s'] ) );
				
			// Update vars
			$args ['s'] = $s;
			$is_search = true;
		}

		// Type
		if (! empty ( $options ['types'] )) {
			$args ['types'] = acf_get_array ( $options ['types'] );
		} elseif (! empty ( $field ['types'] )) {
			$args ['types'] = acf_get_array ( $field ['types'] );
		}

		// Tags
		if (! empty ( $options ['tags'] )) {
			$args ['tags'] = $options ['tags'];
		} else if (! empty ( $field ['tags'] )) {
			$args ['tags'] = $field ['tags'];
		}

		// Get entities grouped by type
		$groups = $this->get_grouped_results ( $args, $field, $options ['post_id'] );

		// Bail early if no posts
		if (empty ( $groups ))
			return false;
					
		foreach ( array_keys ( $groups ) as $group_title ) {
				
			// Vars
			$entities = acf_extract_var ( $groups, $group_title );
				
			// Data
			$data = array (
					'text' => $group_title,
					'children' => array ()
			);
				
			// Convert post objects to post titles
			foreach ( array_keys ( $entities ) as $id ) {
				$entities [$id] = $this->get_title ( $entities [$id],
						$field, $options ['post_id'] );
			}
				
			// Order posts by search
			if ($is_search && empty ( $args ['orderby'] )) {
				$entities = acf_order_by_search ( $entities, $args ['s'] );
			}
				
			// Append to $data
			foreach ( array_keys ( $entities ) as $id ) {
				$data ['children'] [] = $this->get_post_result ( $id, $entities [$id] );
			}
				
			// Append to $results
			$results [] = $data;
		}

		// Add as optgroup or results
		if (count ( $args ['types'] ) == 1)
			$results = $results [0] ['children'];

		$response = array (
				'results' => $results,
				'limit' => $args ['posts_per_page']
		);

		return $response;
	}

	function render_field($field) {
		// Vars
		$values = array ();
		$atts = array (
				'id' => $field ['id'],
				'class' => "acf-relationship acf-external_relationship {$field['class']}",
				'data-min' => $field ['min'],
				'data-max' => $field ['max'],
				'data-s' => '',
				'data-types' => '',
				'data-tags' => '',
				'data-paged' => 1
		);

		// Lang
		if (defined ( 'ICL_LANGUAGE_CODE' ))
			$atts ['data-lang'] = ICL_LANGUAGE_CODE;
				
		// Data types
		$field ['types'] = acf_get_array ( $field ['types'] );
		$field ['tags'] = acf_get_array ( $field ['tags'] );

		// Width for select filters
		$width = array (
				'search' => 0,
				'types' => 0,
				'tags' => 0
		);

		if (! empty ( $field ['filters'] )) {
			$width = array (
					'search' => 50,
					'types' => 25,
					'tags' => 25
			);
				
			foreach ( array_keys ( $width ) as $k ) {
				if (! in_array ( $k, $field ['filters'] )) {
					$width [$k] = 0;
				}
			}
				
			// Search
			if ($width ['search'] == 0) {
				$width ['types'] = ($width ['types'] == 0) ? 0 : 50;
				$width ['tags'] = ($width ['tags'] == 0) ? 0 : 50;
			}
				
			// Type
			if ($width ['types'] == 0) {
				$width ['tags'] = ($width ['tags'] == 0) ? 0 : 50;
			}
				
			// Tags
			if ($width ['tags'] == 0) {
				$width ['types'] = ($width ['types'] == 0) ? 0 : 50;
			}
				
			// Search
			if ($width ['types'] == 0 && $width ['tags'] == 0) {
				$width ['search'] = ($width ['search'] == 0) ? 0 : 100;
			}
		}

		// Type filter
		$types = array ();

		if ($width ['types']) {
			if (! empty ( $field ['types'] )) {
				$types = $field ['types'];
			}
		}

		// Tags filter
		$tags = array ();

		if ($width ['tags'] && ! empty ( $field ['tags'] )) {
			$tags = $field ['tags'];
		}

?>
<div <?php acf_esc_attr_e($atts); ?>>

<div class="acf-hidden">
	<input type="hidden" name="<?php echo $field['name']; ?>" value="" />
</div>
	
	<?php if( $width['search'] || $width['types'] || $width['tags'] ): ?>
<div class="filters">

<ul class="acf-hl">
	
		<?php if( $width['search'] ): ?>
	<li style="width:<?php echo $width['search']; ?>%;">
	<div class="inner">
		<input class="filter" data-filter="s"
			placeholder="<?php _e("Search...",'acf'); ?>" type="text" />
	</div>
</li>
	<?php endif; ?>
	
	<?php if( $width['types'] ): ?>
	<li style="width:<?php echo $width['types']; ?>%;">
	<div class="inner">
		<select class="filter" data-filter="types">
			<option value=""><?php _e('Select type','acf'); ?></option>
			<?php foreach( $types as $k => $v ): ?>
				<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</li>
	<?php endif; ?>
	
	<?php if( $width['tags'] ): ?>
	<li style="width:<?php echo $width['tags']; ?>%;">
	<div class="inner">
		<select class="filter" data-filter="tags">
			<option value=""><?php _e('Select tags','acf'); ?></option>
			<?php foreach( $tags as $k_opt => $v_opt ): ?>
				<optgroup label="<?php echo $k_opt; ?>">
					<?php foreach( $v_opt as $k => $v ): ?>
						<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endforeach; ?>
		</select>
	</div>
</li>
	<?php endif; ?>
		</ul>

</div>
	<?php endif; ?>

<div class="selection acf-cf">

<div class="choices">

	<ul class="acf-bl list"></ul>

</div>

<div class="values">

	<ul class="acf-bl list">
		
<?php
	
		if (! empty ( $field ['value'] )) :
		
		// Get entities
		$entities = $this->get_entities ( array (
				'IDs' => $field ['value']
		), $field );

		// Set choices
		if (! empty ( $entities )) :
			foreach ( array_keys ( $entities ) as $i ) :
				
			// Vars
			$entity = $entities[$i];
?>
	<li>
		<input type="hidden"
			name="<?php echo $field['name']; ?>[]"
			value="<?php echo $entity->ID; ?>" /> 
			<span data-id="<?php echo $entity->ID; ?>" class="acf-rel-item"><?php echo $this->get_title( $entity, $field ); ?>
				<a href="#" class="acf-icon -minus small dark" data-name="remove_item"></a>
			</span>
	</li>
<?php
			endforeach;
		endif;
	endif;
?>
			
			</ul>

		</div>

	</div>

</div>
<?php
	}

	function render_field_settings($field) {
		// Vars
		$field ['min'] = empty ( $field ['min'] ) ? '' : $field ['min'];
		$field ['max'] = empty ( $field ['max'] ) ? '' : $field ['max'];
		
		// Type
		acf_render_field_setting ( $field, array (
				'label' => __ ( 'Filter by Type', self::TEXT_DOMAIN ),
				'instructions' => '',
				'type' => 'select',
				'name' => 'types',
				'choices' => $this->get_types ( $field ),
				'multiple' => 1,
				'ui' => 1,
				'allow_null' => 1,
				'placeholder' => __ ( 'All types', self::TEXT_DOMAIN ) 
		) );
		
		// Tags
		acf_render_field_setting ( $field, array (
				'label' => __ ( 'Filter by Tags', self::TEXT_DOMAIN ),
				'instructions' => '',
				'type' => 'select',
				'name' => 'tags',
				'choices' => $this->get_tags ( $field ),
				'multiple' => 1,
				'ui' => 1,
				'allow_null' => 1,
				'placeholder' => __ ( 'All tags', self::TEXT_DOMAIN ) 
		) );
		
		// Filters
		acf_render_field_setting ( $field, array (
				'label' => __ ( 'Filters', self::TEXT_DOMAIN ),
				'instructions' => '',
				'type' => 'checkbox',
				'name' => 'filters',
				'choices' => array (
						'search' => __ ( 'Search', self::TEXT_DOMAIN ),
						'types' => __ ( 'Types', self::TEXT_DOMAIN ),
						'tags' => __ ( 'Tags', self::TEXT_DOMAIN ) 
				) 
		) );
		
		// Elements
		acf_render_field_setting ( $field, array (
				'label' => __ ( 'Elements', self::TEXT_DOMAIN ),
				'instructions' => 
					__ ( 'Selected elements will be displayed in each result', 
						self::TEXT_DOMAIN ),
				'type' => 'checkbox',
				'name' => 'elements',
				'choices' => array (
						'image' => __ ( 'Image', self::TEXT_DOMAIN ) 
				) 
		) );
		
		// Min
		acf_render_field_setting ( $field, array (
				'label' => __ ( 'Minimum entities', self::TEXT_DOMAIN ),
				'instructions' => '',
				'type' => 'number',
				'name' => 'min' 
		) );
		
		// Max
		acf_render_field_setting ( $field, array (
				'label' => __ ( 'Maximum entities', self::TEXT_DOMAIN ),
				'instructions' => '',
				'type' => 'number',
				'name' => 'max' 
		) );
		
		// Return format
		acf_render_field_setting ( $field, array (
				'label' => __ ( 'Return Format', self::TEXT_DOMAIN ),
				'instructions' => '',
				'type' => 'radio',
				'name' => 'return_format',
				'choices' => array (
						'entity' => __ ( 'Entity', self::TEXT_DOMAIN ),
						'id' => __ ( 'ID', self::TEXT_DOMAIN ) 
				),
				'layout' => 'horizontal' 
		) );
	}
	
	function format_value($value, $id, $field) {
		// Bail early if no value
		if (empty ( $value )) {
			return $value;
		}
		
		// Force value to array
		$value = acf_get_array ( $value );
		
		// Load entities if needed
		if ($field ['return_format'] == 'entity') {
			
			// Get entities
			$value = $this->get_entities ( array (
					'IDs' => $field ['value']
			), $field );
		}
		
		return $value;
	}
	
	function validate_value($valid, $value, $field, $input) {
		// Default
		if (empty ( $value ) || ! is_array ( $value ))
			$value = array ();
			
		// Min
		if (count ( $value ) < $field ['min']) {
			$valid = _n ( '%s requires at least %s selection',
					'%s requires at least %s selections', $field ['min'], self::TEXT_DOMAIN );
			$valid = sprintf ( $valid, $field ['label'], $field ['min'] );
		}
		
		return $valid;
	}
	
	function update_value($value, $post_id, $field) {
		// Validate
		if (empty ( $value ))
			return $value;
			
		// Force value to array
		$value = acf_get_array ( $value );
		
		// Array
		foreach ( $value as $k => $v ) {
			// Object?
			if (is_object ( $v ) && isset ( $v->ID )) {
				$value [$k] = $v->ID;
			}
		}
		
		// Return
		return $value;
	}
}
