{block name="title" prepend}{$LNG.lm_support}{/block}
{block name="content"}
<div id="page">
	<div id="content">
<div id="ally_content" class="conteiner" style="padding-bottom:2px;">
    <div class="gray_stripe" style="padding-right:0;">
      {$LNG.ti_header} <a href="game.php?page=ticket&amp;mode=create"><input type="button" class="right_flank input_blue" style="width:200px; font-weight:bold;" onclick="return add();" value="{$LNG.ti_new}"></a>
    </div> 
    	
		
		{foreach $ticketList as $TicketID => $TicketInfo}	
    <a href="game.php?page=ticket&amp;mode=view&amp;id={$TicketID}" class="ticket_row_linck">
        <span class="ticket_row_linck_id">#{$TicketID}</span>
        <span class="ticket_row_linck_subject">{$TicketInfo.subject}</span>
        <span class="ticket_row_linck_time">{$TicketInfo.time}</span>
		{if $TicketInfo.status == 0}
                <span class="ticket_row_linck_status" style="color:green">{$LNG.ti_status_open}</span>
				{elseif $TicketInfo.status == 1}<span class="ticket_row_linck_status" style="color:orange">{$LNG.ti_status_answer}</span>{elseif $TicketInfo.status == 2}<span class="ticket_row_linck_status" style="color:red">{$LNG.ti_status_closed}</span>
				{if $TicketInfo.rate == 0}
				<span id="{$TicketID}" class="ticket_row_linck_status tckRateStars">
            	<span style="background-image: url('./styles/images/star-gray.png');" star="1" class="tckRateStar"></span>
            	<span style="background-image: url('./styles/images/star-gray.png');" star="2" class="tckRateStar"></span>
            	<span style="background-image: url('./styles/images/star-gray.png');" star="3" class="tckRateStar"></span>
            	<span style="background-image: url('./styles/images/star-gray.png');" star="4" class="tckRateStar"></span>
            	<span style="background-image: url('./styles/images/star-gray.png');" star="5" class="tckRateStar"></span>
            </span>
			{/if}
			{/if}
				
                        <span class="clear"></span>
						
    </a>    
	{foreachelse}
	{/foreach}
	
	
    </div>
</div>
</div>
            <div class="clear"></div>            
        </div>
{/block}
