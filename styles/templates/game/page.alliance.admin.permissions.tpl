{block name="title" prepend}{$LNG.lm_alliance}{/block}
{block name="content"}
<div id="page">
<div id="content">
<div id="ally_content" class="conteiner">
{$countRank = count($avalibleRanks)}
<form action="game.php?page=alliance&amp;mode=admin&amp;action=permissionsSend" method="post">
<input type="hidden" value="1" name="send">

    <div class="gray_stripe" style="padding-right:0;">
    	{$LNG.al_configura_ranks}
        <button id="create_new_alliance_rank" class="right_flank">+ Create Rank</button>
    </div>

	<table class="ally_ranks">
	<tr>
		
		<td>{$LNG.al_rank_name}</td>
		{foreach $avalibleRanks as $rankName}
		<td><img src="styles/resource/images/alliance/{$rankName}.png" alt="" width="16" height="16"></td>
		{/foreach}
	</tr>
	
	{foreach $rankList as $rankID => $rankRow}
	<tr>
		
		<td><a href="game.php?page=alliance&amp;mode=admin&amp;action=permissionsSend&amp;deleteRank={$rankID}">X</a> <input type="text" style="width:86%" name="rank[{$rankID}][name]" value="{$rankRow.rankName}"></td>
		{foreach $avalibleRanks as $rankName}
		<td><input type="checkbox" name="rank[{$rankID}][{$rankName}]" value="1"{if $rankRow[$rankName]} checked{/if}{if !$ownRights[$rankName]} disabled{/if}></td>
		{/foreach}
	</tr>
	
	{foreachelse}
	<tr>
		<td colspan="15">No existing ranks.</td>
	</tr>
	{/foreach}
	
		
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		<tr>
		<td colspan="15"><input type="submit" value="{$LNG.al_save}"></td>
	</tr>
	
	<tr>
	<td colspan="15" class="gray_stripe" style="padding-right:0; text-align:left;">Legenda</td>
		{foreach $avalibleRanks as $index => $rankName}
		<tr><td><img src="styles/resource/images/alliance/{$rankName}.png" alt="{$rankName}" title="{$rankName}" width="16" height="16"></td><td colspan="14">{$ranksDesc[$index]}</td></tr>
		{/foreach}
	</tr>
	</table>

</form>
</div>
<div class="ally_bottom" style="text-align:left;">
    <a href="game.php?page=alliance&amp;mode=admin">back</a>
</div>
<div id="new_alliance_rank" title="{$LNG.al_create_new_rank}" style="display:none;">
	<form action="game.php?page=alliance&amp;mode=admin&amp;action=permissionsSend" method="post">
		<table style="width:740px">
	<tr>
				<td>{$LNG.al_rank_name}</th>
				
				<td><input type="text" name="newrank[rankName]" size="20" maxlength="32" required></td>
	</tr>
			<tr>
				<th colspan="{$countRank + 2}">&nbsp;</th>
			</tr>
	
	<tr>
				<td colspan="{$countRank + 2}"><input type="submit" value="{$LNG.al_create}"></td>
	</tr>
	</table>
</form>
</div>
</div>
</div>
</div>
            <div class="clear"></div>            
        </div>
{/block}