<?php
/**
 *  Copyright notice
 *
 *  (c) 2013 Arno Schoon (arno@maxserv.nl)
 *  All rights reserved
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *
 * $Id$
 *
 * @author    Arno Schoon <arno@maxserv.nl>
 */

class tx_Gtm_Utility_Configuration {

	/**
	 * @return bool
	 */
	public static function isEnabled() {
		$setup = self::getSetup();

		return is_array($setup) && !empty($setup['containerId']);
	}

	/**
	 * @return array|null
	 */
	public static function getSetup() {
		$setup = null;

		if(TYPO3_MODE == 'FE'
			&& $GLOBALS['TSFE'] instanceof tslib_fe
			&& is_array($GLOBALS['TSFE']->config['config']['tx_gtm.'])
			&& !empty($GLOBALS['TSFE']->config['config']['tx_gtm.']['enable'])
			&& $GLOBALS['TSFE']->tmpl instanceof t3lib_TStemplate
		) {
			if(is_array($GLOBALS['TSFE']->tmpl->setup['plugin.']) && is_array($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_gtm.'])) {
				$setup = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_gtm.'];
			}
		}

		return $setup;
	}

	/**
	 * Get an instance of tslib_cObj
	 *
	 * @return tslib_cObj|null
	 */
	public static function getContentObject() {
		$cObj = null;

		if(TYPO3_MODE && $GLOBALS['TSFE'] instanceof tslib_fe && $GLOBALS['TSFE']->cObj instanceof tslib_cObj) {
			$cObj = $GLOBALS['TSFE']->cObj;
		}

		return $cObj;
	}

}