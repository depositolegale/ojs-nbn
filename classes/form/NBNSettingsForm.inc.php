<?php

/**
 * @file plugins/pubIds/urn/URNSettingsForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class URNSettingsForm
 * @ingroup plugins_pubIds_nbn
 *
 * @brief Form for journal managers to setup NBN plugin
 */


import('lib.pkp.classes.form.Form');

class NBNSettingsForm extends Form {

   //
   // Private properties
   //
   /** @var integer */
   var $_journalId;

   /** @var URNCileaPlugin */
   var $_plugin;

   //
   // Constructor
   //
   /**
    * Constructor
    * @param $plugin URNCileaPlugin
    * @param $journalId integer
    */
   function NBNSettingsForm(&$plugin, $journalId) {
      $this->_journalId = $journalId;
      $this->_plugin =& $plugin;

      parent::Form($plugin->getTemplatePath() . 'settingsForm.tpl');

      $this->addCheck(new FormValidatorRegExp($this, 'username', 'required', 'plugins.pubIds.nbnit.manager.settings.form.usernameRequired', '/^[^:]+$/'));
      $this->addCheck(new FormValidatorRegExp($this, 'password', 'required', 'plugins.pubIds.nbnit.manager.settings.form.passwordRequired', '/^[^:]+$/'));
      $this->addCheck(new FormValidatorPost($this));
   }


   /**
    * @see Form::initData()
    */
   function initData() {
      $journalId = $this->_journalId;
      $plugin =& $this->_plugin;

      foreach($this->_getFormFields() as $fieldName => $fieldType) {
         $this->setData($fieldName, $plugin->getSetting($journalId, $fieldName));
      }
   }

   /**
    * @see Form::readInputData()
    */
   function readInputData() {
      $this->readUserVars(array_keys($this->_getFormFields()));
   }

   /**
    * @see Form::validate()
    */
   function execute() {
      $plugin =& $this->_plugin;
      $journalId = $this->_journalId;

      foreach($this->_getFormFields() as $fieldName => $fieldType) {
         $plugin->updateSetting($journalId, $fieldName, $this->getData($fieldName), $fieldType);
      }
   }

   //
   // Private helper methods
   //
   function _getFormFields() {
      return array(
         'username' => 'string',
         'password' => 'string'
         //'urnPrefix' => 'string'
      );
   }
   
   /**
    * Check whether a given setting is optional.
    * @param $settingName string
    * @return boolean
    */
   function isOptional($settingName) {
      return in_array($settingName, array());
   }   
   
}

?>
