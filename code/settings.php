<?php

namespace xmlSitemapGenerator;

// settings for generating a map


class settings
{
	
	public static function addHooks()
	{
		add_action('admin_menu', array(  __CLASS__, 'admin_menu' ) );
		add_action('admin_init', array( __CLASS__, 'register_settings' ) );
	}
	
	public static function admin_menu() 
	{
		add_options_page( 'XML Sitemap Settings','XML Sitemap','manage_options', PLUGIN_NAME , array( __CLASS__ , 'render' ) );
	}
	
	public static function register_settings()
	{
		register_setting( PLUGIN_NAME, PLUGIN_NAME );
	}

	static function getDefaults($name){
		
		$settings = new metaSettings();

		$settings->exclude = ( isset( $_POST[$name . 'Exclude'] ) ?  $_POST[$name . 'Exclude'] : 0 );
		$settings->priority  = ( isset( $_POST[$name . 'Priority'] ) ?  $_POST[$name . 'Priority'] : 0  );
		$settings->frequency  = ( isset( $_POST[$name . 'Frequency'] ) ?  $_POST[$name . 'Frequency'] : 0 );
		
		
		return $settings;
	}

	static function  handlePostBack(){
		
        if (!(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')){ return;  }

		/* Verify the nonce before proceeding. */
	 	if ( !isset( $_POST['wpXSG_meta_nonce'] ) || !wp_verify_nonce( $_POST['wpXSG_meta_nonce'], basename( __FILE__ ) ) )
	  	return ;
		
		
		if ( !current_user_can( 'manage_options') ) {return;}

		
			$globalSettings = new globalSettings();
			

			
			$globalSettings->addRssToHead  = ( isset( $_POST['addRssToHead'] ) ?  $_POST['addRssToHead'] : 0 );
			$globalSettings->pingSitemap = ( isset( $_POST['pingSitemap'] ) ?  $_POST['pingSitemap'] : 0 );
			$globalSettings->addToRobots = ( isset( $_POST['addToRobots'] ) ?  $_POST['addToRobots'] : 0 );
			$globalSettings->sendStats = ( isset( $_POST['sendStats'] ) ?  $_POST['sendStats'] : 0 );
			$globalSettings->smallCredit = ( isset( $_POST['smallCredit'] ) ?  $_POST['smallCredit'] : 0 );
			
			update_option( "wpXSG_global" ,  $globalSettings , true);
	
			$sitemapDefaults = new sitemapDefaults();
			
			$sitemapDefaults->dateField = ( isset( $_POST['dateField'] ) ?  $_POST['dateField'] : $sitemapDefaults->dateField );
			$sitemapDefaults->homepage  = self::getDefaults("homepage");
			$sitemapDefaults->pages = self::getDefaults("pages");
			$sitemapDefaults->posts = self::getDefaults("posts");
			$sitemapDefaults->taxonomyCategories = self::getDefaults("taxonomyCategories");
			$sitemapDefaults->taxonomyTags = self::getDefaults("taxonomyTags");
		 
			$sitemapDefaults->recentArchive = self::getDefaults("recentArchive");
			$sitemapDefaults->oldArchive  = self::getDefaults("oldArchive");
			$sitemapDefaults->authors  = self::getDefaults("authors");
			
			$sitemapDefaults->excludeRules = ( isset( $_POST['excludeRules'] ) ?  $_POST['excludeRules'] : "" );
			
		 	update_option( "wpXSG_sitemapDefaults" ,  $sitemapDefaults , false);
			
			core::updateStatistics("Admin", "SaveSettings",0);
			
     
}
 
	static function RenderDefaultSection($title,$name,$defaults){
		
			?>
							
							<tr>
								<td scope="col"><?php echo $title; ?></td>
								<td scope="col"><select  name="<?php echo $name; ?>Exclude" id="<?php echo $name; ?>Exclude" ></select> </td>
								<td scope="col"><select  name="<?php echo $name; ?>Priority" id="<?php echo $name; ?>Priority" ></select>   </td>
								<td scope="col"><select  name="<?php echo $name; ?>Frequency" id="<?php echo $name; ?>Frequency" ></select>  </td>
							</tr>
							<script>
								populate("<?php echo $name; ?>Exclude" ,excludeDefaults, <?php echo $defaults->exclude  ?>);
								populate("<?php echo $name; ?>Priority" ,priorityDefaults, <?php echo $defaults->priority  ?>);
								populate("<?php echo $name; ?>Frequency" ,frequencyDefaults, <?php echo $defaults->frequency  ?>);
		 
							</script>
			
			<?php

	}
	
	
 
 
	public static function render()
	{
		 
		self::handlePostBack();
		
		$globalSettings =   get_option( "wpXSG_global"   , new globalSettings()  );
		$sitemapDefaults =  get_option( "wpXSG_sitemapDefaults"   , new sitemapDefaults()  );
		
		core::updateStatistics("Admin", "ViewSettings",0);
 
		?>



		
<form method="post"  > 

   <?php 	wp_nonce_field( basename( __FILE__ ), 'wpXSG_meta_nonce' ); ?>
		
		
<div class="wrap" >

        <h2>Google XML Sitemap Generator</h2>

		
		<p style="color:#B80000"><strong>This is the initial release of this plugin and recommend you use it with 
		caution, especially with important live production websites. 
		As always you should regularly backup your website.</p> <p style="color:#B80000">Please 
		<a target="_blank" href="https://xmlsitemapgenerator.org/contact.aspx">contact us</a> with any comments,
		questions, suggestions and bugs.</strong></p>
		
		<p>Here you can edit your admin settings and defaults.</p>
		<p>You can override categories, tags, pages and posts when adding and editing them.</p>

 
		
<div id="poststuff" class="metabox-holder has-right-sidebar">

            <div class="inner-sidebar">
                <div   class="meta-box-sortabless ui-sortable" style="position:relative;">

                    <div  class="postbox">
                        <h3 class="hndle"><span>Sitemap related urls</span></h3>
                        <div class="inside">
						<p>Pages that are created or modified by Xml Sitemap Generator</p>
                            <ul>
		<?php  
			$url = get_bloginfo( 'url' ) ;
		
			echo '<li><a target="_blank" href="' . $url .'/xmlsitemap.xml">XML Sitemap</a></li>';
			echo '<li><a target="_blank" href="' . $url .'/rsssitemap.xml">RSS Sitemap</a></li>';
			echo '<li><a target="_blank" href="' . $url .'/rssnewsitemap.xml">New Pages RSS</a></li>';
			echo '<li><a target="_blank" href="' . $url .'/robots.txt">Robots.txt</a></li>';
		
		?>
		
							</ul>
               
                        </div>
                    </div>
					

                    <div  class="postbox">
                        <h3 class="hndle"><span>Webmaster tools</span></h3>
                        <div class="inside">
						<p>It is highly recommended you register your sitemap 
						with webmaster tools to obtain performance insights.</p>
                            <ul>
								<li><a href="https://www.google.com/webmasters/tools/">Google Webmaster tools</a></li>
								<li><a href="http://www.bing.com/toolbox/webmaster">Bing Webmaster tools</a></li>
								<li><a href="http://zhanzhang.baidu.com/">Baidu Webmaster tools</a></li>
								<li><a href="https://webmaster.yandex.com/">Yandex Webmaster tools</a></li>
								
							</ul>
               
                        </div>
                    </div>
				
				
                    <div  class="postbox">
                        <h3 class="hndle"><span>Useful links</span></h3>
                        <div class="inside">
                            <ul>
							<li><a href="https://xmlsitemapgenerator.org/Wordpress-sitemap-plugin.aspx">Help and support</a></li>
								<li><a href="http://blog.xmlsitemapgenerator.org/">blog.XmlSitemapGenerator.org</a></li>
								<li><a href="https://twitter.com/createsitemaps">twitter : @CreateSitemaps</a></li>
								<li><a href="https://www.facebook.com/XmlSitemapGenerator">facebook XmlSitemapGenerator</a></li>
		
							</ul>
               
                        </div>
                    </div>

					
					<div  class="postbox">
                        <h3 class="hndle"><span>Please support us</span></h3>
                        <div class="inside">
						<p>We take time out of our personal lives to develop and support our sitemap tools and cover costs out of our own pockets. 
						</p>
						<p>Please help with costs by making a small contribution..</p>
                           
						<p><a target="_blank" href="https://xmlsitemapgenerator.org/donate.aspx">Make a small donation</a></p>
                        </div>
                    </div>
					
                </div>
            </div>

		
			
 <div class="has-sidebar">
 

					
<div id="post-body-content" class="has-sidebar-content">
				
	<div class="meta-box-sortabless">

			<script type="text/javascript" src="<?php echo myPluginPath(); ?>scripts.js"></script>
	<div  class="postbox">
		<h3 class="hndle"><span>General settings</span></h3>
		<div class="inside">

               
				<p>General options for your sitemap. 
				We recommend you enable all of these.</p>

					<ul>
						<li>
							<input type="checkbox" name="pingSitemap" id="pingSitemap" value="1" <?php checked($globalSettings->pingSitemap, '1'); ?> /> 
							<label for="sm_b_ping">Automatically ping Google / Bing (MSN & Yahoo) daily</label><br>
						</li>
						<li>
							<input type="checkbox" name="addRssToHead" id=""addRssToHead" value="1" <?php checked($globalSettings->addRssToHead, '1'); ?> />
							<label for="sm_b_ping">Add latest pages / post RSS feed to head tag</label><br>
						</li>
						<li>
							<input type="checkbox" name="addToRobots" id="addToRobots" value="1" <?php checked($globalSettings->addToRobots, '1'); ?> />
							<label for="sm_b_ping">Add sitemap links to your robots.txt file</label><br>
						</li>
						<li>
							<input type="checkbox" name="sendStats" id="sendStats" value="1" <?php checked($globalSettings->sendStats, '1'); ?> />
							<label for="sm_b_ping">Help us improve by allowing basic usage stats (Page count, PHP Version, feature usage, etc.)</label><br>
						</li>
						<li>
							<input type="checkbox" name="smallCredit" id="smallCredit" value="1" <?php checked($globalSettings->smallCredit, '1'); ?> />
							<label for="sm_b_ping">Support us by allowing a small credit in the sitemap file footer (Does not appear on your website)</label><br>
						</li>
					</ul>
		</div>
	</div> 

	<div  class="postbox">
		<h3 class="hndle"><span>Sitemap defaults</span></h3>
		<div class="inside">
		
               
				<p>Set the defaults for your sitemap here.</p>
				
				<ul>
										<li>
							<select name="dateField" id="dateField">
								<option  <?php  if ($sitemapDefaults->dateField == "created") {echo 'selected="selected"';} ?>>created</option>
								<option <?php  if ($sitemapDefaults->dateField == "updated") {echo 'selected="selected"';} ?>>updated</option>
							</select>
							<label for="sm_b_ping">  date field to use for modified date / recently updated.</label><br>
						</li>
					</ul>
					
				<p>You can override the sitemap default settings for taxonomy items (categories, tags, etc), pages and posts when adding and editing them.</p>
		
						<table class="wp-list-table widefat fixed striped tags" style="clear:none;">
							<thead>
							<tr>
								<th scope="col">Page / area</th>
								<th scope="col">Exclude</th>
								<th scope="col">Relative priority</th>
								<th scope="col">Update frequency</th>
							</tr>
							</thead>
							<tbody id="the-list" >
							
							
<?php 

		self::RenderDefaultSection("Home page","homepage",$sitemapDefaults->homepage);
		self::RenderDefaultSection("Regular page","pages",$sitemapDefaults->pages);
		self::RenderDefaultSection("Post page","posts",$sitemapDefaults->posts);
		self::RenderDefaultSection("Taxonomy - categories","taxonomyCategories",$sitemapDefaults->taxonomyCategories);
		self::RenderDefaultSection("Taxonomy - tags","taxonomyTags",$sitemapDefaults->taxonomyTags);
 
		self::RenderDefaultSection("Archive - recent","recentArchive",$sitemapDefaults->recentArchive);
		self::RenderDefaultSection("Archive - old","oldArchive",$sitemapDefaults->oldArchive);
		self::RenderDefaultSection("Authors","authors",$sitemapDefaults->authors);
		

 ?>
							
						
				
							
						</tbody></table>
                		 
          </div>
	</div>

	<div  class="postbox">
		<h3 class="hndle"><span>Log</span></h3> 
		<div class="inside">
			   		 
			<?php echo	core::getStatusHtml();?>
		</div>
	</div> 
				
			

</div>

</div>

<?php submit_button(); ?>

</div>
 
		


</div>
</div>


</form>

<script>
		function setPayment(amount,term)
		{	
			if (term =='')
			{
				document.getElementById("paypalCdm").value = "_xclick";
				document.getElementById("amount").value = amount;
			}
			else
			{
				document.getElementById("paypalCdm").value = "_xclick-subscriptions";
				document.getElementById("t3").value = term;	
				document.getElementById("a3").value = amount;	
			}
		}
		function paymentChange()
		{
			var e = document.getElementById("paymetnTerm");
			var value = e.options[e.selectedIndex].value;
			switch(value) {
				case "1":
					setPayment(1,"M");
					break;
				case "2":
					setPayment(2,"Y");
					break;
				case "3":
					setPayment(5,"Y");
					break;
				case "4":
					setPayment(10,"Y");
					break;
				case "5":
					setPayment(1,"");
					break;
				case "6":
					setPayment(2,"");
					break;
				case "7":
					setPayment(5,"");
					break;
				case "8":
					setPayment(10,"");
					break;
			    case "9":
			        setPayment(20, "");
			        break;
			}
		}
	</script>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">

<input id="paypalCdm" type="hidden" name="cmd" value="_xclick-subscriptions">
<input type="hidden" id="a3"  name="a3" value="5.00">
<input type="hidden" name="p3" value="1">
<input type="hidden" id="t3" name="t3" value="Y">

<input id="amount" type="hidden" name="amount" value="">

<input type="hidden" name="business" value="GT3K2HD88M766">
<input type="hidden" name="lc" value="GB">
<input type="hidden" name="item_name" value="Help keep XML Sitemaps free.">
<input type="hidden" name="no_note" value="0">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="rm" value="1">
<input type="hidden" name="return" value="https://xmlsitemapgenerator.org/donated.aspx">
<input type="hidden" name="cancel_return" value="https://xmlsitemapgenerator.org">
<input type="hidden" name="currency_code" value="GBP">
<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
<input type="hidden" name="page_style" value="xml">


</form>




	<?php 

	}
	

	
}	 




?>
