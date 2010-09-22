<?php
/** 
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
/*------------------------------------------------------------------------------------------------
| dynaformhandler.class.php
| By Erik Amaru Ortiz
| Email: aortiz.erik@gmail.com
+--------------------------------------------------
| Email bugs/suggestions to aortiz.erik@gmail.com
+--------------------------------------------------
| This script has been created and released under
| the GNU GPL and is free to use and redistribute
| only if this copyright statement is not removed
| You can see www.gnu.org the GPL lisence reference
+-------------------------------------------------------------------------------------------------*/

/**
* @Author Erik Amaru Ortiz
* @Date Aug 26th, 2009
* @Description This class is a Dynaform handler for modify directly into file
*/

class dynaFormHandler
{
  private $xmlfile;
  private $dom;
  private $root;

 /**
   * Function constructor
   * @access public
   * @param  string $file
   * @return void
   */
 function __construct($file=null)
  {
    if( !isset($file) ) 
      throw new Exception('[Class dynaFormHandler] ERROR:  xml file was not set!!');
    $this->xmlfile = $file;
    $this->load();
  }

  function load(){
    $this->dom = new DOMDocument();
    $this->dom->preserveWhiteSpace = false;
    $this->dom->formatOutput = true;
    if( is_file($this->xmlfile) ) {
      $this->dom->load($this->xmlfile);
      $this->root = $this->dom->firstChild;
    } else {
      throw new Exception('[Class dynaFormHandler] ERROR:  the ('.$this->xmlfile.') file doesn\'t exits!!');
    }
  }

  function reload(){
    $this->dom = NULL;
    $this->load();
  }

  /**
   * Function __cloneEmpty
   * @access public
   * @return void
   */
  function __cloneEmpty()
  {
    //$cloneObj = clone $this;
    //$cloneObj->xmlfile = '__Clone__' . $cloneObj->xmlfile;
    $xPath = new DOMXPath($this->dom);
    $nodeList = $xPath->query('/dynaForm/*');
    foreach ($nodeList as $domElement){
      //echo $domElement->nodeName.'<br>';
      $elements[] = $domElement->nodeName;
    }
    $this->remove($elements);
    //return $cloneObj;
  }

  /**
   * Function toString
   * @access public
   * @param  string $op
   * @return void
   */
  function toString($op='')
  {
    switch($op){
      case 'html': return htmlentities(file_get_contents($this->xmlfile));
      default: return file_get_contents($this->xmlfile);
    }
  }
  
  /**
   * Function getNode
   * @access public
   * @param  string $nodename
   * @return void
   */
  function getNode($nodename)
  {
    return $this->root->getElementsByTagName($nodename)->item(0);
  }

  /**
   * Function getNode
   * @access public
   * @param  object $node
   * @return object
   */
  function setNode($node){
    $newnode = $this->root->appendChild($node);
    $this->save();
    return $newnode;
  }

  /*
    $child_childs = Array(
      Array(name=>'option', value=>'uno', 'attributes'=>Array('name'=>1)),
      Array(name=>'option', value=>'dos', 'attributes'=>Array('name'=>2)),
      Array(name=>'option', value=>'tres', 'attributes'=>Array('name'=>3)),
    )
  */
  
 /**
  * Add Function
  * @param string $name
  * @param array $attributes
  * @param array $childs
  * @param array $childs_childs
  * @return void
  */
  //attributes (String node-name, Array attributes(atribute-name =>attribute-value, ..., ...), Array childs(child-name=>child-content), Array Child-childs())
  function add($name, $attributes, $childs, $childs_childs=null)
  {
    $newnode = $this->root->appendChild($this->dom->createElement($name));
    foreach($attributes as $att_name => $att_value) {
      $newnode->setAttribute($att_name, $att_value);
    }
    if(is_array($childs)){
      foreach($childs as $child_name => $child_text) {
        $newnode_child = $newnode->appendChild($this->dom->createElement($child_name));
        $newnode_child->appendChild($this->dom->createTextNode($child_text));
        if($childs_childs != null and is_array($childs_childs)){
          foreach($childs_childs as $cc) {
            $ccmode = $newnode_child->appendChild($this->dom->createElement($cc['name']));
            $ccmode->appendChild($this->dom->createTextNode($cc['value']));
            foreach($cc['attributes'] as $cc_att_name => $cc_att_value) {
              $ccmode->setAttribute($cc_att_name, $cc_att_value);
            }
          }
        }
      }
    } else {
      $text_node = $childs;
      $newnode->appendChild($this->dom->createCDATASection($text_node));
    }
    $this->save();
  }

  /**
   * Function replace
   * @access public
   * @param string $replaced
   * @param string $name
   * @param array $attributes
   * @param array $childs
   * @param array $childs_childs
   * @return void
   */
  function replace($replaced, $name, $attributes, $childs, $childs_childs=null)
  {
    $element = $this->root->getElementsByTagName($replaced)->item(0);
    $this->root->replaceChild($this->dom->createElement($name), $element);
    $newnode = $element = $this->root->getElementsByTagName($name)->item(0);
    foreach($attributes as $att_name => $att_value) {
      $newnode->setAttribute($att_name, $att_value);
    }
    if(is_array($childs)){
      foreach($childs as $child_name => $child_text) {
        $newnode_child = $newnode->appendChild($this->dom->createElement($child_name));
        $newnode_child->appendChild($this->dom->createTextNode($child_text));
        if($childs_childs != null and is_array($childs_childs)){
          foreach($childs_childs as $cc) {
            $ccmode = $newnode_child->appendChild($this->dom->createElement($cc['name']));
            $ccmode->appendChild($this->dom->createTextNode($cc['value']));
            foreach($cc['attributes'] as $cc_att_name => $cc_att_value) {
              $ccmode->setAttribute($cc_att_name, $cc_att_value);
            }
          }
        }
      }
    } else {
      $text_node = $childs;
      $newnode->appendChild($this->dom->createTextNode($text_node));
    }
    $this->save();
  }
 /**
  * Function save
  * @param string $fname
  * @return void
  */
  function save($fname=null)
  {
    if( !isset($fname) ){
      $this->dom->save($this->xmlfile);
    } else {
      $this->xmlfile = $fname;
      $this->dom->save($this->xmlfile);
    }
    //$this->fixXmlFile();
  }

/**
  * Function fixXmlFile
  * @return void
  */
  function fixXmlFile()
  {
    $newxml = '';
    $content = file($this->xmlfile);
    foreach($content as $line){
      if( trim($line) != ''){
        $newxml .= $line;
      }
    }
    file_put_contents($this->xmlfile, $newxml);
  }

/**
  * Function setHeaderAttribute
  * @param string $att_name
  * @param string $att_value
  * @return void
  */
  function setHeaderAttribute($att_name, $att_value)
  {
    $this->root->setAttribute($att_name, $att_value);
    $this->save();
  }

/**
  * Function modifyHeaderAttribute
  * @param string $att_name
  * @param string $att_new_value
  * @return void
  */
  function modifyHeaderAttribute($att_name, $att_new_value)
  {
    $this->root->removeAttribute($att_name);
    $this->root->setAttribute($att_name, $att_new_value);
    $this->save();
  }

/**
  * Function updateAttribute
  * @param string $node_name
  * @param string $att_name
  * @param string $att_new_value
  * @return void
  */
  function updateAttribute($node_name, $att_name, $att_new_value)
  {
    $xpath = new DOMXPath($this->dom);
    $nodeList = $xpath->query("/dynaForm/$node_name");
    $node = $nodeList->item(0);
    $node->removeAttribute($att_name);
    $node->setAttribute($att_name, $att_new_value);
    $this->save();
  }

/**
  * Function remove
  * @param string $v
  * @return void
  */
  function remove($v)
  {
    if(!is_array($v)){
      $av[0] = $v;
    } else{
      $av = $v;
    }
    foreach($av as $e){
      $xnode = $this->root->getElementsByTagName($e)->item(0);
      if ( $xnode->nodeType == XML_ELEMENT_NODE ) {
        $dropednode = $this->root->removeChild($xnode);
            /*evaluation field aditional routines*/
        $xpath = new DOMXPath($this->dom);
        $nodeList = $xpath->query("/dynaForm/JS_$e");
        if($nodeList->length != 0){
          $tmp_node = $nodeList->item(0);
          $this->root->removeChild($tmp_node);
        }
      } else {
        print("[Class dynaFormHandler] ERROR:  The \"$e\" element doesn't exist!<br>");
      }
    }
    $this->save();
  }
  
/**
  * Function nodeExists
  * @param string $node_name
  * @return boolean
  */
  function nodeExists($node_name)
  {
    $xpath = new DOMXPath($this->dom);
    $nodeList = $xpath->query("/dynaForm/$node_name");
    $node = $nodeList->item(0);
    if($nodeList->length != 0){
      return true;
    } else {
      return false;
    }
  }

  //new features 
 /**
  * Function moveUp
  * @param string $selected_node
  * @return void
  */
  function moveUp($selected_node)
  {
    /*DOMNode DOMNode::insertBefore  ( DOMNode $newnode  [, DOMNode $refnode  ] )
    This function inserts a new node right before the reference node. If you plan
    to do further modifications on the appended child you must use the returned node. */
    $xpath = new DOMXPath($this->dom);
    $nodeList = $xpath->query("/dynaForm/*");
    $flag = false;
    for($i = 0; $i < $nodeList->length; $i++) {
      $xnode = $nodeList->item($i);
      if($selected_node == $xnode->nodeName){
        //if is a first node move it to final with a circular logic
        if( $flag === false ){
          $removed_node = $this->root->removeChild($xnode);
          $this->root->appendChild($removed_node);
          break;
        } else {
          $removed_node = $this->root->removeChild($xnode);
          $predecessor_node = $nodeList->item($i-1);
          $this->root->insertBefore($removed_node, $predecessor_node);
          break;
        }
      }
      $flag = true;
    }
    $this->save();
  }

 /**
  * Function moveDown
  * @param string $selected_node
  * @return void
  */
  function moveDown($selected_node)
  {
    /*DOMNode DOMNode::insertBefore  ( DOMNode $newnode  [, DOMNode $refnode  ] )
    This function inserts a new node right before the reference node. If you plan
    to do further modifications on the appended child you must use the returned node. */
    $xpath = new DOMXPath($this->dom);
    $nodeList = $xpath->query("/dynaForm/*");
    $real_length = $nodeList->length;
    for($i = 0; $i < $nodeList->length; $i++) {
      $xnode = $nodeList->item($i);
      if($selected_node == $xnode->nodeName){
        //if is a last node move it to final with a circular logic
        if( ($i+1) == $real_length){
          if($real_length != 1){
            $first_node = $nodeList->item(0);
            $removed_node = $this->root->removeChild($xnode);
            $this->root->insertBefore($removed_node, $first_node);
          }
          break;
        } else {
          if( ($i+3) <= $real_length ){
            $removed_node = $this->root->removeChild($xnode);
            $predecessor_node = $nodeList->item($i+2);
            $this->root->insertBefore($removed_node, $predecessor_node);
            break;
          } else {
            $removed_node = $this->root->removeChild($xnode);
            $this->root->appendChild($removed_node);
            break;
          }
        }
      }
    }
    $this->save();
  }
  
 /**
  * Function getFields
  * @param array $aFilter
  * @return array
  */
  function getFields( $aFilter = Array() )
  {
    $xpath = new DOMXPath($this->dom);
    $nodeList = $xpath->query("/dynaForm/*");
    $aList = Array();
    for($i = 0; $i < $nodeList->length; $i++) {
      $xnode = $nodeList->item($i);
      if( is_array($aFilter) && sizeof($aFilter) > 0 ){
        if( isset($aFilter['IN']) ){
          if( isset($aFilter['NOT_IN']) ){
            if( in_array($xnode->nodeName, $aFilter['IN']) && !in_array($xnode->nodeName, $aFilter['NOT_IN']) ){
              array_push($aList, $xnode);
            }
          } else {
            if( in_array($xnode->nodeName, $aFilter['IN']) ){
              array_push($aList, $xnode);
            }
          }
        } else if( isset($aFilter['NOT_IN']) ){
          if( !in_array($xnode->nodeName, $aFilter['NOT_IN']) ){
            array_push($aList, $xnode);
          }
        } else {
          array_push($aList, $xnode);
        }
      } else {
        array_push($aList, $xnode);
      }
    }
    return  $aList;
  }
  
 /**
  * Function getFieldNames
  * @param array $aFilter
  * @return array
  */
  function getFieldNames( $aFilter = Array() )
  {
    $aList = $this->getFields($aFilter);
    $aFieldNames = Array();
    foreach( $aList as $item ){
      array_push($aFieldNames, $item->nodeName);
    }
    return $aFieldNames;
  }
  
  // 
  function addChilds($name, $childs, $childs_childs=null)
  {
    //
    $xpath = new DOMXPath($this->dom);
    $nodeList = $xpath->query("/dynaForm/$name");
    
    if( $nodeList->length == 0 ) {
      $element = $this->root->appendChild($this->dom->createElement($name));
    } else
      $element = $this->root->getElementsByTagName($name)->item(0);
    
    if( is_array($childs) ) {
      foreach( $childs as $child_name => $child_text ) {
        
        $nodeList = $xpath->query("/dynaForm/$name/$child_name");
        
        if( $nodeList->length == 0 ){ //the node doesn't exist
          //$newnode_child 
          $childNode = $element->appendChild($this->dom->createElement($child_name));
          $childNode->appendChild($this->dom->createCDATASection($child_text));
        } else { // the node already exists
          //update its value
          $childNode = $element->getElementsByTagName($child_name)->item(0);
          
          //
          if($child_text !== NULL){
            $xnode = $this->dom->createElement($childNode->nodeName);
            $xnode->appendChild($this->dom->createCDATASection($child_text));
                
            /*if( $childNode->hasChildNodes() ) {
              foreach ($childNode->childNodes as $domElement){
                $domNode = $domElement->cloneNode(true);
                if( $domNode->nodeType != 4 ) {
                  $xnode->appendChild($domNode);
                }
              }
            }*/
            $element->replaceChild($xnode, $childNode); 
            $childNode = $element->getElementsByTagName($child_name)->item(0);
          }
        }
        
        if($childs_childs != null and is_array($childs_childs)){
          foreach($childs_childs as $cc) {
            $ccnode = $childNode->appendChild($this->dom->createElement($cc['name']));
            $ccnode->appendChild($this->dom->createCDATASection($cc['value']));
            foreach($cc['attributes'] as $cc_att_name => $cc_att_value) {
              $ccnode->setAttribute($cc_att_name, $cc_att_value);
            }
            //$this->addOrUpdateChild($childNode, $cc['name'], $cc['value'], $cc['attributes']);
          }
        }
      }
    } else {
      $text_node = $childs;
      $newnode->appendChild($this->dom->createTextNode($text_node));
    }
    $this->save();
  }


  function addOrUpdateChild($xnode, $childName, $childValue, $childAttributes){
    //$element = $this->root->getElementsByTagName($nodeName)->item(0);
    //$childNode = $element->getElementsByTagName($childName)->item(0);
    
    $newNode = $this->dom->createElement($childName);
    $newNode->appendChild($this->dom->createCDATASection($childValue));
    
    foreach($childAttributes as $attName => $attValue) {
      $newNode->setAttribute($attName, $attValue);
    }
    
    if( $xnode->hasChildNodes() ) {
      foreach($xnode->childNodes as $cnode) {
        if( $cnode->nodeName == $childName ) {
          $xnode->replaceChild($newNode, $cnode);
          break;
        }
      }
    } else 
      $xnode->appendChild($newNode);
  }
}

//examples...........
//$o = new dynaFormHandler('xxx.xml');
//attributes (String node-name, Array attributes(atribute-name =>attribute-value, ..., ...), Array childs(child-name=>child-content), Array Child-childs())
/*$child_childs = Array(
  Array('name'=>'option', 'value'=>'uno2', 'attributes'=>Array('name'=>1112)),
  Array('name'=>'option', 'value'=>'dos', 'attributes'=>Array('name'=>222)),
  Array('name'=>'option', 'value'=>'tres', 'attributes'=>Array('name'=>333)),
);*/
//$o->add('erik', Array('att1'=>1, 'att2'=>'dos'), Array('en'=>'hello'), $child_childs);
//$o->addChilds('neyek', Array('en'=>'deutch'), $child_childs);
//print_r($o->getFieldNames());

/* for($i=1; $i<=5; $i++){
  $o->add('lastnamex'.$i, Array('type'=>'text', 'defaultvalue'=>'Ortiz'), Array('es'=>'Apellido'));
}*/
/*
$child_childs = Array(
  Array('name'=>'option', 'value'=>'uno', 'attributes'=>Array('name'=>111)),
  Array('name'=>'option', 'value'=>'tres', 'attributes'=>Array('name'=>333)),
);
$o->replace('antiguedad', 'antiguedad_replaced', Array('type'=>'dropdown', 'required'=>'0'), Array('es'=>'Antiguedad !!'), $child_childs);
*/
//$o->remove('usr_email');
//$o->replace('usr_uid', 'usr_uid222', Array('type'=>'text', 'defaultvalue'=>'111'), Array('es'=>'fucking id'));