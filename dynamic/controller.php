<?php
// this is the it
class the
{
	// configuration
	var $config = array(); // used to set all kinds of custom data
	var $base_file = "index.php"; // the file where the app is defined
	
	// try to set it all at runtime
	var $index_file = "index.php";
	var $uri_string = "";
	var $link_uri = ""; // the segments of the URI
	var $base_uri = ""; // the segments of the URI
	var $uri_segments = array(); // these are used to set parameters via URL in any called model
	
	var $theme = ""; // the folder where the templates are
	var $default = ""; // the default template to load
	var $uri_templates = array(); // associations between uri segments and template files
	
	// template data
	var $models = array();
	var $dry = array();
	var $models_methods_print = array();
	var $models_methods_render = array();
	var $models_methods_data = array();
	var $current_block = "";
	
		
	// instances of loaded models
	var $objects = array();
	
	// database conections
	var $connections = array();
	
	// the connected database
	var $database = "";
	
	// assumes html but can be set
	var $tpl_file_extension = 'html';
	
	// replacements
	var $replace = array();
	// raw template
	var $template_data = "";
	// the result of all the work
	var $output = "";
	
	// singleton
	private static $instance;
	
	// servers where the app may run
	var $servers = array();
	
	//the install token triggers the model install routine
	var $install_token = 'install';
	
	
	// events!
	var $events = array();
	var $debug_events = false; // shows each dispatched event as a comment in the output
	
	function setup()
	{
		// find base URI
		$data = explode($this->base_file,str_replace('//','/',dirname($_SERVER['PHP_SELF']).'/'));
		$this->base_uri = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$data[0];
		
		$this->uri_string = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		
		if($this->index_file == "")
			$this->link_uri = $this->base_uri;
		else
			$this->link_uri = $this->base_uri.$this->index_file."/";
		
		$parts = explode($_SERVER['HTTP_HOST'].$data[0], $this->uri_string);
		if(array_key_exists(1, $parts))
			if($parts[1] != "")
				$this->uri_segments = explode("/", $parts[1]);
		
		if(array_key_exists(0, $this->uri_segments))
			if($this->uri_segments[0] == $this->index_file)
				array_shift($this->uri_segments);
		
		$cwd = explode(DIRECTORY_SEPARATOR, __FILE__);
		unset($cwd[count($cwd)-1]);
		$cwd = implode('/', $cwd);
		define('BASE', $cwd.'/');
		
	}
	
	function html($file)
	{
		return file_get_contents(BASE.'../static/'.$this->theme.'/'.$file.'.'.$this->tpl_file_extension);
	}
	
	function observe($event, $model, $method)
	{
		$this->events[$event][] = array($model, $method);
	}
	
	function dispatch($event)
	{
		if($this->debug_events == true)
			echo "<!-- event! ".$event." -->";
			
		if(!is_array($this->events))
			return false;
			
		if(array_key_exists($event, $this->events))
		{
			// so we can trigger multiple actions on the same event
			foreach ($this->events[$event] as $action) {
				$model = $action[0];
				$method = $action[1];
				if(array_key_exists($model, $this->objects))
				{
					$object = $this->objects[$model];
					$object->$method();
				}
				else
				{
					include BASE.'models/'.$model.'/class.php';
					$this->objects[$model] = new $model();
					$object = $this->objects[$model];
					$object->$method();
				}
			}
			
		}	
	}
	
	// adds an available database connection based on the current URI
	function connection($host, $dbhost, $database, $user, $password)
	{
		$this->connections[$host] = array($dbhost, $database, $user, $password);
	}
	
	// adds a server where the app may run
	function server($name, $type)
	{
		$this->servers[$name] = $type;
	}
	
	// associates an uri segment with a template
	function template($uri_segment, $file_name, $theme="")
	{
		if($theme == "")
		{
			$this->uri_templates[$uri_segment] = $file_name;
		}
		else
		{
			$this->uri_templates[$uri_segment] = array($theme,$file_name);
		}
	}
	
	// set data to be replaced in all templates
	function replace($what, $with, $where = ".*")
	{
		$this->replace[$where][] = array($what,$with);
	}
	
	function output()
	{
		$this->load();
		$this->_print();
		$this->_render();
		$this->_remove();
		return $this->output;
	}
	
	function run()
	{
		$this->dispatch('before_run');
		$this->setup();
		$this->load();
		$this->_print();
		$this->_render();
		$this->_remove();
		$this->dispatch('before_output');
		echo $this->output;
		$this->dispatch('after_output');
	}
	
	function _parse($file)
	{
		
		if(is_array($file))
		{
			$this->theme = $file[0];
			$file = $file[1];
		}
		$this->template_data = $this->html($file);
		
		// replacing global data
		foreach ($this->replace as $where => $replacements) {
			if(preg_match("%".$where."%", $this->uri_string))
			{
				foreach ($replacements as $value) {
					$this->template_data = str_replace($value[0], $value[1], $this->template_data);
				}
			}
		}
		
		$this->output = $this->template_data;
		
		$this->_dry();
		
		// todo:add check $res if there are no matches
		$res = preg_match_all('/<!-- ((print|render)\.(([a-z_,-]*)\.([a-z_,\-\(\)\'\"]*))) -->/', $this->output, $methodstarts);
		
		// we need to load these models
		$this->models = array_unique($methodstarts[4]);
		
		// categorize each method call
		foreach ($methodstarts[2] as $k=>$v) {
			if($v == 'render')
				$this->models_methods_render[] = array($methodstarts[4][$k],$methodstarts[5][$k]);
			if($v == 'print')
				$this->models_methods_print[] = array($methodstarts[4][$k],$methodstarts[5][$k]);
		}
		
		
		
		$base = "<base href='".$this->base_uri."static/".$this->theme."/' />";
		$this->output = str_replace('<head>', "<head>\n".$base, $this->output);
		
		
		
		$this->dispatch('template_parsed');
	}
	
		
	// print replaces a block of html with the result of the method
	function _print()
	{
		$this->dispatch('before_printing');
		foreach ($this->models_methods_print as $action) {
			$model = $action[0];
			$method = $action[1];
			$start = "<!-- print.$model.$method -->";
			$end = "<!-- /print.$model.$method -->";
			$pos1 = strpos($this->output, $start);
			$pos2 = strpos($this->output, $end) - $pos1 + strlen($end);
			
			if(!method_exists($model, $method))
			{
				$this->output = substr_replace($this->output, "missing_".$model."_".$method, $pos1, $pos2);
				continue;
			}	
			
			$object = $this->objects[$model];
			if(strpos($method, "(") === false)
				$data = $object->$method();
			else
				eval('$data = $object->'.$method.';');
			
			$this->dispatch('executed_'.$model."_".$method);
			
			if($data == false)
				$this->output = $this->output;
			else
				$this->output = substr_replace($this->output, $data, $pos1, $pos2);
			
		}
		$this->dispatch('after_printing');
	}
	
	// print replaces a block of html with the result of the method
	function _dry()
	{
		
		$this->dispatch('before_drying');
		
		// remove res comments in files
		$this->output = preg_replace('/<!-- (\/?)res\.([a-z,_,-]*) -->/', "", $this->output);
		
		$res = preg_match_all('/<!-- dry\.([a-z,_,-,\/]*)\.([a-z,_,-]*) -->/', $this->output, $datastarts);
		
		$loaded_files = array();
		foreach ($datastarts[0] as $key => $value) {					
			$start = $value;
			$end = str_replace("<!-- ", "<!-- /", $value);
			$pos1 = strpos($this->output, $start);
			$pos2 = strpos($this->output, $end) - $pos1 + strlen($end);
			
			$file = $datastarts[1][$key];
						
			if(!file_exists(BASE.'/../static/'.$this->theme.'/'.$file.".html"))
				$data = "<!-- template not found -->";
			else
			{
				if(!array_key_exists($file,$loaded_files))
					$loaded_files[$file] = file_get_contents(BASE.'../static/'.$this->theme.'/'.$file.".html");
					
				$data = $loaded_files[$file];
			}
			
			$drystart = "<!-- res.".$datastarts[2][$key]." -->";
			$dryend = "<!-- /res.".$datastarts[2][$key]." -->";
			$drypos1 = strpos($data, $drystart) + strlen($drystart);
			$drypos2 = strpos($data, $dryend) - $drypos1;
			
			$data = substr($data, $drypos1, $drypos2);
			
			$this->dispatch('dried_'.$file);
			
			$this->output = substr_replace($this->output, $data, $pos1, $pos2);
			
		}
		$this->dispatch('after_drying');
	}
	
	// render checks for a returned array, if found loops trough and, if not, replaces data with array keys
	function _render()
	{
		$this->dispatch('before_render');
		foreach ($this->models_methods_render as $action) {
			$model = $action[0];
			$method = $action[1];
			$start = "<!-- render.$model.$method -->";
			$end = "<!-- /render.$model.$method -->";
			$pos1 = strpos($this->output, $start);
			$pos2 = strpos($this->output, $end) - $pos1 + strlen($end);
			
			$this->current_block = substr($this->output, $pos1, $pos2);
			
			$test = explode("(", $method);
			
			if(!method_exists($model, $test[0]))
			{
				$this->output = substr_replace($this->output, "missing_".$model."_".$method, $pos1, $pos2);
				continue;
			}
			
			$object = $this->objects[$model];
			
			if(strpos($method, "(") === false)
				$data_arr = $object->$method();
			else
				eval('$data_arr = $object->'.$method.';');
				
			$this->dispatch('executed_'.$model."_".$method);
			
			
			// we need to march data points into this entry
			$render_template = substr($this->output, $pos1+strlen($start), $pos2 - 2*strlen($end));
			$res = preg_match_all('/<!-- print\.([a-z,_,-]*) -->/', $render_template, $datastarts);
			
			$rendered_data = "";
			
			if($data_arr == false)
			{
				$this->output = substr_replace($this->output, $render_template, $pos1, $pos2);
				continue;
			}
				
			if(is_string($data_arr))
			{
				$this->output = substr_replace($this->output, $data_arr, $pos1, $pos2);
				continue;
			}
			
			foreach($data_arr as $data)
			{
				$rendered_tpl = $render_template;
				foreach ($datastarts[0] as $key => $value) {					
					$start = $value;
					$end = str_replace("<!-- ", "<!-- /", $value);
					$rpos1 = strpos($rendered_tpl, $start);
					$rpos2 = strpos($rendered_tpl, $end) - $rpos1 + strlen($end);
					if(!array_key_exists($datastarts[1][$key], $data))
						$rendered_tpl = substr_replace($rendered_tpl, "missing_".$datastarts[1][$key], $rpos1, $rpos2);
					else
						$rendered_tpl = substr_replace($rendered_tpl, $data[$datastarts[1][$key]], $rpos1, $rpos2);
				}
				$rendered_data .= "\n".$rendered_tpl;
			}	
			$this->output = substr_replace($this->output, $rendered_data, $pos1, $pos2);
		}
		// manage relative links
		$this->output = preg_replace("/href=(\"|')(.*?)\?su=(.*?)(\"|')/", 'href="'.$this->link_uri.'$3"', $this->output);
		$this->dispatch('after_render');
		
	}
	
	static function database()
	{
		$i = self::$instance;
		return $i->database;
	}
	
	// remove deletes the not needed content
	function _remove()
	{
		$res = preg_match_all('/<!-- remove -->/', $this->output, $removesStarts);
		foreach ($removesStarts[0] as $key => $value) {
			$start = $value;
			$end = str_replace("<!-- ", "<!-- /", $value);
			$rpos1 = strpos($this->output, $start);
			$rpos2 = strpos($this->output, $end) - $rpos1 + strlen($end);
			$this->output = substr_replace($this->output, "", $rpos1, $rpos2);
		}
	}
	
	
	function load()
	{
		
		include BASE.'model.php';
		$this->database = db::connect();
		
		if(preg_match("%".$this->install_token."%", $this->uri_string))
			$this->_install();
		
		foreach ($this->uri_templates as $key=>$assoc)
		{
			if($this->template_data != "")
				continue;

			if(preg_match("%".$key."%", $this->uri_string))
				$this->_parse($assoc);
		}
		
		if($this->template_data == "")
			$this->_parse($this->default);
				
		foreach ($this->models as $model)
		{
			if(!array_key_exists($model, $this->objects))
			{
				if(!file_exists(BASE.'models/'.$model.'/class.php'))
				{
					echo '<!-- missing_model_'.$model.' -->';
					continue;
				}
				include BASE.'models/'.$model.'/class.php';
				$object = new $model();
				$this->objects[$model] = $object;
			}
			if(file_exists(BASE.'/models/'.$model."/sql.php"))
			{
				include BASE.'/models/'.$model."/sql.php";
				$this->database->querries = array_merge($this->database->querries, $querries);
			}

		}
		
		
		
	}
	
	function _install()
	{
		
		
		
		if(array_key_exists(1, $this->uri_segments) && $this->uri_segments[1] != "")
		{ 
			$model = $this->uri_segments[1];
			
			if($model == 'all')
			{
				$m = opendir(BASE.'models');

				while ($model = readdir($m))
				{
					if($model != "." && $model != "..")
					$this->database->install($model);
				}
			}
			else
			{
				$this->database->install($model);
			}
		}
		else
		{
			die('No model selected. Use `all` for first run.');
		}
		
		die("Procedure completed.");
	}
	
	public static function app()
	{
	    if (!self::$instance)
	    {
	        self::$instance = new the();
	    }
		
		return self::$instance;
	}
	
	// these are mainly used to set custom data
	public function __set($name, $value) {
        $this->config[$name] = $value;
    }

	public function __get($name) {
        if (array_key_exists($name, $this->config)) {
            return $this->config[$name];
        }
        return null;
    }

	function route($location)
	{
		$p = the::app();
		header("Location: ".$p->link_uri.$location);
	}

	// these are used for forms management and to be able to hook xss filters
	function post($index_name)
	{
		$this->post_pointer = $index_name;
		$this->dispatch("read_post_data");
		if(!array_key_exists($index_name, $_POST))
			return false;
		return $_POST[$index_name];
	}
	
	function get($index_name)
	{
		$this->get_pointer = $index_name;
		$this->dispatch("read_get_data");
		if(!array_key_exists($index_name, $_GET))
			return false;
		return $_GET[$index_name];
	}
	
	function no_get_data()
	{
		if(count($_GET) > 0)
			return false;
		else
			return true;
	}
	
	function no_post_data()
	{
		if(count($_POST) > 0)
			return false;
		else
			return true;
	}
}


?>