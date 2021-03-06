{foreach from=$errors item="error_message" key="error"}
	{if $error eq "INVALID_REQUEST"}
		<p class="error">[[{$error_message}]]</p>
	{elseif $error eq "INVALID_DATA"}
		<p class="error">[[{$error_message}]]</p>
	{elseif $error eq "PARAMETERS_MISSED"}
		<p class="error">[[The key parameters are not specified]]</p>
	{elseif $error eq "MYSQL_ERROR"}
		[[{$error_message}]]
	{elseif $error eq "NOT_LOGGED_IN"}
		<p class="error">[[No logged in user found]]</p>
	{elseif $error eq "NOT_OWNER"}
		<p class="error">[[You're not owner of this listing]]</p>
    {elseif $error eq "USER_DOES_NOT_EXIST"}
		<p class="error">[[There is no such user]]</p>
	{else}
		<p class="error">[[{$error}]] [[{$error_message}]]</p>
	{/if}
{/foreach}