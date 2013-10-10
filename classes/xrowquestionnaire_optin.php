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
                    'name' => 'id' , 
                    'datatype' => 'integer' , 
                    'default' => eZUser::currentUserID() , 
                    'required' => true 
                ) , 
                'optin' => array( 
                    'name' => 'optin' , 
                    'datatype' => 'string' , 
                    'default' => time() , 
                    'required' => true 
                ) , 
                'optout' => array(
                   'name' => 'optout' ,
                   'datatype' => 'integer' ,
                   'default' => null,
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
    public static function optin( eZUser $user )
    {
        if ( $user->isAnonymous() )
        {
        	return false;
        }
        
        $row = array( 
            'user_id' => (int) $user->attribute( 'contentobject_id' ), 
            'optout' => null,
        );
        $object = new self( $row );
        $object->store();
        return $object;
    }
    public static function optout( eZUser $user )
    {
        if ( $user->isAnonymous() )
        {
            return false;
        }
    
        $row = array(
        'user_id' => (int) $user->attribute( 'contentobject_id' ) ,
        );
        $list = eZPersistentObject::fetchObjectList( self::definition(), null, $row, null, null, true );
        foreach ( $list as $item )
        {
        	$item->setAttribute( 'optout', time() );
        	$item->store();
        }
    }
    static function fetchList( eZContentObjectAttribute $attribute )
    {
        return eZPersistentObject::fetchObjectList( self::definition(), null, array( 
            "attribute_id" => $attribute->attribute( 'id' ) 
        ), null, null, true );
    }

    static function cleanup()
    {
        $db = eZDB::instance();
        $db->query( "DELETE FROM ezx_xrowquestionnaire_optin" );
    }
}