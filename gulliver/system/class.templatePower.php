<?php
/**
 * class.templatePower.php
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
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | TemplatePower:                                                       |
// | offers you the ability to separate your PHP code and your HTML       |
// +----------------------------------------------------------------------+
// |                                                                      |
// | Copyright (C) 2001,2002  R.P.J. Velzeboer, The Netherlands           |
// |                                                                      |
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License          |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA            |
// | 02111-1307, USA.                                                     |
// |                                                                      |
// | Author: R.P.J. Velzeboer, rovel@codocad.nl   The Netherlands         |
// |                                                                      |
// +----------------------------------------------------------------------+
// | http://templatepower.codocad.com                                     |
// +----------------------------------------------------------------------+
//
// $Id: Version 3.0.2$

define("T_BYFILE",              0);
define("T_BYVAR",               1);

define("TP_ROOTBLOCK",    '_ROOT');

   /**
    * class TemplatePowerParser
    * @package gulliver.system
    * 
    */
class TemplatePowerParser
{
  var $tpl_base;              //Array( [filename/varcontent], [T_BYFILE/T_BYVAR] )
  var $tpl_include;           //Array( [filename/varcontent], [T_BYFILE/T_BYVAR] )
  var $tpl_count;
  var $parent   = Array();    // $parent[{blockname}] = {parentblockname}
  var $defBlock = Array();
  var $rootBlockName;
  var $ignore_stack;
  var $version;
  var $unhtmlentities = 0;
  
   /**
    * TemplatePowerParser::TemplatePowerParser()
    *
    * @param string $tpl_file
    * @param string $type
    * @access private
    */
  function TemplatePowerParser( $tpl_file, $type )
  {
    $this->version        = '3.0.2';
    $this->tpl_base       = Array( $tpl_file, $type );
    $this->tpl_count      = 0;
    $this->ignore_stack   = Array( false );
  }

   /**
    * TemplatePowerParser::__errorAlert()
    *
    * @param string $message
    *
    * @access private
    */
  function __errorAlert( $message )
  {
    print( '<br>'. $message .'<br>'."\r\n");
  }

   /**
    * TemplatePowerParser::__prepare()
    * @access private
    * @return void
    */
  function __prepare()
  {
    $this->defBlock[ TP_ROOTBLOCK ] = Array();
    $tplvar = $this->__prepareTemplate( $this->tpl_base[0], $this->tpl_base[1]  );
    $initdev["varrow"]  = 0;
    $initdev["coderow"] = 0;
    $initdev["index"]   = 0;
    $initdev["ignore"]  = false;
    $this->__parseTemplate( $tplvar, TP_ROOTBLOCK, $initdev );
    $this->__cleanUp();
  }

    /**
     * TemplatePowerParser::__cleanUp()
     *
     * @return void
     *
     * @access private
     */
  function __cleanUp()
  {
    for( $i=0; $i <= $this->tpl_count; $i++ ){
      $tplvar = 'tpl_rawContent'. $i;
      unset( $this->{$tplvar} );
    }
  }

    /**
     * TemplatePowerParser::__prepareTemplate()
     *
     * @param string $tpl_file
     * @param string $type
     * @access private
     */
  function __prepareTemplate( $tpl_file, $type )
  {
    $tplvar = 'tpl_rawContent'. $this->tpl_count;
    if( $type == T_BYVAR ){
      $this->{$tplvar}["content"] = preg_split("/\n/", $tpl_file, -1, PREG_SPLIT_DELIM_CAPTURE);
    }
    else{
// Trigger the error in the local scope of the function    
//    trigger_error ("Some error", E_USER_WARNING); 
          $this->{$tplvar}["content"] = @file( $tpl_file ) or
          die( $this->__errorAlert('TemplatePower Error: Couldn\'t open [ '. $tpl_file .' ]!'));
    }
    $this->{$tplvar}["size"]    = sizeof( $this->{$tplvar}["content"] );
    $this->tpl_count++;
    return $tplvar;
  }

    /**
     * TemplatePowerParser::__parseTemplate()
     *
     * @param string $tplvar
     * @param string $blockname
     * @param string $initdev
      * @access private
     */
  function __parseTemplate( $tplvar, $blockname, $initdev )
  {
    $coderow = $initdev["coderow"];
    $varrow  = $initdev["varrow"];
    $index   = $initdev["index"];
    $ignore  = $initdev["ignore"];
    while( $index < $this->{$tplvar}["size"] ){
      if ( preg_match('/<!--[ ]?(START|END) IGNORE -->/', $this->{$tplvar}["content"][$index], $ignreg) ){
        if( $ignreg[1] == 'START'){
           //$ignore = true;
          array_push( $this->ignore_stack, true );
        }
        else{
                    //$ignore = false;
          array_pop( $this->ignore_stack );
        }
      }
      else{
        if( !end( $this->ignore_stack ) ){
          if( preg_match('/<!--[ ]?(START|END|INCLUDE|INCLUDESCRIPT|REUSE) BLOCK : (.+)-->/', $this->{$tplvar}["content"][$index], $regs)){
                       //remove trailing and leading spaces
            $regs[2] = trim( $regs[2] );
            if( $regs[1] == 'INCLUDE'){
              $include_defined = true;
                           //check if the include file is assigned
              if( isset( $this->tpl_include[ $regs[2] ]) ){
                $tpl_file = $this->tpl_include[ $regs[2] ][0];
                $type   = $this->tpl_include[ $regs[2] ][1];
              }
              else
                if (file_exists( $regs[2] )){    //check if defined as constant in template
                  $tpl_file = $regs[2];
                  $type     = T_BYFILE;
                }
                else{
                  $include_defined = false;
                }
              if( $include_defined ){
                               //initialize startvalues for recursive call
                $initdev["varrow"]  = $varrow;
                $initdev["coderow"] = $coderow;
                $initdev["index"]   = 0;
                $initdev["ignore"]  = false;
                $tplvar2 = $this->__prepareTemplate( $tpl_file, $type );
                $initdev = $this->__parseTemplate( $tplvar2, $blockname, $initdev );
                $coderow = $initdev["coderow"];
                $varrow  = $initdev["varrow"];
              }
            }
            else
              if( $regs[1] == 'INCLUDESCRIPT' ){
                $include_defined = true;
                           //check if the includescript file is assigned by the assignInclude function
                if( isset( $this->tpl_include[ $regs[2] ]) ){
                  $include_file = $this->tpl_include[ $regs[2] ][0];
                  $type         = $this->tpl_include[ $regs[2] ][1];
                }
                else
                  if (file_exists( $regs[2] )){    //check if defined as constant in template
                    $include_file = $regs[2];
                    $type         = T_BYFILE;
                  }
                  else{
                    $include_defined = false;
                  }
                  if( $include_defined ){
                    ob_start();
                    if( $type == T_BYFILE ){
                      if( !@include_once( $include_file ) ){
                        $this->__errorAlert( 'TemplatePower Error: Couldn\'t include script [ '. $include_file .' ]!' );
                        exit();
                      }
                    }
                    else{
                      eval( "?>" . $include_file );
                    }
                    $this->defBlock[$blockname]["_C:$coderow"] = ob_get_contents();
                    $coderow++;
                    ob_end_clean();
                  }
                }
                else
                  if( $regs[1] == 'REUSE' ){
                         //do match for 'AS'
                    if (preg_match('/(.+) AS (.+)/', $regs[2], $reuse_regs)){
                      $originalbname = trim( $reuse_regs[1] );
                      $copybname     = trim( $reuse_regs[2] );
                              //test if original block exist
                      if (isset( $this->defBlock[ $originalbname ] )){
                                  //copy block
                        $this->defBlock[ $copybname ] = $this->defBlock[ $originalbname ];
                                   //tell the parent that he has a child block
                        $this->defBlock[ $blockname ]["_B:". $copybname ] = '';
                                   //create index and parent info
                        $this->index[ $copybname ]  = 0;
                        $this->parent[ $copybname ] = $blockname;
                      }
                      else{
                        $this->__errorAlert('TemplatePower Error: Can\'t find block \''. $originalbname .'\' to REUSE as \''. $copybname .'\'');
                      }
                    }
                    else{
                               //so it isn't a correct REUSE tag, save as code
                      $this->defBlock[$blockname]["_C:$coderow"] = $this->{$tplvar}["content"][$index];
                      $coderow++;
                    }
                  }
                  else{
                    if( $regs[2] == $blockname ){     //is it the end of a block
                      break;
                    }
                    else{                             //its the start of a block
                               //make a child block and tell the parent that he has a child
                      $this->defBlock[ $regs[2] ] = Array();
                      $this->defBlock[ $blockname ]["_B:". $regs[2]] = '';
                              //set some vars that we need for the assign functions etc.
                      $this->index[ $regs[2] ]  = 0;
                      $this->parent[ $regs[2] ] = $blockname;
                               //prepare for the recursive call
                      $index++;
                      $initdev["varrow"]  = 0;
                      $initdev["coderow"] = 0;
                      $initdev["index"]   = $index;
                      $initdev["ignore"]  = false;
                      $initdev = $this->__parseTemplate( $tplvar, $regs[2], $initdev );
                      $index = $initdev["index"];
                    }
                  }
          }
          else{                                                        //is it code and/or var(s)
                       //explode current template line on the curly bracket '{'
            $sstr = explode( '{', $this->{$tplvar}["content"][$index] );
            reset( $sstr );
            if (current($sstr) != ''){
                           //the template didn't start with a '{',
                           //so the first element of the array $sstr is just code
              $this->defBlock[$blockname]["_C:$coderow"] = current( $sstr );
              $coderow++;
            }
            while (next($sstr)){
                          //find the position of the end curly bracket '}'
              $pos = strpos( current($sstr), "}" );
              if ( ($pos !== false) && ($pos > 0) ){
                             //a curly bracket '}' is found
                              //and at least on position 1, to eliminate '{}'
                              //note: position 1 taken without '{', because we did explode on '{'
                $strlength = strlen( current($sstr) );
                $varname   = substr( current($sstr), 0, $pos );
                if (strstr( $varname, ' ' )){
                                   //the varname contains one or more spaces
                                   //so, it isn't a variable, save as code
                  $this->defBlock[$blockname]["_C:$coderow"] = '{'. current( $sstr );
                  $coderow++;
                }
                else{
                                   //save the variable
                  $this->defBlock[$blockname]["_V:$varrow" ] = $varname;
                  $varrow++;
                                   //is there some code after the varname left?
                  if( ($pos + 1) != $strlength ){
                                       //yes, save that code
                    $this->defBlock[$blockname]["_C:$coderow"] = substr( current( $sstr ), ($pos + 1), ($strlength - ($pos + 1)) );
                    $coderow++;
                  }
                }
              }
              else{
                               //no end curly bracket '}' found
                               //so, the curly bracket is part of the text. Save as code, with the '{'
                $this->defBlock[$blockname]["_C:$coderow"] = '{'. current( $sstr );
                $coderow++;
              }
            }
          }
        }
        else{
          $this->defBlock[$blockname]["_C:$coderow"] = $this->{$tplvar}["content"][$index];
          $coderow++;
        }
      }
      $index++;
    }
    $initdev["varrow"]  = $varrow;
    $initdev["coderow"] = $coderow;
    $initdev["index"]   = $index;
    return $initdev;
  }


    /**
     * TemplatePowerParser::version()
     *
     * @return void
     * @access public
     */
  function version()
  {
    return $this->version;
  }

    /**
     * TemplatePowerParser::assignInclude()
     *
     * @param string $iblockname
     * @param string $value
     * @param string $type
     * @return void
     * @access public
     */
  function assignInclude( $iblockname, $value, $type=T_BYFILE )
  {
    $this->tpl_include["$iblockname"] = Array( $value, $type );
  }
}

   /**
    * class TemplatePower
    * @package gulliver.system
    */
class TemplatePower extends TemplatePowerParser
{
  var $index    = Array();        // $index[{blockname}]  = {indexnumber}
  var $content  = Array();
  var $currentBlock;
  var $showUnAssigned;
  var $serialized;
  var $globalvars = Array();
  var $prepared;

    /**
     * TemplatePower::TemplatePower()
     *
     * @param string $tpl_file
     * @param string $type
     * @return void
     * @access public
     */
  function TemplatePower( $tpl_file='', $type= T_BYFILE )
  {
    TemplatePowerParser::TemplatePowerParser( $tpl_file, $type );
    $this->prepared       = false;
    $this->showUnAssigned = false;
    $this->serialized     = false;  //added: 26 April 2002
  }

    /**
     * TemplatePower::__deSerializeTPL()
     *
     * @param string $stpl_file
     * @param string  $tplvar
     * @return void
     * @access private
     */
  function __deSerializeTPL( $stpl_file, $type )
  {
    if( $type == T_BYFILE ){
      $serializedTPL = @file( $stpl_file ) or
      die( $this->__errorAlert('TemplatePower Error: Can\'t open [ '. $stpl_file .' ]!'));
    }
    else{
      $serializedTPL = $stpl_file;
    }
    $serializedStuff = unserialize( join ('', $serializedTPL) );
    $this->defBlock = $serializedStuff["defBlock"];
    $this->index    = $serializedStuff["index"];
    $this->parent   = $serializedStuff["parent"];
  }

    /**
     * TemplatePower::__makeContentRoot()
     *
     * @return void
     * @access private
     */
  function __makeContentRoot()
  {
    $this->content[ TP_ROOTBLOCK ."_0"  ][0] = Array( TP_ROOTBLOCK );
    $this->currentBlock = &$this->content[ TP_ROOTBLOCK ."_0" ][0];
  }

    /**
     * TemplatePower::__assign()
     *
     * @param string $varname
     * @param string $value
     * @return void
     *
     * @access private
     */
  function __assign( $varname, $value)
  {
    if( sizeof( $regs = explode('.', $varname ) ) == 2 ){  //this is faster then preg_match
      $ind_blockname = $regs[0] .'_'. $this->index[ $regs[0] ];
      $lastitem = sizeof( $this->content[ $ind_blockname ] );
      $lastitem > 1 ? $lastitem-- : $lastitem = 0;
      $block = &$this->content[ $ind_blockname ][ $lastitem ];
      $varname = $regs[1];
    }
    else{
      $block = &$this->currentBlock;
    }
    $block["_V:$varname"] = $value;
  }

    /**
     * TemplatePower::__assignGlobal()
     *
     * @param string $varname
     * @param string $value
     * @return void
     * @access private
     */
  function __assignGlobal( $varname, $value )
  {
    $this->globalvars[ $varname ] = $value;
  }


    /**
     * TemplatePower::__outputContent()
     *
     * @param string $blockname
     * @return void
     * @access private
     */
  function __outputContent( $blockname )
  {
    $numrows = sizeof( $this->content[ $blockname ] );
    for( $i=0; $i < $numrows; $i++){
      $defblockname = $this->content[ $blockname ][$i][0];
      for( reset( $this->defBlock[ $defblockname ]);  $k = key( $this->defBlock[ $defblockname ]);  next( $this->defBlock[ $defblockname ] ) ){
        if ($k[1] == 'C'){
          print( $this->defBlock[ $defblockname ][$k] );
        }
        else
          if ($k[1] == 'V'){
            $defValue = $this->defBlock[ $defblockname ][$k];
            if( !isset( $this->content[ $blockname ][$i][ "_V:". $defValue ] ) ){
              if( isset( $this->globalvars[ $defValue ] ) ){
                $value = $this->globalvars[ $defValue ];
              }
              else{
                          //Verify if $defValue is like 
                          //                            "xmlfile:ID_LABEL"
                          //if it is load an xml label.
                          //if not continues with non assigned value.
                if (preg_match("/(.+):(.+)/",$defValue,$xmlreg)){
                  $value=G::LoadTranslation(/*$xmlreg[1],*/$xmlreg[2]);
                }     
                else  {
                  if( $this->showUnAssigned ){
                                  //$value = '{'. $this->defBlock[ $defblockname ][$k] .'}';
                    $value = '{'. $defValue .'}';
                  }
                  else{
                    $value = '';
                  }
                }
              }
            }
            else{
              $value = $this->content[ $blockname ][$i][ "_V:". $defValue ];
            }
            if ($this->unhtmlentities)
              $value = G::unhtmlentities($value);
              print( $value );
            }
            else
              if ($k[1] == 'B'){
                if( isset( $this->content[ $blockname ][$i][$k] ) ){
                  $this->__outputContent( $this->content[ $blockname ][$i][$k] );
                }
              }
      }
    }
  }
    
     /**
     * function __printVars
     *
     * @return void
     * @access public
     */
  function __printVars()
  {
    var_dump($this->defBlock);
    print("<br>--------------------<br>");
    var_dump($this->content);
   }


  /**********
      public members
            ***********/

    /**
     * TemplatePower::serializedBase()
     *
     * @return void
     *
     * @access public
     */
  function serializedBase()
  {
    $this->serialized = true;
    $this->__deSerializeTPL( $this->tpl_base[0], $this->tpl_base[1] );
  }

    /**
     * TemplatePower::showUnAssigned()
     *
     * @param $state
     * @return void
     * @access public
     */
  function showUnAssigned( $state = true )
  {
    $this->showUnAssigned = $state;
  }

    /**
     * TemplatePower::prepare()
     *
     * @return void
     * @access public
     */
  function prepare()
  {
    if (!$this->serialized){
      TemplatePowerParser::__prepare();
    }
    $this->prepared = true;
    $this->index[ TP_ROOTBLOCK ]    = 0;
    $this->__makeContentRoot();
  }

    /**
     * TemplatePower::newBlock()
     *
     * @param string $blockname
     * @return void
     * @access public
     */
  function newBlock( $blockname )
  {
    $parent = &$this->content[ $this->parent[$blockname] .'_'. $this->index[$this->parent[$blockname]] ];
    $lastitem = sizeof( $parent );
    $lastitem > 1 ? $lastitem-- : $lastitem = 0;
    $ind_blockname = $blockname .'_'. $this->index[ $blockname ];
    if ( !isset( $parent[ $lastitem ]["_B:$blockname"] )){
           //ok, there is no block found in the parentblock with the name of {$blockname}
           //so, increase the index counter and create a new {$blockname} block
      $this->index[ $blockname ] += 1;
      $ind_blockname = $blockname .'_'. $this->index[ $blockname ];
      if (!isset( $this->content[ $ind_blockname ] ) ){
        $this->content[ $ind_blockname ] = Array();
      }
           //tell the parent where his (possible) children are located
      $parent[ $lastitem ]["_B:$blockname"] = $ind_blockname;
    }
       //now, make a copy of the block defenition
    $blocksize = sizeof( $this->content[ $ind_blockname ] );
    $this->content[ $ind_blockname ][ $blocksize ] = Array( $blockname );
       //link the current block to the block we just created
    $this->currentBlock = &$this->content[ $ind_blockname ][ $blocksize ];
  }

    /**
     * TemplatePower::assignGlobal()
     *
     * @param string $varname
     * @param string $value
     * @return void
     *
     * @access public
     */
  function assignGlobal( $varname, $value='' )
  {
    if (is_array( $varname )){
      foreach($varname as $var => $value){
        $this->__assignGlobal( $var, $value );
      }
    }
    else{
      $this->__assignGlobal( $varname, $value );
    }
  }

    /**
     * TemplatePower::assign()
     *
     * @param string $varname
     * @param string $value
     * @return void
     * @access public
     */
  function assign( $varname, $value='' )
  {
    if (is_array( $varname )){
      foreach($varname as $var => $value){
        $this->__assign( $var, $value );
      }
    }
    else{
      $this->__assign( $varname, $value );
    }
  }

    /**
     * TemplatePower::gotoBlock()
     *
     * @return void
     * @param string $blockname
     * @access public
     */
  function gotoBlock( $blockname )
  {
    if ( isset( $this->defBlock[ $blockname ] ) ){
      $ind_blockname = $blockname .'_'. $this->index[ $blockname ];
           //get lastitem indexnumber
      $lastitem = sizeof( $this->content[ $ind_blockname ] );
      $lastitem > 1 ? $lastitem-- : $lastitem = 0;
           //link the current block
      $this->currentBlock = &$this->content[ $ind_blockname ][ $lastitem ];
    }
  }

    /**
     * TemplatePower::getVarValue()
     *
     * @param $varname
     * @param string $varname
     * @access public
     */
  function getVarValue( $varname )
  {
    if( sizeof( $regs = explode('.', $varname ) ) == 2 ){  //this is faster then preg_match{
      $ind_blockname = $regs[0] .'_'. $this->index[ $regs[0] ];
      $lastitem = sizeof( $this->content[ $ind_blockname ] );
      $lastitem > 1 ? $lastitem-- : $lastitem = 0;
      $block = &$this->content[ $ind_blockname ][ $lastitem ];
      $varname = $regs[1];
    }
    else{
      $block = &$this->currentBlock;
    }
    return $block["_V:$varname"];
  }

    /**
     * TemplatePower::printToScreen()
     *
     * @return void
     * @access public
     */
  function printToScreen()
  {
    if ($this->prepared){
      $this->__outputContent( TP_ROOTBLOCK .'_0' );
    }
    else{
      $this->__errorAlert('TemplatePower Error: Template isn\'t prepared!');
    }
  }

    /**
     * TemplatePower::getOutputContent()
     *
     * @return void
     * @access public
     * @package gulliver.system
     */
  function getOutputContent()
  {
    ob_start();
    $this->printToScreen();
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
  }
}
