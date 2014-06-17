<?php
/**
*
* Copyright 2012-2012 Portugalmail Comunicações S.A (http://www.portugalmail.net/)
*
* See the enclosed file LICENCE for license information (GPLv3). If you
* did not receive this file, see http://www.gnu.org/licenses/gpl-3.0.html.
*
* @author Gonçalo Queirós <mail@goncaloqueiros.net>
*/
include_once "HighchartJsExpr.php";

class HighchartOptionRenderer
{
    /**
     * Render the options and returns the javascript that
     * represents them
     *
     * @return string The javascript code
     */
    public static function render($options)
    {
        $jsExpressions = array();
        //Replace any js expression with random strings so we can switch
        //them back after json_encode the options
        $options = static::_replaceJsExpr($options, $jsExpressions);
        
        //TODO: Check for encoding errors
        if (PHP_VERSION_ID >= 50303) { // PHP_VERSION_ID is avaible since 5.2.7
            $result = json_encode($options, JSON_NUMERIC_CHECK);
        } else {
            $result = json_encode($options);
            $result = preg_replace( "/\"([0-9\.]+)\"/", '$1', $result );
        }
        // ozs
                
        //Replace any js expression on the json_encoded string
        foreach ($jsExpressions as $key => $expr) {
            $result = str_replace('"' . $key . '"', $expr, $result);
        }
        return $result;
    }

    /**
     * Replaces any HighchartJsExpr for an id, and save the
     * js expression on the jsExpressions array
     * Based on Zend_Json
     *
     * @param mixed $data           The data to analyze
     * @param array &$jsExpressions The array that will hold
     *                              information about the replaced
     *                              js expressions
     */
    private static function _replaceJsExpr($data, &$jsExpressions)
    {
        if (!is_array($data) &&
            !is_object($data)) {
            return $data;
        }

        if (is_object($data)) {
            if ($data instanceof stdClass) {
                return $data;
            } elseif (!$data instanceof HighchartJsExpr) {
                $data = $data->getValue();
            }
        }

        if ($data instanceof HighchartJsExpr) {
            $magicKey = "____" . count($jsExpressions) . "_" . count($jsExpressions);
            $jsExpressions[$magicKey] = $data->getExpression();
            return $magicKey;
        }
        
        if (empty($data)) return $data;
        
        try {
        
        foreach ($data as $key => $value) {
            $data[$key] = static::_replaceJsExpr($value, $jsExpressions);
        }
        } catch (Exception $e) {
            var_dump($data);
            die();
        }
        return $data;
    }
}
