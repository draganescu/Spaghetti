<?php
class users
{
	
	function users()
	{
		session_start();
	}
	
	function login()
	{
		$p = the::app();
		$d = the::database();
		
		if($p->get('do') == 'logout')
		{
			session_destroy();
			$p->route("login");
		}	
		
		if($p->no_post_data() && isset($_SESSION['loggedin']))
			$p->route("admin/dashboard");
		
		if($p->no_post_data())
			return false;
		
		$username = $p->post('username');
		$password = $p->post('password');
		
		$login = $d->get_user($username, md5($password));
		
		if(!$login)
			return false;
		else
		{
			$_SESSION['loggedin'] = true;
			$p->route("admin/dashboard");
		}
		
		
	}
	
	function login_check()
	{
		$p = the::app();
		
		if(!preg_match("|admin|", $p->uri_string))
			return true;
		
		if(preg_match("|login|", $p->uri_string))
			return true;
		
		if(isset($_SESSION['loggedin']))
			return true;
		else
			$p->route("login");
		
	}
	
}
?>