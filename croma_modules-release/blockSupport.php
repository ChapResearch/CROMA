<?php
//
// blockSupport.php
//
//   This file implements some convenience functions for defining the blocks
//   that we need for all of the stuff in this application.
//
// INTRODUCTION
//
//  Each block needs to have a few certain things that are initialized by
//  Drupal, and these functions make it somewhat easier to do and add new
//  blocks over time.  To use these functions you simply define an array
//  of the names of the functions and titles of the blocks, and then call
//  the block initialization routine.  It then just makes the blocks available
//  to drupal.
//
//  There are two types of blocks that this thing does:  forms and other.
//  There's not much difference between the two, but it is easier to organize
//  along those lines.  Each different set of blocks is defined with an array
//  of the following forms:
//
//  $formBlocks = array(
//      array("id" => "mymodule_fun_entry_form", "title" => "Fun Block",    "form" => "mymoduleFunEntryForm"),
//      array("id" => "mymodule_response_form",  "title" => "Response Form","form" => "mymoduleResponseForm")
//  );
//
//  $otherBlocks = array(
//      array("id" => "mymodule_display_fun",   "title" => "Display Fun",   "content" => "mymoduleDisplayFun"),
//      array("id" => "mymodule_show_response", "title" => "Show Response", "content" => "mymoduleShowResponse")
//  );
//
//  The ["id"] key refers to the internal identification of the block and should be unique in your module.
//  The ["title"] key is used as the block title (if you even use it) but is also used in the Admin display.
//  The ["content"] is the PHP function that is used for processing the block.
//

function blockLoadForms(&$blockInfo,&$blockViewFns,$blocksArray)
{
    foreach($blocksArray as $block) {
        $subject = t($block["title"]);
        if(isset($block["subject"])) {
            $subject = t($block["subject"]);
        }
        $blockInfo[$block["id"]] = array( "info" => t($block["title"]), "cache" => DRUPAL_CACHE_GLOBAL );
        $blockViewFns[$block["id"]] =
            function(&$b) use ($block,$subject) {
                $b['subject'] = $subject;
                $b['content'] = drupal_get_form($block["form"]);
            };
    }
}

function blockLoadOther(&$blockInfo,&$blockViewFns,$blocksArray)
{
    foreach($blocksArray as $block) {
        $subject = t($block["title"]);
        if(isset($block["subject"])) {
            $subject = t($block["subject"]);
        }
        $blockInfo[$block["id"]] = array( "info" => t($block["title"]), "cache" => DRUPAL_CACHE_GLOBAL );
        $blockViewFns[$block["id"]] =
            function(&$b) use ($block,$subject) {
                $b['subject'] = $subject;
                $b['content'] = $block["content"]();
            };
    }
}
