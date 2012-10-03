<?php

/**
 * @defgroup plugins_pubIds_nbn
 */

/**
 * @file plugins/pubIds/urn/index.php
 *
 * Copyright (c) 2012 Cilea
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_pubIds_nbn
 *
 * @brief Wrapper for the NBN plugin.
 */


require_once('NBNPubIdPlugin.inc.php');

return new NBNPubIdPlugin();

?>
