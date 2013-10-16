<?php
if ( ! $isQuiet )
{
    $cli->output( "Sending questionaire notifications" );
}

$lastrun = eZSiteData::fetchByName( xrowQuestionnaireOptin::SITEDATA_LASTRUNTIME );

if ( ! $lastrun )
{
    $lastrun = new eZSiteData( array( 
        'name' => xrowQuestionnaireOptin::SITEDATA_LASTRUNTIME , 
        'value' => time() 
    ) );
    $lastrun->store();
}

$users = xrowQuestionnaireOptin::fetchUserList();

$list = eZFunctionHandler::execute( 'content', 'tree', array( 
    'parent_node_id' => eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) , 
    'class_filter_type' => 'include' , 
    'class_filter_array' => array( 
        'questionnaire' 
    ) , 
    'attribute_filter' => array( 'and', array( 'questionnaire/questionnaire', '>=', $lastrun->value ), array( 'questionnaire/questionnaire', '<', time() ) ),
    'limitation' => array() 
) );

foreach ( $list as $node )
{
    foreach ( $users as $user )
    {
        $params = array();
        $params['user'] = $user->user();
        $params['hash'] = $user->random;
        $params['node'] = $node;
        $params['hostname'] = eZSys::hostname();
        if ( ! xrowQuestionnaireFunctions::sendMail( $params['user'], 'design:questionnaire/mail/notification.tpl', $params, $node ) )
        {
            $cli->output( "Error sending email" );
        }
    }
}

$lastrun->value = time();
$lastrun->store();