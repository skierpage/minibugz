<?php

function isDebugMode() {
    if (isset( $_SERVER['QUERY_STRING'] )) {
        parse_str( $_SERVER['QUERY_STRING'], $query_params );
        if (isset( $query_params['debug'] )) {
            return true;
        }
    } else {
        return false;
    }
}

/**
 * Maybe...
 * Get the values of a named set of keys from the named array.
 *
 * @param array $arr : array holding values
 * @param array $keys : array of key names to return, or one key string
 * @return null|any :  value of key or null
function wantValues( $arr, $keys) {

}
*/
