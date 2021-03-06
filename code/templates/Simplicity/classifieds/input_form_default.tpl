{foreach from=$form_fields item=form_field}
	{$form_field=$form_field scope=global}
	{if $form_field.id == 'youtube'}
		<div class="form-group">
			<label class="form-label">[[{$form_field.caption}]] {if $form_field.is_required}*{/if}</label>
			{input property=$form_field.id}
			{if $form_field.instructions}
				{assign var="instructionsExist" value="1"}
				{include file="instructions.tpl" form_field=$form_field}
			{/if}
		</div>
	{elseif ($listing_type_id == "Resume" || $listing.type.id == "Resume") && $form_field.id == "anonymous"}
		<div class="form-group">
			<label class="form-label">[[{$form_field.caption}]] {if $form_field.is_required}*{/if}</label>
			{input property=$form_field.id}
			{if $form_field.instructions}
				{assign var="instructionsExist" value="1"}
				{include file="instructions.tpl" form_field=$form_field}
			{/if}
		</div>
	{elseif ($listingTypeID == "Job" || $listing.type.id == "Job") && $form_field.id == 'ApplicationSettings'}
		<div class="form-group">
			<label class="form-label">[[{$form_field.caption}]] {if $form_field.is_required}*{/if}</label>
			{input property=$form_field.id template='applicationSettings.tpl'}
			{if $form_field.instructions}
				{assign var="instructionsExist" value="1"}
				{include file="instructions.tpl" form_field=$form_field}
			{/if}
		</div>
	{elseif ($listingTypeID == "Job" || $listing.type.id == "Job") && $form_field.id == 'expiration_date'}
		<div class="form-group form-group__half">
			<label class="form-label">[[{$form_field.caption}]] {if $form_field.is_required}*{/if}</label>
			{input property=$form_field.id template='expiration_date.tpl'}
			{if $form_field.instructions}
				{assign var="instructionsExist" value="1"}
				{include file="instructions.tpl" form_field=$form_field}
			{/if}
		</div>
	{elseif $form_field.type == 'location'}
		{input property=$form_field.id}
	{elseif $form_field.id == 'EmploymentType'}
		<div class="form-group form-group__half margin">
			<label class="form-label">[[{$form_field.caption}]] {if $form_field.is_required}*{/if}</label>
			{input property=$form_field.id}
		</div>
	{elseif $form_field.id == 'JobCategory'}
		<div class="form-group form-group__half margin pull-right">
			<label class="form-label">
				[[{$form_field.caption}]]
				{if $form_field.is_required}*{/if}
			</label>
			{input property=$form_field.id}
		</div>
	{elseif $form_field.id == 'access_type'}
		<div class="form-group">
			{input property=$form_field.id}
			<label class="form-label inline-block access_type">[[{$form_field.caption}]] {if $form_field.is_required}*{/if}</label>
		</div>
	{elseif $form_field.id == 'Phone' && $GLOBALS.current_user.group.id == 'JobSeeker'}
		<div class="form-group form-group__half margin pull-right">
			<label class="form-label">[[{$form_field.caption}]] {if $form_field.is_required}*{/if}</label>
			{input property=$form_field.id}
		</div>
	{elseif $form_field.id == 'GooglePlace' && $GLOBALS.current_user.group.id == 'JobSeeker'}
		<div class="form-group form-group__half">
			<label class="form-label">[[{$form_field.caption}]] {if $form_field.is_required}*{/if}</label>
			{input property=$form_field.id}
		</div>
	{else}
		<div class="form-group {if $form_field.type == 'complex'}form-group__complex{/if}">
			{if $form_field.type == 'complex'}
				{assign var="fixInstructionsForComplexField" value=false}
				<h3 class="title__secondary">[[{$form_field.caption}]] {if $form_field.is_required}*{/if}</h3>
				{input property=$form_field.id}
				{if $form_field.instructions && $fixInstructionsForComplexField}
					{assign var="instructionsExist" value="1"}
					{include file="instructions.tpl" form_field=$form_field}
				{/if}
			{else}
				{assign var="fixInstructionsForComplexField" value=true}
				<label class="form-label">
					[[{$form_field.caption}]]
					{if $form_field.is_required}*{/if}
				</label>
				{input property=$form_field.id}
				{if $form_field.instructions && $fixInstructionsForComplexField}
					{assign var="instructionsExist" value="1"}
					{include file="instructions.tpl" form_field=$form_field}
				{/if}

			{/if}
		</div>
	{/if}
{/foreach}

{if $instructionsExist}
	{literal}
		<script type="text/javascript">
			function instructionFunc() {
				var elem = $(".instruction").prev();
				elem.children().focus(function() {
					$(this).parent().next(".instruction").children(".instr_block").show();
				});
				elem.children().blur(function() {
					$(this).parent().next(".instruction").children(".instr_block").hide();
				});
			}
			$("document").ready(function() {
				instructionFunc();
			});

			CKEDITOR.on('instanceReady', function(e) {
				e.editor.on('focus', function() {
						$("#instruction_"+ e.editor.name).show();
					});
				e.editor.on('blur', function() {
						$("#instruction_"+e.editor.name).hide();
					});
				return;
			});
		</script>
	{/literal}
{/if}