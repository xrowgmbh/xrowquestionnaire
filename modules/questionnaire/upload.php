<?php

$module = $Params['Module'];
$namedParameters = $module->NamedParameters;

$objectID = isset( $namedParameters['ObjectID'] ) ? (int) $namedParameters['ObjectID'] : 0;
$objectVersion = isset( $namedParameters['ObjectVersion'] ) ? (int) $namedParameters['ObjectVersion'] : 0;
$attributeID = isset( $namedParameters['AttributeID'] ) ? (int) $namedParameters['AttributeID'] : 0;
$positionID = isset( $namedParameters['PositionID'] ) ? (int) $namedParameters['PositionID'] : 0;

// Supported content types: image, media and file
// Media is threated as file for now
$contentType = 'images';
if ( isset( $namedParameters['ContentType'] ) && $namedParameters['ContentType'] !== '' )
{
    $contentType = $Params['ContentType'];
}

if ( $objectID === 0  || $objectVersion === 0 )
{
   echo ezpI18n::tr( 'design/standard/ezoe', 'Invalid or missing parameter: %parameter', null, array( '%parameter' => 'ObjectID/ObjectVersion' ) );
   eZExecution::cleanExit();
}

$user = eZUser::currentUser();
if ( $user instanceOf eZUser )
{
    $result = $user->hasAccessTo( 'questionnaire', 'editor' );
}
else
{
    $result = array('accessWord' => 'no');
}

if ( $result['accessWord'] === 'no' )
{
   echo ezpI18n::tr( 'design/standard/error/kernel', 'Your current user does not have the proper privileges to access this page.' );
   eZExecution::cleanExit();
}


$object = eZContentObject::fetch( $objectID );
$http = eZHTTPTool::instance();
$imageIni = eZINI::instance( 'image.ini' );
$params  = array('dataMap' => array('image'));


if ( !$object )
{
   echo ezpI18n::tr( 'design/standard/ezoe', 'Invalid parameter: %parameter = %value', null, array( '%parameter' => 'ObjectId', '%value' => $objectID ) );
   eZExecution::cleanExit();
}


// is this a upload?
// forcedUpload is needed since hasPostVariable returns false if post size exceeds
// allowed size set in max_post_size in php.ini
if ( $http->hasPostVariable( 'uploadButton' ) )
{
    $upload = new eZContentUpload();

    $location = false;
    if ( $http->hasPostVariable( 'location' ) )
    {
        $location = $http->postVariable( 'location' );
        if ( $location === 'auto' || trim( $location ) === '' ) $location = false;
    }

    $objectName = '';
    if ( $http->hasPostVariable( 'objectName' ) )
    {
        $objectName = trim( $http->postVariable( 'objectName' ) );
    }

    $uploadedOk = $upload->handleUpload( $result, 'fileName', $location, false, $objectName );


    if ( $uploadedOk )
    {
        $newObject = $result['contentobject'];
        $newObjectID = $newObject->attribute( 'id' );
        $newObjectName = $newObject->attribute( 'name' );
        $newObjectNodeID = (int) $newObject->attribute( 'main_node_id' ); // this will be empty if object is stopped by approve workflow

        // edit attributes
        $newVersionObject  = $newObject->attribute( 'current' );
        $newObjectDataMap  = $newVersionObject->attribute('data_map');

        foreach ( array_keys( $newObjectDataMap ) as $key )
        {
            //post pattern: ContentObjectAttribute_attribute-identifier
            $base = 'ContentObjectAttribute_'. $key;
            if ( $http->hasPostVariable( $base ) && $http->postVariable( $base ) !== '' )
            {
                switch ( $newObjectDataMap[$key]->attribute( 'data_type_string' ) )
                {
                    case 'eztext':
                    case 'ezstring':
                        // TODO: Validate input ( max length )
                        $newObjectDataMap[$key]->setAttribute('data_text', trim( $http->postVariable( $base ) ) );
                        $newObjectDataMap[$key]->store();
                        break;
                    case 'ezfloat':
                        // TODO: Validate input ( max / min values )
                        $newObjectDataMap[$key]->setAttribute('data_float', (float) str_replace(',', '.', $http->postVariable( $base ) ) );
                        $newObjectDataMap[$key]->store();
                        break;
                    case 'ezinteger':
                        // TODO: Validate input ( max / min values )
                    case 'ezboolean':
                        $newObjectDataMap[$key]->setAttribute('data_int', (int) $http->postVariable( $base ) );
                        $newObjectDataMap[$key]->store();
                        break;
                    case 'ezimage':
                        $content = $newObjectDataMap[$key]->attribute('content');
                        $content->setAttribute( 'alternative_text', trim( $http->postVariable( $base ) ) );
                        $content->store( $newObjectDataMap[$key] );
                        break;
                    case 'ezkeyword':
                        $newObjectDataMap[$key]->fromString( $http->postVariable( $base ) );
                        $newObjectDataMap[$key]->store();
                        break;
                    case 'ezxmltext':
                        $text = trim( $http->postVariable( $base ) );
                        $parser = new eZOEInputParser();
                        $document = $parser->process( $text );
                        $xmlString = eZXMLTextType::domString( $document );
                        $newObjectDataMap[$key]->setAttribute( 'data_text', $xmlString );
                        $newObjectDataMap[$key]->store();
                        break;
                }
            }
        }

        $object->addContentObjectRelation( $newObjectID, $objectVersion, 0, eZContentObject::RELATION_EMBED );
        echo '<html><head><title>HiddenUploadFrame</title><script type="text/javascript">';
        echo 'window.parent.eZOEPopupUtils.selectByEmbedId( ' . $newObjectID . ', ' . $newObjectNodeID . ', "' . $newObjectName . '" );';
        echo '</script></head><body></body></html>';
    }
    else
    {
        echo '<html><head><title>HiddenUploadFrame</title><script type="text/javascript">';
        echo 'window.parent.document.getElementById("upload_in_progress").style.display = "none";';
        echo '</script></head><body><div style="position:absolute; top: 0px; left: 0px;background-color: white; width: 100%;">';
        foreach( $result['errors'] as $err )
            echo '<p style="margin: 0; padding: 3px; color: red">' . $err['description'] . '</p>';
        echo '</div></body></html>';
    }
    eZDB::checkTransactionCounter();
    eZExecution::cleanExit();
}


$siteIni       = eZINI::instance( 'site.ini' );
$contentIni    = eZINI::instance( 'content.ini' );

$groups             = $contentIni->variable( 'RelationGroupSettings', 'Groups' );
$defaultGroup       = $contentIni->variable( 'RelationGroupSettings', 'DefaultGroup' );
$imageDatatypeArray = $siteIni->variable( 'ImageDataTypeSettings', 'AvailableImageDataTypes' );

$classGroupMap         = array();
$groupClassLists       = array();
$groupedRelatedObjects = array();
$relatedObjects        = $object->relatedContentObjectArray( $objectVersion );

foreach ( $groups as $groupName )
{
    $groupedRelatedObjects[$groupName] = array();
    $setting                     = ucfirst( $groupName ) . 'ClassList';
    $groupClassLists[$groupName] = $contentIni->variable( 'RelationGroupSettings', $setting );
    foreach ( $groupClassLists[$groupName] as $classIdentifier )
    {
        $classGroupMap[$classIdentifier] = $groupName;
    }
}

$groupedRelatedObjects[$defaultGroup] = array();

foreach ( $relatedObjects as $relatedObjectKey => $relatedObject )
{
    $srcString        = '';
    $imageAttribute   = false;
    $relID            = $relatedObject->attribute( 'id' );
    $classIdentifier  = $relatedObject->attribute( 'class_identifier' );
    $groupName        = isset( $classGroupMap[$classIdentifier] ) ? $classGroupMap[$classIdentifier] : $defaultGroup;

    if ( $groupName === 'images' )
    {
        $objectAttributes = $relatedObject->contentObjectAttributes();
        foreach ( $objectAttributes as $objectAttribute )
        {
            $classAttribute = $objectAttribute->contentClassAttribute();
            $dataTypeString = $classAttribute->attribute( 'data_type_string' );
            if ( in_array ( $dataTypeString, $imageDatatypeArray ) && $objectAttribute->hasContent() )
            {
                $content = $objectAttribute->content();
                if ( $content == null )
                    continue;

                if ( $content->hasAttribute( 'small' ) )
                {
                    $srcString = $content->imageAlias( 'small' );
                    $imageAttribute = $classAttribute->attribute('identifier');
                    break;
                }
                else
                {
                    eZDebug::writeError( "Image alias does not exist: small, missing from image.ini?",
                        __METHOD__ );
                }
            }
        }
    }
    $item = array( 'object' => $relatedObjects[$relatedObjectKey],
                   'id' => 'eZObject_' . $relID,
                   'image_alias' => $srcString,
                   'image_attribute' => $imageAttribute,
                   'selected' => false );
    $groupedRelatedObjects[$groupName][] = $item;
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'object', $object );
$tpl->setVariable( 'object_id', $objectID );
$tpl->setVariable( 'object_version', $objectVersion );
$tpl->setVariable( 'related_contentobjects', $relatedObjects );
$tpl->setVariable( 'grouped_related_contentobjects', $groupedRelatedObjects );
$tpl->setVariable( 'content_type', $contentType );
$tpl->setVariable( 'access', $result );

$contentTypeCase = ucfirst( $contentType );
if ( $contentIni->hasVariable( 'RelationGroupSettings', $contentTypeCase . 'ClassList' ) )
    $tpl->setVariable( 'class_filter_array', $contentIni->variable( 'RelationGroupSettings', $contentTypeCase . 'ClassList' ) );
else
    $tpl->setVariable( 'class_filter_array', array() );

$tpl->setVariable( 'content_type_name', rtrim( $contentTypeCase, 's' ) );

$tpl->setVariable( 'persistent_variable', array() );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:questionnaire/upload/upload_' . $contentType . '.tpl' );
$Result['pagelayout'] = 'design:questionnaire/upload/popup_pagelayout.tpl';
$Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );

return $Result;