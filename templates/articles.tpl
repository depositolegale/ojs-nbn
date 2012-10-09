{**
 * @file plugins/pubIds/nbn/templates/articles.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Contributed by CILEA
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Select articles for registration.
 *}
{strip}
{assign var="pageTitle" value="plugins.pubIds.nbnit.register.selectArticle"}
{assign var="pageCrumbTitle" value="plugins.pubIds.nbnit.register.selectArticle"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">{literal}
	function toggleChecked() {
		var elements = document.getElementById('articles').elements;
		for (var i=0; i < elements.length; i++) {
			if (elements[i].name == 'articleId[]') {
				elements[i].checked = !elements[i].checked;
			}
		}
	}
{/literal}</script>

<br/>
   {if $registeredFilterChecked}                        
      {assign var="filterChecked" value=true}
   {else}
      {assign var="filterChecked" value=false}
   {/if} 
<form method="post" id="submit" action="{plugin_url path="articles"}">
   <input type="hidden" name="sort" value="id"/>
   <input type="hidden" name="sortDirection" value="ASC"/>
   <input type="hidden" name="registeredFilterChecked" value="{$filterChecked}"/>
   <select name="searchField" size="1" class="selectMenu">
      {html_options_translate options=$fieldOptions selected=$searchField}
   </select>
   <select name="searchMatch" size="1" class="selectMenu">
      <option value="contains"{if $searchMatch == 'contains'} selected="selected"{/if}>{translate key="form.contains"}</option>
      <option value="is"{if $searchMatch == 'is'} selected="selected"{/if}>{translate key="form.is"}</option>
      <option value="startsWith"{if $searchMatch == 'startsWith'} selected="selected"{/if}>{translate key="form.startsWith"}</option>
   </select>
   <input type="text" size="15" name="search" class="textField" value="{$search|escape}" />
   <input type="submit" value="{translate key="common.search"}" class="button" />
</form>
<br/>
<form method="post" id="submit" action="{plugin_url path="articles"}">  
   <input type="hidden" name="filterForm" value="true"/>
   <table width="40%" class="listing">
      <tr>
      <td><input type="checkbox" name="registeredFilter" {$registeredFilterChecked} /></td>
      <td>{translate key="plugins.pubIds.nbnit.registeredFilter"}</td>
      <td><input type="submit" value="{translate key="plugins.pubIds.nbnit.filter"}" class="button" /></td>
      </tr>
   </table>   
</form>   
<br/>   
<div id="articles">
	<form action="{plugin_url path="registerArticles"}" method="post" id="articles">
		<table width="100%" class="listing">
			<tr>
				<td colspan="6" class="headseparator">&nbsp;</td>
			</tr>
			<tr class="heading" valign="bottom">
				<td width="5%">&nbsp;</td>
				<td width="25%">{translate key="issue.issue"}</td>
				<td width="35%">{translate key="article.title"}</td>
				<td width="20%">{translate key="article.authors"}</td>
            <td width="10%">{translate key="plugins.pubIds.nbnit.registered"}</td>
				<td width="5%" align="right">{translate key="common.action"}</td>
			</tr>
			<tr>
				<td colspan="6" class="headseparator">&nbsp;</td>
			</tr>

			{assign var="noArticles" value=true}
			{iterate from=articles item=articleData}
				{assign var=article value=$articleData.article}
					{assign var="noArticles" value=false}
					{assign var=issue value=$articleData.issue}
					<tr valign="top">
						<td><input type="checkbox" name="articleId[]" value="{$article->getId()}"/></td>
						<td><a href="{url page="issue" op="view" path=$issue->getId()}" class="action">{$issue->getIssueIdentification()|strip_tags}</a></td>
						<td><a href="{url page="article" op="view" path=$article->getId()}" class="action">{$article->getLocalizedTitle()|strip_unsafe_html}</a></td>
						<td>{$article->getAuthorString()|escape}</td>
                  <td>{assign var=isRegistered value=$nbn->getNBN($article->getId(), $journalId)}
                  {if $isRegistered}                        
                        {$isRegistered} 
                      {else}  
                        {translate key="common.no"}
                     {/if}               
                  </td>
						<td align="right"><nobr>
							{if $hasCredentials}								
                        <a href="{plugin_url path="registerArticle"|to_array:$article->getId() params=$testMode}" title="{translate key="plugins.pubIds.nbnit.registerDescription"}" class="action">{translate key="plugins.pubIds.nbnit.register"}</a>
							{/if}							
						</nobr></td>
					</tr>
					<tr>
						<td colspan="6" class="{if $articles->eof()}end{/if}separator">&nbsp;</td>
					</tr>
			{/iterate}
			{if $noArticles}
				<tr>
					<td colspan="6" class="nodata">{translate key="plugins.pubIds.nbnit.noArticles"}</td>
				</tr>
				<tr>
					<td colspan="6" class="endseparator">&nbsp;</td>
				</tr>
			{else}
				<tr>
					<td colspan="2" align="left">{page_info iterator=$articles}</td>
					<td colspan="4" align="right">{page_links anchor="articles" name="articles" iterator=$articles registeredFilterChecked=$filterChecked}</td>
				</tr>
			{/if}
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
