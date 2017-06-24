<?php
/**
 * Softaculous Related Functionality
 * Last Changed: $LastChangedDate: 2017-05-30 05:54:53 -0400 (Tue, 30 May 2017) $
 * @author detain
 * @version $Revision: 24894 $
 * @copyright 2017
 * @package MyAdmin-Softaculous-Licensing
 * @category Licenses
 */

namespace Detain\MyAdminSoftaculous;

/**
 * Converts an Array to XML
 */
class ArrayToXML
{
	/**
	 * The main function for converting to an XML document.
	 * Pass in a multi dimensional array and this recursively loops through and builds up an XML document.
	 *
	 * @param array $data
	 * @param string $rootNodeName - what you want the root node to be - defaults to data.
	 * @param SimpleXMLElement $xml - should only be used recursively
	 * @return string XML
	 */
	public function toXML($data, $rootNodeName = 'ResultSet', $xml = NULL) {
		if (is_null($xml)) //$xml = simplexml_load_string( "" );
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
		// loop through the data passed in.
		foreach ($data as $key => $value) {
			$numeric = FALSE;
			// no numeric keys in our xml please!
			if (is_numeric($key)) {
				$numeric = 1;
				$key = $rootNodeName;
			}
			// delete any char not allowed in XML element names
			$key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);
			// if there is another array found recursively call this function
			if (is_array($value)) {
				$node = ArrayToXML::isAssoc($value) || $numeric ? $xml->addChild($key) : $xml;
				// recursive call.
				if ($numeric) $key = 'anon';
				ArrayToXML::toXML($value, $key, $node);
			} else {
				// add single node.
				$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
				$xml->addChild($key, $value);
			}
		}
		// pass back as XML
		return $xml->asXML();
		// if you want the XML to be formatted, use the below instead to return the XML
		//$doc = new DOMDocument('1.0');
		//$doc->preserveWhiteSpace = false;
		//$doc->loadXML( $xml->asXML() );
		//$doc->formatOutput = true;
		//return $doc->saveXML();
	}

	/**
	 * Convert an XML document to a multi dimensional array
	 * Pass in an XML document (or SimpleXMLElement object) and this recursively loops through and builds a representative array
	 *
	 * @param string $xml - XML document - can optionally be a SimpleXMLElement object
	 * @return array ARRAY
	 */
	public function toArray($xml) {
		if (is_string($xml)) $xml = new SimpleXMLElement($xml);
		$children = $xml->children();
		if (!$children) return (string) $xml;
		$arr = [];
		foreach ($children as $key => $node) {
			$node = ArrayToXML::toArray($node);

			// support for 'anon' non-associative arrays
			if ($key == 'anon') $key = count($arr);

			// if the node is already set, put it into an array
			if (isset($arr[$key])) {
				if (!is_array($arr[$key]) || $arr[$key][0] == NULL) $arr[$key] = array($arr[$key]);
				$arr[$key][] = $node;
			} else {
				$arr[$key] = $node;
			}
		}
		return $arr;
	}

	// determine if a variable is an associative array

	/**
	 * @param $array
	 * @return bool
	 */
	public function isAssoc($array) {
		return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
	}

}

