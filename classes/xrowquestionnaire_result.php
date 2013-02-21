<?php

class xrowQuestionnaireResult extends eZPersistentObject
{

    function __construct( $row )
    {
        $this->eZPersistentObject( $row );
    }

    static function definition()
    {
        return array( 
            "fields" => array( 
                'id' => array( 
                    'name' => 'id' , 
                    'datatype' => 'integer' , 
                    'default' => '' , 
                    'required' => true 
                ) , 
                'attribute_id' => array( 
                    'name' => 'attribute_id' , 
                    'datatype' => 'integer' , 
                    'default' => 0 , 
                    'required' => true 
                ) , 
                'contentobject_id' => array( 
                    'name' => 'contentobject_id' , 
                    'datatype' => 'integer' , 
                    'default' => '' , 
                    'required' => true 
                ) , 
                'question_id' => array( 
                    'name' => 'question_id' , 
                    'datatype' => 'string' , 
                    'default' => '' , 
                    'required' => true 
                ) , 
                'answer_id' => array( 
                    'name' => 'answer_id' , 
                    'datatype' => 'string' , 
                    'default' => '' , 
                    'required' => true 
                ) , 
                'score' => array( 
                    'name' => 'score' , 
                    'datatype' => 'integer' , 
                    'default' => '' , 
                    'required' => true 
                ) , 
                'data_text' => array( 
                    'name' => 'data_text' , 
                    'datatype' => 'string' , 
                    'default' => '' , 
                    'required' => false 
                ) , 
                'created' => array( 
                    'name' => 'created' , 
                    'datatype' => 'string' , 
                    'default' => 0 , 
                    'required' => true 
                ) , 
                'user_id' => array( 
                    'name' => 'user_id' , 
                    'datatype' => 'integer' , 
                    'default' => '' , 
                    'required' => true 
                ) , 
                'session' => array( 
                    'name' => 'session' , 
                    'datatype' => 'string' , 
                    'default' => 0 , 
                    'required' => true 
                ) 
            ) , 
            'keys' => array( 
                'id' 
            ) , 
            'function_attributes' => array( 
                'cleanup_by_attribute' => 'cleanupByAttributeID' , 
                'answer_details' => 'getAnswerDetails' 
            ) , 
            'class_name' => 'xrowQuestionnaireResult' , 
            'name' => 'ezx_xrowquestionnaire_results' 
        );
    }

    /**
     * Creates new xrowQuestionnaireResult
     *
     * @param array $row
     * @return xrowQuestionnaireResult
     */
    public static function create( $contentobject_id, $attribute_id, $question_id, $answer_id, $data_text = null, $user_id = null, $session = null, $score = null )
    {
        $db = eZDB::instance();
        
        $row = array( 
            'contentobject_id' => (int) $contentobject_id , 
            'attribute_id' => (int) $attribute_id , 
            'question_id' => (int) $question_id , 
            'answer_id' => (int) $answer_id , 
            'data_text' => (int) $data_text , 
            'user_id' => (int) $user_id , 
            'created' => time() , 
            'session' => (string) $session , 
            'score' => (int) $score 
        );
        
        if ( $user_id === null )
        {
            $user = eZUser::currentUser();
            $row['user_id'] = $user->id();
        }
        $object = new self( $row );
        $object->store();
        return $object;
    }

    static function fetchList( eZContentObjectAttribute $attribute )
    {
        return eZPersistentObject::fetchObjectList( self::definition(), null, array( 
            "attribute_id" => $attribute->attribute( 'id' ) 
        ), null, null, true );
    }

    static function isDuplicate( eZContentObjectAttribute $attribute )
    {
        $db = eZDB::instance();
        if ( ! eZUser::currentUser()->isAnonymous() )
        {
            $data = $attribute->content();
            
            foreach ( $data['questions'] as $question )
            {
                $list = eZPersistentObject::fetchObjectList( self::definition(), null, array( 
                    'question_id' => $question['id'] , 
                    'contentobject_id' => $attribute->ContentObjectID , 
                    'attribute_id' => $attribute->attribute( 'id' ) , 
                    'user_id' => eZUser::currentUserID() 
                ) );
                if ( empty( $list ) )
                {
                    return false;
                }
            }
        }
        return true;
    }

    static function fetchCompletedQuestionIDList( eZContentObjectAttribute $attribute )
    {
        $user = eZUser::currentUser();
        $http = eZHTTPTool::instance();
        $list = eZPersistentObject::fetchObjectList( self::definition(), array( 
            'question_id' 
        ), array( 
            'attribute_id' => $attribute->attribute( 'id' ) , 
            'session' => $http->sessionID() 
        ), null, null, false );
        if ( ! $user->isAnonymous() )
        {
            $userList = eZPersistentObject::fetchObjectList( self::definition(), array( 
                'question_id' 
            ), array( 
                'attribute_id' => $attribute->attribute( 'id' ) , 
                'user_id' => $user->id() 
            ), null, null, false );
            $list = array_merge( $list, $userList );
        }
        $return = array();
        foreach ( $list as $item )
        {
            $return[] = $item['question_id'];
        }
        $return = array_unique( $return );
        return $return;
    }

    static function fetchListByUser( eZContentObjectAttribute $attribute, $question = null )
    {
        $user = eZUser::currentUser();
        $http = eZHTTPTool::instance();
        return eZPersistentObject::fetchObjectList( self::definition(), null, array( 
            'attribute_id' => $attribute->attribute( 'id' ) , 
            'user_id' => $user->id() , 
            'session' => $http->sessionID() , 
            'question_id' => $question['id'] 
        ), null, null, false );
    
    }

    static function fetchListByType( $attribute_id, $itemType, $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( self::definition(), null, array( 
            'attribute_id' => $attribute_id , 
            'type' => $itemType 
        ), null, null, $asObject );
    }

    static function hasData( eZContentObjectAttribute $attribute )
    {
        $db = eZDB::instance();
        $sql = "SELECT * FROM ezx_xrowquestionnaire_results WHERE attribute_id = " . (int) $attribute->attribute('id') . " LIMIT 0,1;";
        $result = $db->arrayQuery( $sql );
        
        if ( isset( $result[0] ) )
        {
            return true;
        }
        return false;
    
    }

    static function canWin( eZContentObjectAttribute $attribute )
    {
        $db = eZDB::instance();
        $sql = "SELECT count( user_id ) as counter, result.* FROM ( ";
        $data = $attribute->content();
        
        $qparts = array();
        
        $keys = array_keys( $data['questions'] );
        for ( $i = 1; $i <= count( $keys ); $i ++ )
        {
            $answers = array();
            foreach ( $data['questions'][$keys[$i - 1]]['answers'] as $answer )
            {
                if ( isset( $answer['correct'] ) and $answer['correct'] == 'on' )
                {
                    $answers[] = "'" . $answer['id'] . "'";
                }
            }
            if ( ! empty( $answers ) )
            {
                $asql = " and t" . $i . ".answer_id in ( " . join( ' , ', $answers ) . " ) ";
            }
            else
            {
                $asql = "";
            }
            $qparts[] = " (SELECT * FROM ezx_xrowquestionnaire_results as t" . $i . " WHERE t" . $i . ".attribute_id = " . (int) $attribute->ID . " and t" . $i . ".question_id = " . $data['questions'][$keys[$i - 1]]['id'] . " " . $asql . " ) ";
        }
        $sql .= join( ' UNION ', $qparts );
        $sql .= ") as result GROUP BY result.user_id HAVING counter = " . count( $data['questions'] ) . " LIMIT 0,1;";
        $result = $db->arrayQuery( $sql );
        
        if ( isset( $result[0] ) )
        {
            return true;
        }
        return false;
    
    }

    static function selectWinner( eZContentObjectAttribute $attribute )
    {
        $db = eZDB::instance();
        $sql = "SELECT count( user_id ) as counter, result.* FROM ( ";
        $data = $attribute->content();
        
        $qparts = array();
        
        $keys = array_keys( $data['questions'] );
        for ( $i = 1; $i <= count( $keys ); $i ++ )
        {
            $answers = array();
            foreach ( $data['questions'][$keys[$i - 1]]['answers'] as $answer )
            {
                if ( isset( $answer['correct'] ) and $answer['correct'] == 'on' )
                {
                    $answers[] = "'" . $answer['id'] . "'";
                }
            }
            if ( ! empty( $answers ) )
            {
                $asql = " and t" . $i . ".answer_id in ( " . join( ' , ', $answers ) . " ) ";
            }
            else
            {
                $asql = "";
            }
            $qparts[] = " (SELECT * FROM ezx_xrowquestionnaire_results as t" . $i . " WHERE t" . $i . ".attribute_id = " . (int) $attribute->ID . " and t" . $i . ".question_id = " . $data['questions'][$keys[$i - 1]]['id'] . " " . $asql . " ) ";
        }
        $sql .= join( ' UNION ', $qparts );
        $sql .= ") as result GROUP BY result.user_id HAVING counter = " . count( $data['questions'] ) . " ORDER BY RAND() LIMIT 0,1;";
        $result = $db->arrayQuery( $sql );
        
        if ( isset( $result[0]['user_id'] ) && eZUser::anonymousId() !== $result[0]['user_id'] )
        {
            return $result[0]['user_id'];
        }
        return false;
    
    }

    static function removeAnswers( $id, $questionID )
    {
        $db = eZDB::instance();
        $http = eZHTTPTool::instance();
        $user = eZUser::currentUser();
        
        if ( ! $user->isAnonymous() )
        {
            $db->query( "DELETE FROM ezx_xrowquestionnaire_results WHERE attribute_id = " . (int) $id . " AND question_id = " . (int) $questionID . ' AND user_id = ' . (int) $user->id() );
        }
        $db->query( "DELETE FROM ezx_xrowquestionnaire_results WHERE attribute_id = " . (int) $id . " AND question_id = " . (int) $questionID . " AND session = '" . $db->escapeString( $http->sessionID() ) . "'" );
    }

    static function cleanupByAttributeID( $id )
    {
        $db = eZDB::instance();
        $db->query( "DELETE FROM ezx_xrowquestionnaire_results WHERE attribute_id = " . (int) $id );
    }

    static function cleanup()
    {
        $db = eZDB::instance();
        $db->query( "DELETE FROM ezx_xrowquestionnaire_results" );
    }
}