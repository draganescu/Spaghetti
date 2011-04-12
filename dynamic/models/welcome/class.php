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
	
		$model = factory(MODEL);
		
		$mailer = factory("PHP_Mailer");
		
		$welcome->authors("id", 1);
		$welcome->categories("name", "PHP, Ruby; 'Programming zen'");
		
		$welcome->getPosts(); // a select
		$welcome->getAuthors(); // another select
		
		$welcome->getAll(); // makes a join
		
		foreach ($welcome->posts as $key => $post)
		{
			$post->title;
			$post->author;
			foreach($post->categories as $category)
			{
				$category;
			}
			
		}
		
		foreach ($welcome->all as $key => $item)
		{
			$item->posts("title");
			foreach($item->categories() as $category)
			{
				$category->id;
				$category->name;
			}
			$item->authors('expertise');
		}
		
		$welcome->authors("name", "Mario Programmer");
		$welcome->authors("expertise", array("Ravioli", "Tech reviews"));
		$welcome->save("authors"); // now $welcome->authors->id is last_insert_id();
		
		$welcome->authors("id", 1);
		
		$welcome->update("authors");
		
		return "Hello World";
	}
	
	
	
}
?>