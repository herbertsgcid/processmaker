<?php

class pmTables extends Controller
{
  public $debug = true;
  
  public function index($httpData)
  {
    global $RBAC;
    $RBAC->requirePermissions('PM_SETUP_ADVANCE');

    G::LoadClass('configuration');
    $c = new Configurations();
    $configPage = $c->getConfiguration('additionalTablesList', 'pageSize','',$_SESSION['USER_LOGGED']);
    $Config['pageSize'] = isset($configPage['pageSize']) ? $configPage['pageSize'] : 20;

    $this->includeExtJS('pmTables/list', $this->debug);
    $this->setView('pmTables/list');
    
    //assigning js variables
    $this->setJSVar('FORMATS',$c->getFormats());
    $this->setJSVar('CONFIG', $Config);
    $this->setJSVar('PRO_UID', isset($_GET['PRO_UID'])? $_GET['PRO_UID'] : false);

    //render content
    G::RenderPage('publish', 'extJs');
  }

  public function edit($httpData)
  {
    $addTabUid = isset($_GET['id']) ? $_GET['id'] : false;
    $table = false;
    $repTabPluginPermissions = false;

    if ($addTabUid !== false) { // if is a edit request
      require_once 'classes/model/AdditionalTables.php';
      require_once 'classes/model/Fields.php';
      $tableFields = array();
      $fieldsList = array();
      
      $additionalTables = new AdditionalTables();
      $table = $additionalTables->load($addTabUid, true);

      // list the case fields
      foreach ($table['FIELDS'] as $i=>$field) {
        $table['FIELDS'][$i]['FLD_KEY']    = $field['FLD_KEY'] == '1' ? TRUE: FALSE;
        $table['FIELDS'][$i]['FLD_NULL']   = $field['FLD_NULL'] == '1' ? TRUE: FALSE;
        $table['FIELDS'][$i]['FLD_FILTER'] = $field['FLD_FILTER'] == '1' ? TRUE: FALSE;
        array_push($tableFields, $field['FLD_DYN_NAME']);
      }

      //list dynaform fields
      switch ($table['ADD_TAB_TYPE']) {
        case 'NORMAL':
          $fields = $this->_getDynafields($table['PRO_UID']);

          foreach ($fields as $field) {
            //select to not assigned fields for available grid
            if (!in_array($field['name'], $tableFields)) {
              $fieldsList[] = array(
                'FIELD_UID'  => $field['name'] . '-' . $field['type'],
                'FIELD_NAME' => $field['name']
              );
            }
          }
          $this->setJSVar('avFieldsList', $fieldsList);
          $repTabPluginPermissions = $this->_getSimpleReportPluginDef();
          $this->setJSVar('_plugin_permissions', $repTabPluginPermissions);
          break;
          
        case 'GRID':
          list($gridName, $gridId) = explode('-', $table['ADD_TAB_GRID']);
          // $G_FORM = new Form($table['PRO_UID'] . '/' . $gridId, PATH_DYNAFORM, SYS_LANG, false);
          // $gridFields = $G_FORM->getVars(false);
          $fieldsList = array();
          $gridFields = $this->_getGridDynafields($table['PRO_UID'], $gridId);
          foreach ($gridFields as $gfield) {
            if (!in_array($gfield['name'], $tableFields)) {
              $fieldsList[] = array(
                'FIELD_UID' => $gfield['name'] . '-' . $gfield['type'],
                'FIELD_NAME' => $gfield['name']
              );
            }
          }

          $this->setJSVar('avFieldsList', $fieldsList);
          $repTabPluginPermissions = $this->_getSimpleReportPluginDef();
          break;
          
        default:
          
          break;
      }
    }

    $jsFile = isset($httpData->tableType) && $httpData->tableType == 'report' ? 'editReport' : 'edit';
    
    $this->includeExtJS('pmTables/' . $jsFile, $this->debug);

    $this->setJSVar('ADD_TAB_UID', $addTabUid);
    $this->setJSVar('PRO_UID', isset($_GET['PRO_UID'])? $_GET['PRO_UID'] : false);
    $this->setJSVar('TABLE', $table);
    $this->setJSVar('_plugin_permissions', $repTabPluginPermissions);

    G::RenderPage('publish', 'extJs');
  }
  
  function data($httpData) 
  {
    require_once 'classes/model/AdditionalTables.php';
    $additionalTables = new AdditionalTables();
    $tableDef = $additionalTables->load($httpData->id, true);
    
    $this->includeExtJS('pmTables/data', $this->debug);
    $this->setJSVar('tableDef', $tableDef);
    //g::pr($tableDef['FIELDS']);
    G::RenderPage('publish', 'extJs');
  } 
   

  /**
   * protected functions
   */
  protected function _getSimpleReportPluginDef()
  {
    global $G_TMP_MENU;
    $oMenu = new Menu();
    $oMenu->load('setup');
    $repTabPluginPermissions = false;

    foreach( $oMenu->Options as $i=>$option) {
      if ($oMenu->Types[$i] == 'private' && $oMenu->Id[$i] == 'PLUGIN_REPTAB_PERMISSIONS') {
        $repTabPluginPermissions = array();
        $repTabPluginPermissions['label'] = $oMenu->Labels[$i];
        $repTabPluginPermissions['fn'] = $oMenu->Options[$i];
        break;
      }
    }

    return $repTabPluginPermissions;
  }

  protected function _getDynafields($proUid, $type = 'xmlform')
  {
    require_once 'classes/model/Dynaform.php';
    $fields = array();
    $fieldsNames = array();
    
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(DynaformPeer::DYN_FILENAME);
    $oCriteria->add(DynaformPeer::PRO_UID, $proUid);
    $oCriteria->add(DynaformPeer::DYN_TYPE, $type);
    $oDataset = DynaformPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
  
    $excludeFieldsList = array('title', 'subtitle', 'link', 'file', 'button', 'reset', 'submit',
                              'listbox', 'checkgroup', 'grid', 'javascript');
    
    $labelFieldsTypeList = array('dropdown', 'checkbox', 'radiogroup', 'yesno');
  
    while ($aRow = $oDataset->getRow()) {
      if (file_exists(PATH_DYNAFORM . PATH_SEP . $aRow['DYN_FILENAME'] . '.xml')) {
        $G_FORM  = new Form($aRow['DYN_FILENAME'], PATH_DYNAFORM, SYS_LANG);
        
        if ($G_FORM->type == 'xmlform' || $G_FORM->type == '') {
          foreach($G_FORM->fields as $fieldName => $fieldNode) {
            if (!in_array($fieldNode->type, $excludeFieldsList) && !in_array($fieldName, $fieldsNames)) {
              $fields[] = array('name' => $fieldName, 'type' => $fieldNode->type, 'label'=> $fieldNode->label);
              $fieldsNames[] = $fieldName;
              
              if (in_array($fieldNode->type, $labelFieldsTypeList) && !in_array($fieldName.'_label', $fieldsNames)) {
                $fields[] = array('name' => $fieldName . '_label', 'type' => $fieldNode->type, 'label'=>$fieldNode->label . '_label');
                $fieldsNames[] = $fieldName;
              }
            }
          }
        }
      }
      $oDataset->next();
    }
    
    return $fields;
  }

  protected function _getGridDynafields($proUid, $gridId)
  {
    $fields = array();
    $fieldsNames = array();
    $excludeFieldsList = array('title', 'subtitle', 'link', 'file', 'button', 'reset', 'submit',
                              'listbox', 'checkgroup', 'grid', 'javascript');
    
    $labelFieldsTypeList = array('dropdown', 'checkbox', 'radiogroup', 'yesno');

    $G_FORM = new Form($proUid . '/' . $gridId, PATH_DYNAFORM, SYS_LANG, false);
    
    if ($G_FORM->type == 'grid') {
      foreach($G_FORM->fields as $fieldName => $fieldNode) {
        if (!in_array($fieldNode->type, $excludeFieldsList) && !in_array($fieldName, $fieldsNames)) {
          $fields[] = array('name' => $fieldName, 'type' => $fieldNode->type, 'label'=> $fieldNode->label);
          $fieldsNames[] = $fieldName;
          
          if (in_array($fieldNode->type, $labelFieldsTypeList) && !in_array($fieldName.'_label', $fieldsNames)) {
            $fields[] = array('name' => $fieldName . '_label', 'type' => $fieldNode->type, 'label'=>$fieldNode->label . '_label');
            $fieldsNames[] = $fieldName;
          }
        }
      }
    }
    
    return $fields;
  }
  
  protected function _getGridFields($proUid)
  {
    $aFields = array();
    $aFieldsNames = array();
    require_once 'classes/model/Dynaform.php';
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(DynaformPeer::DYN_FILENAME);
    $oCriteria->add(DynaformPeer::PRO_UID, $proUid);
    $oDataset = DynaformPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $G_FORM  = new Form($aRow['DYN_FILENAME'], PATH_DYNAFORM, SYS_LANG);
      if ($G_FORM->type == 'xmlform') {
        foreach($G_FORM->fields as $k => $v) {
          if ($v->type == 'grid') {
            if (!in_array($k, $aFieldsNames)) {
              $aFields[] = array('name' => $k, 'xmlform' => str_replace($proUid . '/', '', $v->xmlGrid));
              $aFieldsNames[] = $k;
            }
          }
        }
      }
      $oDataset->next();
    }
    return $aFields;
  }

}
