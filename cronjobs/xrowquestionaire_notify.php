<?php
if ( !$isQuiet )
{
    $cli->output( "Sending questionaire notifications" );
}


$lastrun = eZSiteData::fetchByName( xrowQuestionnaireOptin::SITEDATA_LASTRUNTIME );

if (!$lastrun)
{
    $lastrun = new eZSiteData( array( 'name' => xrowQuestionnaireOptin::SITEDATA_LASTRUNTIME,'value' => time() ) );
}

//TODO Generate email for each user