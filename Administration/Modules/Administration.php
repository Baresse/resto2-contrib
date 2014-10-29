<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2014 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 * 
 */

/**
 * RESTo v2 Administration module
 * 
 * Authors :
 * 
 *      jerome[dot]gasperi[at]gmail[dot]com
 *      jerome[dot]mourembles[at]capgemini[dot]com
 * 
 * This module provides html hmi to administrate RESTo
 * 
 * ** Administration **
 * 
 * 
 *    |          Resource                                      |     Description
 *    |________________________________________________________|______________________________________
 *    |  GET     administration                                |  MMI administration start
 *    |  GET     administration/users                          |  MMI list users
 *    |  GET     administration/history                        |  MMI list history
 *    |  GET     administration/users/{userid}                 |  MMI informations for {userid}
 *    |  GET     administration/users/{userid}/history         |  MMI history for {userid}
 *    |  POST    administration/users                          |  Add new user
 *    |  POST    administration/users/{userid}/rights          |  Add new rights for {userid}
 *    |  POST    administration/users/{userid}/activate        |  Activate {userid}
 *    |  POST    administration/users/{userid}/deactivate      |  Deactivate {userid}
 * 
 */
class Administration extends RestoModule {

    /*
     * Resto context
     */
    public $context;
    
    /*
     * path to php file (wanted MMI)
     */
    private $file;
    
    /*
     * Current user (only set for administration on a single user)
     */
    public $user = null;
    
    /*
     * Templates root path
     */
    private $templatesRoot;
    
    /*
     * segments
     */
    public $segments;

    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param array $options : array of module parameters
     */
    public function __construct($context, $options = array()) {
        
        parent::__construct($context, $options);
        
        $this->templatesRoot = isset($options['templatesRoot']) ? $options['templatesRoot'] : '/Modules/Administration/templates';
        
        // Set context
        $this->context = $context;
        
        /*
         * Templates
         */
        $this->startFile = $this->templatesRoot . '/AdministrationTemplateStart.php';
        $this->usersFile = $this->templatesRoot . '/AdministrationTemplateUsers.php';
        $this->userFile = $this->templatesRoot . '/AdministrationTemplateUser.php';
        $this->groupsFile = $this->templatesRoot . '/AdministrationTemplateGroups.php';
        $this->historyFile = $this->templatesRoot . '/AdministrationTemplateHistory.php';
        $this->userHistoryFile = $this->templatesRoot . '/AdministrationTemplateUserHistory.php';
        $this->userCreationFile = $this->templatesRoot . '/AdministrationTemplateUserCreation.php';
        $this->userRightCreation = $this->templatesRoot . '/AdministrationTemplateUserCreationRight.php';
        $this->footer = 'footer.php';
        $this->header = 'header.php';
    }

    /**
     * Run 
     * 
     * @param array $segments
     * @throws Exception
     */
    public function run($segments) {
        
        if ($this->context->user->profile['groupname'] !== 'admin'){
            /*
             * Only administrators can access to administration
             */
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Only available for administrator', 500);
        } 
        
        if ($this->context->method === 'POST' && $this->context->outputFormat !== 'json') {
            /*
             * Only JSON can be posted
             */
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
        
        $this->segments = $segments;
        $method = $this->context->method;

        /*
         * Switch on HTTP methods
         */
        switch ($method) {
            case 'GET':
                return $this->processGET();
            case 'POST':
                return $this->processPOST();
            default:
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
        
    }

    /**
     * Process on HTTP method POST on /administration
     * 
     * @throws Exception
     */
    private function processPOST() {

        /*
         * Can't post file on /administration
         */
        if (!isset($this->segments[0])) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
        /*
         * Switch on url segments
         */
        else {
            switch ($this->segments[0]) {
                case 'users':
                    return $this->processPostUsers();
                case 'groups':
                    return $this->processPostGroups();
                default:
                    throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
            }
        }
    }

    /**
     * Process on HTTP method GET on /administration
     * 
     * @throws Exception
     */
    private function processGET() {

        /*
         * Display start page on /administration
         */
        if (!isset($this->segments[0])) {
            return $this->to($this->startFile);
        }
        /*
         * Switch on url segments
         */
        else {
            switch ($this->segments[0]) {
                case 'users':
                    return $this->processGetUsers();
                case 'groups':
                    return $this->processGetGroups();
                case 'stats':
                    return $this->processStatistics();
                default:
                    throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
            }
        }
    }
    
    /**
     * Process when GET on /administration/groups
     * 
     * @throws Exception
     */
    private function processGetGroups() {

        /*
         * Get user creation MMI
         */   
        if (isset($this->segments[1])) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
        /*
         * Users list MMI
         */
        else {
            $this->groups = $this->context->dbDriver->listGroups();
            $this->collections = $this->context->dbDriver->listCollections();
            return $this->to($this->groupsFile, $this->groups);
        }
    }
    
    /**
     * Process when POST on /administration/groups
     * 
     * @throws Exception
     */
    private function processPostGroups() {
   
        if (isset($this->segments[1])) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
        /*
         * Update rights
         */
        else {
            return $this->updateRights();
        }
    }


    /**
     * Process when GET on /administration/users
     * 
     * @throws Exception
     */
    private function processGetUsers() {

        /*
         * Get user creation MMI
         */   
        if (isset($this->segments[1])) {
            if ($this->segments[1] == 'creation') {
                return $this->to($this->userCreationFile);
            } else if ($this->segments[1] == 'history') {
                /*
                * Get user history MMI
                */
                
                $this->startIndex = 0;
                $this->numberOfResults = 50;
                $this->keyword = null;
                if (filter_input(INPUT_GET, 'startIndex')) {
                    $this->startIndex = filter_input(INPUT_GET, 'startIndex');
                }
                if (filter_input(INPUT_GET, 'numberOfResults')) {
                    $this->numberOfResults = filter_input(INPUT_GET, 'numberOfResults');
                }
                
                $collection = null;
                $service = null;
                $orderBy = null;
                $ascordesc = null;
                if (filter_input(INPUT_GET, 'collection')) {
                    $collection = filter_input(INPUT_GET, 'collection');
                }
                if (filter_input(INPUT_GET, 'service')) {
                    $service = filter_input(INPUT_GET, 'service');
                }
                if (filter_input(INPUT_GET, 'orderBy')) {
                    $orderBy = filter_input(INPUT_GET, 'orderBy');
                }
                if (filter_input(INPUT_GET, 'ascordesc')) {
                    $ascordesc = filter_input(INPUT_GET, 'ascordesc');
                }
                if (filter_input(INPUT_GET, 'limit')) {
                    $limit = filter_input(INPUT_GET, 'limit');
                }

                $options = array(
                    'orderBy' => $orderBy,
                    'ascOrDesc' => $ascordesc,
                    'collectionName' => $collection,
                    'service' => $service,
                    'startIndex' => $this->startIndex,
                    'numberOfResults' => $this->numberOfResults
                );
                $this->historyList = $this->context->dbDriver->getHistory(null, $options);
                $this->collectionsList = $this->context->dbDriver->listCollections();
     
                return $this->to($this->historyFile, $this->historyList);
            } else {
                return $this->processGetUser();
            }
        } else {
            /*
            * Users list MMI
            */
            $this->min = 0;
            $this->number = 50;
            $this->keyword = null;
            if (filter_input(INPUT_GET, 'min')) {
                $this->min = filter_input(INPUT_GET, 'min');
            }
            if (filter_input(INPUT_GET, 'number')) {
                $this->number = filter_input(INPUT_GET, 'number');
            }
            if (filter_input(INPUT_GET, 'keyword')) {
                $this->keyword = filter_input(INPUT_GET, 'keyword');
                $this->global_search_val = filter_input(INPUT_GET, 'keyword');
            } else {
                $this->keyword = null;
                $this->global_search_val = $this->context->dictionary->translate('_menu_globalsearch');
            }
            $this->usersProfiles = $this->context->dbDriver->getUsersProfiles($this->keyword, $this->min, $this->number);
            
            return $this->to($this->usersFile, $this->usersProfiles);
        }
    }

    /**
     * Process get on /administration/users/{userid}
     * 
     * @throws Exception
     */
    private function processGetUser() {

        $this->user = new RestoUser($this->segments[1], null, $this->context->dbDriver, false);
        if ($this->user->profile['userid'] === -1) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }

        $this->licenses = $this->context->dbDriver->getSignedLicenses($this->user->profile['email']);
        $this->rightsList = $this->context->dbDriver->getRightsList($this->user->profile['email']);
        
        if (!isset($this->segments[2])) {
            /*
            * Get user informations MMI
            */
            $options = array(
                'numberOfResults' => 4
            );
            $this->historyList = $this->context->dbDriver->getHistory($this->user->profile['userid'], $options);
            $this->collectionsList = $this->context->dbDriver->listCollections();

            return $this->to($this->userFile, $this->user->profile);
        } else if ($this->segments[2] == 'history') {
            /*
             * Get user history MMI
             */
            $this->collectionsList = $this->context->dbDriver->listCollections();
            $this->user = new RestoUser($this->segments[1], null, $this->context->dbDriver, false);
            $this->userProfile = $this->user->profile;
            if (!isset($this->userProfile['email'])) {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Wrong way', 404);
            }
            $this->startIndex = 0;
            $this->numberOfResults = 50;
            if (filter_input(INPUT_GET, 'startIndex')) {
                $this->startIndex = filter_input(INPUT_GET, 'startIndex');
            }
            if (filter_input(INPUT_GET, 'numberOfResults')) {
                $this->numberOfResults = filter_input(INPUT_GET, 'numberOfResults');
            }
            
            $collection = null;
            $service = null;
            $orderBy = null;
            $ascordesc = null;
            if (filter_input(INPUT_GET, 'collection')) {
                $collection = filter_input(INPUT_GET, 'collection');
            }
            if (filter_input(INPUT_GET, 'service')) {
                $service = filter_input(INPUT_GET, 'service');
            }
            if (filter_input(INPUT_GET, 'orderBy')) {
                $orderBy = filter_input(INPUT_GET, 'orderBy');
            }
            if (filter_input(INPUT_GET, 'ascordesc')) {
                $ascordesc = filter_input(INPUT_GET, 'ascordesc');
            }
            if (filter_input(INPUT_GET, 'limit')) {
                $limit = filter_input(INPUT_GET, 'limit');
            }

            $options = array(
                'orderBy' => $orderBy,
                'ascOrDesc' => $ascordesc,
                'collectionName' => $collection,
                'service' => $service,
                'startIndex' => $this->startIndex,
                'numberOfResults' => $this->numberOfResults
            );

            $this->historyList = $this->context->dbDriver->getHistory($this->segments[1], $options);
            
            return $this->to($this->userHistoryFile, $this->historyList);
        } else if ($this->segments[2] == 'rights') {
            /*
             * Get user rights creation MMI
             */
            $this->collectionsList = $this->context->dbDriver->listCollections();
            $this->user = new RestoUser($this->segments[1], null, $this->context->dbDriver, false);
            $this->userProfile = $this->user->profile;
            
            return $this->to($this->userRightCreation);
        } else {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
    }

    /**
     * Process when POST on /administration/users
     * 
     * @throws Exception
     */
    private function processPostUsers() {

        if (isset($this->segments[1])) {
            return $this->processPostUser();
        } else {
            /*
             * Insert user
             */
            return $this->insertUser();
        }
    }

    /**
     * Process when post on /administration/users/{userid}
     * 
     * @throws Exception
     */
    private function processPostUser() {

        if (isset($this->segments[2])) {
            
            /*
             * Activate user
             */
            if ($this->segments[2] == 'activate') {
                return $this->activate();
            }
            /*
             * Deactivate user
             */
            else if ($this->segments[2] == 'deactivate') {
                return $this->deactivate();
            }
            /*
             * Add rights to user
             */
            else if ($this->segments[2] == 'rights') {
                return $this->processPostRights();
            }
            else {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
            }
        }
        else {
            /*
             * Update user
             */
            return $this->updateUser();
        }
    }

    /**
     * Process post on /administration/user/{userid}/rights
     * This post is different because it calls a delete method on rights
     * 
     * @throws Exception
     */
    private function processPostRights() {

        if (isset($this->segments[3])) {
            /*
             * This post delete rights passed with data
             */
            if ($this->segments[3] === 'delete') {
                return $this->deleteRights();
            } else if ($this->segments[3] === 'update') {
                return $this->updateRights();
            } else {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
            }
        } else {
            return $this->addRights();
        }
    }

    private function updateRights() {
        try {
            /*
             * Get posted data
             */
            $postedData = array();
            $postedData['emailorgroup'] = filter_input(INPUT_POST, 'emailorgroup');
            $postedData['collection'] = filter_input(INPUT_POST, 'collection');
            $postedData['field'] = filter_input(INPUT_POST, 'field');
            $postedData['value'] = filter_input(INPUT_POST, 'value');

            $emailorgroup = $postedData['emailorgroup'];
            $collectionName = ($postedData['collection'] === '') ? null : $postedData['collection'];
            
            /*
             * Posted rights
             */
            $rights = array($postedData['field'] => $postedData['value']);

            $right = $this->context->dbDriver->getRights($emailorgroup, $collectionName);
            if (!$right) {
                /*
                 * Store rights
                 */
                $this->context->dbDriver->storeRights($rights, $emailorgroup, $collectionName);

                /*
                 * Success information
                 */
                return json_encode(array('status' => 'success', 'message' => 'success'));
            }else{
                /*
                 * Upsate rights
                 */
                $this->context->dbDriver->updateRights($rights, $emailorgroup, $collectionName);

                /*
                 * Success information
                 */
                return json_encode(array('status' => 'success', 'message' => 'success'));
            }
        } catch (Exception $ex) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Error while updating rights', 500);
        }
    }

    /**
     * Add rights 
     * 
     * @throws Exception
     */
    private function addRights() {
        
        try {
            /*
             * Get posted data
             */
            $postedData = array();
            $postedData['emailorgroup'] = filter_input(INPUT_POST, 'emailorgroup');
            $postedData['collection'] = filter_input(INPUT_POST, 'collection');
            $postedData['featureid'] = filter_input(INPUT_POST, 'featureid');
            $postedData['search'] = filter_input(INPUT_POST, 'search');
            $postedData['visualize'] = filter_input(INPUT_POST, 'visualize');
            $postedData['download'] = filter_input(INPUT_POST, 'download');
            $postedData['canput'] = filter_input(INPUT_POST, 'canput');
            $postedData['canpost'] = filter_input(INPUT_POST, 'canpost');
            $postedData['candelete'] = filter_input(INPUT_POST, 'candelete');
            $postedData['filters'] = filter_input(INPUT_POST, 'filters');

            $emailorgroup = $postedData['emailorgroup'];
            $collectionName = ($postedData['collection'] === '') ? null : $postedData['collection'];
            $featureIdentifier = ($postedData['featureid'] === '') ? null : $postedData['featureid'];

            /*
             * Posted rights
             */
            $rights = array('search' => $postedData['search'], 'visualize' => $postedData['visualize'], 'download' => $postedData['download'], 'canput' => $postedData['canput'], 'canpost' => $postedData['canpost'], 'candelete' => $postedData['candelete'], 'filters' => $postedData['filters']);

            /*
             * Store rights
             */
            $this->context->dbDriver->storeRights($rights, $emailorgroup, $collectionName, $featureIdentifier);

            /*
             * Success information
             */
            return json_encode(array('status' => 'success', 'message' => 'success'));
            
        } catch (Exception $e) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Error while creating rights', 500);
        }
    }

    /**
     * Delete rights
     * 
     * @throws Exception
     */
    private function deleteRights() {
        try {
            $rights = array();
            $rights['emailorgroup'] = filter_input(INPUT_POST, 'emailorgroup');
            $rights['collection'] = filter_input(INPUT_POST, 'collection');
            $rights['featureid'] = filter_input(INPUT_POST, 'featureid');
            
            if ($rights) {
                $this->context->dbDriver->deleteRights($rights['emailorgroup'], ($rights['collection'] === '' ? null : $rights['collection']), ($rights['featureid'] === '' ? null : $rights['featureid']));
                return json_encode(array('status' => 'success', 'message' => 'success'));
            }
            else {
                throw new Exception();
            }
        } catch (Exception $ex) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Error while deleting rights', 500);
        }
    }

    /**
     * insertUser - insert new user in database
     * 
     * @throws Exception
     */
    private function insertUser() {
        $userParam = array_merge($_POST);
        if ($userParam) {
            try {
                $this->context->dbDriver->storeUserProfile($userParam);
                return json_encode(array('status' => 'success', 'message' => 'success'));
            } catch (Exception $e) {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'User not created', 500);
            }
        } else {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'No data to create user', 500);
        }
    }
    
    /**
     * updateUser - update new user in database
     * 
     * @throws Exception
     */
    private function updateUser() {
        $userParam = array_merge($_POST);
        if ($userParam) {
            try {
                $this->context->dbDriver->updateUserProfile($userParam);
                return json_encode(array('status' => 'success', 'message' => 'success'));
            } catch (Exception $e) {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'User not updated', 500);
            }
        } else {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'No data to update user', 500);
        }
    }


    /**
     * Activate user
     * 
     * @throws Exception
     */
    private function activate() {
        try {
            $this->context->dbDriver->activateUser($this->segments[1]);
            return json_encode(array('status' => 'success', 'message' => 'success'));
        } catch (Exception $e) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Error while activating user', 500);
        }
    }

    /**
     * Deactivate user
     * 
     * @throws Exception
     */
    private function deactivate() {
        try {
            $this->context->dbDriver->deactivateUser($this->segments[1]);
            return json_encode(array('status' => 'success', 'message' => 'success'));
        } catch (Exception $ex) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Error while deactivating user', 500);
        }
    }
    
    /**
     * Process statistics
     * 
     * @return type
     * @throws Exception
     */
    private function processStatistics(){
        switch ($this->segments[1]) {
            case 'collections':
                return $this->to(null, $this->statisticsService());
            case 'users':
                if (!isset($this->segments[2])){
                    return $this->to(null, $this->statisticsUsers());
                }else if (isset($this->segments[2]) && !isset($this->segments[3])){
                    return $this->to(null, $this->statisticsService($this->segments[2]));
                }else{
                    throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
                }
                break;
            default:
                break;
        }
    }
    
    /**
     * Statistics over users
     * 
     * @return type
     */
    private function statisticsUsers(){
        /**
         * nb users
         * nb download
         * nb visualize
         * nb 
         */
        $statistics = array();
        $statistics['users'] = $this->context->dbDriver->countUsers();
        $statistics['download'] = $this->context->dbDriver->countService('download');
        $statistics['search'] = $this->context->dbDriver->countService('search');
        $statistics['visualize'] = $this->context->dbDriver->countService('resource');
        $statistics['insert'] = $this->context->dbDriver->countService('insert');
        $statistics['create'] = $this->context->dbDriver->countService('create');
        $statistics['update'] = $this->context->dbDriver->countService('update');
        $statistics['remove'] = $this->context->dbDriver->countService('remove');
        return $statistics;
    }
    
    /**
     * statisticsService - services stats on collections
     * 
     * @param int $userid
     * @return type
     */
    private function statisticsService($userid = null){
        /*
         * Statistics for each collections
         */
        $statistics = array();
        $collections = $this->context->dbDriver->listCollections();
        foreach ($collections as $collection) {
            $collection_statistics = array();
            $collection_statistics['download'] = $this->context->dbDriver->countService('download', $collection['collection'], $userid);
            $collection_statistics['search'] = $this->context->dbDriver->countService('search', $collection['collection'], $userid);
            $collection_statistics['visualize'] = $this->context->dbDriver->countService('resource', $collection['collection'], $userid);
            $collection_statistics['insert'] = $this->context->dbDriver->countService('insert', $collection['collection'], $userid);
            $collection_statistics['create'] = $this->context->dbDriver->countService('create', $collection['collection'], $userid);
            $collection_statistics['update'] = $this->context->dbDriver->countService('update', $collection['collection'], $userid);
            $collection_statistics['remove'] = $this->context->dbDriver->countService('remove', $collection['collection'], $userid);
            $statistics[$collection['collection']] = $collection_statistics;
        }
        return $statistics;
    }

    /**
     * toHTML
     */
    public function toHTML() {
        return RestoUtil::get_include_contents(realpath(dirname(__FILE__)) . '/../../../themes/' . $this->context->config['theme'] . $this->file, $this);
    }
    
     /**
     * Output collection description as a JSON stream
     * 
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false){
        return RestoUtil::json_format($this->data, $pretty);
    }
    
    /**
     * to - return method depending on return type
     * 
     * @param String $file
     * @param array $data
     * @return method
     * @throws Exception
     */
    private function to($file, $data = null){
        if ($this->context->method === 'GET' && $this->context->outputFormat === 'json' && isset($data)) {
            $pretty = false;
            if (filter_input(INPUT_GET, '_pretty')) {
                $pretty = filter_input(INPUT_GET, '_pretty');
            }
            $this->data = $data;
            return $this->toJSON($pretty);
        }else if($this->context->method === 'GET' && $this->context->outputFormat === 'html'){
            if (!isset($file)){
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
            }
            $this->file = $file;
            return $this->toHTML();
        }else{
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
    }
}
