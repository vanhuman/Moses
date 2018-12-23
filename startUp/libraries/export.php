<?php
namespace Harm;

class Export {
	public $type;
	public $id;
	public $value;
	public $parent_id;
}

class Main_export {
	/**
	 *
	 * @var All_type[]
	 */
	public $reference_objects;
	/**
	 *
	 * @var All_type
	 */
	public $main_object;
}

class Reference {
	public function __construct( $export_id )
	{
		$this->id = $export_id;
	}
	public $id;
}

class Circular_reference {
    public $circular_ref_id;
    public $parents;
    public function __construct($ref_id, $parents) {
        $this->circular_ref_id = $ref_id;
        $this->parents = $parents;
    }
}

class Container_export extends Export {
	public $out_of_depth = false;
}

class Array_export extends Container_export {
	public $type = 'array';
	public $value = [];
}

class Array_row_export {
	public $key;
	public $value;
}


class Object_export extends Container_export {
	public $type = 'object';
	public $ancestor;
	public $class;
	public $file;
	public $properties = [];
}

class Property_export {
	public $is_property = true;
	public $visibility = 'public';
	public $per_instance = true;
	public $field;
	public $value;
}