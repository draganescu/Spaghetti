<?php
class example
{
	
	function theposts()
	{
		
		$db = the::database();
		return $db->get_some_posts(10,0);
	}
	
	function themenu()
	{
		$menu = '
	<ul>
		<li class="current_page_item"><a href="#">Home</a></li>
		<li><a href="#">Fresh air</a></li>
		<li><a href="#">Photos</a></li>
		<li><a href="#">About</a></li>
		<li><a href="#">Links</a></li>
		<li><a href="#">Contact</a></li>
	</ul>
';
		return $menu;
	}
	
	function cache()
	{
		$blog = the::app();
		$cache_life = $blog->cache_life;
		$file = md5($blog->uri_string);
		$created_at = false;
		// this uri never been visited before
		if(file_exists(BASE.'/../cache/'.$file))
			$created_at = filemtime(BASE.'/../cache/'.$file);
		else
		{
			if($blog->output != "")
				file_put_contents(BASE.'/../cache/'.$file, $blog->output);
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
				if($blog->output != "")
					file_put_contents(BASE.'/../cache/'.$file, $blog->output);
			}
		}
		
	}
	
	function admin_list_posts()
	{
		$db = the::database();
		return $db->get_some_posts(10,0);
	}
	
	function pages_of_posts()
	{
		return false;
	}
	
	function admin_new_post()
	{
		// the standard procedure for any form saving method (post or get)
		$blog = the::app();
		$db = the::database();
		
		if($blog->no_post_data())
			return false;
		
		$post_title = $blog->post('post_title');
		$post_content = $blog->post('post_content');
		
		$db->save_a_post($post_title, $post_content);
		
		
	}
	
}
?>