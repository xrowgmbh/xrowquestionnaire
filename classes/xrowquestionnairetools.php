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
        if ( !function_exists( 'recaptcha_check_answer') )
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
}