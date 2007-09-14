<form action={'akismet/submit'|ezurl} method="post" name="spam">
<table class="list" cellspacing="0">
<tr>
    <th class="tight"><img src={'toggle-button-16x16.gif'|ezimage} alt="{'Toggle selection'|i18n( 'design/admin/role/view' )}" onclick="ezjs_toggleCheckboxes( document.spam, 'ObjectIDList[]' ); return false;"/></th>
    <th>Name</th>
    <th>Published</th>
</tr>
{foreach $nodes as $node sequence array('bglight','bgdark') as $sequence}
<tr class="{$sequence}">
    <td><input type="checkbox" name="ObjectIDList[]" value="{$node.contentobject_id}" {if $object_id_list|contains($node.contentobject_id)}checked="checked"{/if} /></td>
    <td><a href={$node.url_alias|ezurl}>{if $node.name|trim|ne('')}{$node.name|wash}{else}[Untitled]{/if}</a></td>
    <td>{$node.object.published|l10n( shortdatetime )}</td>
</tr>
{/foreach}
</table>

<div class="button-block">
    <input type="submit" class="button" value="Report as spam" name="SpamSubmitButton" />
    <input type="submit" class="button" value="Remove" name="SpamRemoveButton" />
</div>

</form>

<div class="context-toolbar">
{include name=navigator
         uri='design:navigator/google.tpl'
         page_uri='akismet/submit'
         item_count=$nodes_count
         view_parameters=$view_parameters
         item_limit=$limit}
</div>
