<?php
class extension_tag_filter extends Extension {
	
	public $cookie = null;

	/*-------------------------------------------------------------------------
		Extension definition
	-------------------------------------------------------------------------*/
	public function about() {
		return array( 'name' => 'tag_filter',
			'version' => '0.1',
			'release-date' => '2019-03-07',
			'author' => array( 'name' => 'Michele' ),
			'description' => 'Tag filter'
		);
	}

	public function uninstall() {
		return Symphony::Database()->query("DROP TABLE `tbl_fields_tag_filter`");

		// Remove preferences
		// Nothing to write at the moment
		// Symphony::Configuration()->remove( 'tag_filter' );
		// Symphony::Configuration()->write();
		// Administration::instance()->saveConfig();
	}

	public function install() {
		// this is a simple TagList field
		return Symphony::Database()->query("CREATE TABLE
			`tbl_fields_tag_filter` (
			`id` int(11) unsigned NOT NULL auto_increment,
			`field_id` int(11) unsigned NOT NULL,
			`validator` varchar(100) default NULL,
			`pre_populate_source` varchar(255) default NULL,			
			PRIMARY KEY  (`id`),
			KEY `field_id` (`field_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
	}

	public function getSubscribedDelegates() {
		return array(

			array(
				'page' => '/frontend/',
				'delegate' => 'FrontendInitialised',
				'callback' => 'frontendInitialised'
			),			

			array(
				'page' => '/frontend/',
				'delegate' => 'FrontendParamsResolve',
				'callback' => 'frontendParamsResolve'
			),
		);
	}

	public function frontendInitialised( ) {		
		// initialize cookie if needed
		if (is_null($this->cookie)) {
			// keep valid for 600 sec (10 min)	
			$this->cookie = Symphony::Engine()->Cookie;
		}
	}
	
	// url-tagaction: is a comma-separated value of 'actions' that will be execute in given order:
	// action prototype : <action>:<tag> 
	// actions : 'c' => clean, 'a' => add, 'r' => remove
	// i.e. "c,a:tag1, a:tag2, r:tag3" (clean all tags, then add tag1/tag2 and remove tag3)
	public function parseTagActions($tagactions) {
		if (isset($tagactions) == false || $tagactions == "") {
			return NULL;
		}
		$commasplit = explode(',', $tagactions);
		// commasplit is a list of <action>:<target> pair
		$cmds = array();
		foreach ($commasplit as $split) {
			$tsplit = trim($split);
			$ss = explode(':', $tsplit);
			$action = trim($ss[0]);	// must exist!!!
			switch ($action) {
				case "c":
					$cmds[] = array("action" => "clean", "value" => "");
					break;
				case "a":
					if (count($ss) > 1 && $ss[1] !== "") {
						$cmds[] = array("action" => "add", "value" => trim($ss[1]));
					}
					break;
				case "r":
					if (count($ss) > 1 && $ss[1] !== "") {
						$cmds[] = array("action" => "rem", "value" => trim($ss[1]));
					}
					break;
				default:					
			} 
		}				
		return $cmds;
	}


	public function frontendParamsResolve( array $context = null ) {	
		
		$taglist = $this->cookie->get('taglist');	
		if (is_null($taglist)) {
			$this->cookie->set('taglist', '');
		}
		if ($taglist == '') {
			$context['params']['taglist'] = array();
		} else {
			$context['params']['taglist'] = explode(' ', $taglist);
		}

		if (isset($context['params']['url-tagaction'])) {
			$cmds = $this->parseTagActions($context['params']['url-tagaction']);
			if ($cmds !== NULL) {
				foreach ($cmds as $action) {
					if ($action["action"] == "add") {
						if (in_array($action["value"], $context['params']['taglist']) == false) {
							// add this
							$context['params']['taglist'][] = $action["value"];
						}
					} elseif ($action["action"] == "rem") {
						if (in_array($action["value"], $context['params']['taglist'])) {
							// delete this
							$todel = $action["value"];
							array_splice($context['params']['taglist'], array_search($todel, $context['params']['taglist'] ), 1);
							// unset($context['params']['taglist'][$todel]);
						}	
					} elseif ($action["action"] == "clean") {
						$context['params']['taglist'] = array();
					}
				}
			}
		}
		$this->cookie->set('taglist', join(' ',$context['params']['taglist']));					
	}	
}
