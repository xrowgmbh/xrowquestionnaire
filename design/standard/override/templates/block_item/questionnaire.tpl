<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc float-break">

<div class="content-view-line">
    <div class="class-questionnaire">
		
		<h2>{$node.object.name|wash()}</h2>
			{if $node.object.data_map.description.has_content}
			<div class="description">
				{attribute_view_gui attribute=$node.object.data_map.description}
			</div>
		{/if}
		
		<a href={$node.url_alias|ezurl()} title="{$node.name|wash()}">read more</a>
		
	</div>
	
</div>
	
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>