<?php
	require_once(TOOLKIT . "/fields/field.taglist.php");
    if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
    
	// Extend base taglist
	Class fieldTag_filter extends fieldTagList {

        public function __construct(){
			Field::__construct();
			$this->_name = 'Tag Filter';
        }

        public function canFilter()
        {
            return true;
        }

		// filtering: 
		// check 'taglist' cookie variable and compare with items of this field.
		// all cookie tags must be present in tag_filter field to retrieve this Entry.
		// if 'taglist' is an empty string, Entry is retrieved.
        public function buildDSRetrievalSQL($data, &$joins, &$where, $andOperation = false) {
            $field_id = $this->get('id');

            $cookie = Symphony::Engine()->Cookie;            
            $taglist = $cookie->get('taglist');	
            // var_dump($taglist);
            if ($taglist == "") {
                // return ALL items!
                return true;
            }

            $envs = explode(' ', $taglist);
            $tagscount = 0;
            $tags = "(";
            foreach ($envs as $tag) {
                $tags .= "'".$tag."',";
                $tagscount++;
            }				
            $tags = substr_replace($tags ,")",-1);

            $where = "";
            $join = "";
            // `tbl_entries_data_{$field_id}`

            if ($tagscount > 0) {
                $where = "AND (select distinct count(*) from tbl_entries_data_{$field_id} as ftf where ftf.`entry_id` = e.`id` and ftf.`handle` in {$tags})={$tagscount}";
                $joins = "join tbl_entries_data_{$field_id} on tbl_entries_data_{$field_id}.`entry_id` = e.`id`";	
            }
			return true;
		}
	}