{**
 * @file plugins/pubIds/nbn/templates/issues.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Contributed by CILEA
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Select issues for registration.
 *}
{strip}
{assign var="pageTitle" value="plugins.pubIds.nbnit.register.selectIssue"}
{assign var="pageCrumbTitle" value="plugins.pubIds.nbnit.register.selectIssue"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">{literal}
	function toggleChecked() {
		var elements = document.getElementById('issues').elements;
		for (var i=0; i < elements.length; i++) {
			if (elements[i].name == 'issueId[]') {
				elements[i].checked = !elements[i].checked;
			}
		}
	}
{/literal}</script>

<br/>

<div id="issues">
	<form action="{plugin_url path="registerIssues"}" method="post" id="issues">
		<table width="100%" class="listing">
			<tr>
				<td colspan="5" class="headseparator">&nbsp;</td>
			</tr>
			<tr class="heading" valign="bottom">
				<td width="5%">&nbsp;</td>
				<td width="55%">{translate key="issue.issue"}</td>
				<td width="15%">{translate key="editor.issues.published"}</td>
				<td width="15%">{translate key="editor.issues.numArticles"}</td>
				<td width="10%" align="right">{translate key="common.action"}</td>
			</tr>
			<tr>
				<td colspan="5" class="headseparator">&nbsp;</td>
			</tr>

			{assign var="noIssues" value=true}
			{iterate from=issues item=issue}

					<tr valign="top">
						<td><input type="checkbox" name="issueId[]" value="{$issue->getId()}"/></td>
						<td><a href="{url page="issue" op="view" path=$issue->getId()}" class="action">{$issue->getIssueIdentification()|strip_unsafe_html|nl2br}</a></td>
						<td>{$issue->getDatePublished()|date_format:"$dateFormatShort"|default:"&mdash;"}</td>
						<td>{$issue->getNumArticles()|escape}</td>
						<td align="right"><nobr>
							{if $hasCredentials}
                        <a href="{plugin_url path="registerIssue"|to_array:$issue->getId() params=$testMode}" title="{translate key="plugins.pubIds.nbnit.registerDescription"}" class="action">{translate key="plugins.pubIds.nbnit.register"}</a>
							{/if}
							
						</nobr></td>
					</tr>
					<tr>
						<td colspan="5" class="{if $issues->eof()}end{/if}separator">&nbsp;</td>
					</tr>

			{/iterate}
				<tr>
					<td colspan="2" align="left">{page_info iterator=$issues}</td>
					<td colspan="3" align="right">{page_links anchor="issues" name="issues" iterator=$issues}</td>
				</tr>
		</table>
		<p>
			{if !empty($testMode)}<input type="hidden" name="testMode" value="1" />{/if}
			{if $hasCredentials}
				<input type="submit" name="register" value="{translate key="plugins.pubIds.nbnit.register"}" title="{translate key="plugins.pubIds.nbnit.registerDescription.multi"}" class="button defaultButton"/>
				&nbsp;
			{/if}			
			&nbsp;
			<input type="button" value="{translate key="common.selectAll"}" class="button" onclick="toggleChecked()" />
		</p>
		<p>
			{if $hasCredentials}
				{translate key="plugins.pubIds.nbnit.register.warning"}
			{else}
				{capture assign="settingsUrl"}{plugin_url path="settings"}{/capture}
				{translate key="plugins.pubIds.nbnit.register.noCredentials" settingsUrl=$settingsUrl}
			{/if}
		</p>
	</form>
</div>

{include file="common/footer.tpl"}
