<?php

class GradLeadPlugin extends SJB_PluginAbstract
{
    const LISTING_TYPES_TABLE = "listing_types";
    const LISTING_FIELD_LIST_TABLE = "listing_field_list";
    const POSTING_PAGES_TABLE = "posting_pages";

    const LISTING_TYPE_IDEA = "Idea";
    const LISTING_TYPE_OPPORTUNITY = "Opportunity";
    const LISTING_TYPE_SID_IDEA = 8;
    const LISTING_TYPE_SID_OPPORTUNITY = 9;

    const POSTING_PAGE_SID_IDEA = 20;
    const POSTING_PAGE_SID_OPPORTUNITY = 21;

    const LISTING_FIELD_SID_IDEA_TYPE = 194;
    const LISTING_FIELD_SID_OPPORTUNITY_TYPE = 195;
    const LISTING_FIELD_SID_CATEGORIES = 196;
    const LISTING_FIELD_SID_BADGES = 197;
    const LISTING_FIELD_SID_LOCATION = 359;

    public static function init()
    {
        GradLeadPlugin::install();
    }

    function pluginSettings()
    {
        return array(
            array(
                'id' => 'gradlead_enable_application',
                'caption' => 'Enable Application Management Feature',
                'type' => 'boolean',
                'length' => '50',
                'order' => null,
            ),
            array(
                'id' => 'gradlead_enable_badges',
                'caption' => 'Enable Badges Feature',
                'type' => 'boolean',
                'length' => '50',
                'order' => null,
            ),
            array(
                'id' => 'gradlead_enable_opportunity',
                'caption' => 'Enable Opportunity Feature',
                'type' => 'boolean',
                'length' => '50',
                'order' => null,
            ),
            array(
                'id' => 'gradlead_enable_screening',
                'caption' => 'Enable Questionnaire Screening Feature',
                'type' => 'boolean',
                'length' => '50',
                'order' => null,
            ),
        );
    }

    public static function install()
    {
        $groups = array(
            array
            (
                'type' => 'Investor',
                'product' => 'Free Opportunity Posting',
                'desc' => 'Post your opportunities to attract entrepreneurs.',
                'product_extra' => array('idea_access'=>1),
                'list_type_sid' => GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY
            ),
            array(
                'type' => 'Entrepreneur',
                'product' => 'Free Idea Posting',
                'product_extra' => array('opportunity_access'=>1),
                'desc' => 'Post your ideas to attract investors.',
                'list_type_sid' => GradLeadPlugin::LISTING_TYPE_SID_IDEA
            )
        );

        GradLeadPlugin::setupListingTypes();
        GradLeadPlugin::setupListingTypeFields();
        GradLeadPlugin::setupPostingPages();
        GradLeadPlugin::setupUserGroups($groups);
        
        GradLeadPlugin::loadModules();

        SJB_Event::handle('onAfterAdminMenuCreated', array('GradLeadPlugin', 'setupMenus'));
    }

    public static function loadModules() 
    {
        $plugin = SJB_PluginManager::getPluginByName('GradLeadPlugin');
        $isPluginActive = $plugin && $plugin['active'] == '1';

        if (!$isPluginActive) {
            return;
        }
        
        //if (SJB_Settings::getSettingByName('gradlead_enable_badges')) {
        //    SJB_System::getModuleManager()->includeModule(SJB_BASE_DIR . 'system/plugins/gradlead_plugin/modules', 'badges');
        //    $modules = SJB_System::getModuleManager()->getModulesList();
        //    require_once __DIR__ . '/modules/badges/Badges.php';
        //}
    }

    private static function setupUserGroups($groups)
    {
        foreach ($groups as $group) {
            $sid = SJB_UserGroupManager::getUserGroupSIDByID($group['type']);
            if ($sid == "") {
                $userGroupInfo = array('name' => $group['type'], 'id' => $group['type']);
                $userGroup = new SJB_UserGroup($userGroupInfo);
                SJB_UserGroupManager::saveUserGroup($userGroup);

                $sid = SJB_UserGroupManager::getUserGroupSIDByID($group['type']);
                $pid = GradLeadPlugin::createProduct($sid, $group['list_type_sid'], $group['type'], $group['product'], $group['desc'], $group['product_extra']);
                SJB_UserGroupManager::setDefaultProduct($sid, $pid);
                GradLeadPlugin::setupGroupProfile($sid);
            }
        }
    }

    private static function setupGroupProfile($sid) {
        $sql = "INSERT INTO `user_profile_fields` (`user_group_sid`, `order`, `id`, `caption`, `type`, `default_value`, `is_required`, `instructions`, `maxlength`, `width`, `height`, `second_width`, `second_height`, `template`, `level_1`, `level_2`, `level_3`, `level_4`, `display_as_select_boxes`, `parent_sid`, `hidden`, `display_as`, `choiceLimit`) VALUES
                    (SID, 0, 'Logo', 'Logo', 'logo', NULL, 0, NULL, NULL, 250, 250, 150, 150, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 0),
                    (SID, 1, 'CompanyName', 'Company Name', 'string', '', 1, '', 256, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 0),
                    (SID, 2, 'FullName', 'Full Name', 'string', NULL, 0, '', 256, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 0),
                    (SID, 3, 'WebSite', 'Website', 'string', NULL, 0, '', 256, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 0),
                    (SID, 4, 'Location', 'Location', 'location', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 0),
                    (SID, 4, 'GooglePlace', 'Location', 'google_place', NULL, 0, '', 256, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 0),
                    (SID, 5, 'Country', 'Country', 'string', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 119, 0, 'country_name', 0),
                    (SID, 6, 'State', 'State', 'string', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 119, 0, 'state_name', 0),
                    (SID, 7, 'City', 'City', 'string', NULL, 0, NULL, 256, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 119, 0, NULL, 0),
                    (SID, 8, 'CompanyDescription', 'Company Description', 'text', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'text.tpl', NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 0),
                    (SID, 10, 'ZipCode', 'Zip Code', 'string', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 119, 0, NULL, 0),
                    (SID, 11, 'Latitude', 'Latitude', 'string', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 119, 0, NULL, 0),
                    (SID, 12, 'Longitude', 'Longitude', 'string', '', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 119, 0, NULL, 0)";
        $sql = preg_replace("/SID/", $sid, $sql);
        SJB_DB::query($sql);
    }

    private static function createProduct($userGroupSID, $listingTypeSID, $type, $productName, $productDesc, $extra)
    {
        $productInfo = array(
            'name' => $productName,
            'detailed_description' => $productDesc,
            'user_group_sid' => $userGroupSID,
            'listing_type_sid' => $listingTypeSID,
            'active'=>1,
        );
        if (sizeof($extra)) {
            foreach($extra as $key => $val) {
                $productInfo[$key] = $val;
            }
        }
        GradLeadPlugin::deleteProduct($userGroupSID, $type);
        $product = new SJB_Product($productInfo, 'mixed_product');
        $product->saveProduct($product);
        return $product->getSID();
    }

    private static function deleteProduct($userGroupSID, $type)
    {
        $products = SJB_ProductsManager::getProductsInfoByUserGroupSID($userGroupSID);
        foreach ($products as $product) {
            if (strpos(strtolower($product['name']), $type) !== false) {
                SJB_ProductsManager::deleteProductBySID($product['sid']);
            }
        }
    }

    private static function setupPostingPages()
    {
        SJB_DB::query('INSERT INTO `' . GradLeadPlugin::POSTING_PAGES_TABLE . '` SET `sid` = ?s, `page_id` = ?s, `page_name`=?s, `listing_type_sid` = ?s, `order`=3 ON DUPLICATE KEY UPDATE `page_id`= ?s, `page_name` = ?s',
            GradLeadPlugin::POSTING_PAGE_SID_IDEA
            , "PostIdea"
            , GradLeadPlugin::LISTING_TYPE_IDEA
            , GradLeadPlugin::LISTING_TYPE_SID_IDEA
            , "PostIdea"
            , GradLeadPlugin::LISTING_TYPE_IDEA
        );

        SJB_DB::query('INSERT INTO `' . GradLeadPlugin::POSTING_PAGES_TABLE . '` SET `sid` = ?s, `page_id` = ?s, `page_name`= ?s, `listing_type_sid` = ?s, `order`=4 ON DUPLICATE KEY UPDATE `page_id`= ?s, `page_name` = ?s',
            GradLeadPlugin::POSTING_PAGE_SID_OPPORTUNITY
            , "PostOpportunity"
            , GradLeadPlugin::LISTING_TYPE_OPPORTUNITY
            , GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY
            , "PostOpportunity"
            , GradLeadPlugin::LISTING_TYPE_OPPORTUNITY
        );
    }

    private static function setupListingTypes()
    {
        SJB_DB::query('INSERT INTO `' . GradLeadPlugin::LISTING_TYPES_TABLE . '` SET `sid` = ?s, `id` = ?s, `name` = ?s ON DUPLICATE KEY UPDATE `name` = ?s',
            GradLeadPlugin::LISTING_TYPE_SID_IDEA
            , GradLeadPlugin::LISTING_TYPE_IDEA
            , GradLeadPlugin::LISTING_TYPE_IDEA
            , GradLeadPlugin::LISTING_TYPE_IDEA);

        SJB_DB::query('INSERT INTO `' . GradLeadPlugin::LISTING_TYPES_TABLE . '` SET `sid` = ?s, `id` = ?s, `name` = ?s ON DUPLICATE KEY UPDATE `name` = ?s',
            GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY
            , GradLeadPlugin::LISTING_TYPE_OPPORTUNITY
            , GradLeadPlugin::LISTING_TYPE_OPPORTUNITY
            , GradLeadPlugin::LISTING_TYPE_OPPORTUNITY);
    }

    private static function setupListingTypeFields()
    {
        $sql = "INSERT INTO `listing_fields` SET `sid` = ?s, `id` = ?s, `listing_type_sid` = ?s, `order`= ?s, `caption`= ?s, `type`=?s, `is_required` = ?s, `sort_by_alphabet` = ?s, `display_as_select_boxes` = ?s, `choiceLimit` = ?s, `hidden` = ?s, `display_as` = ?s ON DUPLICATE KEY UPDATE `id` = ?s";
        SJB_DB::query($sql, GradLeadPlugin::LISTING_FIELD_SID_CATEGORIES, 'OpportunityCategory', GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY, 2, 'Categories', 'multilist', 1, 1, 0, 5, 0, 'multilist', 'OpportunityCategory');

        $sql = "INSERT INTO `listing_fields` (`sid`, `id`, `listing_type_sid`, `order`, `caption`, `type`, `default_value`, `is_required`, `instructions`, `maxlength`, `width`, `height`, `second_width`, `second_height`, `sort_by_alphabet`, `template`, `minimum`, `maximum`, `signs_num`, `display_as_select_boxes`, `level_1`, `level_2`, `level_3`, `level_4`, `choiceLimit`, `add_parameter`, `parent_sid`, `hidden`, `display_as`) VALUES (" . GradLeadPlugin::LISTING_FIELD_SID_IDEA_TYPE . ", 'IdeaType', ".GradLeadPlugin::LISTING_TYPE_SID_IDEA.", 2, 'Idea Stage', 'list', '', 1, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 5, NULL, NULL, 0, 'multilist') ON DUPLICATE KEY UPDATE `id`='IdeaType'";
        SJB_DB::query($sql);

        $sql = "INSERT INTO `listing_fields` (`sid`, `id`, `listing_type_sid`, `order`, `caption`, `type`, `default_value`, `is_required`, `instructions`, `maxlength`, `width`, `height`, `second_width`, `second_height`, `sort_by_alphabet`, `template`, `minimum`, `maximum`, `signs_num`, `display_as_select_boxes`, `level_1`, `level_2`, `level_3`, `level_4`, `choiceLimit`, `add_parameter`, `parent_sid`, `hidden`, `display_as`) VALUES (" . GradLeadPlugin::LISTING_FIELD_SID_OPPORTUNITY_TYPE . ", 'OpportunityType', ".GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY.", 2, 'Opportunity Type', 'list', '', 1, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 5, NULL, NULL, 0, 'multilist') ON DUPLICATE KEY UPDATE `id`='OpportunityType'";
        SJB_DB::query($sql);

        $listingFields = SJB_ListingFieldManager::getListingFieldsInfoByListingType(GradLeadPlugin::LISTING_TYPE_SID_IDEA);
        if (sizeof($listingFields) == 0) {
            $sql = "INSERT INTO `listing_fields` (`id`, `listing_type_sid`, `order`, `caption`, `type`, `default_value`, `is_required`, `instructions`, `maxlength`, `width`, `height`, `second_width`, `second_height`, `sort_by_alphabet`, `template`, `minimum`, `maximum`, `signs_num`, `display_as_select_boxes`, `level_1`, `level_2`, `level_3`, `level_4`, `choiceLimit`, `add_parameter`, `parent_sid`, `hidden`, `display_as`) VALUES "
                . "('Title', " . GradLeadPlugin::LISTING_TYPE_SID_IDEA . ", 1, 'Idea Title', 'string', '', 1, '', '256', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, NULL), "
                . "('JobDescription', " . GradLeadPlugin::LISTING_TYPE_SID_IDEA . ", 7, 'Idea Description', 'text', NULL, 0, '', '99999', NULL, NULL, NULL, NULL, 0, 'text.tpl', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, NULL)";
            SJB_DB::query($sql);
            SJB_DB::query("ALTER TABLE `listings` ADD `IdeaType` TEXT NULL, ADD `OpportunityType` TEXT NULL, ADD `OpportunityCategory` TEXT NULL");
        }

        $listings = SJB_ListingFieldManager::getListingFieldsInfoByListingType(GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY);
        if (sizeof($listings) == 0) {
            $sql = "INSERT INTO `listing_fields` (`id`, `listing_type_sid`, `order`, `caption`, `type`, `default_value`, `is_required`, `instructions`, `maxlength`, `width`, `height`, `second_width`, `second_height`, `sort_by_alphabet`, `template`, `minimum`, `maximum`, `signs_num`, `display_as_select_boxes`, `level_1`, `level_2`, `level_3`, `level_4`, `choiceLimit`, `add_parameter`, `parent_sid`, `hidden`, `display_as`) VALUES "
                . " ('Title', " . GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY . ", 1, 'Opportunity Title', 'string', '', 1, '', '256', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, NULL), "
                . " ('JobDescription', " . GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY . ", 7, 'Opportunity Description', 'text', NULL, 0, '', '99999', NULL, NULL, NULL, NULL, 0, 'text.tpl', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, NULL), "
                . " ('expiration_date'," . GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY . ", 13, 'Expiration Date', 'date', NULL, 0, '', '', 0, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, NULL)";
            SJB_DB::query($sql);
        }

        GradLeadPlugin::addListingFieldsOnPage();
        GradLeadPlugin::addPages();
    }

    private static function addListingFieldsOnPage()
    {
        $pages = SJB_PostingPagesManager::getPagesByListingTypeSID(GradLeadPlugin::LISTING_TYPE_SID_IDEA);
        if (isset($pages['fields_num']) && $pages['fields_num'] == 0) {
            $fields = SJB_ListingFieldManager::getListingFieldsInfoByListingType(GradLeadPlugin::LISTING_TYPE_SID_IDEA);
            foreach ($fields as $f) {
                SJB_PostingPagesManager::addListingFieldOnPage($f['sid'], GradLeadPlugin::POSTING_PAGE_SID_IDEA, $f['listing_type_sid']);
            }
            SJB_PostingPagesManager::addListingFieldOnPage(GradLeadPlugin::LISTING_FIELD_SID_LOCATION, GradLeadPlugin::POSTING_PAGE_SID_IDEA, GradLeadPlugin::LISTING_TYPE_SID_IDEA);
            SJB_PostingPagesManager::addListingFieldOnPage(GradLeadPlugin::LISTING_FIELD_SID_CATEGORIES, GradLeadPlugin::POSTING_PAGE_SID_IDEA, GradLeadPlugin::LISTING_TYPE_SID_IDEA);
            SJB_PostingPagesManager::addListingFieldOnPage(GradLeadPlugin::LISTING_FIELD_SID_IDEA_TYPE, GradLeadPlugin::POSTING_PAGE_SID_IDEA, GradLeadPlugin::LISTING_TYPE_SID_IDEA);
        }

        $pages = SJB_PostingPagesManager::getPagesByListingTypeSID(GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY);
        if (isset($pages['fields_num']) && $pages['fields_num'] == 0) {
            $fields = SJB_ListingFieldManager::getListingFieldsInfoByListingType(GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY);
            foreach ($fields as $f) {
                SJB_PostingPagesManager::addListingFieldOnPage($f['sid'], GradLeadPlugin::POSTING_PAGE_SID_OPPORTUNITY, $f['listing_type_sid']);
            }
            SJB_PostingPagesManager::addListingFieldOnPage(GradLeadPlugin::LISTING_FIELD_SID_LOCATION, GradLeadPlugin::POSTING_PAGE_SID_OPPORTUNITY, GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY);
            SJB_PostingPagesManager::addListingFieldOnPage(GradLeadPlugin::LISTING_FIELD_SID_CATEGORIES, GradLeadPlugin::POSTING_PAGE_SID_OPPORTUNITY, GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY);
            SJB_PostingPagesManager::addListingFieldOnPage(GradLeadPlugin::LISTING_FIELD_SID_OPPORTUNITY_TYPE, GradLeadPlugin::POSTING_PAGE_SID_OPPORTUNITY, GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY);
        }
    }

    private static function addPages()
    {
        if (!GradLeadPlugin::inPages('/manage-opportunities/')) {
            $params = array('listing_type_sid' => GradLeadPlugin::LISTING_TYPE_SID_OPPORTUNITY);
            $sql = "INSERT INTO `pages` SET `uri` = ?s, `module` = ?s, `function` = ?s, `template`= ?s, `title` = ?s, `access_type`= ?s, `parameters`= ?s ON DUPLICATE KEY UPDATE `uri` = ?s";
            SJB_DB::query($sql, '/manage-opportunities/', 'classifieds', 'manage_listings', 'index.tpl', 'Manage Opportunities', 'admin', serialize($params), '/manage-opportunities/');

            $params = array('default_sorting_field' => '', 'activation_date'=>'', 'default_sorting_order'=>'DESC',
                            'default_listings_per_page'=>20, 'results_template'=>'search_results_opportunities.tpl', 'listing_type_id'=>'Opportunity');
            $sql = "INSERT INTO `pages` SET `uri` = ?s, `module` = ?s, `function` = ?s, `template`= ?s, `title` = ?s, `access_type`= ?s, `parameters`= ?s ON DUPLICATE KEY UPDATE `uri` = ?s";
            SJB_DB::query($sql, '/opportunities/', 'classifieds', 'search_results', 'display.tpl', 'Opportunities', 'user', serialize($params), '/opportunities/');

            $sql = <<<EOD
INSERT INTO `pages` (`uri`, `pass_parameters_via_uri`, `module`, `function`, `template`, `title`, `access_type`, `parameters`, `keywords`, `description`, `content`) VALUES
('/my-opportunities/', 0, 'classifieds', 'my_listings', '', '', 'user', 'a:1:{s:15:"listing_type_id";s:11:"Opportunity";}', '', '', NULL),
('/my-opportunity-details/', 1, 'classifieds', 'display_my_listing', 'display.tpl', 'My Opportunity Details', 'user', 'a:1:{s:16:"display_template";s:23:"display_opportunity.tpl";}', NULL, '', NULL),
('/investor-products/', 0, 'payment', 'user_products', '', 'Pricing', 'user', 'a:1:{s:11:"userGroupID";s:8:"Investor";}', '', '', NULL),
('/opportunity/', 1, 'classifieds', 'display_listing', 'display.tpl', NULL, 'user', 'a:2:{s:16:"display_template";s:23:"display_opportunity.tpl";s:15:"listing_type_id";s:11:"Opportunity";}', NULL, '', NULL),
('/opportunity-preview/', 1, 'classifieds', 'display_my_listing', '', 'Opportunity Preview', 'user', 'a:1:{s:16:"display_template";s:23:"display_opportunity.tpl";}', '', '', NULL),
('/opportunity-import/', 0, 'classifieds', 'opportunity_import', 'index.tpl', 'Import', 'user', 'a:1:{s:15:"listing_type_id";s:11:"Opportunity";}', '', '', NULL),
('/find-opportunities/', 0, 'classifieds', 'search_form', 'index.tpl', 'Find Opportunities', 'user', 'a:1:{s:15:"listing_type_id";s:11:"Opportunity";}', '', '', NULL),
('/edit-opportunity/', 1, 'classifieds', 'edit_listing', '', 'Edit Opportunity', 'user', 's:0:"";', '', '', NULL)";
EOD;
            SJB_DB::query($sql);
        }

        if (!GradLeadPlugin::inPages('/manage-ideas/')) {
            $params = array('listing_type_sid' => GradLeadPlugin::LISTING_TYPE_SID_IDEA);
            $sql = "INSERT INTO `pages` SET `uri` = ?s, `module` = ?s, `function` = ?s, `template`= ?s, `title` = ?s, `access_type`= ?s, `parameters`= ?s ON DUPLICATE KEY UPDATE `uri` = ?s";
            SJB_DB::query($sql, '/manage-ideas/', 'classifieds', 'manage_listings', 'index.tpl', 'Manage Ideas', 'admin', serialize($params), '/manage-ideas/');

            $params = array('default_sorting_field' => '', 'activation_date'=>'', 'default_sorting_order'=>'DESC',
                            'default_listings_per_page'=>20, 'results_template'=>'search_results_idea.tpl', 'listing_type_id'=>'Idea');
            $sql = "INSERT INTO `pages` SET `uri` = ?s, `module` = ?s, `function` = ?s, `template`= ?s, `title` = ?s, `access_type`= ?s, `parameters`= ?s ON DUPLICATE KEY UPDATE `uri` = ?s";
            SJB_DB::query($sql, '/ideas/', 'classifieds', 'search_results', 'display.tpl', 'Ideas', 'user', serialize($params), '/ideas/');

            $sql = <<<EOD
INSERT INTO `pages` (`uri`, `pass_parameters_via_uri`, `module`, `function`, `template`, `title`, `access_type`, `parameters`, `keywords`, `description`, `content`) VALUES
('/my-ideas/', 0, 'classifieds', 'my_listings', '', '', 'user', 'a:1:{s:15:"listing_type_id";s:4:"Idea";}', '', '', NULL),
('/my-idea-details/', 1, 'classifieds', 'display_my_listing', 'display.tpl', 'My Idea Details', 'user', 'a:1:{s:16:"display_template";s:16:"display_idea.tpl";}', NULL, '', NULL),
('/entrepreneur-products/', 0, 'payment', 'user_products', '', 'Pricing', 'user', 'a:1:{s:11:"userGroupID";s:12:"Entrepreneur";}', '', '', NULL),
('/idea/', 1, 'classifieds', 'display_listing', 'display.tpl', NULL, 'user', 'a:2:{s:16:"display_template";s:16:"display_idea.tpl";s:15:"listing_type_id";s:4:"Idea";}', NULL, '', NULL),
('/idea-preview/', 1, 'classifieds', 'display_my_listing', '', 'Idea Preview', 'user', 'a:1:{s:16:"display_template";s:16:"display_idea.tpl";}', '', '', NULL),
('/idea-import/', 0, 'classifieds', 'idea_import', 'index.tpl', 'Import', 'user', 'a:1:{s:15:"listing_type_id";s:4:"Idea";}', '', '', NULL),
('/find-ideas/', 0, 'classifieds', 'search_form', 'index.tpl', 'Find Ideas', 'user', 'a:1:{s:15:"listing_type_id";s:4:"Idea";}', '', '', NULL),
('/edit-idea/', 1, 'classifieds', 'edit_listing', '', 'Edit Idea', 'user', 's:0:"";', '', '', NULL);
EOD;
            SJB_DB::query($sql);
        }
        
        
        if (!GradLeadPlugin::inPages('/investor/')) {
            $sql = <<<EOD
            INSERT INTO `pages` (`uri`, `pass_parameters_via_uri`, `module`, `function`, `template`, `title`, `access_type`, `parameters`, `keywords`, `description`, `content`) VALUES
	        ('/investor/',1,'classifieds','search_results','display.tpl','','user','a:5:{s:21:"default_sorting_field";s:15:"activation_date";s:21:"default_sorting_order";s:4:"DESC";s:25:"default_listings_per_page";s:2:"20";s:16:"results_template";s:32:"search_results_opportunities.tpl";s:15:"listing_type_id";s:11:"Opportunity";}','','',NULL);
EOD;
            SJB_DB::query($sql);
        }
        
        if (!GradLeadPlugin::inPages('/apply-now-opportunity/')) {
            $sql = <<<EOD
            INSERT INTO `pages` (`uri`, `pass_parameters_via_uri`, `module`, `function`, `template`, `title`, `access_type`, `parameters`, `keywords`, `description`, `content`) VALUES
	        ('/apply-now-opportunity/',0,'classifieds','apply_now_opportunity','','Apply Now','user','N;','','',NULL);
EOD;
            SJB_DB::query($sql);
        }
        
        if (!GradLeadPlugin::inPages('/products/investor/')) {
            $sql = <<<EOD
            INSERT INTO `pages` (`uri`, `pass_parameters_via_uri`, `module`, `function`, `template`, `title`, `access_type`, `parameters`, `keywords`, `description`, `content`) VALUES
	        ('/products/investor/',NULL,'payment','products','index.tpl','Products','admin','a:1:{s:13:"user_group_id";s:8:"Investor";}','','',NULL);
EOD;
            SJB_DB::query($sql);
        }
        
        if (!GradLeadPlugin::inPages('/products/entrepreneur/')) {
            $sql = <<<EOD
            INSERT INTO `pages` (`uri`, `pass_parameters_via_uri`, `module`, `function`, `template`, `title`, `access_type`, `parameters`, `keywords`, `description`, `content`) VALUES
	        ('/products/entrepreneur/',NULL,'payment','products','index.tpl','Products','admin','a:1:{s:13:"user_group_id";s:12:"Entrepreneur";}','','',NULL);
EOD;
            SJB_DB::query($sql);
        }
        
        if (!GradLeadPlugin::inPages('/screening-questionnaires/')) {
            $sql = <<<EOD
            ALTER TABLE `applications` ADD `status` ENUM('Pending','Rejected','Approved') DEFAULT 'Pending' AFTER `email`, ADD `questionnaire` TEXT NOT NULL AFTER `status`, ADD `score` FLOAT NOT NULL AFTER `questionnaire`;
EOD;
            SJB_DB::query($sql);
            
            $sql = <<<EOD
                INSERT INTO `pages` (`uri`, `pass_parameters_via_uri`, `module`, `function`, `template`, `title`, `access_type`, `parameters`, `keywords`, `description`, `ID`, `content`) VALUES
('/screening-questionnaires/', 0, 'applications', 'screening_questionnaires', '', 'Screening Questionnaires', 'user', 'N;', '', '', 342, 0),
('/screening-questionnaires/new/', 0, 'applications', 'add_questionnaire', '', '', 'user', 'a:2:{s:6:"action";s:3:"add";s:13:"template_name";s:21:"add_questionnaire.tpl";}', '', '', 343, 0),
('/screening-questionnaires/edit/', 1, 'applications', 'add_questionnaire', '', '', 'user', 'a:2:{s:6:"action";s:4:"edit";s:13:"template_name";s:22:"edit_questionnaire.tpl";}', '', '', 344, 0),
('/screening-questionnaires/add-questions/', 1, 'applications', 'add_questions', '', '', 'user', 'a:1:{s:13:"template_name";s:17:"add_questions.tpl";}', '', '', 345, 0),
('/edit-questions/', 1, 'applications', 'edit_questions', '', '', 'user', 'N;', '', '', 346, 0),
('/edit-question/', 1, 'applications', 'edit_question', '', '', 'user', 'N;', '', '', 347, 0);
('/screening-questionnaires/delete-question/', 1, 'applications', 'edit_questions', '', '', 'user', 'N;', '', '', 348, 0),
('/applications/view-questionnaire/', 1, 'applications', 'view_questionnaire', '', '', 'user', 'N;', '', '', 349, 0);
EOD;            
            SJB_DB::query($sql);
        }
        
        
        if (!GradLeadPlugin::inPages('/edit-badge/')) {
            $sql = <<<EOD
            INSERT INTO `pages` (`uri`, `pass_parameters_via_uri`, `module`, `function`, `template`, `title`, `access_type`, `parameters`, `keywords`, `description`, `content`) VALUES
            ('/add-badge/', NULL, 'badge', 'add_badge', 'index.tpl', 'Add Badge', 'admin', '', '', '', NULL, NULL),
          ('/edit-badge/', NULL, 'badge', 'edit_badge', 'index.tpl', 'Edit Badge', 'admin', '', '', '', NULL, NULL),
          ('/user-badges/', NULL, 'badge', 'user_badge', 'index.tpl', 'User Badges', 'admin', 'a:1:{s:4:"page";s:11:"user_badges";}', '', '', NULL, NULL),
          ('/user-badge/', NULL, 'badge', 'user_badge', 'empty.tpl', 'User Badge', 'admin', 'a:1:{s:4:"page";s:10:"user_badge";}', '', '', NULL, NULL),
          ('/badges/jobseeker/',NULL,'badge','badges','index.tpl','Badges','admin','a:1{s:13:"user_group_id";s:9:"JobSeeker";}','','',NULL);
EOD;
            SJB_DB::query($sql);
        }        
    }

    private static function inPages($uri) {
        $res = SJB_DB::query("SELECT COALESCE(SUM(IF(uri='$uri',1,0)),0) AS p FROM pages");
        return ($res[0]['p'] > 0);
    }


    public static function setupMenus()
    {
        $menu = array();

        $uri = SJB_System::getSystemSettings('SITE_URL') . '/system/miscellaneous/plugins/?plugin=GradLeadPlugin&action=';

        $badgeMenu = [
            'Badges' => [
               [
                    'title' => 'Job Seeker Badges',
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/badges/jobseeker/',
                    'highlight' => [],
                ],
            ]];
        
        $oppMenu = [
            'Opportunities' => [
                [
                    'title' => "Investor Profiles",
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/manage-users/investor/',
                    'highlight' => [],
                ],
                [
                    'title' => 'Opportunity Postings',
                    //'reference' => $uri . 'manage-opportunities',
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/manage-opportunities/',
                    'highlight' => [],
                ],
                [
                    'title' => 'Opportunity Products',
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/products/investor/',
                    'highlight' => [],
                ],
                [
                    'title' => 'Manage Opportunity Fields',
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/posting-pages/opportunity/edit/' . GradLeadPlugin::POSTING_PAGE_SID_OPPORTUNITY,
                    'highlight' => [],
                ],

                [
                    'title' => 'Manage Opportunity Types',
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/edit-listing-field/edit-list/?field_sid=' . GradLeadPlugin::LISTING_FIELD_SID_OPPORTUNITY_TYPE,
                    'highlight' => [],
                ],
                [
                    'title' => 'Manage Categories',
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/edit-listing-field/edit-list/?field_sid=' . GradLeadPlugin::LISTING_FIELD_SID_CATEGORIES,
                    'highlight' => [],
                ],
            ],
            'Ideas' => [
                [
                    'title' => "Entrepreneur Profiles",
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/manage-users/entrepreneur/',
                    'highlight' => [],
                ],
                [
                    'title' => 'Idea Postings',
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/manage-ideas/',
                    'highlight' => [],
                ],
                [
                    'title' => 'Idea Products',
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/products/entrepreneur/',
                    'highlight' => [],
                ],
                [
                    'title' => 'Manage Idea Fields',
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/posting-pages/idea/edit/' . GradLeadPlugin::POSTING_PAGE_SID_IDEA,
                    'highlight' => [],
                ],
                [
                    'title' => 'Manage Idea Stages',
                    'reference' => SJB_System::getSystemSettings('SITE_URL') . '/edit-listing-field/edit-list/?field_sid=' . GradLeadPlugin::LISTING_FIELD_SID_IDEA_TYPE,
                    'highlight' => [],
                ],
            ]
        ];


        if (is_array($GLOBALS)) {
            if (SJB_Settings::getSettingByName('gradlead_enable_opportunity')) {
                $GLOBALS['LEFT_ADMIN_MENU'] = array_merge(array_slice($GLOBALS['LEFT_ADMIN_MENU'], 0, 1, true), $oppMenu, array_slice($GLOBALS['LEFT_ADMIN_MENU'], 1, null, true));
            }
        
            if (SJB_Settings::getSettingByName('gradlead_enable_badges')) {
                $GLOBALS['LEFT_ADMIN_MENU'] = array_merge(array_slice($GLOBALS['LEFT_ADMIN_MENU'], 0, 1, true), $badgeMenu, array_slice($GLOBALS['LEFT_ADMIN_MENU'], 1, null, true));
            }
            return true;
        }
        return false;
    }
}
