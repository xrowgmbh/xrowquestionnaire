<?php

class eZEMailType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "ezemail";

    function eZEMailType()
    {
        $this->eZWorkflowEventType( eZEMailType::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'kernel/workflow/event', "Email" ) );
        $this->setTriggerTypes( array( 'questionnaire' => array( 'completed' => array( 'after' ) ) ) );
    }

    function attributeDecoder( $event, $attr )
    {
        switch ( $attr )
        {

            case 'approve_users':
            {
                $attributeValue = trim( $event->attribute( 'data_text3' ) );
                $returnValue = empty( $attributeValue ) ? array() : explode( ',', $attributeValue );
            }break;

            case 'approve_groups':
            {
                $attributeValue = trim( $event->attribute( 'data_text4' ) );
                $returnValue = empty( $attributeValue ) ? array() : explode( ',', $attributeValue );
            }break;

            case 'selected_usergroups':
            {
                $attributeValue = trim( $event->attribute( 'data_text2' ) );
                $returnValue = empty( $attributeValue ) ? array() : explode( ',', $attributeValue );
            }break;

            default:
                $returnValue = null;
        }
        return $returnValue;
    }

    function typeFunctionalAttributes( )
    {
        return array( 'approve_users',
                      'approve_groups',
                      'selected_usergroups' );
    }

    function attributes()
    {
        return array_merge( array( 'users',
                                   'usergroups' ),
                            eZWorkflowEventType::attributes() );

    }

    function hasAttribute( $attr )
    {
        return in_array( $attr, $this->attributes() );
    }

    function execute( $process, $event )
    {
        eZDebugSetting::writeDebug( 'kernel-workflow-email', $process, 'eZEMailType::execute' );
        eZDebugSetting::writeDebug( 'kernel-workflow-email', $event, 'eZEMailType::execute' );
        $parameters = $process->attribute( 'parameter_list' );
        $userID = $parameters['user_id'];
        $user = eZUser::fetch($userID);
        if ( !in_array( $parameters['object_id'], $event->attribute("approve_groups") ) )
        {
            return eZWorkflowType::STATUS_ACCEPTED;
        }

        if ( !$user )
        {
            eZDebugSetting::writeError( 'kernel-workflow-email', "No object with ID $userID", __METHOD__ );
            return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
        }
        $objectID = $parameters['object_id'];
        $object = eZContentObject::fetch( $objectID );
        if ( !$object )
        {
            eZDebugSetting::writeError( 'kernel-workflow-email', "No object with ID $objectID", __METHOD__ );
            return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
        }
        eZDebugSetting::writeDebug( 'kernel-workflow-email', $user->id(), "we are not going to create approval "  );
        $hostName = eZSys::hostname();
        $subject = ezpI18n::tr( 'kernel/content', '%1: Questionaire completed', null, array( $object->attribute( 'name' ) ) );
        
        $mail = new eZMail();
        $ini = eZINI::instance();
        // Sender might not be given by default settings
        if ( $ini->variable( 'MailSettings', 'EmailSender' ) )
            $mail->setSender( $ini->variable( 'MailSettings', 'EmailSender' ) );
        else
            $mail->setSender( $ini->variable( 'MailSettings', 'AdminEmail' ) );

        $mail->setReceiver( $user->attribute( 'email' ), $user->attribute( 'contentobject' )->attribute( 'name' ) );
        $mail->setSubject( $subject );
        foreach( $event->attribute("approve_users") as $approverID )
        {
            $approver = eZUser::fetch($approverID);
            $mail->addBcc( $approver->attribute( 'email' ), $approver->attribute( 'contentobject' )->attribute( 'name' ) );
        }
        
        // fetch
        $sectionID = $object->attribute( 'section_id' );
        $section = eZSection::fetch( $sectionID );
        $res = eZTemplateDesignResource::instance();
        $res->setKeys( array( array( 'object',           $object->attribute( 'id' ) ),
        array( 'remote_id',        $object->attribute( 'remote_id' ) ),
        array( 'class',            $object->attribute( 'contentclass_id' ) ),
        array( 'class_identifier', $object->attribute( 'class_identifier' ) ),
        array( 'class_group',      $object->attribute( 'match_ingroup_id_list' ) ),
        array( 'section',          $object->attribute( 'section_id' ) ),
        array( 'section_identifier', $section->attribute( 'identifier' ) )
        ) );
        $overrideKeysAreSet = true;
        
        // fetch text from mail template
        $mailtpl = eZTemplate::factory();
        $mailtpl->setVariable( 'hostname', $hostName );
        $mailtpl->setVariable( 'object', $object );
        $mailtpl->setVariable( 'user', $user );

        $mailtext = $mailtpl->fetch( 'design:questionnaire/workflow/mail.tpl' );
        
        if ( $mailtpl->hasVariable( 'content_type' ) )
            $mail->setContentType( $mailtpl->variable( 'content_type' ) );
        
        $mail->setBody( $mailtext );
        
        // mail was sent ok
        if ( !eZMailTransport::send( $mail ) )
        {
            eZDebugSetting::writeError( 'kernel-workflow-email', "Mail was not send for object with ID $objectID", __METHOD__ );
        }
        
        return eZWorkflowType::STATUS_ACCEPTED;
    }

    function initializeEvent( $event )
    {
    }

    function validateUserIDList( $userIDList, &$reason )
    {
        $returnState = eZInputValidator::STATE_ACCEPTED;
        foreach ( $userIDList as $userID )
        {
            if ( !is_numeric( $userID ) or
                 !eZUser::isUserObject( eZContentObject::fetch( $userID ) ) )
            {
                $returnState = eZInputValidator::STATE_INVALID;
                $reason[ 'list' ][] = $userID;
            }
        }
        $reason[ 'text' ] = "Some of passed user IDs are not valid, must be IDs of existing users only.";
        return $returnState;
    }

    function validateHTTPInput( $http, $base, $workflowEvent, &$validation )
    {
        $returnState = eZInputValidator::STATE_ACCEPTED;
        $reason = array();

        if ( !$http->hasSessionVariable( 'BrowseParameters' ) )
        {
            // check approve-users
            $approversIDs = array_unique( $this->attributeDecoder( $workflowEvent, 'approve_users' ) );
            if ( is_array( $approversIDs ) and
                 count( $approversIDs ) > 0 )
            {
                $returnState = eZEMailType::validateUserIDList( $approversIDs, $reason );
            }
            else
                $returnState = false;

            if ( $returnState != eZInputValidator::STATE_INVALID )
            {
                // check approve-groups
                $userGroupIDList = array_unique( $this->attributeDecoder( $workflowEvent, 'approve_groups' ) );
                if ( !is_array( $userGroupIDList ) or
                     count( $userGroupIDList ) < 1 )
                {
                    // if no one user or user-group was passed as approvers
                    $returnState = eZInputValidator::STATE_INVALID;
                    $reason[ 'text' ] = "There must be passed at least one valid user or user group who approves content for the event.";
                }
            }
        }
        else
        {
            $browseParameters = $http->sessionVariable( 'BrowseParameters' );
            if ( isset( $browseParameters['custom_action_data'] ) )
            {
                $customData = $browseParameters['custom_action_data'];
                if ( isset( $customData['event_id'] ) and
                     $customData['event_id'] == $workflowEvent->attribute( 'id' ) )
                {
                    if ( !$http->hasPostVariable( 'BrowseCancelButton' ) and
                         $http->hasPostVariable( 'SelectedObjectIDArray' ) )
                    {
                        $objectIDArray = $http->postVariable( 'SelectedObjectIDArray' );
                        if ( is_array( $objectIDArray ) and
                             count( $objectIDArray ) > 0 )
                        {
                            switch( $customData['browse_action'] )
                            {
                            case "AddApproveUsers":
                                {
                                    $returnState = eZEMailType::validateUserIDList( $objectIDArray, $reason );
                                } break;
                            case 'AddObjects':
                                {
                                    $returnState = eZInputValidator::STATE_ACCEPTED;
                                } break;
                            }
                        }
                    }
                }
            }
        }

        if ( $returnState == eZInputValidator::STATE_INVALID )
        {
            $validation[ 'processed' ] = true;
            $validation[ 'events' ][] = array( 'id' => $workflowEvent->attribute( 'id' ),
                                               'placement' => $workflowEvent->attribute( 'placement' ),
                                               'workflow_type' => &$this,
                                               'reason' => $reason );
        }
        return $returnState;
    }


    function fetchHTTPInput( $http, $base, $event )
    {
        if ( $http->hasSessionVariable( 'BrowseParameters' ) )
        {
            $browseParameters = $http->sessionVariable( 'BrowseParameters' );
            if ( isset( $browseParameters['custom_action_data'] ) )
            {
                $customData = $browseParameters['custom_action_data'];
                if ( isset( $customData['event_id'] ) &&
                     $customData['event_id'] == $event->attribute( 'id' ) )
                {
                    if ( !$http->hasPostVariable( 'BrowseCancelButton' ) and
                         $http->hasPostVariable( 'SelectedObjectIDArray' ) )
                    {
                        $objectIDArray = $http->postVariable( 'SelectedObjectIDArray' );
                        if ( is_array( $objectIDArray ) and
                             count( $objectIDArray ) > 0 )
                        {

                            switch( $customData['browse_action'] )
                            {
                            case 'AddApproveUsers':
                                {
                                    foreach( $objectIDArray as $key => $userID )
                                    {
                                        if ( !eZUser::isUserObject( eZContentObject::fetch( $userID ) ) )
                                        {
                                            unset( $objectIDArray[$key] );
                                        }
                                    }
                                    $event->setAttribute( 'data_text3', implode( ',',
                                                                                 array_unique( array_merge( $this->attributeDecoder( $event, 'approve_users' ),
                                                                                                            $objectIDArray ) ) ) );
                                } break;

                            case 'AddObjects':
                                {
                                    $event->setAttribute( 'data_text4', implode( ',',
                                                                                 array_unique( array_merge( $this->attributeDecoder( $event, 'approve_groups' ),
                                                                                                            $objectIDArray ) ) ) );
                                } break;
                            }
                        }
                        $http->removeSessionVariable( 'BrowseParameters' );
                    }
                }
            }
        }
    }

    function customWorkflowEventHTTPAction( $http, $action, $workflowEvent )
    {
        $eventID = $workflowEvent->attribute( "id" );
        $module =& $GLOBALS['eZRequestedModule'];
        switch ( $action )
        {
            case 'AddApproveUsers' :
            {
                $userClassNames = eZUser::fetchUserClassNames();
                if ( count( $userClassNames ) > 0 )
                {
                    eZContentBrowse::browse( array( 'action_name' => 'SelectMultipleUsers',
                                                    'from_page' => '/workflow/edit/' . $workflowEvent->attribute( 'workflow_id' ),
                                                    'custom_action_data' => array( 'event_id' => $eventID,
                                                                                   'browse_action' => $action ),
                                                    'class_array' => $userClassNames ),
                                             $module );
                }
            } break;

            case 'RemoveApproveUsers' :
            {
                if ( $http->hasPostVariable( 'DeleteApproveUserIDArray_' . $eventID ) )
                {
                    $workflowEvent->setAttribute( 'data_text3', implode( ',', array_diff( $this->attributeDecoder( $workflowEvent, 'approve_users' ),
                                                                                          $http->postVariable( 'DeleteApproveUserIDArray_' . $eventID ) ) ) );
                }
            } break;

            case 'AddObjects' :
            {

                    eZContentBrowse::browse( array( 'action_name' => 'SelectMultipleObjects',
                                                    'from_page' => '/workflow/edit/' . $workflowEvent->attribute( 'workflow_id' ),
                                                    'custom_action_data' => array( 'event_id' => $eventID,
                                                                                   'browse_action' => $action ),
                                                     ),
                                             $module );
                
            } break;

            case 'RemoveObjects' :
            {
                if ( $http->hasPostVariable( 'DeleteApproveGroupIDArray_' . $eventID ) )
                {
                    $workflowEvent->setAttribute( 'data_text4', implode( ',', array_diff( $this->attributeDecoder( $workflowEvent, 'approve_groups' ),
                                                                                          $http->postVariable( 'DeleteApproveGroupIDArray_' . $eventID ) ) ) );
                }
            } break;

        }
    }

    function cleanupAfterRemoving( $attr = array() )
    {
        foreach ( array_keys( $attr ) as $attrKey )
        {
          switch ( $attrKey )
          {
              case 'DeleteContentObject':
              {
                     $contentObjectID = (int)$attr[ $attrKey ];
                     $db = eZDB::instance();
                     // Cleanup "User who approves content"
                     $db->query( "UPDATE ezworkflow_event
                                  SET    data_int1 = '0'
                                  WHERE  workflow_type_string = '{$this->TypeString}' AND
                                         data_int1 = $contentObjectID" );
              } break;
          }
        }
    }
}
if ( eZPublishSDK::VERSION_MAJOR == 4 )
{
    eZWorkflowEventType::registerEventType( eZEMailType::WORKFLOW_TYPE_STRING, 'eZEMailType' );
}