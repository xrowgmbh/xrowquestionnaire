<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc float-break">
	
<div class="content-view-full">
    <div class="class-questionnaire">
		
		<h1>{$node.object.name|wash()}</h1>
		{if $node.object.data_map.description.has_content}
			<div class="description">
				{attribute_view_gui attribute=$node.object.data_map.description}
			</div>
		{/if}
	</div>
	
	<div class="float-break"></div>
	
	
	{attribute_view_gui attribute=$node.object.data_map.questionnaire}
	
</div>
	
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>