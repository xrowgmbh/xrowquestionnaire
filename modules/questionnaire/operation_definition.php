<?php

$OperationList = array();

$OperationList['completed'] = array( 'name' => 'completed',
                                       'default_call_method' => array( 'include_file' => 'kernel/shop/ezshopoperationcollection.php',
                                                                       'class' => 'eZShopOperationCollection' ),
                                       #'default_call_method' => array( 'class' => 'xrowQuestionnaireOperationCollection' ),
                                       'parameter_type' => 'standard',
                                       'parameters' => array( array( 'name' => 'questionnaire_id',
                                                                     'type' => 'integer',
                                                                     'required' => true ),
                                                              array( 'name' => 'user_id',
                                                                     'type' => 'integer',
                                                                     'required' => true ) ),
                                       'keys' => array( 'questionnaire_id', 'user_id' ),
                                       'body' => array( array( 'type' => 'trigger',
                                                               'name' => 'pre_completed',
                                                               'keys' => array( 'questionnaire_id', 'user_id' ) ),
                                                        array( 'type' => 'trigger',
                                                               'name' => 'post_completed',
                                                               'keys' => array( 'questionnaire_id', 'user_id' ) ) ) );

