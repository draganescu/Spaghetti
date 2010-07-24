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
		include BASE.'/models/'.$model."_sql.php";
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
			
		for($i=1; $i<=$version; $i++)
		{
			foreach ($upgrade[$i] as $query) {
				echo $query;
				echo '<br>';
				mysql_query($query);
			}
		}
		echo 'Install ok'; die;
	}
		
}


?>