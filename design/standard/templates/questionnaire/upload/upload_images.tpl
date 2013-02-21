{set scope=global persistent_variable=hash('title', 'Upload new Image'|i18n('design/standard/ezoe'),
                                           'scripts', array('ezoe/ez_core.js',
                                                            'ezoe/ez_core_animation.js',
                                                            'ezoe/ez_core_accordion.js',
                                                            'popup_utils.js'),
                                           'css', array()
                                           )}
<script type="text/javascript">
<!--
var contentType = '{$content_type}', classFilter = [];

{foreach $class_filter_array as $class_filter}
    classFilter.push('{$class_filter}');
{/foreach}

{literal}

tinyMCEPopup.onInit.add( function(){
    var slides = ez.$$('div.panel'), navigation = ez.$$('#tabs li.tab');
    slides.accordion( navigation, {duration: 100, transition: ez.fx.sinoidal, accordionAutoFocusTag: 'input[type=text]'}, {opacity: 0, display: 'none'} );
    // custom link generator, to redirect links to browse view if not in browse view
    eZOEPopupUtils.settings.browseLinkGenerator = function( n, mode, ed )
    {
        if ( n.children_count )
        {
           var tag = document.createElement("a");
           tag.setAttribute('href', 'JavaScript:eZOEPopupUtils.browse(' + n.node_id + ');');
           tag.setAttribute('title', ed.getLang('browse') + ': ' + n.url_alias );
           if ( mode !== 'browse' ) ez.$( tag ).addEvent('click', function(){ slides.accordionGoto( 2 ); });
           return tag;
        }
        var tag = document.createElement("span");
        tag.setAttribute('title', n.url_alias );
        return tag;
    };
});

eZOEPopupUtils.settings.browseClassGenerator = function( n, hasImage ){
    if ( hasImage && jQuery.inArray( n.class_identifier, classFilter ) !== -1 )
        return '';
    if ( n.children_count )
        return 'node_not_image';
    return 'node_not_image node_fadeout';
};

-->
</script>
{/literal}

<div class="upload-view">
    <form action={concat('questionnaire/upload/', $object_id, '/', $object_version, '/auto/1' )|ezurl} method="post" target="embed_upload" name="EmbedForm" id="EmbedForm" enctype="multipart/form-data" onsubmit="document.getElementById('upload_in_progress').style.display = '';">
        <div id="tabs" class="tabs">
            <ul>
                <li class="tab" title="{'Search for content already in eZ Publish.'|i18n('design/standard/ezoe/wai')}"><span><a href="JavaScript:void(0);">{'Search'|i18n('design/admin/content/search')}</a></span></li>
                <li class="tab" title="{'Browse the content tree in eZ Publish.'|i18n('design/standard/ezoe/wai')}"><span><a href="JavaScript:void(0);">{'Browse'|i18n('design/standard/ezoe')}</a></span></li>
            </ul>
        </div>

        <div class="panel_wrapper" style="min-height: 360px;">
            {include uri="design:questionnaire/upload/box_search.tpl" box_class_filter_array=$class_filter_array}
            {include uri="design:questionnaire/upload/box_browse.tpl" box_class_filter_array=$class_filter_array}
        </div>
     </form>
</div>
{undef $box_has_access}