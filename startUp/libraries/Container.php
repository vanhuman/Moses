<?php
namespace Harm;

require_once HARM_START_UP_BASE_PATH . '/libraries/export.php';

ini_set('xdebug.max_nesting_level', 1000);


class Container
{
        const IS_DUBLICATE_ARRAY_KEY = '88u484uhgasj4849__+DS:{S"@~SDFE#DS$S';
    
	static $known_types = array(
		'boolean',
		'integer',
		'double',
		'string',
		'array',
		'object',
		'resource',
		'NULL',
		'unknown type'
	);
	/**
	 *
	 * @var \Harm\All_type[]
	 */
	static $elements = array();
	static $max_depth = 1;        
	private $main_element_id = null;
	/**
	 * @var \stdClass
	 */
	private $reference_objects;
	protected static $known_objects = [];

	public function __construct($max_depth = 1) {
		self::$max_depth = $max_depth;
		$this->add_to_elements($this->get_container_id(), $this);
		$this->reference_objects = new \stdClass();
		// $this->reference_objects->set = [];
	}

	public function set_var($var)
	{
		$type = gettype($var);
		if (in_array($type, self::$known_types)) {
			$type_object_name = '\Harm\\'.$type.'_type';
			$type_object = new $type_object_name($this->get_new_id(), $this->get_container_id());
			$this->add_to_elements($type_object->get_id()->value, $type_object);
			$type_object->set_var($var);
			$this->main_element_id = $type_object->get_id()->value;
		}
	}

	public function get_object_allready_processed_reference( $object, $object_token ) {
		if ( ! is_object($object)) {
			return;
		}
		$object_hash = spl_object_hash($object);
		if (!empty(self::$known_objects[$object_hash][$object_token])) {
			return self::$known_objects[$object_hash][$object_token];
		}
		// return null;
	}

	public function set_object_as_allready_processed( $object, $object_hash, $reference, $object_token) {
		if ( ! is_object($object)) {
			return;
		}
		self::$known_objects[$object_hash][$object_token] = $reference;
	}

	public final function add_to_elements($key, $value) {
		self::$elements[$this->get_container_id()][$key] = $value;
	}

	public final function get_from_elements($key) {
		if (isset(self::$elements[$this->get_container_id()][$key])) {
			return self::$elements[$this->get_container_id()][$key];
		}
		$unkown = new unknown_type_type($this->get_new_id(), $this->get_container_id());
		$unkown->set_var('Container::get_from_elements() = null');
		return $unkown;
	}

	public final function get_new_id() {
		if (!isset(self::$elements[$this->get_container_id()])) {
			self::$elements[$this->get_container_id()] = [];
			return 0;
		}
		return count(self::$elements[$this->get_container_id()]);
	}

	public final function add_to_reference_objects( $key, $object) {
		if ($object instanceof Reference || $object instanceof Export) {
			$this->get_from_elements($this->get_container_id())->reference_objects->$key = $object;
		}
	}

	/**
	 *
	 * @param type $var
	 * @return \Harm\All_type
	 */
	protected function get_new_object($var, $depth)
	{
		$type = gettype($var);
		if (in_array($type, self::$known_types)) {
                        $type = $type === 'unknown type' ? 'unknown_type' : $type;
			$type_object_name = '\Harm\\'.$type.'_type';
			$type_object = new $type_object_name($this->get_new_id(), $this->get_container_id());
			$this->add_to_elements($type_object->get_id()->value, $type_object);
			$type_object->set_var($var, $depth);
			return $type_object;
		}
	}
	protected function get_container_id() {
		return spl_object_hash($this);
	}
	/**
	 *
	 * @return \Harm\All_type[]
	 */
	public function get_elements()
	{
		return self::$elements;
	}

	public function construct_export()
	{
		if (is_null($this->main_element_id)) {
			return;
		}

		$export = new \Harm\Main_export();
		$export->main_object = $this->get_from_elements($this->main_element_id);
		$export->reference_objects = $this->reference_objects;

		return $export;
	}
}

class Container_identifier_8394837
{
	private static $main = null;
	public $container_id;
	public $export_id;
	public $value = null;

	public function __construct($id, $container_id) {
		if (is_null(self::$main)) {
			self::$main = getmypid();
		}
		$this->export_id = str_replace('.', '-', (string) microtime(true)).'_'.self::$main.'-'.$container_id.'-'.$id;
		$this->value = $id;
		$this->container_id = $container_id;
	}
}

class All_type extends Container
{
	protected $value = null;
	private $id = null;
	protected $simple = true;
	protected $short = '-';
	protected $reference_to = null;

	public function __construct( $id, $container_id)
	{
		$this->id = new \Harm\Container_identifier_8394837($id, $container_id);
	}

	public function set_var($var)
	{
		$this->value = $var;
		return $this->id->value;
	}
	/**
	 *
	 * @return Container_identifier_8394837
	 */
	public function get_id()
	{
		return $this->id;
	}

	protected function get_container_id() {
		return $this->id->container_id;
	}

	public function is_simple()
	{
		return $this->simple;
	}

	public function create_html_value($before, $after)
	{
		$this->html_value = $before.$this->value.$after;

	}

	public function get_short()
	{
		return $this->short;
	}

	public function construct_export() {
		return var_export($this->value, true);
	}

	public function is_reference_to() {
		return $this->reference_to;
	}
}

class boolean_type extends All_type
{
	protected $short = 'bool';

	public function construct_export() {
		return $this->value;
	}
}

class integer_type extends All_type
{
	protected $short = 'int';

	public function construct_export() {
		return $this->value;
	}
}

class double_type extends All_type
{
	protected $short = 'float';

	public function construct_export() {
		return $this->value;
	}
}

class string_type extends All_type
{
	protected $short = 'string';

	public function construct_export() {
		return $this->value;
	}
}

class resource_type extends All_type
{
	protected $short = 'resource';

	public function construct_export() {
		return '\Resource';
	}

}

class NULL_type extends All_type
{
	protected $short = 'null';

	public function construct_export() {
		return $this->value;
	}
}

class unknown_type_type extends All_type
{
	protected $short = 'unkown';
}

class array_type extends All_type
{
	protected $short = 'array';        

	public function set_var($array, $depth = null)
	{
		if ($depth === null) {
			$depth = self::$max_depth;
		} elseif (!$depth) {
			// this array is out of depth
			$this->value = null;
			return $this->get_id()->value;
		}
		$depth--;
		$this->value = [];
		foreach ($array as $key => $value)
		{
			$key_object = $this->get_new_object($key, $depth);
			$value_object = $this->get_new_object($value, $depth);
			$this->value[$key_object->get_id()->value] = $value_object->get_id()->value;
		}
		return $this->get_id()->value;
	}


	public function construct_export(Nesting $nesting = null)
	{
		if (is_null($nesting)) {
		    $nesting = new Nesting;
		}
		$nesting->hello($this->get_id()->value);

		$export = new \Harm\Array_export();
		$export->id = $this->get_id()->export_id;
		if (is_null($this->value)) {
			$export->out_of_depth = true;
			return $export;
		}
		foreach ($this->value as $key_id => $value_id) {
			$row = new \Harm\Array_row_export();
			$row->key = $this->get_from_elements($key_id)->construct_export();
			$row->value = $this->get_from_elements($value_id)->construct_export($nesting->meet_next($this->get_id()->value));
			$export->value[] = $row;
		}
		$this->add_to_reference_objects($export->id, $export);
		return new \Harm\Reference($export->id);
	}
}

class object_type extends All_type
{
	protected $short = 'object';
	/**
	 *
	 * @var \ReflectionClass
	 */
	private $reflection;
	private $depth;
	private $export_reference;
	private $object_hash;

	public function set_var($object, $depth = null) {
		$object_token = $this->get_object_token($object);
		// if this object is already processed, this object will act as a reference to that object
		if ($this->reference_to = $this->get_object_allready_processed_reference($object, $object_token)) {
			return $this->get_id()->value;
		}

		$this->object_hash = spl_object_hash($object);

		if ($depth === null) {
			$depth = self::$max_depth;
		} elseif (!$depth) {
			// this array is out of depth
			$this->value = null;
			return $this->get_id()->value;
		}
		$depth--;
		$this->depth = $depth;

		if ($object instanceof \ReflectionClass) {
			$this->reflection = $object;
			$this->value = $object->_origin_object;
		} else {
			$this->reflection = new \ReflectionClass($object);
                        if ($this->reflection->isCloneable()) {
							try {
								$this->value = clone $object;								
							} catch (\Exception $ex) {
								std()->show($ex, 'Container Object_type::set_var');
								$this->value = $object;
							}
                        } else {
							$this->value = $object;
                        }
		}

		$this->set_object_as_allready_processed($object, $this->object_hash, $this->get_id()->value, $object_token);
		return $this->get_id()->value;
	}

	public function construct_export(Nesting $nesting = null)
	{
		if (is_null($nesting)) {
		    $nesting = new Nesting;
		}		
		$nesting->hello($this->get_id()->value);
		
		if ($this->export_reference) {
			return $this->export_reference;
		}

		if ($this->reference_to) {
                        if ($nesting->is_circular($this->reference_to)) {
			    $circular_ref = new Circular_reference($this->reference_to, $nesting->get_ancestors());
                            return $circular_ref;
                        }
			return $this->get_from_elements($this->reference_to)->construct_export($nesting->meet_next($this->get_id()->value));
		} else if ($nesting->get_depth() > self::$max_depth) {
		    $export = new \Harm\Object_export();
		    $export->id = $this->get_id()->export_id;
		    $export->out_of_depth = true;
		    return $export;
		}

		$export = new \Harm\Object_export();
		$export->id = $this->get_id()->export_id;

		if (!$this->value) {
			$export->out_of_depth = true;
		} else {
			$this->process_reflection_class($this->reflection, $export, $nesting);
		}
		$this->add_to_reference_objects($export->id, $export);

		$this->export_reference = new \Harm\Reference($export->id);
		return $this->export_reference;
	}

	private function process_reflection_class(\ReflectionClass $reflect, \Harm\Object_export $export, $nesting)
	{
		$ancestor_reflect = $reflect->getParentClass();
		if ($ancestor_reflect) {
			$ancestor_reflect->_origin_object = $this->value;
			if (!isset($ancestor_reflect->_origin_properties)) {
				$ancestor_reflect->_origin_properties = [];
			}
			foreach ($reflect->getProperties() as $prop) {
				$ancestor_reflect->_origin_properties[$prop->getName()] = true;
			}
			$ancestor_object = $this->get_new_object($ancestor_reflect, $this->depth);
			if ($ancestor_object) {
				$export->ancestor = $ancestor_object->construct_export($nesting->meet_previous($this->get_id()->value));
			}
		}

		if ($this->value instanceof \stdClass) {
			foreach ((array) $this->value as $key => $value) {
				$prop_export = new \Harm\Property_export();
				$prop_export->field = $key;
				$prop_export->value = $this->get_property_value_export($value, $nesting);
				$export->properties[] = $prop_export;
			}
		} else {
			foreach ($this->reflection->getProperties() ?: array() as $prop) {
				if (isset($reflect->_origin_properties[$prop->getName()])) {
					continue; // prop is overriden
				}
				$prop_export = new \Harm\Property_export();
				if ($prop->isPrivate()) {
					$prop_export->visibility = 'private';
					$prop->setAccessible(true);
				}
				if ($prop->isProtected()) {
					$prop_export->visibility = 'protected';
					$prop->setAccessible(true);
				}
				if ($prop->isStatic()) {
					$prop_export->per_instance = false;
				}
				$prop_export->field = $prop->getName();
				$prop_export->value = $this->get_property_value_export($prop->getValue($this->value), $nesting);
				$export->properties[] = $prop_export;
			}
		}

		$export->class = $reflect->getName();
		$export->file = $reflect->getFileName();
	}

	public function get_property_value_export($value, $nesting)
	{
		$prop_object = $this->get_new_object($value, $this->depth);
		return $prop_object->construct_export($nesting->meet_next($this->get_id()->value));
	}

	private function get_object_token($object) {
	    try {
		return md5(serialize($object));
	    } catch (\PDOException $ex) {
			$depth = 5;
			while ($depth) {
				try {
				return md5(json_encode($object, 0, $depth));
				} catch (\PDOException $ex) {
				$depth--;
				}
			}
			// we can not check if this object is unique
			if (isset($object->harm_container_object_token)) {
				return $object->harm_container_object_token;
			}
			$object->harm_container_object_token = uniqid();
			return $object->harm_container_object_token;
	    } catch (\Exception $ex) {
			if ($object instanceof \Closure) {
				return uniqid();
			}
			if (isset($object->harm_container_object_token)) {
				return $object->harm_container_object_token;
			}
			$object->harm_container_object_token = uniqid();
			return $object->harm_container_object_token;

		}
	}

}

class Nesting {
    private $expecting_meeting = false;
    private $last_direction = false;

    private $pointers = [];

    private $current;
    private $last;

    public function hello( $id ) {
	$new = new Nesting_pointer;
	$new->id = $id;
	if ($this->expecting_meeting = 1) {
	    $new->previous = $this->current;
	    if ($this->current) {
		$new->depth = $this->current->depth + 1;
	    }
	} elseif ($this->expecting_meeting = -1) {
	    $new->next[] = $this->current;
	}
	if ($this->current) {
	    $new->history = $this->current->history;
	    $new->history[] = $this->current->id;
	}
	$new->last_direction = $this->expecting_meeting;
	$this->last = $this->current;
	$this->current = $new;
	$this->pointers[$id] = $new;
	$this->expecting_meeting = false;
	return $this;
    }

    public function meet_previous( $id ) {
	if (! isset($this->pointers[$id])) {
	    throw new \Exception(" $id not known in Nesting");
	}
	$this->current = $this->pointers[$id];
	$this->expecting_meeting = -1;
	return $this;
    }

    public function meet_next( $id ) {
	if (! isset($this->pointers[$id])) {
	    throw new \Exception(" $id not known in Nesting");
	}
	$this->current = $this->pointers[$id];
	$this->expecting_meeting = 1;
	return $this;
    }

    public function is_circular( $id ) {
	return in_array($id, $this->current->history);
	//return $this->current->has_ancestor( $id ) || $this->current->has_descendant( $id );
    }

    public function get_ancestors($id_only = true) {
	$ancestors = [];
	$point = $this->current;
	while ($point->previous) {
	    if ($id_only) {
		array_unshift($ancestors, $point->previous->id);
	    } else {
		array_unshift($ancestors, $point->previous);
	    }
	    $point = $point->previous;
	}
	return $ancestors;
    }

    public function get_depth() {
	return $this->current->depth;
    }
}

class Nesting_pointer {
    public $id;
    public $previous;
    public $next = [];
    public $last_direction;
    public $depth = 0;
    public $history = [];

    public function has_ancestor($id) {
	return $this->previous && ($this->previous->id === $id || $this->previous->has_ancestor($id));
    }

    public function has_descendant($id) {
	foreach ($this->next as $child) {
	    if ($child->id === $id || $child->has_descendant($id)) {
		return true;
	    }
	}
	return false;
    }
}
