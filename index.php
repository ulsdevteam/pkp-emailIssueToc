<?php

/**
 * @defgroup plugins_generic_emailIssueToc
 */

/**
 * @file plugins/generic/emailIssueToc/index.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @ingroup plugins_generic_emailIssueToc
 * @brief Wrapper for EmailIssueToc plugin.
 *
 */

require_once('emailTOCPlugin.inc.php');

return new emailTOCPlugin();