<?php
/*
 * http://bugs.jquery.com/ticket/13223 Trim needs to be used around html code, fixed in Jquery 1.10
 */
class xrowQuestionnaireServerFunctions extends ezjscServerFunctions
{

    public static function userData()
    {
        return true;
    }

    private static function initOverrides( eZContentObject $object )
    {
        $node = $object->mainNode();
        $section = eZSection::fetch( $object->attribute( 'section_id' ) );
        if ( $section )
        {
            $navigationPartIdentifier = $section->attribute( 'navigation_part_identifier' );
            $sectionIdentifier = $section->attribute( 'identifier' );
        }
        else
        {
            $navigationPartIdentifier = null;
            $sectionIdentifier = null;
        }
        
        $keyArray = array( 
            array( 
                'object' , 
                $object->attribute( 'id' ) 
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
                'class' , 
                $object->attribute( 'contentclass_id' ) 
            ) , 
            array( 
                'class_identifier' , 
                $node->attribute( 'class_identifier' ) 
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
                'navigation_part_identifier' , 
                $navigationPartIdentifier 
            ) , 
            array( 
                'depth' , 
                $node->attribute( 'depth' ) 
            ) , 
            array( 
                'url_alias' , 
                $node->attribute( 'url_alias' ) 
            ) , 
            array( 
                'class_group' , 
                $object->attribute( 'match_ingroup_id_list' ) 
            ) , 
            array( 
                'state' , 
                $object->attribute( 'state_id_array' ) 
            ) , 
            array( 
                'state_identifier' , 
                $object->attribute( 'state_identifier_array' ) 
            ) , 
            array( 
                'section' , 
                $object->attribute( 'section_id' ) 
            ) , 
            array( 
                'section_identifier' , 
                $sectionIdentifier 
            ) 
        );
        
        $parentClassID = false;
        $parentClassIdentifier = false;
        $parentNode = $node->attribute( 'parent' );
        if ( is_object( $parentNode ) )
        {
            $parentObject = $parentNode->attribute( 'object' );
            if ( is_object( $parentObject ) )
            {
                $parentClass = $parentObject->contentClass();
                if ( is_object( $parentClass ) )
                {
                    $parentClassID = $parentClass->attribute( 'id' );
                    $parentClassIdentifier = $parentClass->attribute( 'identifier' );
                    
                    $keyArray[] = array( 
                        'parent_class' , 
                        $parentClassID 
                    );
                    $keyArray[] = array( 
                        'parent_class_identifier' , 
                        $parentClassIdentifier 
                    );
                }
            }
        }
        
        $res = eZTemplateDesignResource::instance();
        $res->setKeys( $keyArray );
    }

    public static function questionnaire()
    {
        $http = eZHTTPTool::instance();
        $tpl = eZTemplate::factory();
        if ( isset( $_POST ) )
        {
            $data = $_POST;
        }
        
        $attribute = eZContentObjectAttribute::fetch( (int) $data['attribute_id'], (int) $data['version'] );
        $tpl->setVariable( 'attribute', $attribute );
        
        if ( $attribute->DataTypeString != xrowQuestionnaireType::DATA_TYPE_STRING )
        {
            throw new Exception( "Questionnaire not configured." );
        }
        if ( ! $attribute->hasContent() )
        {
            throw new Exception( "Questionnaire empty." );
        }
        $content = $attribute->content();
        
        $questionCount = count( $content['questions'] );
        $settings = $content['settings'];
        
        $object = $attribute->object();
        self::initOverrides( $object );
        
        $tpl->setVariable( 'number_of', 1 );
        $tpl->setVariable( 'count', (int) $questionCount );
        
        // Captcha validate
        if ( isset( $data['recaptcha_response_field'] ) )
        {
            if ( xrowQuestionnaireFunctions::validateCaptcha( $data['recaptcha_response_field'], $data['recaptcha_challenge_field'] ) == true )
            {
                $http->setSessionVariable( 'captcha_' . $data['attribute_id'], true );
            }
            else
            {
                $errors[] = ezpI18n::tr( 'xrowquestionnaire/view', 'Captcha konnte nicht validiert werden!' );
            }
        }
        // Captcha required
        if ( ! $http->hasSessionVariable( 'captcha_' . $data['attribute_id'], false ) and eZUser::currentUser()->isAnonymous() and isset( $content["settings"]["captcha"] ) and $content["settings"]["captcha"] == "on" )
        {
            $result['captcha'] = true;
            $tpl->setVariable( 'errors', $errors );
            $result['template'] = $tpl->fetch( 'design:questionnaire/captcha.tpl' );
            return $result;
        }
        if ( isset( $data['submit'] ) && !isset( $data['answer_id'] ) && (int) $data['question_id'] )
        {
        	$errors[] = ezpI18n::tr( 'xrowquestionnaire/view', 'Bitte wählen Sie eine Option!' );
        }

        // Voting closed
        if ( isset( $content["persistent"]["closed"] ) and $content["persistent"]["closed"] == "on" )
        {
            
            $tpl->setVariable( 'question', $content['questions'][0] ); //show the first question 
            $result['template'] = $tpl->fetch( 'design:questionnaire/closed.tpl' );
            return $result;
        }
        // Login required
        if ( ( eZUser::currentUser()->isAnonymous() and isset( $content["settings"]["lottery"] ) ) || ( eZUser::currentUser()->isAnonymous() and isset( $content["settings"]["user_loggedin"] ) and $content["settings"]["user_loggedin"] == "on" )  || ( eZUser::currentUser()->isAnonymous() and isset( $content["settings"]["play_once"] ) and $content["settings"]["play_once"] == "on" ) )
        {
            $tpl->setVariable( 'question', $content['questions'][0] );
            $result['template'] = $tpl->fetch( 'design:questionnaire/error_login.tpl' );
            return $result;
        }
        //UserAttributes Required
        if ( isset( $content["settings"]["user_attributes"] ) and !eZUser::currentUser()->isAnonymous() )
        {
            foreach ( $content["settings"]["user_attributes"] as $attributeID )
            {
                $classattr = eZContentClassAttribute::fetch( $attributeID );
        
                $co = eZUser::currentUser()->attribute( 'contentobject' );
                $dm = $co->dataMap();
                $missing = array();
                if ( isset( $dm[$classattr->Identifier] ) and ! $dm[$classattr->Identifier]->hasContent() )
                {
                    $missing[] = $classattr;
                }
            }
            if ( ! empty( $missing ) )
            {
                $tpl->setVariable( 'missing', $missing );
            }
            $tpl->setVariable( 'question', $content['questions'][0] );
            $result['template'] = $tpl->fetch( 'design:questionnaire/error_userprofile.tpl' );
            return $result;
        }
        if ( ! eZUser::currentUser()->isAnonymous() and isset( $content["settings"]["play_once"] ) and $content["settings"]["play_once"] == "on" and xrowQuestionnaireResult::isDuplicate( $attribute ) )
        {
            $tpl->setVariable( 'question', $content['questions'][0] );
            $result['template'] = $tpl->fetch( 'design:questionnaire/error_duplicate.tpl' );
            return $result;
        }
        
        $showQuestionResult = false;
        
        if ( isset( $data['answer_id'] ) and ! isset( $settings['lottery'] ) and isset( $settings['quiz'] ) and $data['prev'] != 'on')
        {
            $showQuestionResult = true;
        }
        if ( isset( $data['again'] ) )
        {
            foreach ( $content['questions'] as $questionremove )
            {
                xrowQuestionnaireResult::removeAnswers( $attribute->ID, $questionremove['id'] );
            }
        }
        
        //submit || next || prev
        if ( (int) $data['answer_id'] && ( isset( $data['submit'] ) || isset( $data['next'] ) ) || isset( $data['prev'] ) )
        {
            for ( $i = 0; count( $content['questions'] ) > $i; $i ++ )
            {
                $question = $content['questions'][$i];
                if ( $questionCount == $i + 1 )
                {
                    $last_question = $content['questions'][$i];
                }
                if ( isset( $content['questions'][$i + 1] ) and $content['questions'][$i + 1]['id'] == (int) $data['question_id'] and $data['prev'] == 'on' )
                {
                    $question = $content['questions'][$i];
                    break;
                }
                if ( $content['questions'][$i + 1] == (int) $data['question_id'] and isset( $data['answer_id'] ) and isset( $content['settings']['lottery'] ) )
                {
                    break;
                }
                
                if ( $question['id'] == (int) $data['question_id'] )
                {
                    if ( $showQuestionResult )
                    {
                        $question = $content['questions'][$i];
                    }
                    else
                    {
                        $i ++;
                        $question = $content['questions'][$i];
                    }
                    break;
                }
            }
        }
        else
        {
            $i = 0;
            $ids = xrowQuestionnaireResult::fetchCompletedQuestionIDList( $attribute );
            if ( $content['questions']['settings']['play_once'] != 'on' )
            {
                for ( $i = 0; count( $content['questions'] ) > $i; $i ++ )
                {
                    if ( ! in_array( $content['questions'][$i]['id'], $ids ) )
                    {
                        $question = $content['questions'][$i];
                        break;
                    }
                    if ( $questionCount == $i + 1 )
                    {
                        $last_question = $content['questions'][$i];
                    }
                }
            }
            else
            {
                $i = 0;
                $question = $content['questions'][$i];
                if ( $questionCount == $i + 1 )
                {
                    $last_question = $content['questions'][$i];
                }
            }
            
            $firstVisit = true;
        }
        $prev_question = false;
        $prev_answers = false;
        if ( isset( $content['questions'][$i - 1] ) and $i >= 0 )
        {
            $prev_question = $content['questions'][$i - 1];
            $prev_answers = xrowQuestionnaireResult::fetchListByUser( $attribute, $prev_question );
            
            foreach ( $prev_answers as $value )
            {
                $val[] = $value['answer_id'];
            }
            $tpl->setVariable( 'prev_answers', $val );
        }
        $tpl->setVariable( 'prev_answers', $val );
        $tpl->setVariable( 'prev_question', $prev_question );
        
        $first = false;
        if ( $content['questions'][0]['id'] == $question['id'] )
        {
            $first = true;
        }
        $last = false;
        if ( $content['questions'][- 1]['id'] == $question['id'] )
        {
            $last = true;
        }
        
        if ( ! empty( $errors ) && ! isset( $data['start'] ) )
        {
            $question = ( $content['questions'][$i - 1] <= 0 ) ? $content['questions'][0] : $content['questions'][$i];
            ( ! isset( $data['again'] ) ) ? $tpl->setVariable( 'errors', $errors ) : null;
        }

        //store vote - delete vote
        if ( isset( $data['answer_id'] ) and ( $data['answer_id'] || is_array( $data['answer_id'] ) ) )
        {
            $db = eZDB::instance();
            $db->begin();
            xrowQuestionnaireFunctions::storeResult( $attribute, $data );
            if ( isset( $last_question ) and $last_question['id'] == (int) $data['question_id'] and isset( $data['answer_id'] ) )
            {
                $operationResult = eZOperationHandler::execute( 'questionnaire', 'completed', array( 
                    'user_id' => eZUser::currentUserID() , 
                    'questionnaire_id' => $attribute->ID 
                ) );
                
                switch ( $operationResult['status'] )
                {
                    case eZModuleOperationInfo::STATUS_CONTINUE:
                        {
                        
                        }
                        break;
                    case eZModuleOperationInfo::STATUS_CANCELLED:
                    case eZModuleOperationInfo::STATUS_HALTED:
                    case eZModuleOperationInfo::STATUS_REPEAT:
                        {
                            throw new Exception( "Something is wrong in " . __METHOD__ );
                        }
                        break;
                }
            }
            $db->commit();
        }
        $tpl->setVariable( 'settings', $settings );
        $tpl->setVariable( 'question', $question );
        $tpl->setVariable( 'first', $first );
        $tpl->setVariable( 'last', $last );
        $tpl->setVariable( 'number_of', $i + 1 );
        
        if ( $showQuestionResult )
        {
            $correct_answers = array();
            $current_answers = array();
            
            foreach ( $content['questions'][$i]['answers'] as $answer )
            {
                if ( $answer['correct'] )
                {
                    $correct_answers[] = $answer;
                }
                if ( in_array( $answer['id'], $data['answer_id'] ) )
                {
                    $current_answers[] = $answer;
                }
            }

            $tpl->setVariable( 'current_answers', $current_answers );
            $tpl->setVariable( 'correct_answers', $correct_answers );
            
            $result['template'] = $tpl->fetch( 'design:questionnaire/question_result.tpl' );
        }
        elseif ( isset( $last_question ) && ( $last_question['id'] == (int) $data['question_id'] || $firstVisit ) )
        {
            $result['template'] = $tpl->fetch( 'design:questionnaire/completed.tpl' );
        }
        else
        {
            $result['template'] = $tpl->fetch( 'design:questionnaire/question.tpl' );
        }
        return $result;
    
    }

    public static function show_result()
    {
        if ( isset( $_POST ) )
        {
            $data = $_POST;
        }
        
        $attribute = eZContentObjectAttribute::fetch( (int) $data['attribute_id'], (int) $data['version'] );
        $content = $attribute->content();
        
        $tpl = eZTemplate::factory();
        
        eZContentCacheManager::clearContentCacheIfNeeded( $data['contentobject_id'] );
        
        $object = $attribute->object();
        self::initOverrides( $object );
        
        $tpl->setVariable( 'attribute', $attribute );
        
        $results = xrowQuestionnaireResult::fetchList( $attribute ); //all
        

        $http = eZHTTPTool::instance();
        $db = eZDB::instance();
        if ( $content );
        {
            $content['total'] = 0;
            $content['max_score'] = 0;
            $content['total_score'] = 0;
            
            foreach ( $content['questions'] as $key => $value )
            {
                $content['questions'][$key]['total'] = 0;

                $result = $db->arrayQuery( "SELECT count( DISTINCT session ) as total FROM `ezx_xrowquestionnaire_results` WHERE question_id = '" . $value['id']. "'" );
                if (isset ($result[0]['total'] ) )
                {
                    $content['questions'][$key]['total'] = $result[0]['total'];
                }
                $content['total'] += $content['questions'][$key]['total'];

                foreach ( $content['questions'][$key]['answers'] as $key2 => $value2 )
                {
                    $content['questions'][$key]['answers'][$key2]['total'] = "0";
                    if ( $content['settings']['results'] == 'my' )
                    {
                        if ( eZUser::currentUser()->isAnonymous() )
                        {
                            $sql = "SELECT count(answer_id) as count, answer_id, score FROM `ezx_xrowquestionnaire_results` WHERE session = '" . $db->escapeString( $http->sessionID() ) . "' and question_id = '" . $value['id'] . "' and answer_id='" . $content['questions'][$key]['answers'][$key2]['id'] . "' GROUP by answer_id";
                        
                        }
                        else
                        {
                            $sql = "SELECT count(answer_id) as count, answer_id, score FROM `ezx_xrowquestionnaire_results` WHERE user_id = '" . $db->escapeString( eZUser::currentUserID() ) . "' and question_id = '" . $value['id'] . "' and answer_id='" . $content['questions'][$key]['answers'][$key2]['id'] . "' GROUP by answer_id";
                        }
                        $total = 1;
                    }
                    else
                    {
                        $sql = "SELECT count(answer_id) as count, answer_id, score FROM `ezx_xrowquestionnaire_results` WHERE question_id = '" . $value['id'] . "' and answer_id='" . $content['questions'][$key]['answers'][$key2]['id'] . "' GROUP by answer_id";
                    }

                    $result = $db->arrayQuery( $sql );
                    
                    if ( isset( $result[0] ) )
                    {
                        $content['questions'][$key]['answers'][$key2]['total'] = $result[0]['count'];
                        $content['questions'][$key]['total'] = $content['questions'][$key]['total'];
                        $content['questions'][$key]['score'] += $result[0]['score']; //user Score
                        $content['total_score'] += $result[0]['score'];
                    }
                    else
                    {
                        $content['questions'][$key]['answers'][$key2]['total'] = 0;
                    }
                }
                
                for ( $i = 0; $i < count( $content['questions'][$key]['answers'] ); $i ++ )
                {
                    $content['max_score'] += $content['questions'][$key]['answers'][$i]['points'];
                }
                
                $content['max_score'] += $content['questions'][$key]['points']; //adds the points from the question to the reachable points count
				
                $avgResult = $db->arrayQuery( "SELECT sum(score)/count(*) AS avgScore, question_id FROM `ezx_xrowquestionnaire_results` WHERE contentobject_id=" . $data['contentobject_id'] . " AND question_id ='" . $value['id'] . "' GROUP BY question_id" );
                $content['questions'][$key]['avg_score'] =  $avgResult[0]['avgScore'];
                
				foreach ( $content['questions'][$key]['answers'] as $key3 => $value3 )
                {
                    $content['questions'][$key]['answers'][$key3]['percent'] = 0;
                    
                    if ( $content['questions'][$key]['answers'][$key3]['total'] > 0 )
                    {
                        $content['questions'][$key]['answers'][$key3]['percent'] = round( $content['questions'][$key]['answers'][$key3]['total'] / $content['questions'][$key]['total'] * 100, 2 );
                    }
                }
            }
        }
        if ( isset( $data['completed'] ) && $data['completed'] == 'on' )
        {
            $tpl->setVariable( 'completed', true );
        }
        $content['avgTotal'] = $content['total']/count($content['questions']);
        $tpl->setVariable( 'results', $content );

        $result['template'] = trim( $tpl->fetch( 'design:questionnaire/result.tpl' ) );
        return $result;
    }

    ############################BACKEND###############################	
    

    //BE: adds an PointsRange in {*POINTSRANGE*} datatype/edit Form
    public static function addRange()
    {
        $http = eZHTTPTool::instance();
        $tpl = eZTemplate::factory();
        
        $tpl->setVariable( 'attrID', $http->postVariable( 'attr' ) );
        $tpl->setVariable( 'attribute_base', $http->postVariable( 'base' ) );
        
        $result['template'] = trim( $tpl->fetch( 'design:questionnaire/range.tpl' ) );
        
        return $result;
    }

    //BE: adds the whole TabElement in {*TABSVIEW*} datatype/edit Form
    public static function add()
    {
        $http = eZHTTPTool::instance();
        $tpl = eZTemplate::factory();
        
        $tpl->setVariable( 'attrID', $http->postVariable( 'attr' ) );
        $tpl->setVariable( 'attribute_base', $http->postVariable( 'base' ) );
        $tpl->setVariable( 'contentobject_id', $http->postVariable( 'contentobject_id' ) );
        $tpl->setVariable( 'version', $http->postVariable( 'version' ) );
        
        $result['template'] = trim( $tpl->fetch( 'design:questionnaire/tab.tpl' ) );
        
        return $result;
    }

    //BE: adds an answer to the TabElemnt {*ANSWER CONTAINER*} parts/tab.tpl
    public static function addAnswer()
    {
        $http = eZHTTPTool::instance();
        $tpl = eZTemplate::factory();
        
        $tpl->setVariable( 'attrID', $http->postVariable( 'attr' ) );
        $tpl->setVariable( 'attribute_base', $http->postVariable( 'base' ) );
        $tpl->setVariable( 'contentobject_id', $http->postVariable( 'contentobject_id' ) );
        $tpl->setVariable( 'version', $http->postVariable( 'version' ) );
        
        $result['template'] = trim( $tpl->fetch( 'design:questionnaire/answer.tpl' ) );
        
        return $result;
    }

    public static function getAnswerSettings()
    {
        $settings = eZINI::Instance( 'xrowquestionnaire.ini' )->group( eZHTTPTool::instance()->postVariable( 'answer_type' ) );
        if ( $settings )
        {
            $result['settings'] = $settings;
            return $result;
        }
        else
        {
            return array();
        }
        return false;
    
    }

}