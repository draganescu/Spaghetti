<?php
class welcome
{
	
	function hello_mysql()
	{
		$db = the::database();
		$result = $db->hello_mysql();
		return $result[0]['today'];
	}
	
	function hello_world()
	{
		return "Hello World";
	}
	
	/**
	 * Advanced code
	 * that is neat and i didnt want to delete it
	*/
	function cache()
	{
		$app = the::app();
		$cache_life = $app->cache_life;
		$file = md5($app->uri_string);
		$created_at = false;
		// this uri never been visited before
		if(file_exists(BASE.'/../cache/'.$file))
			$created_at = filemtime(BASE.'/../cache/'.$file);
		else
		{
			if($app->output != "")
				file_put_contents(BASE.'/../cache/'.$file, $app->output);
		}
			
		if($created_at)
		{
			if(time() - $created_at < $cache_life)
			{
				echo file_get_contents(BASE.'/../cache/'.$file);
				exit;
			}
			else
			{
				// expire cache
				unlink(BASE.'/../cache/'.$file);
				if($app->output != "")
					file_put_contents(BASE.'/../cache/'.$file, $app->output);
			}
		}
		
	}
	
}
?>