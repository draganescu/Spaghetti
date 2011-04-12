<?php
// everything has an id of INT(10) AUTOINCREMENT

model::name("welcome");

model::table("posts",
	"Post title", "title text", // input type text, VARCHAR
	"Content", "body longtext", // textarea, TEXT
	"", "edited_on timestamp", // empty name is input type hidden, TIMESTAMP
	"Written on", "date datetime", // html 5 input type date, in html 4 is a text input, DATETIME
	"The author", "name one-from authors", // select, id based fk, INT
	"Categories", "name some-from categories", // select multiple, many to many, posts_categories table, nothing in the posts table, but a new posts_categories table
	"Publish", "published one-of Yes, No", // radio, ENUM in mysql
	"Properties", "properties some-of Private, Sticky, Special, 'Shocking feature'" // SET in mysql, checkboxes
);

model::validation("posts",
	"title", "required, min-10",
	"date", "datetime",
	"edited_on", "required, number"
)

model::json("files/categories.json as categories",
	"Category name", "name text"
);

model::csv("files/authors.csv",
	"Author name", "name text",
	"Salutation", "salutation one-of NULL Mr. Mrs. Doctor",
	"Expertise", "expertise some-of 'Tech reviews', Ravioli, Travel"	
);

model::data("authors",
	"John Kelly", "Mr.", "Ravioli",
	"Andrei Draganescu", "Mr.", "Travel, 'Tech reviews'"
);

model::data("categories",
	"PHP",
	"Ruby",
	"Programming zen"
)

model::data("posts",
	"The art of Sugar project starter",
	"<h4>The subtitle here</h4>
	<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in <b>voluptate</b> velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>",
	time(),
	date("Y-m-d H:i:s"),
	"1,3",
	"Yes",
	"Sticky, 'Shocking feature'"	
)


