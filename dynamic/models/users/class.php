<?php
class users
{
	
	function login()
	{
		$p = the::app();
		$d = the::database();
		
		if($p->no_post_data())
			return false;
		
		session_start();
		$username = $p->post('username');
		$password = $p->post('password');
		
		$login = $d->get_user($username, md5($password));
		
		if(!$login)
			return false;
		else
		{
			$_SESSION['loggedin'] = true;
			header("Location: ".$p->base_uri."admin/dashboard");		
		}
		
		
	}
	
}
?>