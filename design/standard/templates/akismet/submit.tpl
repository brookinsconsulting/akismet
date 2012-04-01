

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">{'Report spam'|i18n( 'extension/contactivity/akismet/submit' )}</h1>

<div class="header-mainline"></div>

</div></div></div></div></div></div>

<div class="box-ml"><div class="box-mr"><div class="box-content">

{if gt($feedback|count,0)}
 <div class="message-feedback">
	{foreach $feedback as $item}
		<p>{$item}</p>
	{/foreach}
</div>
{/if}

<form action={'akismet/reportspam'|ezurl} method="post" name="spam">
<table class="list" cellspacing="0">
<tr>
    <th class="tight"><img src={'toggle-button-16x16.gif'|ezimage} alt="{'Toggle selection'|i18n( 'design/admin/role/view' )}" onclick="ezjs_toggleCheckboxes( document.spam, 'ObjectIDList[]' ); return false;"/></th>
    <th class="name">{'Name'|i18n( 'extension/contactivity/akismet/submit' )}</th>
    <th class="class">{'Type'|i18n( 'extension/contactivity/akismet/submit' )}</th>
    <th class="modified">{'Published'|i18n( 'extension/contactivity/akismet/submit' )}</th>
</tr>
{foreach $nodes as $node sequence array('bglight','bgdark') as $sequence}
<tr class="{$sequence}">
    <td><input type="checkbox" name="ObjectIDList[]" value="{$node.contentobject_id}" {if $object_id_list|contains($node.contentobject_id)}checked="checked"{/if} /></td>
    <td><a href={$node.url_alias|ezurl}>{if $node.name|trim|ne('')}{$node.name|wash}{else}[{'Untitled'|i18n( 'extension/contactivity/akismet/submit' )}]{/if}</a></td>
    <td>{$node.class_name|wash()}</td>
    <td>{$node.object.published|l10n( shortdatetime )}</td>
</tr>
{/foreach}
</table>

<div class="context-toolbar">
{include name=navigator
         uri='design:navigator/google.tpl'
         page_uri='akismet/reportspam'
         item_count=$nodes_count
         view_parameters=$view_parameters
         item_limit=$limit}
</div>
</div></div></div>

<div class="controlbar">
<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
     <input type="submit" class="button" value="{'Report as spam'|i18n( 'extension/contactivity/akismet/submit' )}" name="SpamSubmitButton" />
     <input type="submit" class="button" value="{'Remove'|i18n( 'extension/contactivity/akismet/submit' )}" name="SpamRemoveButton" />
</div>
</div></div></div></div></div></div>
</div>


</form>




