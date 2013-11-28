<?php

class xrowQuestionnaireType extends eZDataType
{
    const DATA_TYPE_STRING = "xrowquestionnaire";
    
    /*
     * ! Construction of the class, note that the second parameter in eZDataType is the actual name showed in the datatype dropdown list.
     */
    function __construct()
    {
        parent::__construct( self::DATA_TYPE_STRING, ezpI18n::tr( 'xrowquestionnaire/datatype', 'Questionnaire', 'Datatype name' ), array( 
            'serialize_supported' => false 
        ) );
    }

    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $contentObjectID = $contentObjectAttribute->attribute( 'contentobject_id' );
            $originalContentObjectID = $originalContentObjectAttribute->attribute( 'contentobject_id' );
            
            if ( $contentObjectID != $originalContentObjectID )
            {
                $data = $originalContentObjectAttribute->content();
                
                foreach ( $data['questions'] as $index => $question )
                {
                    $newQuestionID = floor( rand() * 2000 );
                    $question['id'] = $newQuestionID;
                    
                    foreach ( $question['answers'] as $key => $answer )
                    {
                        $answer['parentID'] = $newQuestionID;
                        $answer['id'] = floor( rand() * 2000 );
                        
                        $question['answers'][$key] = $answer;
                    }
                    $data['questions'][$index] = $question;
                }
                $contentObjectAttribute->setContent( $data );
                $contentObjectAttribute->store();
            }
            else
            {
                $data = $originalContentObjectAttribute->content();
                $contentObjectAttribute->setContent( $data );
                $contentObjectAttribute->store();
            }
        }
    }
    
    /*
     * ! Validates the input for this datatype. \return True if input is valid.
     */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $data = $http->attribute( 0 );
        if ( isset( $data['PublishButton'] ) )
        {
            $count = 0;
            $questions = $data[$base . '_' . xrowQuestionnaireType::DATA_TYPE_STRING][$contentObjectAttribute->attribute( 'id' )]['questions'];
            
            foreach ( $questions as $question )
            {
                $textCount = 0;
                $count = count( $question['answers'] );
                
                if ( $count < 2 )
                {
                    $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'There has to be a minimum of two answers per Question!' ) );
                    return eZInputValidator::STATE_INVALID;
                }
                for ( $i = 1; $i <= $count; $i ++ )
                {
                    $answer = trim( $question['answers'][$i]['text'] );
                    if ( $answer == '' )
                    {
                        $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Each Answer needs a Text!' ) );
                        return eZInputValidator::STATE_INVALID;
                    }
                }
            }
            return eZInputValidator::STATE_ACCEPTED;
        }
        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * store the contentobjectattribute into database
     * 
     * @see kernel/classes/eZDataType#storeObjectAttribute($objectAttribute)
     */
    function storeObjectAttribute( $contentObjectAttribute )
    {
        $content = $contentObjectAttribute->content();
        $xml = ArrayToXML::toXML( $content );
        $contentObjectAttribute->setAttribute( 'data_text', $xml );
        if( isset( $content['settings']['date_start'] ) )
        {
            $contentObjectAttribute->setAttribute( 'data_int', $content['settings']['date_start'] );
            $contentObjectAttribute->setAttribute( 'sort_key_int', $content['settings']['date_start'] );
        }
        
        return true;
    }

    function objectAttributeContent( $objectAttribute )
    {
        if ( ! isset( $objectAttribute->Content ) )
        {
            try
            {
                $data = ArrayToXML::toArray( $objectAttribute->attribute( 'data_text' ) );
                $objectAttribute->setContent( $data );
                return $objectAttribute->Content;
            }
            catch ( Exception $e )
            {
                eZDebug::writeError( $e->getMessage(), __METHOD__ );
                return null;
            }
        }
        else
        {
            return $objectAttribute->Content;
        }
    }

    function customObjectAttributeHTTPAction( $http, $action, $contentObjectAttribute, $parameters )
    {
        switch ( $action )
        {
            case "winner":
                $content = $contentObjectAttribute->attribute( 'content' );
                $userid = xrowQuestionnaireResult::selectWinner( $contentObjectAttribute );
                if ( !isset( $content['persistent']['winner'] ) )
                {
                    $content['persistent']['winner'] = array();
                }
                if ( $userid )
                {
                    $content['persistent']['winner'][] = $userid;
                    $content['persistent']['winner'] = array_unique( $content['persistent']['winner'] );
                    $contentObjectAttribute->setContent( $content );
                    
                }
                break;
            case "close":
                $content = $contentObjectAttribute->attribute( 'content' );
                $content['persistent']['closed'] = true;
                $contentObjectAttribute->setContent( $content );
                break;
            case "open":
                $content = $contentObjectAttribute->attribute( 'content' );
                if ( isset( $content['persistent']['closed'] ) )
                {
                    unset( $content['persistent']['closed'] );
                }         
                $contentObjectAttribute->setContent( $content );
                break;
            case "reset":
                $content = $contentObjectAttribute->attribute( 'content' );
                if ( $content['persistent']['winner'] )
                {
                    unset( $content['persistent']['winner'] );
                }
                $contentObjectAttribute->setContent( $content );
                xrowQuestionnaireResult::cleanupByAttributeID( $contentObjectAttribute->attribute( 'id' ) );
                break;
            case "download":
                xrowQuestionnaireResult::downloadParticipants( $contentObjectAttribute );
                
                break;
            default:
                ;
                break;
        }
    }

    static function isQuiz( $questions )
    {
        foreach ( $questions as $question )
        {
            foreach ( $question['answers'] as $answer )
            {
                if ( isset( $answer['correct'] ) && $answer['correct'] == 'on' )
                {
                    return true;
                }
            }
        }
        return false;
    }

    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $data = $http->attribute( 0 );
        
        $data = $data[$base . '_xrowquestionnaire'];
        $id = key( $data );
        $data = $data[$id];
        $dataold = $contentObjectAttribute->content();
        
        if ( self::isQuiz( $data['questions'] ) )
        {
            
            $data['settings']['quiz'] = true;
        }
        elseif ( isset( $data['settings']['quiz'] ) )
        {
            unset( $data['settings']['quiz'] );
        }
        if ( isset( $data['settings']['date_start'] ) and is_numeric( $data['settings']['date_start'] ) )
        {
           $data['settings']['date_start'] = $data['settings']['date_start'] / 1000;
        }
        else 
        {
            unset( $data['settings']['date_start'] );
        }
        if ( isset( $dataold['persistent'] ) )
        {
            $data['persistent'] = $dataold['persistent'];
        }
        if ( is_array( $data['settings']['user_attributes'] ) and count( $data['settings']['user_attributes'] ) )
        {
            $data['settings']['user_loggedin'] = true;
        }
        $contentObjectAttribute->setContent( $data );
        
        return true;
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        $content = $contentObjectAttribute->content();
        if ( ! empty( $content ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    function isInformationCollector()
    {
        return false;
    }
    function isIndexable()
    {
        return false;
    }

    function sortKeyType()
    {
        return 'int';
    }
    function sortKey( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_int' );
    }
}

eZDataType::register( xrowQuestionnaireType::DATA_TYPE_STRING, 'xrowQuestionnaireType' );
