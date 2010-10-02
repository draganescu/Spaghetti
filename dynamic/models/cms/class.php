<?php
class cms
{
	function cms()
	{
		$p = the::app();
		$p->tweet_cache = 600;
	}	
	
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
	
	function archives()
	{
		$db = the::database();
		return $db->get_posts();
	}
	
	function the_page()
	{
		$p = the::app();
		$db = the::database();
		
		return $db->load_by_id("projects", $p->uri_segments[1]);
	}
	
	function send_form()
	{
		$p = the::app();
		if($p->no_post_data())
			return false;
		
		include BASE.'helpers/htmlpurifier-4.2.0-standalone/HTMLPurifier.standalone.php';
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Cache.DefinitionImpl', null);
		$obj = new HTMLPurifier($config);
		$email = $obj->purify($p->post('email'));
		$message = $obj->purify($p->post('message'));
		
		$db = the::database();
		$db->insert_a_contact($email,$message);
		
		return $p->html('html/thank_you');
		
	}
	
	function recent_post_titles()
	{
		$db = the::database();
		return $db->select_recent_post_titles(3);
	}
	
	function twitter()
	{
		$p = the::app();
		
		$cache_file = BASE.'models/cms/tweet.txt';
		$latest_tweet = file_get_contents($cache_file);
		$cache = filemtime($cache_file);		
		
		if(time() - $cache > $p->tweet_cache)
		{
			$url = "http://search.twitter.com/search.json?q=from:scriitoru&rpp=1";
			$content = file_get_contents($url);
			
			if(!$content)
				return "Now i dont.";
			
			$content = json_decode($content);
			$latest_tweet = $content->results[0]->text;
			
			file_put_contents($cache_file, $latest_tweet);
			
		}
		
		return $latest_tweet;
		
		
	}
	
	function the_contacts()
	{
		$p = the::app();
		$db = the::database();

		if(array_key_exists(2, $p->uri_segments))
			if(strpos($p->uri_segments[2], "-") !== false)
				$db->remove_by_id('contacts', substr($p->uri_segments[2],1));
		
		return $db->get_contacts();
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