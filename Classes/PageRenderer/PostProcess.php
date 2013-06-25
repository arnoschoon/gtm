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

class tx_Gtm_PageRenderer_PostProcess {

	/**
	 * @var string
	 */
	const DATA_LAYER_NAME = 'txGtmDataLayer';

	/**
	 * Render a code snippet for including Google Tag Manager and inject it to the pages' content
	 *
	 * @param array $params
	 * @param t3lib_PageRenderer $parentObject
	 *
	 * @return void
	 */
	public function injectGtmCode(array $params, t3lib_PageRenderer $parentObject) {
		if(tx_Gtm_Utility_Configuration::isEnabled() && stripos($params['bodyContent'], '<body') !== FALSE) {
			$matches = NULL;
			preg_match('/<body[^>]*>/sim', $params['bodyContent'], $matches);

			if(is_array($matches) && !empty($matches[0])) {
				$params['bodyContent'] = str_replace($matches[0], $matches[0] . PHP_EOL . $this->renderCodeSnippet(), $params['bodyContent']);
			}
		}
	}

	/**
	 * @return string
	 */
	protected function renderCodeSnippet() {
		$setup = tx_Gtm_Utility_Configuration::getSetup();
		$dataLayer = array();

		$containerId = $setup['containerId'];

		if(is_array($setup['dataLayer.'])) {
			$dataLayer = $this->getDataLayerArray($setup['dataLayer.']);
			$normalizedDataLayer = array();

			for($i = 0; $i < count($dataLayer); $i++) {
				$key = key($dataLayer[$i]);
				$value = current($dataLayer[$i]);

				$normalizedDataLayer[$key] = $value;
			}

			$queryString = t3lib_div::implodeArrayForUrl('', $normalizedDataLayer, FALSE, TRUE);
		}

		if(!empty($setup['tagTypeBlacklist'])) {
			$dataLayer[] = array(
				'tagTypeBlacklist' => t3lib_div::trimExplode(',', $setup['tagTypeBlacklist'])
			);
		}

		$script = t3lib_div::minifyJavaScript(vsprintf('(function(w,d,s,l){
			w.%1$s = %2$s;
			w.%1$s.push({\'gtm.start\': new Date().getTime()});

			var f = d.getElementsByTagName(s)[0],
				j = d.createElement(s);

			j.async = true;
			j.src = \'//www.googletagmanager.com/gtm.js?id=%3$s&l=%1$s\';

			f.parentNode.insertBefore(j,f);
		})(window, document, \'script\');', array(
			self::DATA_LAYER_NAME,
			json_encode($dataLayer),
			$containerId
		)));

		$noScript = vsprintf('<noscript><iframe src="//www.googletagmanager.com/ns.html?id=%1$s%2$s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' , array(
			$containerId,
			htmlspecialchars($queryString)
		));

		/**
		 * Omit <noscript /> fallback cause it breaks xHTML validation
		 */

		return implode(PHP_EOL, array(
			'<!-- Google Tag Manager -->',
			t3lib_div::wrapJS($script),
			'<!-- End Google Tag Manager -->'
		));
	}

	/**
	 * @param array $typoScriptConfiguration
	 *
	 * @return array
	 */
	protected function getDataLayerArray(array $typoScriptConfiguration) {
		$cObj = tx_Gtm_Utility_Configuration::getContentObject();
		$dataLayer = array();
		$renderedKeys = array();

		$typoScriptKeys = array_keys($typoScriptConfiguration);

		foreach($typoScriptKeys as $key) {
			$normalizedKey = $key;

			if(stripos($key, '.') !== FALSE) {
				$normalizedKey = substr($key, 0, -1);
			}

			if(!in_array($normalizedKey, $renderedKeys)) {
				$value = $cObj->stdWrap($typoScriptConfiguration[$normalizedKey], $typoScriptConfiguration[$normalizedKey . '.']);

				if(!empty($value)) {
					if(!empty($typoScriptConfiguration[$normalizedKey . '.']['isJson'])) {
						$value = json_decode($value);
					}

					$dataLayer[] = array(
						$normalizedKey => $value
					);
				}

				$renderedKeys[] = $normalizedKey;
			}
		}

		return $dataLayer;
	}

}