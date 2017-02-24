<?php
/**
 * Smarty plugin
 *
 */

require_once( dirname(__FILE__).'/markdown.php');

function smarty_modifier_markdown($text) {
    $instance = new MarkdownExtra_Parser;
    return $instance->transform($text);
}

