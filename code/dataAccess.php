<?php

namespace xmlSitemapGenerator;





class dataAccess {
	
	
	
	function __construct() 
	{

	}
	
	static function execute($cmd) 
	{
		global $wpdb;
		$results = $wpdb->get_results($cmd, OBJECT );	

		return $results;
	}

	static function getDateField($name)
	{
		if ($name == "created")
		{ 
			return "post_date";
		}
		else
		{
			return "post_modified";
		}
	}
 

 
	public static function createMetaTable()
	{
		global $wpdb;		
		$tableMeta = $wpdb->prefix . 'xsg_sitemap_meta';
		$cmd = "CREATE TABLE IF NOT EXISTS `{$tableMeta}` (
				  `itemId` int(11) DEFAULT '0',
				  `inherit` int(11) DEFAULT '0',
				  `itemType` varchar(8) DEFAULT '',
				  `exclude` int(11) DEFAULT '0',
				  `priority` int(11) DEFAULT '0',
				  `frequency` int(11) DEFAULT '0',
				  UNIQUE KEY `idx_xsg_sitemap_meta_ItemId_ItemType` (`itemId`,`itemType`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='generatated by XmlSitemapGenerator.org';";

			
		$wpdb->query($cmd);

	}
	public static function getMetaItem($id, $type)
	{
		global $wpdb;
		$tableMeta = $wpdb->prefix . 'xsg_sitemap_meta';
		$cmd = " SELECT * FROM {$tableMeta}
				 WHERE itemId = %d AND itemType = %s ";
		
		$settings = $wpdb->get_row($cmd);
		
		if (!$settings) {return new metaSettings(); }
 		
		return $settings ;
	}
	
 
	public static function saveMetaItem($metaItem)
	{
		global $wpdb;		
		$tableMeta = $wpdb->prefix . 'xsg_sitemap_meta';
		$cmd = " INSERT INTO {$tableMeta} (itemId, itemType, exclude, priority, frequency, inherit) 
				 VALUES(%d, %s, %d, %d, %d, %d) 
						ON DUPLICATE KEY UPDATE 
							exclude=VALUES(exclude), priority=VALUES(priority), frequency=VALUES(frequency), inherit=VALUES(inherit) ";
			
		
	 
		$itemId = $metaItem->itemId;
		$itemType = $metaItem->itemType;
		$exclude = $metaItem->exclude;
		$priority = $metaItem->priority;
		$frequency = $metaItem->frequency;
		$inherit = $metaItem->inherit;
	 		
		$cmd = $wpdb->prepare($cmd, $itemId, $itemType, $exclude, $priority , $frequency,$inherit);
		
		$settings = $wpdb->query($cmd);
	
	}
	
	// type = "post" or "page" , date = "created" or "updated"
	//$limit = 0 for no limit.)
	public static function getPages($date  , $limit)
	{
		global $wpdb;
		$date = self::getDateField($date);
 
		$tableMeta = $wpdb->prefix . 'xsg_sitemap_meta';
		
		$cmd = "SELECT 
				posts.*,   
				PostMeta.*,  Tag_Meta.* , {$date} as sitemapDate
				FROM {$wpdb->posts} as Posts 
					LEFT JOIN {$tableMeta} as PostMeta ON Posts.Id = PostMeta.ItemId AND PostMeta.itemId
					LEFT JOIN 
							(SELECT  
								Terms.object_id as Post_id,
								Max(Meta.exclude) as tagExclude,
								Max(Meta.priority) as tagPriority,
								Max(Meta.frequency) as tagFrequency
							FROM {$tableMeta} as Meta 
								INNER JOIN {$wpdb->term_relationships} as Terms
								ON  Meta.itemId = Terms.term_taxonomy_id
							WHERE Meta.itemType = 'taxonomy' AND Meta.inherit = 1
								
							GROUP BY Terms.object_id 
							) as Tag_Meta
						ON Posts.Id = Tag_Meta.Post_id
				WHERE post_status = 'publish' AND (post_type = 'page' OR  post_type = 'post')   
					AND Posts.post_password = ''
				ORDER BY {$date} DESC  ";
				 
			
		if ($limit > 0 ) 
		{ 
			$cmd .= " LIMIT {$limit} " ; 
		}


		$results = self::execute($cmd);
		
		return $results;				
	}
	
	public static function  getTaxonomy($date = "updated"){

		global $wpdb;
		$date = self::getDateField($date);
		$tableMeta = $wpdb->prefix . 'xsg_sitemap_meta';
		$cmd = "SELECT  Terms.term_id, Terms.name, Terms.slug, Terms.term_group,
					tax.term_taxonomy_id,  tax.taxonomy, tax.description,   tax.description,
						Meta.exclude, Meta.priority, Meta.frequency,
						Max(Posts.{$date}) as sitemapDate,  Count(Posts.ID) as posts
				  
				FROM {$wpdb->terms} as Terms
					INNER JOIN {$wpdb->term_relationships} as Relationships ON Terms.Term_id = Relationships.term_taxonomy_id
					INNER JOIN {$wpdb->posts} as Posts ON Relationships.object_id = Posts.Id
							AND Posts.post_status = 'publish' AND Posts.post_password = ''
					INNER JOIN {$wpdb->term_taxonomy} as tax ON Terms.term_id = tax.term_id
					LEFT JOIN {$tableMeta} as Meta ON Terms.term_Id = Meta.ItemId AND Meta.itemType = 'taxonomy' 
				WHERE tax.taxonomy IN ('post_tag','category')
				GROUP BY  Terms.term_id, Terms.name, Terms.slug, Terms.term_group, tax.description, tax.term_taxonomy_id,  tax.taxonomy, tax.description, Meta.exclude, Meta.priority, Meta.frequency";
			
		$results = self::execute($cmd);
		 
		return $results;		
		
		
		
	}
 
	public static function  getAuthors($date = "updated") {

		global $wpdb;
		$date = self::getDateField($date);
		$tableMeta = $wpdb->prefix . 'xsg_sitemap_meta';
	
		$cmd = "SELECT  users.user_nicename, users.user_login, users.display_name ,
					MAX(posts.{$date}) AS sitemapDate, 	Count(Posts.ID) as posts
				FROM {$wpdb->users} users LEFT JOIN {$wpdb->posts} as posts ON users.Id = posts.post_author 
						AND posts.post_status = 'publish' AND Posts.post_password = ''
				GROUP BY  users.user_nicename, users.user_login, users.display_name ";

		$results = self::execute($cmd);
		 
		return $results;		

	}
		
	
	
	public static function  getArchives($date = "updated"){
		
		global $wpdb;
		
		$date = self::getDateField($date);
		
		$cmd = "SELECT DISTINCT YEAR({$date}) AS year,MONTH({$date}) AS month, 
					MAX(posts.{$date}) AS sitemapDate, 	Count(Posts.ID) as posts
			FROM {$wpdb->posts} as posts
			WHERE post_status = 'publish' AND post_type = 'post' AND Posts.post_password = ''
			GROUP BY YEAR({$date}), MONTH({$date})
			ORDER BY {$date} DESC";

		$results = self::execute($cmd);
		 
		return $results;	
						
	}

	
	public static function getLastModified($date = "updated")
	{
		 
		
		global $wpdb;
	
		$date = self::getDateField($date);
	 
		$cmd = "SELECT MAX({$date})
				FROM {$wpdb->posts} as posts
				WHERE post_status = 'publish'";
			
		$date = $wpdb->get_var($cmd);
		 
		return $date;
	}
	
	
	public static function getPostCountBand()
	{
		
		global $wpdb;
	
		
		$cmd = "SELECT COUNT(*)
				FROM {$wpdb->posts} as posts
				WHERE post_status = 'publish'";
			
		$postCount = $wpdb->get_var($cmd);
		 
		if( $postCount = 0) {$postCountLabel = "0";}
		else if( $postCount <= 10) {$postCountLabel = "1 to 10";}
		else if( $postCount <= 25) {$postCountLabel = "11 to 25";}
		else if ($postCount <= 50) {$postCountLabel = "26 to 50";}
		else if ($postCount <= 100) {$postCountLabel = "51 to 100";}
		else if ($postCount <= 500) {$postCountLabel = "101 to 500";}
		else if ($postCount <= 500) {$postCountLabel = "501 to 1000";}
		else if($postCount < 10000) {$postCountLabel = round($postCount / 1000) * 1000;}
		else if($postCount < 100000) {$postCountLabel = round($postCount / 10000) * 10000;}
		else {$postCountLabel = round($postCount / 100000) * 100000;}
		
		return $postCountLabel;
		
	}
 



	
	
}




?>