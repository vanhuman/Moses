<?php

namespace Harm;

class Debug_controller
{
	/** @var \Harm\Show */
	private $show = null;
	private $output_type = 'html';
	private $output_depth = 5;
	private $output_buffer;
	private $max_string_length = 100;
	private static $backlog = array();
	/**
	 *
	 * @param mixed $string
	 */
	public function __destruct() {
		$this->finish();
	}
	public function index($args)
	{
		foreach (!is_array($args) ? array($args) : $args as $arg) {
			$this->show($arg);
		}
	}

	public function show($to_be_shown, $tags = array())
	{
		require_once HARM_START_UP_BASE_PATH . '/libraries/Show.php';

		if (!$this->show) {
			$this->show = new \Harm\Show();
			$this->show->output_type = $this->output_type;
			$this->show->max_depth = $this->output_depth;
			$this->show->max_string_length = $this->max_string_length;
		}

		if (file_exists(HARM_START_UP_FILES_PATH . '/record') && file_get_contents(HARM_START_UP_FILES_PATH . '/record') === 'ON') {
			$this->show->prepaire($to_be_shown, $tags);
		}

		return $this;
	}

	public function set_output_type($arg)
	{
		$this->output_type = $arg;
		if ($this->show) {
			$this->show->output_type = $this->output_type;
		}
		return $this;
	}

	public function set_output_depth($arg)
	{
		$this->output_depth = (int) $arg;
		if ($this->show) {
			$this->show->max_depth = $this->output_depth;
		}
		return $this;
	}

	public function set_max_string($arg)
	{
		$this->max_string_length = $arg;
		if ($this->show) {
			$this->show->max_string_length = $this->max_string_length;
		}
		return $this;
	}

	public function buffer_output()
	{
		$this->output_buffer = array();
		return $this;
	}

	public function get_output($as_string = true)
	{
		$output = $this->output_buffer;
		$this->output_buffer = null;
		if ($as_string) {
			return implode('', $output);
		}
		return $output;
	}

	public function benchmark($arg = false)
	{
		$micro_time = microtime(true);
		if (!self::$backlog) {
			self::$backlog = array(
				'start' => $micro_time,
				'last' => $micro_time,
				'per_type' => array()
			);
		}
		$back_log = debug_backtrace(false);
		if ($back_log) {
			$difference = $micro_time - self::$backlog['last'];
			self::$backlog['last'] = $micro_time;
			$back_log_text = $back_log[0]['file'].' : '.$back_log[0]['line'].'('.$arg.') time since last benchmark: '.number_format($difference, 3).' seconds<br>';
			if ($arg && is_string($arg)) {
				if (!isset(self::$backlog['per_type'][$arg])) {
					self::$backlog['per_type'][$arg] = $difference;
				} else {
					self::$backlog['per_type'][$arg] += $difference;
				}
			}
		}
		$this->show($back_log_text);
	}

	public function finish()
	{		
		if (!empty(self::$backlog['per_type'])) {
			$this->show(json_encode(self::$backlog['per_type']));
			self::$backlog = [];
		}
	}

	public function verb($incl_backtrace = false) {
		$back_log = debug_backtrace(true, $incl_backtrace ? 0 : 2);
		if (isset($back_log[1])) {
			$class = !empty($back_log[1]['class']) ? $back_log[1]['class'] : null;
			$function = !empty($back_log[1]['function']) ? $back_log[1]['function'] : null;
			if ($class) {
				$this->benchmark('verb-'.$class.'::'.$function);
				$args = [];
				if ($function && !empty($back_log[1]['args'])) {
					try {
						$reflection = new \ReflectionClass($class);
						$method = $reflection->getMethod($function);
						$meth_args = $method->getParameters();
						foreach ($back_log[1]['args'] as $i => $arg) {
							$key = isset($meth_args[$i]) ? $meth_args[$i]->getName() : 'unkown' + $i;
							$args[$key] = $arg;
						}
					} catch (ReflectionException $ex) {

					}
				}
				$backtrace = [];
				if (isset($back_log[2])) {
					for ($trace = 2; $trace < count($back_log); $trace++) {
						$bclass = !empty($back_log[$trace]['class']) ? $back_log[$trace]['class'].'::' : '';
						$bfunction = !empty($back_log[$trace]['function']) ? $back_log[$trace]['function'] : '';
						$bline = isset($back_log[$trace]['line']) ? $back_log[$trace]['line'].' ' : '';
						$bfile = isset($back_log[$trace]['file']) ? $back_log[$trace]['file'].':' : '';
						$backtrace[] = $bfile.$bline.$bclass.$bfunction;
					}
				}
				$this->show($args, $class.'::'.$function);
				if ($backtrace) {
					$this->show($backtrace, 'backtrace '.$class.'::'.$function);
				}
			} else {
				$this->show('NO CLASS FOUND');
			}
		}
	}
	/**
	 * use : std()->infect_class( <<<'INFECT'
	 *  class {}
	 * INFECT
	 * , true);
	 *
	 *
	 * @param type $class_declaration
	 * @param type $inclusive_backtrace
	 * @return type
	 */
	public function infect_class($class_declaration, $inclusive_backtrace = false)
	{		
		$function_string = 'infected_';
		$class_declaration = $this->remove_base_comments($class_declaration);

		$classes = [];

		if (!preg_match('/\s*(abstract\s+)?class\s+(\w+)([^\n]*)/', $class_declaration, $classes)) {
			eval($class_declaration);
			return;
		}
		/**
		 * decifer class
		 */
		$is_abstract = trim($classes[1]) === 'abstract';

		$class_name = $classes[2];

		$class_extention = trim(trim($classes[3]), '{');

		$parent_match = [];
		$parent_class = null;
		if (preg_match('/^\s?extends\s+([\w_]+).*/', $class_extention, $parent_match)) {
			$parent_class = $parent_match[1];
		}
		
		$function_names = [];
		if (!preg_match_all('/\s*([\w\s\&]*?)function(\s+\w*\s*)\(([^\{]*)/i', $class_declaration, $function_names)) {
			eval($class_declaration);
			return; // nothing to do
		}

		$infection_class_abstract = $is_abstract ? 'abstract' : '';
		$infection_class_name = ucfirst($function_string).'_'.$class_name;
		$infection_class = "$infection_class_abstract class $infection_class_name $class_extention";

		$functions = [];
		foreach (array_keys($function_names[1]) as $i) {
			$functions[] = [
				'old_prefix' => $function_names[1][$i],
				'old_name' => $function_names[2][$i],
				'old_param' => trim(trim($function_names[3][$i]), ')')				
			];
		}

		$infection_declaration = $infection_class.' { '
			. 'protected static $inclusive_backtrace = '.($inclusive_backtrace ? 'true' : 'false').';';
		
		$infection_trigger =
			"\n std()->verb(".$infection_class_name.'::$inclusive_backtrace'."); "
			. "\n ".$infection_class_name.'::$inclusive_backtrace = false;';

		$infection_clean_up_evidence = "\n ".$infection_class_name.'::$inclusive_backtrace'." = ".($inclusive_backtrace ? 'true' : 'false').";";

		foreach ($functions as $function) {
			if (trim($function['old_name']) === '__construct') {
				$construct_pos = strpos($class_declaration, '__construct');
				$reading = 1000;
				$bracket = null;
				$construct_found = false;
				$construct_trigger = null;
				while($reading) {
					$char = substr($class_declaration,$construct_pos, 1);
					if ($char === '{') {
						$bracket = $bracket ? $bracket + 1 : 0;
					} elseif ($char === '}') {
						$bracket--;
					} elseif ($char === 'p') {
						// parent::__construct 19
						$posible_parent_call = substr($class_declaration, $construct_pos, 19);
						if ($posible_parent_call === 'parent::__construct') {
							$construct_found = true;
						}						
					} elseif ($construct_found && $char === '(') {
						$construct_trigger = '';
					} elseif ($construct_found && $char === ')') {
						break;
					} elseif ($construct_found && !is_null($construct_trigger)) {
						$construct_trigger .= $char;
					}
					$construct_pos++;
					$reading--;
				}
				if ($construct_found) {
					$infection_declaration .= ' public function __construct('.$construct_trigger.') { parent::__construct('.$construct_trigger.');} ';
				}
			} else {
				$new_name = $function_string.trim($function['old_name']);
				$is_static = strpos($function['old_prefix'], 'static') !== false;
				$infection_declaration .= "\n {$function['old_prefix']} function {$function['old_name']} ({$function['old_param']}) {"
					. $infection_trigger
					."\n".'$return_value'." = ".$this->create_function_call($new_name, 'func_get_args()', $is_static ? $class_name : false)
					. "\n std()->show(".'$return_value,"Return of: '.$function['old_name'].'");'
					. "\n return ".'$return_value;'
					. $infection_clean_up_evidence.'}';

				$class_declaration = preg_replace('/function\s+'.trim($function['old_name']).'/', 'function '.$new_name, $class_declaration);
			}

		}

		$infection_declaration .= '}';
		$infection_declaration_no_private = str_replace('private', 'protected', $infection_declaration);
		eval($infection_declaration_no_private);

		$class_declaration_no_private = str_replace('private', 'protected', $class_declaration);
		if ($parent_class) {
			$class_declaration_no_private = preg_replace('/parent::(?!__construct)/', $parent_class.'::', $class_declaration_no_private);
		}
		$class_declaration_infected = preg_replace('/class\s+'.$class_name.'[^{]*/', 'class '.$class_name.' extends '.$infection_class_name, $class_declaration_no_private);

		$use_declarations = [];
		if (preg_match('/\s* use ([\w\s\\\_]+);/', $class_declaration_infected, $use_declarations)) {
			
		}
		eval($class_declaration_infected);
	}

	private function create_function_call($function_name, $param_string, $static_so_class_name = null) {
		if ($static_so_class_name) {
			return "forward_static_call_array(['$static_so_class_name', '$function_name'], $param_string);";
		} else {
			return "call_user_func_array([".'$this'.", '$function_name'], $param_string);";
		}
	}

	private function extract_params($string) {
		$string_without_brace = trim($string, '()');
		$explode = explode(',', $string_without_brace);
		foreach ($explode as $i => $param) {
			$explode[$i] = preg_replace('/^.*?(\$[^\s]+).*/', '$1', $param);
		}
		return $explode;
	}

	private function remove_base_comments($string) {
		$string = preg_replace('/\n\s*\/\/[^\n]*/', "\n", $string);
		$string = preg_replace('/\/\*[^*(?=\/)]*\//', "\n", $string);
		return $string;
	}



}






