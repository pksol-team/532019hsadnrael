<?php

if (!function_exists('iconv_get_encoding')) {
    function iconv_get_encoding($type)
    {
        if (extension_loaded('mb_string')) {
            switch ($type) {
                case 'input_encoding':
                case 'internal_encoding':
                    return mb_internal_encoding();
                    break;
                case 'output_encoding':
                    return mb_http_output();
                    break;
                case 'all':
                    return array(
                        'input_encoding' => mb_internal_encoding(),
                        'internal_encoding' => mb_internal_encoding(),
                        'output_encoding' => mb_internal_encoding(),
                    );

            }

            return FALSE;
        }
    }
}

if (!function_exists('iconv_set_encoding')) {
    function iconv_set_encoding($type, $charset)
    {
        if (extension_loaded('mb_string')) {
            switch ($type) {
                case 'input_encoding':
                case 'internal_encoding':
                    return mb_internal_encoding($charset);
                    break;
                case 'output_encoding':
                    return mb_http_output($charset);
                    break;
            }
        }

        return FALSE;
    }
}

if (!function_exists('iconv_strlen')) {
    function iconv_strlen($str, $charset=NULL)
    {
        if (extension_loaded('mb_string')) {
            if (is_null($charset)) {
                $charset = mb_internal_encoding();
            }
            return mb_strlen($str, $charset);
        }

        return strlen($str);
    }
}
