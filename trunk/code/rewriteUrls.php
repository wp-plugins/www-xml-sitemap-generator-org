<?php

namespace xmlSitemapGenerator;

 class rewriteUrls
 {
	 
	function __construct() {
		
	//	add_action('init', 'custom_rewrite_basic');

	}
	
	static function addRules() 
	{

	  add_rewrite_rule('^SitemapXml.xml/?'		, 'index.php?page_id=$matches[1]', 'top');
	  add_rewrite_rule('^SitemapRss.xml/?'		, 'index.php?page_id=$matches[1]', 'top');
	  add_rewrite_rule('^SitemapRssNew.xml/?'	, 'index.php?page_id=$matches[1]', 'top');
	}

	 
	 
 }

?>
