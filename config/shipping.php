<?php

return [

	'api_key' => env('SHIPPING_API_KEY'),

	'api_secret' => env('SHIPPING_API_SECRET'),

	"post_auth" => 'Basic '.base64_encode(env('SHIPPING_API_KEY').':'.env('SHIPPING_API_SECRET')),
	// "post_auth" => 'Basic ZjMwMmM0MzU2YjRiNGJiNDhiODRlZWNhYjNlZjQ3NWI6YTkyMDdlMjY3MzYxNDM3NDg5OGRhOTM4OGYyOTQxY2Q=',

	"test_post_auth" => 'Basic '.base64_encode(env('TEST_SHIPPING_API_KEY').':'.env('TEST_SHIPPING_API_SECRET')),
	// "test_post_auth" => 'Basic ZTY3MTYxZTdlYzg5NGUzOTgyZjUyYjQ3YTIwMjkzNGI6ZTdlZTA1MzQxN2IyNDYzNDkzMjMwMjQ1ZjU1NWMxOTc=',
	
];