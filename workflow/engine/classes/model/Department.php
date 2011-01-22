<?php
/**
 * Department.php
 *  
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2008 Colosa Inc.23
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * For more information, contact Colosa Inc, 2566 Le Jeune Rd., 
 * Coral Gables, FL, 33134, USA, or email info@colosa.com.
 * 
 */

require_once 'classes/model/om/BaseDepartment.php';
require_once 'classes/model/Users.php';


/**
 * Skeleton subclass for representing a row from the 'DEPARTMENT' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    workflow.classes.model
 */
class Department extends BaseDepartment {


protected $depo_title = '';
/**
   * Create the Department
   * 
   * @param      array $aData  
   * @return     void
   */

  function create ($aData ) {
    $con = Propel::getConnection( DepartmentPeer::DATABASE_NAME );
    try {
      if ( isset ( $aData['DEP_UID'] ) ) 
        $this->setDepUid ( $aData['DEP_UID'] );
      else 
        $this->setDepUid ( G::generateUniqueID() );
        
      if ( isset ( $aData['DEP_PARENT'] ) ) 
        $this->setDepParent ( $aData['DEP_PARENT'] );
      else 
        $this->setDepParent ( '' );
        
      if ( isset ( $aData['DEP_MANAGER'] ) ) 
        $this->setDepManager ( $aData['DEP_MANAGER'] );
      else 
        $this->setDepManager ( '' );
        
      if ( isset ( $aData['DEP_LOCATION'] ) ) 
        $this->setDepLocation ( $aData['DEP_LOCATION'] );
      else 
        $this->setDepLocation ( '' );
        
      if ( isset ( $aData['DEP_STATUS'] ) ) 
        $this->setDepStatus ( $aData['DEP_STATUS'] );
      else 
        $this->setDepStatus ( 'ACTIVE' );

      if ( isset ( $aData['DEP_REF_CODE'] ) ) 
        $this->setDepRefCode ( $aData['DEP_REF_CODE'] );
      else 
        $this->setDepRefCode ( '' );

      if ( isset ( $aData['DEP_LDAP_DN'] ) ) 
        $this->setDepLdapDn ( $aData['DEP_LDAP_DN'] );
      else 
        $this->setDepLdapDn ( '' );

      if ( isset ( $aData['DEP_TITLE'] ) ) 
        $this->setDepTitle ( $aData['DEP_TITLE'] );
      else 
        $this->setDepTitle ( '' );
        
      if ( $this->validate() ) {
        $con->begin(); 
        $res = $this->save();
        
        $con->commit(); 
        return $this->getDepUid();
      }
      else {
        $msg = '';
        foreach($this->getValidationFailures() as $objValidationFailure) 
          $msg .= $objValidationFailure->getMessage() . "<br/>";
         
        throw ( new Exception ( " The Department row cannot be created $msg " ) );
      }

    }
    catch (Exception $e) {
      $con->rollback(); 
      throw ($e);
    }
  }
  
/**
   * Get the [depo_title] column value.
   * @return     string
   */
  public function getDepTitle()
  {
    if ( $this->getDepUid() == '' ) {
      throw ( new Exception( "Error in getDepTitle, the DEP_UID can't be blank") );
    }
    $lang = defined ( 'SYS_LANG') ? SYS_LANG : 'en';
    $this->depo_title = Content::load ( 'DEPO_TITLE', '', $this->getDepUid(), $lang );
    return $this->depo_title;
  }

  /**
   * Set the [depo_title] column value.
   * 
   * @param      string $v new value
   * @return     void
   */
  public function setDepTitle($v)
  {
    if ( $this->getDepUid() == '' ) {
      throw ( new Exception( "Error in setGrpTitle, the GRP_UID can't be blank") );
    }
    // Since the native PHP type for this column is string,
    // we will cast the input to a string (if it is not).
    if ($v !== null && !is_string($v)) {
      $v = (string) $v; 
    }

    if ($this->depo_title !== $v || $v === '') {
      $this->depo_title = $v;
      $lang = defined ( 'SYS_LANG') ? SYS_LANG : 'en';
      $res = Content::addContent( 'DEPO_TITLE', '', $this->getDepUid(), $lang, $this->depo_title );
    }

  } // set()


/**
   * Load the Process row specified in [depo_id] column value.
   * 
   * @param      string $ProUid   the uid of the Prolication 
   * @return     array  $Fields   the fields 
   */
  
  function Load ( $DepUid ) {
    $con = Propel::getConnection(DepartmentPeer::DATABASE_NAME);
    try {
      $oDept = DepartmentPeer::retrieveByPk( $DepUid );
      if (is_object ($oDept) && get_class ($oDept) == 'Department' ) {
        $aFields = $oDept->toArray(BasePeer::TYPE_FIELDNAME);
        $this->fromArray ($aFields, BasePeer::TYPE_FIELDNAME );
        $aFields['DEPO_TITLE'] = $oDept->getDepTitle();
        return $aFields;
      }
      else {
        throw(new Exception( "The row '$DepUid' in table Department doesn't exist!" ));
      }
    }
    catch (Exception $oError) {
      throw($oError);
    }
  }

/**
   * Update the Dep row
   * @param     array $aData
   * @return    variant
  **/
  
  public function update($aData)
  {
    $con = Propel::getConnection( DepartmentPeer::DATABASE_NAME );
    try {
      $con->begin(); 
      $oPro = DepartmentPeer::retrieveByPK( $aData['DEP_UID'] );
      if (is_object($oPro) && get_class ($oPro) == 'Department' ) {
        $oPro->fromArray( $aData, BasePeer::TYPE_FIELDNAME );
        if ($oPro->validate()) {
          if ( isset ( $aData['DEPO_TITLE'] ) )
            $oPro->setDepTitle( $aData['DEPO_TITLE'] );
          if ( isset ( $aData['DEP_STATUS'] ) )
            $oPro->setDepStatus( $aData['DEP_STATUS'] );
          if ( isset ( $aData['DEP_PARENT'] ) )
            $oPro->setDepParent( $aData['DEP_PARENT'] );
          if ( isset ( $aData['DEP_MANAGER'] ) )
            $oPro->setDepManager( $aData['DEP_MANAGER'] );
          $res = $oPro->save();
          $con->commit(); 
          return $res;
        }
        else {
          $msg = '';
          foreach($this->getValidationFailures() as $objValidationFailure) 
            $msg .= $objValidationFailure->getMessage() . "<br/>";
         
          throw ( new PropelException ( 'The Department row cannot be created!', new PropelException ( $msg ) ) );
        }
      }
      else {
        $con->rollback(); 
        throw(new Exception( "The row '" . $aData['DEP_UID'] . "' in table Department doesn't exist!" ));
      }
    }
    catch (Exception $oError) {
      throw($oError);
    }
  }


 /**
   * Remove the row
   * @param     array $aData or string $ProUid 
   * @return    string
  **/
  public function remove($ProUid)
  {
    if ( is_array ( $ProUid ) ) {
      $ProUid = ( isset ( $ProUid['DEP_UID'] ) ? $ProUid['DEP_UID'] : '' );
    }
    try {
      
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(UsersPeer::USR_UID);
      $oCriteria->add(UsersPeer::DEP_UID, $ProUid, Criteria::EQUAL);
      $oDataset = UsersPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      
      $oDataset->next();
      $aFields = array();
      while ($aRow = $oDataset->getRow()) {
          
          $aFields['USR_UID'] = $aRow['USR_UID'];
          $aFields['DEP_UID'] = '';
          $oDepto = UsersPeer::retrieveByPk($aFields['USR_UID']);
            if (is_object($oDepto) && get_class($oDepto) == 'UsersPeer') {
                return true;
            } else {
                $oDepto = new Users();
                $oDepto->update($aFields);
            }
          
          $oDataset->next();
      }
      
      
      
      $oPro = DepartmentPeer::retrieveByPK( $ProUid );
      if (!is_null($oPro))
      {
        Content::removeContent('DEPO_TITLE', '',       $oPro->getDepUid());
        Content::removeContent('DEPO_DESCRIPTION', '',  $oPro->getDepUid());
        return $oPro->delete();
      }
      else {
        throw(new Exception( "The row '$ProUid' in table Group doesn't exist!" ));
      }
    }
    catch (Exception $oError) {
      throw($oError);
    }
  }


/**
   * Load the Department row specified in [depo_id] column value.
   * 
   * @param      string $ProUid   the uid of the Prolication 
   * @return     array  $Fields   the fields 
   */
  
  function existsDepartment( $DepUid ) {
    $con = Propel::getConnection(DepartmentPeer::DATABASE_NAME);
    $oPro = DepartmentPeer::retrieveByPk( $DepUid );
    if (is_object($oPro) && get_class ($oPro) == 'Department' ) {
      return true;
    }
    else {
      return false;
    }
  }

  function existsUserInDepartment( $depId, $userId ) {
    $con = Propel::getConnection(DepartmentPeer::DATABASE_NAME);
    $oUser = UsersPeer::retrieveByPk( $userId );
    if (is_object($oUser) && get_class ($oUser) == 'Users' ) {
      if ( $oUser->getDepUid() == $depId )
        return true;
    }

    return false;
  }
  
  function updateDepartmentManager ($depId) {
    $managerId = '';
    $depParent = '';
    $oDept = DepartmentPeer::retrieveByPk( $depId );
    if (is_object($oDept) && get_class ($oDept) == 'Department' ) {
      $managerId = $oDept->getDepManager( );
      $depParent = $oDept->getDepParent( );
    }

    // update the reportsTo field to all users in that department
    $conn = Propel::getConnection(UsersPeer::DATABASE_NAME);
    $selectCriteria = new Criteria('workflow');
    $selectCriteria->add(UsersPeer::DEP_UID, $depId );
    $selectCriteria->add(UsersPeer::USR_UID, $managerId , Criteria::NOT_EQUAL);

    // Create a Criteria object includes the value you want to set
    $updateCriteria = new Criteria('workflow');
    $updateCriteria->add(UsersPeer::USR_REPORTS_TO, $managerId );
    BasePeer::doUpdate($selectCriteria, $updateCriteria, $conn);

    // update manager's manager, getting the manager of PARENT DEPARTMENT in order to enable scalating
    $oUser = UsersPeer::retrieveByPk( $managerId );
    if (is_object($oUser) && get_class ($oUser) == 'Users' ) {
      $oDept = DepartmentPeer::retrieveByPk( $depParent );
      $oUser->setUsrReportsTo( '' ); //by default no manager
      if (is_object($oUser) && get_class ($oDept) == 'Department' ) {
        $managerParentId = $oDept->getDepManager( );
        if ( trim($managerParentId) != '' ) {
          $oUser->setUsrReportsTo( $managerParentId );
        }
      }
      $oUser->save();      
    }
    
    // get children departments to update the reportsTo of these children
    $childrenCriteria = new Criteria('workflow');
    $childrenCriteria->add(DepartmentPeer::DEP_PARENT, $depId );
    $oDataset = DepartmentPeer::doSelectRS($childrenCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      
    $oDataset->next();
    while ( $aRow = $oDataset->getRow() ) {
      $oUser = UsersPeer::retrieveByPk($aRow['DEP_MANAGER']);
      if (is_object($oUser) && get_class($oUser) == 'Users') {
        $oUser->setUsrReportsTo ( $managerId );
        $oUser->save();
      }
      $oDataset->next();
    }      

  }

  //add an user to a department and sync all about manager info
  function addUserToDepartment( $depId, $userId, $manager, $updateManager = false )  {
    try {
      //update the field in user table
      $oUser = UsersPeer::retrieveByPk( $userId );
      if (is_object($oUser) && get_class ($oUser) == 'Users' ) {
        $oUser->setDepUid( $depId );
        $oUser->save();
      }
      
      //if the user is a manager update Department Table
      if ( $manager ) {
        $oDept = DepartmentPeer::retrieveByPk( $depId );
        if (is_object($oDept) && get_class ($oDept) == 'Department' ) {
          $oDept->setDepManager( $userId );
          $oDept->save();
        }
      }
      
      //now update the reportsto to all 
      if ( $updateManager ) {
        $this->updateDepartmentManager ($depId);
      }   
      return true;
    }
    catch ( Exception $oError) {
      throw($oError);
    }
  }

  // select departments
  // this function is used to draw the hierachy tree view
  function getDepartments( $DepParent )  {
    try {
      $result = array();
      $criteria = new Criteria('workflow');
      $criteria->add(DepartmentPeer::DEP_PARENT, $DepParent, Criteria::EQUAL);
      $con = Propel::getConnection(DepartmentPeer::DATABASE_NAME);
      $objects = DepartmentPeer::doSelect($criteria, $con);
      foreach( $objects as $oDepartment ) {
        $node = array();
        $node['DEP_UID']      = $oDepartment->getDepUid();
        $node['DEP_PARENT']   = $oDepartment->getDepParent();
        $node['DEP_TITLE']    = $oDepartment->getDepTitle();
        $node['DEP_LAST']     = 0;

        $criteriaCount = new Criteria('workflow');
        $criteriaCount->clearSelectColumns();
        $criteriaCount->addSelectColumn( 'COUNT(*)' );
        $criteriaCount->add(DepartmentPeer::DEP_PARENT, $oDepartment->getDepUid(), Criteria::EQUAL);
        $rs = DepartmentPeer::doSelectRS($criteriaCount);
        $rs->next();
        $row = $rs->getRow();
        $node['HAS_CHILDREN'] = $row[0];
        $result[] = $node;
      }
      if ( count($result) >= 1 )
        $result[ count($result) -1 ]['DEP_LAST'] = 1;
      return $result;
    }
    catch (exception $e) {
      throw $e;
    }
  }
    
  function getUsersFromDepartment( $sDepUid, $sManagerUid )  {
    try {
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(UsersPeer::USR_UID);
      $oCriteria->addSelectColumn(UsersPeer::USR_REPORTS_TO);
      $oCriteria->add(UsersPeer::USR_STATUS, 'CLOSED', Criteria::NOT_EQUAL);
      $oCriteria->add(UsersPeer::DEP_UID, $sDepUid);
          
      $rs = UsersPeer::doSelectRS($oCriteria);
      $rs->setFetchmode(ResultSet::FETCHMODE_ASSOC);
          
      $oUser = new Users();
      $aUsers[] = array('USR_UID' =>'char', 'USR_USERNAME' =>'char','USR_FULLNAME' =>'char', 'USR_REPORTS_TO'=>'char','USR_MANAGER' =>'char');          
      $rs->next();
      $row = $rs->getRow();
      while( is_array($row) ) {
        $usrFields = $oUser->LoadDetails( $row['USR_UID'] );
        $row['USR_USERNAME'] = $usrFields['USR_USERNAME'];
        $row['USR_FULLNAME'] = $usrFields['USR_FULLNAME'];
        $row['USR_MANAGER']  = $row['USR_UID'] == $sManagerUid ? G::loadTranslation("ID_YES") : G::loadTranslation("ID_NO");
        $row['DEP_UID']      = $sDepUid;
        if ( $row['USR_REPORTS_TO'] != '' ) {
          try {
            $managerFields = $oUser->LoadDetails( $row['USR_REPORTS_TO'] );
            $row['USR_REPORTS_NAME'] = $managerFields['USR_FULLNAME'];
          }
          catch (exception $e) {
            $row['USR_REPORTS_NAME'] = '.';
          }
        }
        else
          $row['USR_REPORTS_NAME'] = '.';
        $aUsers[] = $row;
        $rs->next();
        $row = $rs->getRow();
      }
      
      G::LoadClass('ArrayPeer');
      global $_DBArray;
      $_DBArray['DepartmentUserList'] = $aUsers ;
       $_SESSION['_DBArray'] = $_DBArray;
      $oCriteriaT = new Criteria('dbarray');
      $oCriteriaT->setDBArrayTable('DepartmentUserList');
        
      return $oCriteriaT;
    }
    catch (exception $e) {
      throw $e;
    }
  }
  
  /*
  * Remove a user from Departments
  * @param string $DepUid, $UsrUid
  * @return array
  */
  function removeUserFromDepartment($DepUid, $UsrUid) {   
    $aFields = array ('USR_UID'=> $UsrUid,'DEP_UID'=> '', 'USR_REPORTS_TO' => '');
    try {
      $oUser = UsersPeer::retrieveByPk( $UsrUid );
      if (is_object($oUser) && get_class($oUser) == 'Users' ) {
        //$oDepto = new Users();
        $oUser->setDepUid ( '');
        $oUser->setUsrReportsTo ( '');
        $oUser->save();
      } 
    }
    catch (exception $oError) {
      throw ($oError);
    }
  }

    /*
    * Return the available users list criteria object
    * @param string $sGroupUID
    * @return object
    */
    function getAvailableUsersCriteria($sGroupUID = '')
    {
        try {
            $oCriteria = new Criteria('workflow');
            $oCriteria->addSelectColumn(UsersPeer::USR_UID);
            $oCriteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
            $oCriteria->addSelectColumn(UsersPeer::USR_LASTNAME);
            $oCriteria->add(UsersPeer::DEP_UID, "", Criteria::EQUAL);
            $oCriteria->add(UsersPeer::USR_STATUS, 'ACTIVE');
            return $oCriteria;
        }
        catch (exception $oError) {
            throw ($oError);
        }
    }
    
    /*
    * Return the cant Users In Department
    * @param string $sDepUID
    * @return object
    */
    function cantUsersInDepartment ( $sDepUID ) {
      try {
        $c = new Criteria('workflow');
        $c->addSelectColumn('COUNT(*)');
        $c->add(UsersPeer::USR_STATUS, 'CLOSED', Criteria::NOT_EQUAL);
        $c->add(UsersPeer::DEP_UID, $sDepUID);

        $rs = UsersPeer::doSelectRS($c);
        $rs->next();
        $row = $rs->getRow();
        $count = $row[0];
        return $count;
      }
      catch (exception $oError) {
        throw ($oError);
      }
    }
		function loadByGroupname ( $Groupname ) {       
		    $c = new Criteria('workflow');    
		    $del = DBAdapter::getStringDelimiter();
		
		    $c->clearSelectColumns();
		    $c->addSelectColumn( ContentPeer::CON_CATEGORY );
		    $c->addSelectColumn( ContentPeer::CON_VALUE );
		                    
		    $c->add(ContentPeer::CON_CATEGORY,  'DEPO_TITLE');
		    $c->add(ContentPeer::CON_VALUE,  $Groupname);
		    $c->add(ContentPeer::CON_LANG,  SYS_LANG );          
		    return $c;   
		  }

} // Department
