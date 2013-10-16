<?php

class xrowQuestionnaireFunctions
{

    static function scoreSet( eZContentObjectAttribute $attribute )
    {
        $return = array();
        $content = $attribute->content();
        foreach ( $content['questions'] as $question )
        {
            foreach ( $question['answers'] as $answer )
            {
                $return[$question['id']][$answer['id']] = $answer['points'];
            }
        }
        return $return;
    }

    static function storeResult( eZContentObjectAttribute $attribute, $data )
    {
        $db = eZDB::instance();
        $db->begin();
        xrowQuestionnaireResult::removeAnswers( $attribute->ID, $data['question_id'] );
        $set = self::scoreSet( $attribute );
        
        $http = eZHTTPTool::instance();
        $sessionID = $http->sessionID();
        
        if ( empty( $sessionID ) )
        {
            eZSession::start();
            $sessionID = $http->sessionID();
        }
        
        if ( is_array( $data['answer_id'] ) )
        {
            foreach ( $data['answer_id'] as $key => $id )
            {
                $score = $set[$data['question_id']][$id];
                xrowQuestionnaireResult::create( $attribute->ContentObjectID, $attribute->ID, $data['question_id'], $id, null, null, $sessionID, $score );
            }
        }
        else
        {
            $score = $set[$data['question_id']][$data['answer_id']];
            xrowQuestionnaireResult::create( $data['contentobject_id'], $data['attribute_id'], $data['question_id'], $data['answer_id'], null, null, $sessionID, $score );
        }
        $db->commit();
        return true;
    }

    static function validateCaptcha( $recaptcha_response_field, $recaptcha_challenge_field )
    {
        if ( ! function_exists( 'recaptcha_check_answer' ) )
        {
            require_once 'extension/xrowquestionnaire/classes/recaptchalib.php';
        }
        
        $ini = eZINI::instance( 'xrowquestionnaire.ini' );
        $privateKey = $ini->variable( 'RecaptchaSetting', 'PrivateKey' );
        if ( ! empty( $recaptcha_challenge_field ) && ! empty( $recaptcha_response_field ) )
        {
            $ip = $_SERVER["REMOTE_ADDR"];
            $capchaResponse = recaptcha_check_answer( $privateKey, $ip, $recaptcha_challenge_field, $recaptcha_response_field );
            
            if ( ! $capchaResponse->is_valid )
            {
                return false;
            }
        }
        else
        {
            return false;
        }
        return true;
    }

    static function sendMail( eZUser $user, $template = 'design:questionnaire/email.tpl', $params = array(), eZContentObjectTreeNode $node = null )
    {
        if ( $node )
        
        {
            $object = $node->object();
            
            // fetch
            $sectionID = $object->attribute( 'section_id' );
            $section = eZSection::fetch( $sectionID );
            $res = eZTemplateDesignResource::instance();
            $res->setKeys( array( 
                array( 
                    'object' , 
                    $object->attribute( 'id' ) 
                ) , 
                array( 
                    'remote_id' , 
                    $object->attribute( 'remote_id' ) 
                ) , 
                array( 
                    'node_remote_id' , 
                    $node->attribute( 'remote_id' ) 
                ) , 
                array( 
                    'class' , 
                    $object->attribute( 'contentclass_id' ) 
                ) , 
                array( 
                    'class_identifier' , 
                    $object->attribute( 'class_identifier' ) 
                ) , 
                array( 
                    'class_group' , 
                    $object->attribute( 'match_ingroup_id_list' ) 
                ) , 
                array( 
                    'section' , 
                    $object->attribute( 'section_id' ) 
                ) , 
                array( 
                    'section_identifier' , 
                    $section->attribute( 'identifier' ) 
                ) , 
                array( 
                    'node' , 
                    $node->attribute( 'node_id' ) 
                ) , 
                array( 
                    'parent_node' , 
                    $node->attribute( 'parent_node_id' ) 
                ) , 
                array( 
                    'depth' , 
                    $node->attribute( 'depth' ) 
                ) , 
                array( 
                    'url_alias' , 
                    $node->attribute( 'url_alias' ) 
                ) 
            ) );
            $overrideKeysAreSet = true;
        }
        // fetch text from mail template
        $mailtpl = eZTemplate::factory();
        foreach ( $params as $key => $param )
        {
            $mailtpl->setVariable( $key, $param );
        }
        
        $mailtext = $mailtpl->fetch( $template );
        
        $mail = new eZMail();
        $ini = eZINI::instance();
        // Sender might not be given by default settings
        if ( $ini->variable( 'MailSettings', 'EmailSender' ) )
            $mail->setSender( $ini->variable( 'MailSettings', 'EmailSender' ) );
        else
            $mail->setSender( $ini->variable( 'MailSettings', 'AdminEmail' ) );
        
        $mail->setReceiver( $user->attribute( 'email' ), $user->attribute( 'contentobject' )->name() );
        
        if ( $mailtpl->hasVariable( 'subject' ) )
        {
            $mail->setSubject( $mailtpl->variable( 'subject' ) );
        }
        
        if ( $mailtpl->hasVariable( 'content_type' ) )
            $mail->setContentType( $mailtpl->variable( 'content_type' ) );
        
        $mail->setBody( $mailtext );
        
        // mail was sent ok
        return eZMailTransport::send( $mail );
    }
}