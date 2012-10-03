<?php

/**
 * @file plugins/pubIds/nbn/NBNPubIdPlugin.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NBNPubIdPlugin
 * @ingroup plugins_pubIds_nbn
 *
 * @brief NBN registration plugin.
 * 
 */


import('classes.plugins.PubIdPlugin');

import('plugins.pubIds.nbn.classes.NnbDAO');

import('pages.search.SearchHandler');

import('classes.oai.ojs.JournalOAI');

// NBN API
define('NBN_API_URL', 'http://nbn.depositolegale.it/test/nbn_generator.pl');

// Configuration errors.
define('NBN_CONFIGERROR_SETTINGS', 0x01);

class NBNPubIdPlugin extends PubIdPlugin {
   
   //
   // Implement template methods from PKPPlugin.
   //
   /**
    * @see PubIdPlugin::register()
    */
   function register($category, $path) {
      $success = parent::register($category, $path);
      $this->addLocaleData();
      return $success;
   }

   /**
    * @see PKPPlugin::getName()
    */
   function getName() {
      return 'NBNPubIdPlugin';
   }

   /**
    * @see PKPPlugin::getDisplayName()
    */
   function getDisplayName() {
      return __('plugins.pubIds.nbnit.displayName');
   }

   /**
    * @see PKPPlugin::getDescription()
    */
   function getDescription() {
      return __('plugins.pubIds.nbnit.description');
   }

   /**
    * @see PKPPlugin::getLocaleFilename($locale)
    */
   function getLocaleFilename($locale) {
      $localeFilenames = parent::getLocaleFilename($locale);

      // Add shared locale keys.
      $localeFilenames[] = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . 'common.xml';

      return $localeFilenames;
   }

   //
   // Implement template methods from DOIExportPlugin
   //
   /**
    * @see DOIExportPlugin::getPluginId()
    */
   function getPluginId() {
      return 'NBN';
   } 

   /**
    * @see PubIdPlugin::getSettingsFormName()
    */
   function getSettingsFormName() {
      return 'classes.form.NBNSettingsForm';
   }

   /**
    * Return the class name of the plug-in's settings form.
    * @return string
    */
   function getSettingsFormClassName() {
      return 'NBNSettingsForm';
   }   
   
   /**
    * @see PKPPlugin::getTemplatePath()
    */
   function getTemplatePath() {
      return parent::getTemplatePath().'templates/';
   } 
   
   //
   // Implement template methods from PubIdPlugin.
   //
   /**
    * @see PubIdPlugin::getPubId()
    */   
   function getPubId(&$pubObject, $preview = false){
      // Determine the type of the publishing object
      $pubObjectType = $this->getPubObjectType($pubObject);
     
      // NBNs are not enabled for issues and Galleys
      if ($pubObjectType == 'Issue' || $pubObjectType == 'Galley') return null;
            
      $journalId = $pubObject->getJournalId();
      $articleId = $pubObject->getPublishedArticleId();
      $nbnDAO = new NbnDAO();
      $nbn = $nbnDAO->getNBN($articleId, $journalId);
      unset($nbnDAO);
      return $nbn;
      
   }   
   
   /**
    * @see PubIdPlugin::getPubIdType()
    */
   function getPubIdType() {
      return 'other::nbn';
   }

   /**
    * @see PubIdPlugin::getPubIdDisplayType()
    */
   function getPubIdDisplayType() {
      return 'NBN';
   }   
   
   /**
    * @see PubIdPlugin::getResolvingURL()
    */
   function getResolvingURL($journalId, $pubId) {
      return 'http://nbn.depositolegale.it/'.urlencode($pubId);
   }   
      
   //
   // Private helper methods
   //   
   /**
    * Display a list of articles for registration.
    * @param $templateMgr TemplateManager
    * @param $journal Journal
    */
   function _displayArticleList(&$templateMgr, &$journal, &$request) {
      $this->_setBreadcrumbs(array(), true);

      // Reagistration without account .
      $username = $this->getSetting($journal->getId(), 'username');
      $password = $this->getSetting($journal->getId(), 'username');
      $templateMgr->assign('hasCredentials', !(empty($username) && empty($password)));        
           
      // Paginate articles.
      $rangeInfo = Handler::getRangeInfo('articles');
     
      $search = $request->getUserVar('search');
      // If a search was performed, get the necessary info.      
      if(!empty($search)){
         $editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
         
         // Get the user's search conditions, if any
         $searchField = $request->getUserVar('searchField');
         $searchMatch = $request->getUserVar('searchMatch');
         
         $sort = isset($sort) ? $sort : 'id';
         $sortDirection = $request->getUserVar('sortDirection');
         $sortDirection = (isset($sortDirection) && ($sortDirection == 'ASC' || $sortDirection == 'DESC')) ? $sortDirection : 'ASC';
         
         $rawSubmissions =& $editorSubmissionDao->_getUnfilteredEditorSubmissions(
            $journal->getId(),
            null,
            0,
            $searchField,
            $searchMatch,
            $search,
            null,
            null,
            null,
            null,
            $rangeInfo,
            $sort,
            $sortDirection
         );
         $articles = new DAOResultFactory($rawSubmissions, $editorSubmissionDao, '_returnEditorSubmissionFromRow', array('article_id'));

         foreach($articles->records as $article){
            $articleIds[] = $article[0];
         }                     
      }else{         
         // Retrieve all published articles.
         $publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO'); /* @var $publishedArticleDao PublishedArticleDAO */
         $articleIds = $publishedArticleDao->getPublishedArticleIdsByJournal($journal->getId());                       
      }

      // Whether filter is on, show only not registered articles.
      $filter = $request->getUserVar('registeredFilter');
      if(!empty($filter)){
         foreach($articleIds as $index => $articleId){
            if($this->isRegistered($articleId, $journal->getId())){
               unset($articleIds[$index]);   
            }   
         }
      }
      
      $totalArticles = count($articleIds);
      if ($rangeInfo->isValid()) {
         $articles = array_slice(ArticleSearch::formatResults($articleIds), $rangeInfo->getCount() * ($rangeInfo->getPage()-1), $rangeInfo->getCount());
      }       
      
      // Instantiate article iterator.
      import('lib.pkp.classes.core.VirtualArrayIterator');
      $iterator = new VirtualArrayIterator($articles, $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());  
      
      // Prepare and display the article template.      
      $templateMgr->assign('fieldOptions', $this->_getSearchFieldOptions());      
      $templateMgr->assign('journalId', $journal->getId());
      $templateMgr->assign_by_ref('nbn', new NbnDAO());
      $templateMgr->assign_by_ref('articles', $iterator);
      $templateMgr->display($this->getTemplatePath() . 'articles.tpl');
   }   

   /**
    * Get the list of fields that can be searched by contents.
    * @return array
    */
   function _getSearchFieldOptions() {
      return array(
         3 => 'article.title',
         1 => 'user.role.author'
      );
   }   

   /**
    * Set the page's breadcrumbs, given the plugin's tree of items
    * to append.
    * @param $crumbs Array ($url, $name, $isTranslated)
    * @param $subclass boolean
    */
   function _setBreadcrumbs($crumbs = array(), $isSubclass = false) {
      $templateMgr =& TemplateManager::getManager();
      $pageCrumbs = array(
         array(
            Request::url(null, 'user'),
            'navigation.user'
         ),
         array(
            Request::url(null, 'manager'),
            'user.role.manager'
         ),
         array(
            Request::url(null, 'manager', 'plugins'),
            'manager.plugins'
         )
      );
      if ($isSubclass) $pageCrumbs[] = array(
         Request::url(null, 'manager', 'plugin', array('pubIds', $this->getName(), 'generate')),
         $this->getDisplayName(),
         true
      );

      $templateMgr->assign('pageHierarchy', array_merge($pageCrumbs, $crumbs));
   }
   
   /**
    * Display a list of issues for registration.
    * @param $templateMgr TemplateManager
    * @param $journal Journal
    */
   function _displayIssueList(&$templateMgr, &$journal) {
      $this->_setBreadcrumbs(array(), true);

      // Export without account.
      $username = $this->getSetting($journal->getId(), 'username');
      $templateMgr->assign('hasCredentials', !empty($username));  
      
      // Retrieve all published issues.
      AppLocale::requireComponents(array(LOCALE_COMPONENT_OJS_EDITOR));
      $issueDao =& DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
      $issues =& $issueDao->getPublishedIssues($journal->getId(), Handler::getRangeInfo('issues'));

      // Prepare and display the issue template.
      $templateMgr->assign_by_ref('issues', $issues);
      $templateMgr->display($this->getTemplatePath() . 'issues.tpl');
   }
   
   //
   // Private helper methods
   //
   /**
    * Display the plug-in home page.
    * @param $templateMgr TemplageManager
    * @param $journal Journal
    */
   function _displayPluginHomePage(&$templateMgr, &$journal) {
      $this->_setBreadcrumbs();    

      // Check for configuration errors:
      $configurationErrors = array();

      // missing plug-in setting.
      $form =& $this->_instantiateSettingsForm($journal);
      foreach($form->_getFormFields() as $fieldName => $fieldType) {
         if ($form->isOptional($fieldName)) continue;

         $setting = $this->getSetting($journal->getId(), $fieldName);
         if (empty($setting)) {
            $configurationErrors[] = NBN_CONFIGERROR_SETTINGS;
            break;
         }
      }

      $templateMgr->assign_by_ref('configurationErrors', $configurationErrors);

      // Prepare and display the index page template.
      $templateMgr->assign_by_ref('journal', $journal);
      $templateMgr->display($this->getTemplatePath() . 'index.tpl');
   }   
   
   /**
    * Instantiate the settings form.
    * @param $journal Journal
    * @return NBNSettingsForm
    */
   function &_instantiateSettingsForm(&$journal) {
      $settingsFormClassName = $this->getSettingsFormClassName();
      $this->import('classes.form.' . $settingsFormClassName);
      $settingsForm = new $settingsFormClassName($this, $journal->getId());
      assert(is_a($settingsForm, 'Form'));
      return $settingsForm;
   }          
   
   /**
    * Add a notification.
    * @param $request Request
    * @param $message string An i18n key.
    * @param $notificationType integer One of the NOTIFICATION_TYPE_* constants.
    * @param $param string An additional parameter for the message.
    */
   function _sendNotification(&$request, $message, $notificationType, $param = null) {
      static $notificationManager = null;

      if (is_null($notificationManager)) {
         import('classes.notification.NotificationManager');
         $notificationManager = new NotificationManager();
      }

      if (!is_null($param)) {
         $params = array('param' => $param);
      } else {
         $params = null;
      }

      $user =& $request->getUser();
      $notificationManager->createTrivialNotification(
         $user->getId(),
         $notificationType,
         array('contents' => __($message, $params))
      );
   }      
   
   //
   // Implement template methods from PubIdPlugin
   //
   /**
    * @see PubIdPlugin::getManagementVerbs()
    */
   function getManagementVerbs() {
      $verbs = parent::getManagementVerbs();
      if ($this->getEnabled()) {
         $verbs[] = array('generate', __('plugins.pubIds.nbnit.register'));
      }
      return $verbs;
   }   
   
   /**
    * @see Plugin::manage()
    */
   function manage($verb, $args, &$message, &$messageParams, &$request) {
      parent::manage($verb, $args, $message, $messageParams, $request);

      $router =& $request->getRouter();
      $journal =& $router->getContext($request); 
      $templateMgr =& TemplateManager::getManager();
           
      switch ($verb) {
         case 'issues':
            $this->_displayIssueList($templateMgr, $journal);
            return true;
         case 'registerIssue':
            $target = 'issue';
            $objectIds = $args;
            break;
         case 'registerIssues':
            $target = 'issue';
            $objectIds = $request->getUserVar($target . 'Id');
            $request->cleanUserVar();
            break;             
         case 'articles':
            $this->_displayArticleList($templateMgr, $journal, $request);            
            return true;
         case 'registerArticle':
            $target = 'article';
            $objectIds = $args;      
            break;
         case 'registerArticles':
            $target = 'article';
            $objectIds = $request->getUserVar($target . 'Id');
            break;                    
         case 'generate':
            $this->_displayPluginHomePage($templateMgr, $journal);
            return true;
         default:
            return false;              
            
      }
      
      // Register selected objects.
      $result = $this->registerObjects($target, $objectIds, $journal);      
      
      // Provide the user with some visual feedback that
      // registration was successful.
      if ($result === true) {
         $this->_sendNotification(
            $request,
            'plugins.pubIds.nbnit.register.success',
            NOTIFICATION_TYPE_SUCCESS
         );
      }elseif ($result !== true) {
         // registration was not successful
         if (is_array($result) && !empty($result)) {
            foreach($result as $error) {
               assert(is_array($error) && count($error) >= 1);
               $this->_sendNotification(
                  $request,
                  $error[0],
                  NOTIFICATION_TYPE_ERROR,
                  (isset($error[1]) ? $error[1] : null)
               );
            }
         }
      }
      $listAction = $target . 's';
      $request->redirect(
         null, 'manager', 'plugin',
         array('pubIds', $this->getName(), $listAction), null
      );      
            
   }
   
   /**
    * Register publishing objects.
    *
    * @param $target Target 
    * @param $objectIds array An array with object IDs to register.
    * @param $journal Journal
    *
    * @return boolean|array True for success or an array of error messages.
    */
   function registerObjects($target, $objectIds, &$journal) {
      // Registering can take a long time.
      set_time_limit(0);
      $articles = array();
      $articleDao =& DAORegistry::getDAO('PublishedArticleDAO'); /* @var $articleDao PublishedArticleDAO */      
      foreach($objectIds as $objectId){
         if($target == 'issue'){
            $a = $articleDao->getPublishedArticles($objectId);
            $articles += $articleDao->getPublishedArticles($objectId);   
         }else{      
            $articles[] =& $articleDao->getPublishedArticleByArticleId($objectId, $journal->getId(), true);
         }         
      }
      // Generate NBNs.
      foreach($articles as $article) {
         $result = $this->generateNBN($journal, $article);
         if ($result !== true) {
            return $result;
         }
      }

      return true;
   }      
   
   /**
    * Generate NBN.
    *
    * @param $journal Journal
    * @param $article Article
    *
    * @return boolean|array True for success or an array of error messages.
    */   
   function generateNBN(&$journal, &$article){
      // Get username and password
      $username = $this->getSetting($journal->getId(), 'username');
      $password = $this->getSetting($journal->getId(), 'password');      

      // Retrieve Article URL
      $articleURL = Request::url($journal->getPath(), 'article', 'view', $article->getId());
      // Retrieve Article metadatas URL
      $articleIdentifier = 'oai:' . Config::getVar('oai', 'repository_id') . ':' . 'article/' . $article->getId();      
      $metadataURL = Request::url($journal->getPath(), 'oai', "?verb=GetRecord&metadataPrefix=oai_dc&identifier=$articleIdentifier");

      // Register NBN
      $curlHandle = curl_init(NBN_API_URL);
      $curl_post_data = json_encode(array(
               'action' => 'nbn_create' ,
               'url'    => $articleURL ,
               'metadataurl' => $metadataURL
               ));
       $headers = array('Content-Type: application-json', 'charset: UTF-8');       
       curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);  
       curl_setopt($curlHandle, CURLOPT_USERPWD, $username . ':' . $password);
       curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
       curl_setopt($curlHandle, CURLOPT_POST, true);
       curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $curl_post_data);
       $curl_response = json_decode(curl_exec($curlHandle));
       $responseInfo  = curl_getinfo($curlHandle); 
       curl_close($curlHandle);
       if($responseInfo['http_code'] == 201){
          return $this->saveNBN($article->getId(), $journal->getId(), $curl_response->nbn);   
       }elseif($responseInfo['http_code'] == 402){
          if($this->isRegistered($article->getId(), $journal->getId())){
             return true;
          }else{          
             return $this->saveNBN($article->getId(), $journal->getId(), $curl_response->nbn);
          }
       }else{
          $dom = new DOMDocument();
          $dom->loadHTML($curl_response);
          $xpath = new DOMXpath($dom);
          $elements = $xpath->query("//p");
          $error = $elements->item(0)->nodeValue;
          return array(
             array('plugins.pubIds.nbnit.register.error.mdsError', "{$responseInfo['http_code']}  - $error")
          );                 
       }
   }

   /**
    * Check whether the given article has a NBN.
    *
    * @param $articleId Article ID
    * @param $journalId Journal ID
    *
    * @return boolean
    */      
   function isRegistered($articleId, $journalId){
      $nbnDAO = new NbnDAO();
      $result = $nbnDAO->getNBN($articleId, $journalId);
      unset($nbnDAO);
      if($result){
         return true;
      }else{
         return false;
      }
   }
   
   /**
    * Save the given NBN.
    *
    * @param $articleId Article ID
    * @param $journalId Journal ID
    * @param $nbn string NBN to save
    *
    * @return boolean|array True for success or false otherwise.
    */     
   function saveNBN($articleId, $journalId, $nbn){
      $parts = explode('-', $nbn);
      $assignedString = end($parts);
      $subNamespace = str_replace('-' . $assignedString, '', $nbn);

      $nbnDAO = new NbnDAO();
      $result = true;

      if(!$nbnDAO->journalSubnamespaceExixts($journalId)){
         $result = $nbnDAO->insertJournalNamespace($journalId, $subNamespace);
      }
      if($result){
         $result = $nbnDAO->insertAssignedString($articleId, $journalId, $assignedString);
      }
      unset($nbnDAO);
      return $result;
   }   
   

   

   

   
}

?>
