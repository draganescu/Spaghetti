<?php if ( ! defined('BASE')) exit('No direct script access allowed');
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
 * The database layer
 *
 * The data is handled by plain old SQL. This class handles routine
 * by managing the current connection depending on server, executing
 * the correct SQL by method name (looks like orm!) and also has a few
 * helper functions for crud managemend and application install
 *
 * @package		Spaghetti
 * @category	Database
 * @author		Andrei Draganescu
 * @link		http://andreidraganescu.info/spaghetti/how_it_works.html
 */
class db
{
	// singleton
	private static $instance;
	private $escape = true;
	public $profile = false;
	public $current_query = '';
	
	// available querries
	var $querries = array();
	
	var $model = null;
	
	// allows the calling of querries by name with parameters
	public function __call($name, $arguments) {
		$app = the::app();
		if(strpos($name,"fetch") !== false)
		{
			$querry = str_replace("fetch_","", $name);
			return $this->fetch($querry, $arguments);
		}
		if(array_key_exists($name, $this->querries[$this->model]))
			return $this->querry($this->querries[$this->model][$name], $arguments);
		else if(array_key_exists($name, $this->querries))
			return $this->querry($this->querries[$name], $arguments);
		$app->log($name ." is an undefined querry or method of the ".$this->model." model.");
	}
	
	// shortcut for querries that return a single value
	public function fetch($name, $arguments)
	{
		$app = the::app();
		if(array_key_exists($name, $this->querries[$this->model]))
			$data = $this->querry($this->querries[$this->model][$name], $arguments);
		else if(array_key_exists($name, $this->querries))
			$data = $this->querry($this->querries[$name], $arguments);
		else
			$app->log($name ." is an undefined querry or method of the ".$this->model." model.");
		if(is_array($data[0]))
			return array_shift($data[0]);
		else
			return false;
	}
	
	// deprecated
	function manage_data($table)
	{
		$p = the::app();
		$db = the::database();
		
		if($p->no_post_data() && array_key_exists(3, $p->uri_segments))
			return $this->delete($table);
		
		if($p->no_post_data())
			return false;
		
		if($p->post("edit_token"))
			$this->form_update($table);
		else
			$this->form_insert($table);
		
		$p->route("admin/$table/list");
	}
	
	// deprecated
	function delete($table)
	{
		$p = the::app();
		$db = the::database();
		
		if(strpos($p->uri_segments[3], '-') !== false)
		{
			$db->querry("delete from `%s` where `id` = '%s'", array($table, str_replace("-", "", $p->uri_segments[3])));
			$p->route("admin/$table/list");
		}
		else
			return $this->prep_form($table);
	}
	
	// deprecated
	function prep_form($table)
	{
		$p = the::app();
		$db = the::database();
		
		$resume = $db->querry("select * from `%s` where `id` = '%s'", array($table, $p->uri_segments[3]));
						
		foreach ($resume[0] as $key => $value) {
			$p->current_block = preg_replace('/<input(.*?)name="_'.$key.'"/','$0 value="'.$value.'"', $p->current_block);
			$p->current_block = preg_replace("/<textarea(.*?)name=\"_".$key."\"(.*?)>/", "$0".$value, $p->current_block);
		}
		$input = "<input type='hidden' name='edit_token' value='".$p->uri_segments[3]."' />";
		$p->current_block = str_replace('</form>',$input.'</form>', $p->current_block);
		return $p->current_block;
	}
	
	// deprecated
	function form_update($table)
	{
		$p = the::app();
		$db = the::database();
		
		$update_query = "update `".$table."` set --data-- where `id` = %s";
		
		$data = "";
		foreach ($_POST as $key => $value)
		{
			if($key[0] == "_")
			{
				$key = substr($key, 1);
				$data .= ",`$key` = '%s'";
				$v[] = $p->post("_".$key);
			}
		}
		
		$data = substr($data, 1);
		$update_query = str_replace("--data--", $data, $update_query);
		
		$v[] = $p->post("edit_token");
		
		$db->querry($update_query, $v);
	}
	
	// deprecated
	function form_insert($table)
	{
		$p = the::app();
		$db = the::database();
		
		//insert
		$fields = "";
		$values = "";
		$data = array($table);
		
		foreach ($_POST as $key => $value)
		{
			if($key[0] == "_")
			{
				$key = substr($key, 1);
				$fields .= ",`%s`";
				$f[] = $key;
				$values .= ", '%s'";
				$v[] = $p->post("_".$key);
			}
		}
		
		$data = array_merge($data, $f, $v);
		
		$fields = substr($fields, 1);
		$values = substr($values, 1);
		
		$insert_query = "insert into %s (".$fields.") values (".$values.")";
		
		$db->querry($insert_query, $data);
	}
	
	// manual disabling/enabling of querry escaping
	// $val can be true or false
	function escape($val)
	{
		$this->escape = $val;
	}
	
	// a wrapper for mysql_querry with parameter replacement and escaping
	function querry()
	{
		$app = the::app();
		$args = func_get_args();
		if (count($args) < 2)
		{
			$args = $args[0];
		}
		else
		{
			$query = array_shift($args);
			if($this->escape === true)
				$args = array_map('mysql_real_escape_string', $args[0]);
			else
				$args = $args[0];
			array_unshift($args, $query);
		}
		
	    $query = call_user_func_array('sprintf', $args);
		
		if($this->profile == true)
			echo $query . "<br />";
		
		$this->current_query = $query;	

		if($app->profile)
			$app->log("QUERY: ".$query);
		
		if($app->profile)
			$start = $this->microtime_float();
		if (PHP_SAPI !== 'cli')
			$result = mysql_query($query) or $app->log(mysql_error() . " in querry \n".$query, true);
		else
			$result = mysql_query($query) or die(mysql_error() . " in querry \n".$query);
		
		if($app->profile)
			$end = $this->microtime_float();
		
		if($app->profile)
			$app->log('TIME: '.($end - $start)." seconds.");
		
	    if($result === true)
		{
		   return true; 	
		}
		while ($row = mysql_fetch_assoc($result)) {
		    $data[] = $row;
		}
		mysql_free_result($result);
		if(!isset($data))
			return false;
		return $data;
	}
	
	// generic insert query
	function insert_records($table, $fields, $values)
	{
		$sql = "INSERT into `$table` ";
		$field_string = "(".implode(",", array_fill(0, count($fields), "`%s`")).")";
		if(count($values[0]) > 1)
		{
			foreach($values as $vals)
			{
				foreach($vals as $v)
					$val[] = $val;
				$vs[] = "(".implode(",", array_fill(0, count($vals), "'%s'")).")";
			}
			$values_string = implode(",", $vs);
			$values = $val;
		}
		else
			$values_string = "(".implode(",", array_fill(0, count($values), "'%s'")).")";
		
		$query = $sql . $field_string ." VALUES ". $values_string;
		return $this->querry($query, array_merge($fields, $values));
	}
	
	// generic update query
	function update_records($table, $fields, $values, $where = null, $what = null)
	{
		$sql = "UPDATE `$table` SET ";
		foreach($fields as $k=>$f)
			$update[] = "`$f` = '%s'";
		
		$condition = "";
		if($where != null && $what != null)
		{
			if(is_numeric($what))
				$condition = " WHERE `".$where."` = %s"; 
			else
				$condition = " WHERE `".$where."` = '%s'"; 
		}	
		$query = $sql . implode(", ", $update) . $condition;
		if($what != null)
			return $this->querry($query, array_merge($values, array($what)));
		else
			return $this->querry($query, $values);
	}
	
	// a simple method for cru.
	// TODO: add delete functionality
	function manage_table($table, $token, $values)
	{
		$app = the::app();
		if(!$app->post($token))
			return $this->insert_records($table,array_keys($values), array_values($values));
		else
			return $this->update_records($table, array_keys($values), array_values($values), 'id', $app->post($token));

	}
	
	// database connection
	static function connect($secondary = false)
	{
		$stuff = the::app();
		if($secondary === false)
			$credentials = $stuff->connections;
		else 
			if(array_key_exists($secondary, $stuff->secondary_connections))
				$credentials = $stuff->secondary_connections[$secondary];
			
		if(count($credentials) > 0)
		{
			foreach ($credentials as $host => $credentials) {
				if(preg_match("|".$host."|", $stuff->uri_string))
				{
					$link = mysql_connect($credentials[0], $credentials[2], $credentials[3]);
					if (!$link) {
					    die('Could not connect: ' . mysql_error());
					}
					$sb = mysql_select_db($credentials[1], $link);
					if (!$sb) {
    					    die ('Can\'t use '.$credentials[1].':' . mysql_error());
					}
				}
			}
		}
		else
		{
			$stuff->log("Please define a database connection in your index file if you plan to use a database.");
		}
		
		if (!self::$instance)
	    {
	        self::$instance = new db();
	    }
		
	    return self::$instance;
	    
	}
	
	// runs queries in each model install file
	function install($model)
	{
		unset($upgrade, $downgrade, $i);
		$app = the::app();
		$model_name = true; $noname = true;
		
		if(!file_exists(BASE.'../models/'.$model."/".$model."_install.php"))
			$model_name = false;
			
		if(!file_exists(BASE.'../models/'.$model."/"."install.php"))
			$noname = false;
		
		if(!$model_name && !$noname)
			return false;

		if($model_name)
			include BASE.'../models/'.$model."/".$model."_install.php";

		if($noname)
			include BASE.'../models/'.$model."/"."install.php";
		
		if(!isset($upgrade))
			return false;
			
		if(!is_array($upgrade))
			return false;
		
		$maxver = count($upgrade);
		if(array_key_exists(2, $app->uri_segments))
			$version = $app->uri_segments[2];
		else
			$version = $maxver;
		
		if($maxver > $version )
		{
			for($i=$maxver; $i>=$version; $i--)
			{
				if(!array_key_exists($i, $downgrade)) continue;
				foreach ($downgrade[$i] as $query) {
					echo "<pre>".$query."</pre>";
					echo '<br>';
					mysql_query($query) or null;
				}
			}
		}
			
		for($i=1; $i<=$version; $i++)
		{
			if(!array_key_exists($i, $upgrade)) continue;
			foreach ($upgrade[$i] as $query) {
				echo "<pre>".$query."</pre>";
				echo '<br>';
				mysql_query($query) or null;
			}
		}
		echo $model.' install ok <br/>';
	}
	
	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}
		
}


?>
