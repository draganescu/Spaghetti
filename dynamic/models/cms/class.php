<?php
class cms
{
	
	function login_check()
	{
		
		$p = the::app();
		
		if(!preg_match("|admin|", $p->uri_string))
			return true;
		
		if($_SESSION["logged_in"] == true)
			return true;
		else
			header("Location: ".$p->link_uri."/admin/");
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
	
	
	
	
	
}
?>