<?php
/**
 * Spaghetti
 *
 * A small app starter framework build with MVC Pull pattern in mind
 *
 * @package		Spaghetti
 * @author		Andrei Draganescu
 * @link		http://andreidraganescu.info/_spaghetti
 * @since		Version 1.0
 */

// ------------------------------------------------------------------------

/**
 * The application controller
 *
 * In spaghetti there is only one controller that handless all
 * application requests. It also handles routing, a few helper methods, 
 * events, in app messaging, logging and the handshake between models and 
 * the database layer.
 *
 * @package		Spaghetti
 * @category	Superobject
 * @author		Andrei Draganescu
 * @link		http://andreidraganescu.info/spaghetti/controller_methods.html
 */
class the
{
	// singleton is the state of the app
	public static $instance;
	// configuration array is accessed by the magic __get and __set
	var $config = array();
	// the file where the application is started from
	// it may be blank if htaccess is used to redirect/rewrite requests 
	var $index_file = "index.php";
	// change this to true to enable index file less uris
	var $rewrite = false;
	// http or https
	var $protocol = "";
	// the current uri being executed
	var $uri_string = "";
	// if detected __ its used to redirect to physical directories
	var $pad_uri = "";
	// the uri with a slash
	var $link_uri = "";
	// the host part including the appdir
	var $base_uri = "";
	// an array with the uri_sting exploded by slash 
	var $uri_segments = array(); 
	// the views folder
	var $theme = "";
	// the default template to load when no rule is set in the index file
	var $default = "";
	// associations between uri segments and template files
	var $uri_templates = array(); 
	// the models detected in the template
	var $models = array();
	// the blocks that can be accessed from some other template
	var $dry = array();
	// <!-- print.model.method -->
	var $models_methods_print = array();
	// <!-- render.model.method -->
	var $models_methods_render = array();
	// <!-- render.model.method -->CURRENT BLOCK<!-- /render.model.method -->
	var $current_block = "";
	// the current model being called
	public static $model = "";
	// the results of replacing the values delivered by the model in the
	// $current_block template
	var $render_results = array();
	// log events, querries and their execution time and custom messages in a pageBehind
	var $log = false;
	// log events, querries and their execution time and custom messages in a long file
	var $track = false;
	// measure querries' execution time in the pageBehind or the long log file
	var $profile = false;
	// instances of loaded models
	var $objects = array();
	// database conections
	var $connections = array();
	// the connected database
	var $database = "";
	// assumes html but can be set
	var $tpl_file_extension = 'html';
	// the key that builds spaghetti uris from the template
	var $tpl_uri = 'su';
	// replacements are blocks in the template that are completely replaced at app init
	// this is for emergency situations when a proper model and template update is not an option
	var $replace = array();
	// raw template
	var $template_data = "";
	// the result of all the work
	var $output = "";	
	// servers where the app may run
	// these determine time zone,logging prefferences and also can provide a switch
	// in models if needed
	var $servers = array();
	// the currently used server
	var $current_server = '';
	//the install token triggers the model install routine
	// this is a 'secret' keyword and should be set to something loooong
	var $install_token = null;
	// this array holds the registered models and methods for all events
	// that may be triggered
	var $events = array();
	// logs each event if logging is enabled
	// expecially useful to track execution of the application for debugging
	var $debug_events = false;
	// prints the log at the end of the output
	var $lazy_debug = false;
	// hold the logged messages to print them at the end of the output
	var $inline_debug = array(); 
	// the currently dispatched event during its execution
	// which is the call of the registered listeneres (registered models' methods)
	var $current_event = '';


	/**
	 * Constructor
	 *
	 * This is the singleton instancing method of the application
	 * Use $app = the::app(); to get an updated instance of the application
	 * 
	 * @return	the
	 */
	public static function app($init=false)
	{
		if (!self::$instance)
		{
			// the BASE constant is essential for all the file
			// operation the application does
			// it is set here instead of setup 
			// so we can have early logging
			$cwd = explode(DIRECTORY_SEPARATOR, __FILE__);
			unset($cwd[count($cwd)-1]);
			$cwd = implode('/', $cwd);
			define('BASE', $cwd.'/');
			// this is a straightforward way to have sessions always on
			session_start();
			// sets the new application object on first run
			//if the app is extended dont instantiate the
			//if($init === true) return;
			//else go ahead
			self::$instance = new the();
		}
		return self::$instance;
	}
		
	/**
	 * Setup
	 *
	 * Creates the environment for the application to run smoothly
	 * 
	 *
	 * @access	private
	 * @return	void
	 */
	function setup()
	{
		$this->protocol = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '');

		preg_match("|([a-z,A-Z,_,\.]*)\.php|", $_SERVER["SCRIPT_NAME"], $matches);
		$index_file = $matches[0];
		
		
		$this->uri_segments =
			explode("/",str_replace($_SERVER['SCRIPT_NAME'],"", $_SERVER["REQUEST_URI"]));
		$this->base_uri = $this->protocol.'://'.$_SERVER['HTTP_HOST'] . 
						  str_replace($index_file,'',$_SERVER['SCRIPT_NAME']);
		$this->uri_string = $this->base_uri.implode("/", $this->uri_segments);

		// we use this to construct correct links automagically
		// and to respect rewrite
		if ($this->rewrite === true)
			$this->link_uri = $this->base_uri;
		else if ($this->index_file != '')
			$this->link_uri = $this->base_uri.$this->index_file."/";
		else
			$this->link_uri = $this->base_uri;
		
		// removes the index file part from the segments array for easier access
		if(array_key_exists(0, $this->uri_segments) && $this->index_file != '')
			array_shift($this->uri_segments);
		if(count($this->uri_segments)==1 && $this->uri_segments[0] == '')
			$this->uri_segments = array();
			
		// sets the global pereferences based on the currently running server
		foreach($this->servers as $server=>$prefs)
		{
			if(strpos($this->uri_string, $server) === false) continue;
			if(strpos($prefs['log'],'no-') === false) $this->log = true;
			if(strpos($prefs['track'],'no-') === false) $this->track = true;
			if(strpos($prefs['profile'],'no-') === false) $this->profile = true;
			if(strpos($prefs['debug'],'no-') === false) $this->debug_events = true;
			if($prefs['lazy'] == 'lazy') $this->lazy_debug = true;
			$this->current_server = $server;
		}
		// we'll use the application log to log php errors too
		// TODO: catch fatal errors
		set_error_handler(array("the","error_log"));

	}

	/**
	 * Run the application
	 *
	 * This method sequentially executed all the controller actions
	 * that determine what the application must to to wrap up successfully
	 *
	 * @access	public
	 * @return	void
	 */
	function run()
	{
		// this is an event used to halt the system if some serious thing is missing
		if($this->dispatch('app_init', false) === false) exit;
		
		// if command line call exec and exit
		$this->cli();
				
		// setup the application
		$this->setup(); 
		// enable logging, profiling and tracking according
		// to application pereferences
		$this->log(true);

		$this->dispatch('before_run');
		
		// load the template according to the current URI
		$this->load();
		// call the rendering models' methods
		// and inject the resultsin the template
		$this->_render();
		// call the printing models' methods
		// and inject the results in the template
		$this->_print();
		// remove unwanted parts from the template
		$this->_remove();

		$this->dispatch('before_output');

		// the application's result is sent
		if($this->lazy_debug === true)
			$this->output = $this->output . '<h1 style="padding-top:200px; clear:both;float:none;border-bottom: 1px solid black;">Lazy debug</h1>' . implode('<br/>', $this->inline_debug);

		echo $this->output;

		$this->dispatch('after_output');
	}

	/**
	 * Silently run the application
	 *
	 * This method sequentially executed all the controller actions
	 * that determine what the application must to to wrap up successfully
	 * and returns the result
	 * 
	 * @access	public
	 * @return	string $output
	 */
	function output()
	{
		$this->load();
		$this->_render();
		$this->_print();
		$this->_remove();
		return $this->output;
	}

	/**
	 * Get Param from URI
	 *
	 * Since uri segments are passed as values separated by a slash
	 * one could easly pass in key value pairs separated by slashes
	 * for example /name/andrew is param("name") is andrew
	 * the second argument is returned if the param is not found
	 *
	 * @access	public
	 * @param string $name
	 * @param mixed $v
	 * @return	string $ret
	 */
	function param($name, $v = false)
	{
		$value=false;
		if(in_array($name, $this->uri_segments))
			$value = $this->uri_segments[array_search($name, $this->uri_segments) + 1];
		(!$value) ? $ret = $v : $ret = $value;
		return $ret;
	}

	/**
	 * Read a file from the theme folder
	 *
	 * @access	public
	 * @param string $file
	 * @return string file contents
	 */
	function html($file)
	{
		if(file_exists(BASE.'../views/'.$this->theme.'/'.$file.'.'.$this->tpl_file_extension))
			return file_get_contents(BASE.'../views/'.$this->theme.'/'.$file.'.'.$this->tpl_file_extension);
		else
			$this->log(BASE.'../views/'.$this->theme.'/'.$file.'.'.$this->tpl_file_extension.
				" does not exist.", true);
	}

	/**
	 * Handle PHP errors in the application log
	 *
	 * @access	public
	 * @return	bool true
	 */
	static function error_log($errno, $errstr, $errfile, $errline)
	{
		$app = the::app();

		$exit = false;
		if($errno == E_ERROR || $errno == E_WARNING) $exit = true;
		$message = " $errstr in ".$errfile." at line ".$errline;

		$app->log($message, $exit);

		return true;
	}

	/**
	 * The Logging method of the application controller
	 *
	 * In spaghetti there are two options about logging: one in a long
	 * track file and one in a page accessible by the same URI with the .log
	 * extension appended.
	 *
	 * @access	public
	 * @param string $message
	 * @param bool $exit
	 * @return	bool
	 */
	function log($message = false, $exit = false)
	{
		// if the log is not writable we cannot continue
		if(!is_writable(BASE.'../logs')) spa('Please make the logging directory writable.');
		// logging is kept separate for each server the app runs on
		$folder = BASE.'../logs/'.str_replace(array("/",":","@"), "_", $this->current_server).'/';
		if(!is_dir($folder)) mkdir($folder);
		// hold the messages to print after the output for a lazy mood
		if($this->lazy_debug === true)
			$this->inline_debug[] = $message."<br/>\n";

		// if the tracking is enabled the message is simply appended to the long track file
		// TODO: add a time and a date to the log message
		if($this->track !== false && $message !== true)
			file_put_contents($folder.str_replace(array("/",":","@"), "_",$this->current_server).'.log', strip_tags($message)."\n", FILE_APPEND | LOCK_EX);
		// if logging is disabled the function will do nothing
		if(!$this->log) return false;
		// WTF!?
		if($message == false && strpos($this->uri_string, '.log') === false) return false;		
		// find the exact location of the log file and store it in the application object
		$logfile = str_replace("_.", ".", 
									$folder.str_replace(array("/",":","?","="),"_",$this->uri_string).'.html');
		$this->current_log = $logfile;
		// clean the log file name
		if(strpos($this->uri_string, '/.log') !== false) $logfile = str_replace("_.log",'',$logfile);
		if(strpos($this->uri_string, '.log') !== false) $logfile = str_replace(".log",'',$logfile);
		// this is executed at runtime initialization to clear the page behind log
		if(strpos($this->uri_string, '.log') === false && $message === true)
		{
			file_put_contents($logfile, '');
			$this->dispatch('clean_log_file', false);
			return;
		}
		// if the log is requested it outputed on screen and the application ends
		if(file_exists($logfile) && strpos($this->uri_string, '.log') !== false)
			// this event can stop log file reading for security or other reasons
			if($this->dispatch('read_log_file', false))		
				if(file_exists($logfile) && strpos($this->uri_string, '.log') !== false)
					exit(file_get_contents($logfile));
			else
				return false;
		// if the file doesnt exits but it is requested
		if(!file_exists($logfile) && strpos($this->uri_string, '.log') !== false)
			exit('Log file doesnt exist');

		$this->dispatch('write_log_file', false);
		// write the page behind log
		if($message)
			file_put_contents($logfile, $message."<br/>\n", FILE_APPEND | LOCK_EX);
		
		// this will be shown if the error is fatal for the application
		if($exit) exit("Darn. Check the log.");
		// otherwise the application moves on
		return true;

	}

	function observe($event, $model, $method)
	{
		$this->events[$event][] = array($model, $method);
	}

	function dispatch($event, $log = true)
	{
		$this->current_event = $event;

		if($this->debug_events == true && $log == true)
			$this->log("EVENT: ".$event);

		if(!is_array($this->events))
			return true;

		if(array_key_exists($event, $this->events))
		{
			// so we can trigger multiple actions on the same event
			foreach ($this->events[$event] as $action) {
				$model = $action[0];
				$method = $action[1];
				if(array_key_exists($model, $this->objects))
				{ 
					$object = $this->objects[$model];
					return $object->$method($event);
				}
				else
				{
					if(!$this->model($model))
						echo '<!-- missing_model_'.$model.' -->';
					$object = $this->objects[$model];
					return $object->$method($event);
				}
			}

		}
		return true;	
	}

	// adds an available database connection based on the current URI
	function connection($host, $dbhost, $database, $user, $password)
	{
		$this->connections[$host] = array($dbhost, $database, $user, $password);
	}

	// adds a server where the app may run
	function server($name, $log='log', $track='no-track', $profile='profile', $debug='no-debug', $tz = 'America/New_York', $lazy = 'not')
	{
		$this->servers[$name] = array("log"=>$log,"track"=>$track,"profile"=>$profile,"debug"=>$debug, "lazy"=>$lazy);
		if($tz == null) $tz = 'America/New_York';
		date_default_timezone_set($tz);
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
		$res = preg_match_all('/<!-- ((print|render)\.(([a-z,_,-,0-9]*)\.([a-z,A-Z,0-9_,\.\-\(\)\'\"\s]*))) -->/', $this->output, $methodstarts);
		// we need to load these models
		$this->models = array_unique($methodstarts[4]);

		// categorize each method call
		foreach ($methodstarts[2] as $k=>$v) {
			if($v == 'render')
				$this->models_methods_render[] = array($methodstarts[4][$k],$methodstarts[5][$k]);
			if($v == 'print')
				$this->models_methods_print[] = array($methodstarts[4][$k],$methodstarts[5][$k]);
		}

		/* i do this to have the deepest nested executed first */
		$this->models_methods_render = array_reverse($this->models_methods_render);
		$this->models_methods_print = array_reverse($this->models_methods_print);
		(($tpl_path = explode("/", $file)) && (count($tpl_path) == 1)) ?
			$tpl_folder = '' : $tpl_folder = array_pop($tpl_path) . '/';

		if(stripos($this->output,'<base') === false)
			$base = "<base href='".$this->base_uri."views/".$this->theme . "/" . $tpl_folder .
				"' />\n<script type='text/javascript'>var BASE = '".$this->link_uri."'</script>";
		else
			$base = "<script type='text/javascript'>var BASE = '".$this->link_uri."'</script>";
			
		$this->output = str_replace('<head>', "<head>\n".$base, $this->output);

		//add the page's javascript
		$scripts = "";
		if(is_dir(BASE.'../javascript/'.$this->theme.'/all'))
		{
			$m = opendir(BASE.'../javascript/'.$this->theme.'/all');
			while ($script = readdir($m))
				if($script != "." && $script != ".." && strpos($script, "json") === false && strpos($script, "js") !== false)
					$scripts .= "<script type='text/javascript'
						src='".$this->base_uri."javascript/".
						$this->theme."/all/".$script."'></script>\n";
		}


		if($file == $this->default)
			$file = "default";

		if(is_dir(BASE.'../javascript/'.$this->theme.'/'.$file))
		{
			$m = opendir(BASE.'../javascript/'.$this->theme.'/'.$file);
			while ($script = readdir($m))
				if($script != "." && $script != ".." && strpos($script, "json") === false && strpos($script, "js") !== false)
					$scripts .= "<script type='text/javascript'
						src='".$this->base_uri."javascript/".
						$this->theme."/".$file."/".$script."'></script>\n";
		}

		$this->output = str_replace('</body>', $scripts."\n</body>\n", $this->output);

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
			self::$model = $model;

			if($pos1 === false) continue;

			$this->current_block = substr($this->output, $pos1+strlen($start), $pos2 - 2*strlen($end));
			$render_template = substr($this->output, $pos1+strlen($start), $pos2 - 2*strlen($end) + 1);

			$test = explode("(", $method);

			if($model == 'session')
			{
				if(array_key_exists($method, $_SESSION))
					$this->output = substr_replace($this->output, $_SESSION[$method], $pos1, $pos2);
				else
					$this->output = substr_replace($this->output, "", $pos1, $pos2);
				continue;
			}

			// @TODO implement else
			if($model == 'if')
			{
				if($this->$method === true) 
					$this->output = substr_replace($this->output, $render_template, $pos1, $pos2);
				else
					$this->output = substr_replace($this->output, '', $pos1, $pos2);
				continue;
			}

			if(!array_key_exists($model, $this->objects))
				continue;

			$object = $this->objects[$model];
			if(!is_callable(array($object, $test[0])))
			{	/*$this->output = substr_replace($this->output, "missing_".$model."_".$method, $pos1, $pos2);
				continue;*/
				$object = the::database();
				$querry = str_replace("fetch_", "", $test[0]);
				if(!array_key_exists($querry, $object->querries[$model]))
				{	
					$this->output = substr_replace($this->output, $model."_".$method." not implemented", $pos1, $pos2);
					continue;
				}
			}

			if(strpos($method, "(") === false)
				$data = $object->$method();
			else
				eval('$data = $object->'.$method.';');

			$this->dispatch('executed_'.$model."_".$method);

			if($data === false)
				$this->output = substr_replace($this->output, $render_template, $pos1, $pos2);
			else
				$this->output = substr_replace($this->output, $data, $pos1, $pos2);

			unset($object);
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

			if(!file_exists(BASE.'/../views/'.$this->theme.'/'.$file.".html"))
				$data = "<!-- template not found -->";
			else
			{
				if(!array_key_exists($file,$loaded_files))
					$loaded_files[$file] = file_get_contents(BASE.'../views/'.$this->theme.'/'.$file.".html");

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
			self::$model = $model;

			$this->current_block = substr($this->output, $pos1+strlen($start), $pos2 - 2*strlen($end));

			$test = explode("(", $method);

			if(!array_key_exists($model, $this->objects))
				continue;

			$object = $this->objects[$model];
			if(!is_callable(array($object, $test[0])))
			{	/*$this->output = substr_replace($this->output, "missing_".$model."_".$method, $pos1, $pos2);
				continue;*/
				$object = the::database();
				$querry = str_replace("fetch_", "", $test[0]);
				if(!array_key_exists($querry, $object->querries[$model]))
				{	
					$this->output = substr_replace($this->output, $model."_".$method." not implemented", $pos1, $pos2);
					continue;
				}
			}
			$this->dispatch('executing_'.$model."_".$method);

			if(strpos($method, "(") === false)
				$data_arr = $object->$method();
			else
				if(@eval('$data_arr = $object->'.$method.';') === false)
					$this->log("Malformed tag ".htmlentities($start)." !", true);

			$this->dispatch('executed_'.$model."_".$method);


			// we need to march data points into this entry
			$render_template = substr($this->output, $pos1+strlen($start), $pos2 - 2*strlen($end)+1);
			$res = preg_match_all('/<!-- print\.([a-z,A-Z,_,-]*) -->/', $render_template, $datastarts);

			$rendered_data = "";

			if($data_arr === false)
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
				if(!is_array($data))
					continue;

				$rendered_tpl = $render_template;
				foreach ($datastarts[0] as $key => $value) {					
					$start = $value;
					$end = str_replace("<!-- ", "<!-- /", $value);
					$rpos1 = strpos($rendered_tpl, $start);
					$rpos2 = strpos($rendered_tpl, $end) - $rpos1 + strlen($end);

					$current_item = substr($rendered_tpl, $rpos1 + strlen($start), $rpos2 - 2*strlen($end)+1);

					if(!array_key_exists($datastarts[1][$key], $data))
						$rendered_tpl = substr_replace($rendered_tpl, "missing_".$datastarts[1][$key], $rpos1, $rpos2);
					else
					{
						if($data[$datastarts[1][$key]] === false)
							$rendered_tpl = substr_replace($rendered_tpl, $current_item, $rpos1, $rpos2);
						else
							$rendered_tpl = substr_replace($rendered_tpl, $data[$datastarts[1][$key]], $rpos1, $rpos2);
					}
				}
				$rendered_data .= "\n".$rendered_tpl;
			}

			$this->render_results[$model][$method][] = $rendered_data;

			if(!array_key_exists("__", $data_arr))
				$this->output = substr_replace($this->output, $rendered_data, $pos1, $pos2);
			else
				$this->output = substr_replace($this->output, "", $pos1, $pos2);
		}
		$this->output = preg_replace("/(href|action)=(\"|')(.*?)\?".$this->tpl_uri."=(.*?)(\"|')/", '$1="'.$this->link_uri.'$4"', $this->output);
		$this->output = str_replace($this->link_uri."__", $this->link_uri.$this->pad_uri, $this->output);
		$this->dispatch('after_render');

	}

	function render_results($model, $method, $index = 0)
	{
		if($index === false)
			return $this->render_results[$model][$method];
		else
			return $this->render_results[$model][$method][$index];
	}

	function form_state($data = null)
	{ 
		// we should remove the old values because we dont need them
		// this also removes the print statements we otherwise dont use
		$this->current_block = preg_replace('/(<input(.*?)(text|hidden)(.*?))value="(.*?)"/',
											"$1",
											$this->current_block);
		
		if($data == null) $data = $_POST;
		foreach($data as $key => $value)
		{
			if(is_array($value))
			{	
				foreach($value as $v)
				{
					$evalue = str_replace("/","\/",$v);
					$value = $v;
					
					$this->current_block = 	
					preg_replace('/<input(.*?)type="checkbox"(.*?)name="'.$key.'\[\]"(.*?)value="'.$evalue.'"/',
													'$0 checked="true"',
													$this->current_block, -1, $checkboxes);
					$this->current_block =
					preg_replace("/<select(.*?)name=\"".$key."\[\]\"(.*?)<option(.*?)value=\"".$evalue."\"/",
													"$0 selected=\"true\"",
													$this->current_block, -1, $selects);
				}
 			} else {
				$evalue = str_replace("/","\/",$value);
				$this->current_block = preg_replace('/<input(.*?)type="text"(.*?)name="'.$key.'"/',
												'$0 value="'.$value.'"',
												$this->current_block, -1, $textfields);
				
				$this->current_block = 
				preg_replace('/<input(.*?)type="radio"(.*?)name="'.$key.'\[\]"(.*?)value="'.$evalue.'"/',
													'$0 checked="true"',
													$this->current_block, -1, $radios);
				
				$this->current_block = 
				preg_replace('/<input(.*?)type="checkbox"(.*?)name="'.$key.'"(.*?)value="'.$evalue.'"/',
													'$0 checked="true"',
													$this->current_block, -1, $checkboxes);
				
				
				$this->current_block = preg_replace("/<textarea(.*?)name=\"".$key."\"(.*?)>/ims",
													"$0".$value,
													$this->current_block, -1, $textareas);
				
				$this->current_block =
				preg_replace("/<select(.*?)name=\"".$key."\"(.*?)<option(.*?)value=\"".$evalue."\"/ims",
													"$0 selected=\"true\"",
													$this->current_block, -1, $selects);
				
				$this->current_block = preg_replace('/<input(.*?)type="hidden"(.*?)name="'.$key.'"/',
													'$0 value="'.$value.'"',
													$this->current_block, -1, $hiddens);
				
				$this->current_block = preg_replace('/class="spa_'.$key.'">(.*?)<\//',
													'class="spa_'.$key.'">'.$value.'</',
													$this->current_block);
				
			}
			$totals = array_sum(compact('textfields', 'textareas', 'selects', 'radios', 'checkboxes', 'hiddens'));
			if($totals == 0)
			{
				$hidden = '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
				$this->current_block = str_replace("</form>", $hidden."\n"."</form>", $this->current_block);
			}

		}

		return $this->current_block;
	}

	static function database($model = null, $profile = false)
	{
		$instance = self::$instance;
		$database = $instance->database;
		if($model == null)
			$database->model = self::$model;
		else
			$database->model = $model;

		$database->profile = $profile;
		return $database;
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
		$this->dispatch('before_load');
		if(array_key_exists(0,$this->uri_segments))
		{
			if(strpos($this->uri_segments[0], "_") !== false)
			{
				$this->uri_segments[0] = str_replace("_","",$this->uri_segments[0]);
				$path = implode("/",$this->uri_segments);
				header("Location: ".$this->base_uri.$path);
				exit;
			}

		}

		include BASE.'database.php';
		$this->database = db::connect();

		if($this->install_token != null && preg_match("%".$this->install_token."%", $this->uri_string))
			$this->_install();

		foreach ($this->uri_templates as $key=>$assoc)
		{
			if($this->template_data != "")
				continue;
			
			if(preg_match("%".$key."%", $this->uri_string))
				$this->_parse($assoc);
		}

		if($this->template_data == "" && count($this->uri_segments) > 0)
			$this->_parse(implode("/", $this->uri_segments));
			
		if($this->template_data == "")
			$this->_parse($this->default);

		if(file_exists(BASE.'../models/sql.php'))
		{
			include BASE.'../models/sql.php';
			$this->database->querries = array_merge($this->database->querries, $querries);
		}

		foreach ($this->models as $model)
		{
			if(!$this->model($model))
				echo '<!-- missing_model_'.$model.' -->';
		}
		$this->dispatch('after_load');
	}

	function dependency($model)
	{
		if(!$this->model($model))
			die("A required model dependency is missing ". $model);
	}

	function factory($model)
	{
		if(array_key_exists($model, $this->objects))
			return $this->objects[$model];
		else
			$this->log("The required model was not loaded. 
						Either use it in the template or add it as a dependency.");
	}

	function model($model)
	{
		$has_class = true; $has_model = true;
		if(!array_key_exists($model, $this->objects))
		{
			if($model == 'session') return true;
			if($model == 'if') return true;
			if(!file_exists(BASE.'../models/'.$model.'/class.php'))
				$has_class = false;

			if(!file_exists(BASE.'../models/'.$model.'/'.$model.'.php'))
				$has_model = false;

			if(!$has_class && !$has_model)
				return false;

			if($has_class)
				include BASE.'../models/'.$model.'/class.php';

			if($has_model)
				include BASE.'../models/'.$model.'/'.$model.'.php';

			$object = new $model();
			$this->objects[$model] = $object;
		}

		if(file_exists(BASE.'../models/'.$model."/".$model."_sql.php"))
		{
			include BASE.'../models/'.$model."/".$model."_sql.php";
			//$this->database->querries = array_merge($this->database->querries, $querries);
			$this->database->querries[$model] = $querries;
		}

		if(file_exists(BASE.'../models/'.$model."/"."sql.php"))
		{
			include BASE.'../models/'.$model."/"."sql.php";
			//$this->database->querries = array_merge($this->database->querries, $querries);
			$this->database->querries[$model] = $querries;
		}

		return true;
	}

	function _install()
	{



		if(array_key_exists(1, $this->uri_segments) && $this->uri_segments[1] != "")
		{ 
			$model = $this->uri_segments[1];

			if($model == 'all')
			{
				$m = opendir(BASE.'../models');

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

	// redirect to a location within the app
	function route($location)
	{
		$p = the::app();
		header("Location: ".$p->link_uri.$location);
	}

	/* these are used for forms management and to be able to hook xss filters */

	// get a value of the $_POST array
	function post($index_name)
	{
		$this->post_pointer = $index_name;
		if(!array_key_exists($index_name, $_POST))
			return false;
		$this->dispatch("read_post_data");
		return $_POST[$index_name];
	}

	// get a value of the $_COOKIE array
	function cookie($index_name)
	{
		$this->cookie_pointer = $index_name;
		if(!array_key_exists($index_name, $_COOKIE))
			return false;
		$this->dispatch("read_cookie_data");
		return $_COOKIE[$index_name];
	}

	// get a value of the $_GET array
	function get($index_name)
	{
		$this->get_pointer = $index_name;
		if(!array_key_exists($index_name, $_GET))
			return false;
		$this->dispatch("read_get_data");
		return $_GET[$index_name];
	}
	// retrieve a portion of the $_POST array
	function post_filter()
	{
		$args = func_get_args();
		return array_intersect_key($_POST, array_flip($args));
	}
	// boolean check if there is any data in $_GET
	function no_get_data()
	{
		if(count($_GET) > 0)
			return false;
		else
			return true;
	}
	// boolean check if there is any data in $_POST
	function no_post_data()
	{
		if(count($_POST) > 0)
			return false;
		else
			return true;
	}
	
	// utility for command line apps
	function cli()
	{
		
		if (PHP_SAPI !== 'cli') return false;

		global $argv;
		
		$model = $argv[1];
		$method = $argv[2];
		$server = $argv[3];
		$args = array_slice($argv, 4);
		
		$this->uri_string = $server;
		include BASE.'database.php';
		$this->database = db::connect();
		
		$this->dependency($model);
		$class = $this->factory($model);
		eval('echo $class->'.$method.'($args[0]);');
		exit;
	}
}

// shortcut for a bad way of debugging
function spa($what)
{
	print_r($what); exit;
}
// END app class

/* End of file controller.php */
/* Location: ./dynamic/controller.php */
