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
                'user' => 'user' ,
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
    public static function user()
    {
        return eZUser::fetch( $this->attribute( 'user_id' ) );
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
    static function fetchScore( eZContentObjectAttribute $attribute, eZUser $user )
    {
        $users = eZPersistentObject::fetchObjectList( self::definition(), null, array(
        'attribute_id' => $attribute->attribute( 'id' ), 'user_id' => $user->attribute( 'contentobject_id' )
        ), null, null, false, array( 'user_id' ), array( "sum( score ) as total" ) );
        $total = isset( $users[0] ) ? $users[0]['total'] : 0;
        return $total;
    }
    static function fetchParticipants( eZContentObjectAttribute $attribute )
    {

        $users = eZPersistentObject::fetchObjectList( self::definition(), null, array(
        'attribute_id' => $attribute->attribute( 'id' )
        ), null, null, false, array( 'user_id' ), array( "sum( score ) as total" ) );

        if ( !$users )
        {
        	return array();
        }
        return $users;
    }
    static function downloadParticipants( eZContentObjectAttribute $attribute )
    {
        $list = self::fetchParticipants( $attribute );
    
        $tmpfname = tempnam( eZSys::cacheDirectory(), "csv_" );
        $head = array("contentobject_id", "name", "email", "score", "url", "firstname", "lastname", "address" );
        $fp = fopen( $tmpfname, 'w' );
        fprintf( $fp, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
    
        fputcsv( $fp, $head, ";" );
    
        foreach ( $list as $rows )
        {
            $user = eZUser::fetch( $rows["user_id"] );
            $user_dm=$user->attribute('contentobject')->dataMap();
            #var_dump($user_dm);
            #die("sdjfh");
            $firstname="N/A";
            $lastname="N/A";
            $address="";
            if( array_key_exists( 'last_name', $user_dm ) )
            {
                $lastname=$user_dm["last_name"]->attribute('data_text');
            }
    
            if( array_key_exists( 'first_name', $user_dm ) )
            {
                $firstname=$user_dm["first_name"]->attribute('data_text');
            }
    
            // street
            // housenumber
            // postcode
            // city
            if( array_key_exists( 'street', $user_dm ) )
            {
                $address=$user_dm["street"]->attribute('data_text');
            }
            if( array_key_exists( 'housenumber', $user_dm ) )
            {
                $address.= " " . $user_dm["housenumber"]->attribute('data_text');
            }
            if( array_key_exists( 'postcode', $user_dm ) )
            {
                $address.= ", " . $user_dm["postcode"]->attribute('data_text');
            }
            if( array_key_exists( 'city', $user_dm ) )
            {
                $address.= " " . $user_dm["city"]->attribute('data_text');
            }
            
            $row = array( $user->attribute( "contentobject_id" ), $user->attribute('contentobject')->attribute('name'),$user->attribute('email'), $rows['total'], "http://" . eZSys::hostname() . "/content/view/full/" . $user->attribute('contentobject')->attribute('main_node_id'), $firstname, $lastname, $address );
            fputcsv( $fp, $row, ";" );
        }
        
        fclose( $fp );
        ob_end_clean();
        eZSession::stop();
        eZFile::downloadHeaders( $tmpfname, true, "Participants.csv" );
        eZFile::downloadContent( $tmpfname );
        unlink( $tmpfname );
        eZExecution::cleanExit();
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