<?php
class cms
{
	
	function login_check()
	{
		session_start();
		$p = the::app();
		
		if(!preg_match("|admin|", $p->uri_string))
			return true;
		
		if(isset($_SESSION['loggedin']))
		{
			return true;
		}
		else
		{
			if($_GET['login'] = false)
				header("Location: ".$p->link_uri."/admin?login=true");
		}
	}
	
	function the_resume()
	{
		$db = the::database();
		return $db->select_recent_resume();
	}
	
	function recent_post_titles()
	{
		$db = the::database();
		return $db->select_recent_post_titles(3);
	}
	
	function twitter()
	{
		return "Now i dont.";
	}
	
	function recent_work()
	{
		$db = the::database();
		return $db->select_recent_post_titles(3);
	}
	
	function the_ideas()
	{
		$db = the::database();
		return $db->select_recent_ideas();
	}
	
	
	
}
?>