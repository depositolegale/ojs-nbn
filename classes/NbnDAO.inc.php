<?php

/**
 * @file plugins/pubIds/nbn/classes/NbnDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Contributed by CILEA
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NbnDAO
 * @ingroup plugins_pubIds_nbn_classes
 *
 * @brief Operations for retrieving and modifying NBN objects.
 * 
 */


class NbnDAO extends DAO {

	/**
	 * Constructor.
	 */
	function NbnDAO() {
		parent::DAO();
	}

   /**
    * Retrieve NBN for the given article.
    * @param $articleId int
    * @param $journalId int
    */   
   function getNBN($articleId, $journalId){
      $params = array(         
         (int)$articleId,
         (int)$journalId
      );
      
      $nbn = $this->concat('subnamespace', '\'-\'', 'assigned_string');
      $sql = "SELECT article_id, $nbn as nbn
              FROM nbn_assigned_string as nas join nbn_journal_subnamespace as njs on nas.journal_id = njs.journal_id
              WHERE article_id = ? AND njs.journal_id = ?";
              
      $result =& $this->retrieve($sql, $params);
      $returner = null;
      if ($result->RecordCount() != 0) {
         $returner = isset($result->fields[1]) ? $result->fields[1] : false;
      }

      $result->Close();
      unset($result);

      return $returner;              
   }
   
	/**
	 * Insert a new assigned String.
	 * @param $articleId int
    * @param $journalId int
    * @param $assignedString string
	 */
	function insertAssignedString($articleId , $journalId, $assignedString) {
		$returner = $this->update(
			'INSERT INTO nbn_assigned_string
                     (article_id, journal_id, assigned_string)
                     VALUES (?, ?, ?)',
				
			array(
				(int)$articleId,
            (int)$journalId,
            $assignedString
			)
		);
      
		return $returner;
	}
   
   /**
    * Insert a new Journal Sub-namespace.
    * @param $journalId int
    * @param $journalNamespace string Journal sub-namespace
    */
   function insertJournalNamespace($journalId, $journalNamespace) {
      $returner = $this->update(
         'INSERT INTO nbn_journal_subnamespace
                     (journal_id, subnamespace)
                     VALUES (?, ?);',
            
         array(
            (int)$journalId,
            $journalNamespace
         )
      );
            
      return $returner;
   }

   /**
    * Check whether the given journal has a sub-namespace.
    * @param $articleId int
    * @param $userId int
    * @param $journalId int
    * @return boolean
    */   
   function journalSubnamespaceExixts($journalId){
      $result =& $this->retrieve(
         'SELECT COUNT(*)
         FROM nbn_journal_subnamespace 
         WHERE journal_id = ? ',
         array(
            (int)$journalId
         )
      );
      $returner = $result->fields[0] ? true : false;
      $result->Close();
      return $returner;
   }  
   
}

?>
