<?php
// this is the it
class db
{
	// singleton
	private static $instance;
	
	// available querries
	var $querries = array();
	
	public function __call($name, $arguments) {
		if(array_key_exists($name, $this->querries))
			return $this->querry($this->querries[$name], $arguments);
    }

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
	
	function querry()
	{
		$args = func_get_args();
		if (count($args) < 2)
		{
			$args = $args[0];
		}
		else
		{
			$query = array_shift($args);
			$args = array_map('mysql_real_escape_string', $args[0]);
			array_unshift($args, $query);
		}
		
	    $query = call_user_func_array('sprintf', $args);
	    $result = mysql_query($query) or die(mysql_error());
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
	
	static function connect()
	{
		$stuff = the::app();
		
		if(count($stuff->connections) != 0)
		{
			foreach ($stuff->connections as $host => $credentials) {
				if(preg_match("|".$host."|", $stuff->uri_string))
				{
					$link = mysql_connect($credentials[0], $credentials[2], $credentials[3]);
					if (!$link) {
					    die('Could not connect: ' . mysql_error());
					}
					mysql_select_db($credentials[1]);
				}
			}
		}
		if (!self::$instance)
	    {
	        self::$instance = new db();
	    }
		
	    return self::$instance;
	    
	}
	
	function install($model)
	{
		$app = the::app();
		include BASE.'/models/'.$model."/install.php";
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
				foreach ($downgrade[$i] as $query) {
					echo $query;
					echo '<br>';
					mysql_query($query);
				}
			}
		}
			
		for($i=$maxver; $i<=$version; $i++)
		{
			foreach ($upgrade[$i] as $query) {
				echo $query;
				echo '<br>';
				mysql_query($query);
			}
		}
		echo $model.' install ok <br/>';
	}
		
}


?>