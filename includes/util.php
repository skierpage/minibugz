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

