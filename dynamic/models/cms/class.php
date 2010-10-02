<?php
class cms
{
	
	function the_resume()
	{
		$db = the::database();
		return $db->select_recent_resume();
	}
	
	function the_post()
	{
		$p = the::app();
		$db = the::database();
		
		return $db->load_by_id("blog", $p->uri_segments[1]);
	}
	
	function the_page()
	{
		$p = the::app();
		$db = the::database();
		
		return $db->load_by_id("projects", $p->uri_segments[1]);
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
		return $db->select_recent_work(3);
	}
	
	/*work*/
	function the_work($type)
	{
		$db = the::database();
		return $db->select_work_by_type($type);	
	}
	
	
	function the_ideas()
	{
		$db = the::database();
		return $db->select_ideas();
	}
	
	
	function select_resume()
	{
		$resume = the::database();
		return $resume->get_resume();
	}
	
	function manage_resume()
	{
		$db = the::database();
		return $db->manage_data("resume");
	}
	
	function select_ideas()
	{
		$resume = the::database();
		return $resume->get_ideas();
	}
	
	function manage_ideas()
	{
		$db = the::database();
		return $db->manage_data("projects");
	}
	
	function select_posts()
	{
		$resume = the::database();
		return $resume->get_posts();
	}
	
	function manage_post()
	{
		$p = the::app();
		
		if($p->post("type"))
			$_POST["_types"] = implode(",", $p->post("type"));
		
		$db = the::database();
		return $db->manage_data("blog");
	}
	
	function select_work()
	{
		$resume = the::database();
		return $resume->get_work();
	}
	
	function manage_work()
	{
		$p = the::app();
		
		if($p->post("type"))
			$_POST["_types"] = implode(",", $p->post("type"));

		$db = the::database();
		return $db->manage_data("work");
	}
		
}
?>