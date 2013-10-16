<?php

$Module = array( 'name' => 'Image upload' );

$ViewList = array();

$ViewList['relations'] = array(
    'functions' => array( 'editor' ),
    'ui_context' => 'edit',
    'script' => 'relations.php',
    'params' => array( 'ObjectID', 'ObjectVersion', 'ContentType', 'EmbedID', 'EmbedInline', 'EmbedSize' )
);

$ViewList['upload'] = array(
    'functions' => array( 'editor' ),
    'ui_context' => 'edit',
    'script' => 'upload.php',
    'params' => array( 'ObjectID', 'ObjectVersion', 'ContentType', 'AttributeID', 'PositionID' )
);
$ViewList['optout'] = array(
'ui_context' => 'content',
'script' => 'optout.php',
'params' => array( 'hash' )
);
$FunctionList = array();
$FunctionList['relations'] = array();
$FunctionList['editor'] = array();
$FunctionList['search'] = array();// only used by template code to see if user should see this feature in ezoe
$FunctionList['browse'] = array();// only used by template code to see if user should see this feature in ezoe