<?php

class xrowQuestionnaireOperator
{

    function operatorList()
    {
        return array( 
            'questionnaire_sort' , 
            'questionnaire_has_data' , 
            'questionnaire_score' ,
            'questionnaire_can_win' 
        );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array( 
            'questionnaire_sort' => array( 
                'result' => array( 
                    'type' => 'array' , 
                    'required' => true , 
                    'default' => null 
                ) , 
                'sort_by' => array( 
                    'type' => 'string' , 
                    'required' => false , 
                    'default' => 'score' 
                ) , 
                'sort_order' => array( 
                    'type' => 'boolean' , 
                    'required' => false , 
                    'default' => true 
                ) 
            ) , 
            'questionnaire_has_data' => array( 
                'attribute_id' => array( 
                    'type' => 'int' , 
                    'required' => true, 
                    'default' => null 
                ) 
            ) , 
            'questionnaire_score' => array( 
                                'attribute_id' => array( 
                    'type' => 'int' , 
                    'required' => true , 
                    'default' => null 
                )  , 
                'user_id' => array( 
                    'type' => 'int' , 
                    'required' => true , 
                    'default' => 'nul' 
                )
            ) , 
            'questionnaire_can_win' => array( 
                'attribute_id' => array( 
                    'type' => 'int' , 
                    'required' => true , 
                    'default' => null 
                ) 
            )
        );
    }

    function sort_asc( $struc_a, $struc_b )
    {
        if ( $GLOBALS['questionnaire_sort_key'] )
        {
            $a = (double)$struc_a[$GLOBALS['questionnaire_sort_key']];
            $b = (double)$struc_b[$GLOBALS['questionnaire_sort_key']];
        }
        else
        {
            throw new Exception( 'questionnaire_sort_key is not defined' );
        }
        if ( $a == $b )
        {
            return 0;
        }
        return ( $a < $b ) ? - 1 : + 1;
    }

    function sort_desc( $struc_a, $struc_b )
    {
        if ( $GLOBALS['questionnaire_sort_key'] )
        {
            $a = (double)$struc_a[$GLOBALS['questionnaire_sort_key']];
            $b = (double)$struc_b[$GLOBALS['questionnaire_sort_key']];
        }
        else
        {
            throw new Exception( 'questionnaire_sort_key is not defined' );
        }
        
        if ( $a == $b )
        {
            return 0;
        }
        
        return ( $a > $b ) ? - 1 : + 1;
    }
    function modify( $tpl, $operatorName, $operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        switch ( $operatorName )
        {
            case 'questionnaire_sort':
                {
                    if ( isset( $namedParameters['result'] ) )
                    {
                        $GLOBALS['questionnaire_sort_key'] = $namedParameters['sort_by'];

                        if ( $namedParameters['sort_order'] )
                        {
                            usort( $namedParameters['result'], 'xrowQuestionnaireOperator::sort_desc' );
                        }
                        else
                        {
                            usort( $namedParameters['result'], 'xrowQuestionnaireOperator::sort_asc' );
                        }
                        $operatorValue = $namedParameters['result'];
                    }
                    else
                    {
                        $operatorValue = false;
                    }
                }
                break;
            case 'questionnaire_can_win':
                {
                    if ( isset( $namedParameters['attribute_id'] ) )
                    {
                        
                        $attributes = eZPersistentObject::fetchObjectList( eZContentObjectAttribute::definition(), null, array( 
                            "id" => (int) $namedParameters['attribute_id'] 
                        ) );
                        
                        $object = $attributes[0]->attribute( 'object' );
                        $attribute = eZContentObjectAttribute::fetch( (int) $namedParameters['attribute_id'], $object->attribute( 'current_version' ) );
                        $operatorValue = xrowQuestionnaireResult::canWin( $attribute );
                    }
                    else
                    {
                        $operatorValue = false;
                    }
                }
                break;
            case 'questionnaire_has_data':
                {
                    if ( isset( $namedParameters['attribute_id'] ) )
                    {
                        $attributes = eZPersistentObject::fetchObjectList( eZContentObjectAttribute::definition(), null, array( 
                            "id" => (int) $namedParameters['attribute_id'] 
                        ) );
                        
                        $object = $attributes[0]->attribute( 'object' );
                        $attribute = eZContentObjectAttribute::fetch( (int) $namedParameters['attribute_id'], $object->attribute( 'current_version' ) );
                        $return = xrowQuestionnaireResult::hasData( $attribute );
                        $operatorValue = $return;
                    }
                    else
                    {
                        $operatorValue = false;
                    }

                }break;
                case 'questionnaire_score':
                    {
                        if ( isset( $namedParameters['attribute_id'] ) && isset( $namedParameters['user_id'] ) )
                        {
                            $attributes = eZPersistentObject::fetchObjectList( eZContentObjectAttribute::definition(), null, array(
                            "id" => (int) $namedParameters['attribute_id']
                            ) );
                
                            $object = $attributes[0]->attribute( 'object' );
                            $attribute = eZContentObjectAttribute::fetch( (int) $namedParameters['attribute_id'], $object->attribute( 'current_version' ) );
                            $operatorValue = xrowQuestionnaireResult::fetchScore( $attribute, eZUser::fetch( $namedParameters['user_id'] ) );
                        }
                        else
                        {
                            $operatorValue = false;
                        }
                    }
                break;
        }
    }
}