{include file="$template/pageheader.tpl" title=$LANG.clientareanavsupporttickets desc=$LANG.supportticketsintro}

<div class="internalpadding"><input type="button" value="{$LANG.opennewticket}" class="btn" onclick="window.location='submitticket.php'" /></div>

{if $sirportly_error}
<div class="alert-message error">
    <p>An error occurred, please try again later.</p>
</div>
{/if}
<p>{$numtickets} {$LANG.recordsfound}, {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</p>

<table class="zebra-striped">
    <thead>
        <tr>
            <th>{$LANG.supportticketsdate}</th>
            <th>{$LANG.supportticketsdepartment}</th>
            <th>{$LANG.supportticketssubject}</th>
            <th>{$LANG.supportticketsstatus}</th>
            <th>{$LANG.supportticketsticketlastupdated}</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
{foreach key=num item=ticket from=$tickets}
        <tr>
            <td>{$ticket.date}</td>
            <td>{$ticket.department}</td>
            <td><a href="viewticket.php?tid={$ticket.tid}&amp;c={$ticket.c}">{if $ticket.unread}<strong>{/if}{$ticket.subject}{if $ticket.unread}</strong>{/if}</a></td>
            <td>{$ticket.status}</td>
            <td>{$ticket.lastreply}</td>
            <td class="textcenter"><form method="get" action="viewticket.php"><input type="hidden" name="tid" value="{$ticket.tid}" /><input type="hidden" name="c" value="{$ticket.c}" /><input type="submit" value="{$LANG.supportticketsviewticket}" class="btn info" /></form></td>
        </tr>
{foreachelse}
        <tr>
            <td colspan="7" class="textcenter">{$LANG.norecordsfound}</td>
        </tr>
{/foreach}
    </tbody>
</table>



<div class="pagination">
    <ul>
        <li class="prev{if !$prevpage} disabled{/if}"><a href="{if $prevpage}supporttickets.php?page={$prevpage}{else}javascript:return false;{/if}">&larr; {$LANG.previouspage}</a></li>
        <li class="next{if !$nextpage} disabled{/if}"><a href="{if $nextpage}supporttickets.php?page={$nextpage}{else}javascript:return false;{/if}">{$LANG.nextpage} &rarr;</a></li>
    </ul>
</div>