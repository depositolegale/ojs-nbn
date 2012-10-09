{**
 * @file plugins/pubIds/nbn/templates/index.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Contributed by CILEA
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * NBN plug-in home page.
 *}
{strip}
{assign var="pageTitle" value="plugins.pubIds.nbnit.displayName"}
{include file="common/header.tpl"}
{/strip}

<br/>

<h3>{translate key="plugins.pubIds.nbnit.settings"}</h3>
{if !empty($configurationErrors)}
	{foreach from=$configurationErrors item=configurationError}
		{if $configurationError == $smarty.const.NBN_CONFIGERROR_SETTINGS}
			{translate key="plugins.pubIds.nbnit.error.pluginNotConfigured"}
		{/if}
	{/foreach}
{/if}
{capture assign="settingsUrl"}{plugin_url path="settings"}{/capture}
{translate key="plugins.pubIds.nbnit.settings.description" settingsUrl=$settingsUrl}
<br />
<br />

{if empty($configurationErrors)}
	<h3>{translate key="plugins.pubIds.nbnit.register"}</h3>

	<ul class="plain">
		<li>&#187; <a href="{plugin_url path="issues"}">{translate key="plugins.pubIds.nbnit.issues"}</a></li>
		<li>&#187; <a href="{plugin_url path="articles"}">{translate key="plugins.pubIds.nbnit.articles"}</a></li>
	</ul>
{/if}

<br /><br />
{include file="common/footer.tpl"}
