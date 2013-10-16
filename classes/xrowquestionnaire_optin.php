<?php

class xrowQuestionnaireOptin extends eZPersistentObject
{
    const SITEDATA_LASTRUNTIME = 'questionaire_notify_last_run';

    function __construct( $row )
    {
        $this->eZPersistentObject( $row );
    }

    static function definition()
    {
        return array( 
            "fields" => array( 
                'user_id' => array( 
                    'name' => 'user_id' , 
                    'datatype' => 'integer' , 
                    'default' => eZUser::currentUserID() , 
                    'required' => true 
                ) , 
                'optin' => array( 
                    'name' => 'optin' , 
                    'datatype' => 'integer' , 
                    'default' => time() , 
                    'required' => true 
                ) , 
                'optout' => array( 
                    'name' => 'optout' , 
                    'datatype' => 'integer' , 
                    'default' => null , 
                    'required' => false 
                ) , 
                'random' => array( 
                    'name' => 'random' , 
                    'datatype' => 'string' , 
                    'default' => eZRemoteIdUtility::generate( 'optin' ) , 
                    'required' => true 
                ) 
            ) , 
            'keys' => array( 
                'user_id' 
            ) , 
            'function_attributes' => array( 
                'user' => 'user' 
            ) , 
            'class_name' => 'xrowQuestionnaireOptin' , 
            'name' => 'ezx_xrowquestionnaire_optin' 
        );
    }

    /**
     * Creates new xrowQuestionnaireResult
     *
     * @param array $row            
     * @return xrowQuestionnaireResult
     */
    public static function optinUser( eZUser $user )
    {
        if ( $user->isAnonymous() )
        {
            return false;
        }
        
        $row = array( 
            'user_id' => (int) $user->attribute( 'contentobject_id' ) , 
            'optout' => null 
        );
        $object = new self( $row );
        $object->store();
        return $object;
    }

    public static function optoutUser( eZUser $user )
    {
        if ( $user->isAnonymous() )
        {
            return false;
        }
        
        $row = array( 
            'user_id' => (int) $user->attribute( 'contentobject_id' ) 
        );
        $list = eZPersistentObject::fetchObjectList( self::definition(), null, $row, null, null, true );
        foreach ( $list as $item )
        {
            $item->setAttribute( 'optout', time() );
            $item->store();
        }
    }

    public function optout()
    {
        $this->setAttribute( 'optout', time() );
        $this->store();
    }

    public static function fetchByHash( $hash )
    {
        if ( ! $hash )
        {
            return false;
        }
        $row = array( 
            'random' => $hash 
        );
        return eZPersistentObject::fetchObjectList( self::definition(), null, $row, null, null, true );
    }

    public function user()
    {
        return eZUser::fetch( $this->user_id );
    }

    public static function fetchUserList()
    {
        $db = eZDB::instance();
        $newlist = array();
        $list = $db->arrayQuery( "SELECT user_id, optin, optout, random
        FROM   ezx_xrowquestionnaire_optin o, ezuser u WHERE o.optout is null and u.contentobject_id = o.user_id" );
        foreach ( $list as $row )
        {
            $newlist[] = new xrowQuestionnaireOptin( $row );
        }
        return $newlist;
    }

    static function cleanup()
    {
        $db = eZDB::instance();
        $db->query( "DELETE FROM ezx_xrowquestionnaire_optin" );
    }
}