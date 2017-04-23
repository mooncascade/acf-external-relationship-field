# Acf External Relationship #
**Contributors:** Mati Kärner  
**Tags:** ACF, AJAX  
**Requires at least:** 3.7  
**Tested up to:** 4.7.4  
**Stable tag:** 1.2.0  
**License:** GPLv3 or later  
**License URI:** https://www.gnu.org/licenses/gpl-3.0.en.html  

Connect external entitites via ACF Relationship field

## Description ##

Connect external entitites via ACF Relationship field. Tested with ACF 5.5.10.

## Installation ##

How to install the plugin and get it working:

1. Upload folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use filters to poulate post selector with values. 

	    class DataSource {
		    protected $args;
	        
	        public function __construct($args = array()) {
	            $this->args = array_merge ( array (
	                    'name' => 'myFieldName' 
	            ), $args );
	            
	            $this->setUpHooksAndFilters ();
	        }
	        
	        protected function setUpHooksAndFilters() {
	            $filter_pre = sprintf ( 'acf/fields/%s', 'external_relationship' );
	            $filter_after = sprintf ( 'name=%s', $this->args ['name'] );
	        
	            $tag = function ($name, $after = true) use ($filter_pre, $filter_after) {
				    return sprintf ( '%s/%s%s', $filter_pre, $name, $after ? $filter_after : '' );
			    };
	
	            // Results
	            add_filter ( $tag ( 'fetch' ), array (
	                    $this,
	                    'getEntities'
	            ), 10, 2 );
	        
	            // Title for ID-s
	            add_filter ( $tag ( 'result' ), array (
	                    $this,
	                    'getTitle'
	            ), 10, 5 );
	        
	            // Tags (no filter for name/key)
	            add_filter ( $tag ( 'query_tags', false ), array (
	                    $this,
	                    'getTags'
	            ), 10, 2 );
	        
	            // Types (no filter for name/key)
	            add_filter ( $tag ( 'query_types', false ), array (
	                    $this,
	                    'getTypes'
	            ), 10, 2 );
	        }
	
	        public function doQuery($args) {
	            $entities = array ();
	
	            // Return array of objects:
	            $id = 'myID';
	            $entities [$id] = new \stdClass ();
	            $entities [$id]->ID = $id;
	            $entities [$id]->name = 'Name';
	
	            return $entities;
	        }
	
	        public function getEntities($entities, $args) {
	            $result = $this->doQuery ( $args );
	            
	            if (isset ( $args ['IDs'] )) // Resolve form ID-s
	                $entities = $result;
	            else
	                $entities [] = $result;  // Query
	            
	            return $entities;
	        }
	        
	        public function getTitle($title, $entity, $field, $post_id, $is_search) {
	            return $entity->name;
	        }
	        
	        public function getTags($tags, $field) {
	            // TODO FIXME
	            return $tags;
	        }
	        
	        public function getTypes($types, $field) {
	            // TODO FIXME
	            return $types;
	        }
	    }
	
	    // Init (e.g. In themes/plugins functions.php)
	    add_action('init', function () {
	        global $dataSource;
	        $dataSource = new DataSource();
	    })


## Frequently Asked Questions ##

### A question that someone might have ###

An answer to that question.

## Changelog ##
### 1.2 ###
* Fixes for filters governing Types/Tags

### 1.1 ###
* Fixes for various hooks
* Added usage description

### 1.0 ###
* First release
