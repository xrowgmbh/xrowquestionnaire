<?php
$module = $Params['Module'];
$namedParameters = $module->NamedParameters;

$hash = isset( $namedParameters['hash'] ) ? (string) $namedParameters['hash'] : false;

$tpl = eZTemplate::factory();

$list = xrowQuestionnaireOptin::fetchByHash( $hash );

foreach ( $list as $item )
{
    $tpl->setVariable( 'success', true );
    $item->optout();
}

$tpl->setVariable( 'hash', $hash );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:questionnaire/optout.tpl' );
$Result['path'] = array( 
    array( 
        'text' => ezpI18n::tr( 'kernel/content', 'Questionnaire' ) , 
        'url' => false 
    ) , 
    array( 
        'text' => ezpI18n::tr( 'kernel/content', 'Optout' ) , 
        'url' => false 
    ) 
);
return $Result;