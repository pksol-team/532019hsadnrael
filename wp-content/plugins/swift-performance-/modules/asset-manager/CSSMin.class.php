<?php

class Swift_Performance_CSSMin {

      public static function minify($css){
            if (Swift_Performance::check_option('minify-css', 1)){
                  return self::basic($css);
            }
            else if (Swift_Performance::check_option('minify-css', 2)){
                  return self::full($css);
            }

            return $css;
      }

      /**
       * Basic optimization
       * @param string $css
       * @return string
       */
      public static function basic($css){
            $css = preg_replace('~/\*.*?\*/~s', '', $css);
            $css = preg_replace('~\r?\n~', ' ', $css);
            $css = preg_replace('~(\s{2}|\t)~', ' ', $css);

            return $css;
      }

      /**
       * Full optimization
       * @param string $css
       * @return string
       */
      public static function full($css){
            // Encode content attribute for pseudo elements before minify
            $css = preg_replace_callback('~content\s?:\s?(\'|")([^\'"]*)(\'|")~', function($matches){
                  return 'content: ' . $matches[1] . base64_encode($matches[2]) . $matches[1];
            }, $css);


            $css = self::strip_comments($css);
            $css = self::strip_whitespace($css);
            $css = self::shorten_hex($css);
            $css = self::shorten_zeroes($css);
            $css = self::shorten_font_weights($css);
            $css = self::strip_empty_tags($css);

            // Decode content attribute for pseudo elements after minify
            $css = preg_replace_callback('~content\s?:\s?(\'|")([^\'"]*)(\'|")~', function($matches){
                  return 'content: ' . $matches[1] . base64_decode($matches[2]) . $matches[1];
            }, $css);

            return $css;
      }

      /**
       * Strip comments
       * @param string $content
       * @return string
       */
      public static function strip_comments($content){
            return preg_replace('~/\*.*?\*/~s', '', $content);
      }

      /**
       * Strip whitespace.
       * Thanks for Matthias Mullie (https://github.com/matthiasmullie)
       * @param string $content The CSS content to strip the whitespace for
       * @return string
       */
      public static function strip_whitespace($content){
            // remove leading & trailing whitespace
            $content = preg_replace('/^\s*/m', '', $content);
            $content = preg_replace('/\s*$/m', '', $content);
            // replace newlines with a single space
            $content = preg_replace('/\s+/', ' ', $content);
            // remove whitespace around meta characters
            // inspired by stackoverflow.com/questions/15195750/minify-compress-css-with-regex
            $content = preg_replace('/\s*([\*$~^|]?+=|[{};,>~]|!important\b)\s*/', '$1', $content);
            $content = preg_replace('/([\[(:])\s+/', '$1', $content);
            $content = preg_replace('/\s+([\]\)])/', '$1', $content);
            $content = preg_replace('/\s+(:)(?![^\}]*\{)/', '$1', $content);
            // whitespace around + and - can only be stripped inside some pseudo-
            // classes, like `:nth-child(3+2n)`
            // not in things like `calc(3px + 2px)`, shorthands like `3px -2px`, or
            // selectors like `div.weird- p`
            $pseudos = array('nth-child', 'nth-last-child', 'nth-last-of-type', 'nth-of-type');
            $content = preg_replace('/:('.implode('|', $pseudos).')\(\s*([+-]?)\s*(.+?)\s*([+-]?)\s*(.*?)\s*\)/', ':$1($2$3$4$5)', $content);
            // remove semicolon/whitespace followed by closing bracket
            $content = str_replace(';}', '}', $content);
            return trim($content);
    }


    /**
      * Shorthand hex color codes.
      * Thanks for Matthias Mullie (https://github.com/matthiasmullie)
      * @param string $content The CSS content to shorten the hex color codes for
      * @return string
      */
     public static function shorten_hex($content){
            $content = preg_replace('/(?<=[: ])#([0-9a-z])\\1([0-9a-z])\\2([0-9a-z])\\3(?=[; }])/i', '#$1$2$3', $content);
            // we can shorten some even more by replacing them with their color name
            $colors = array(
                  '#F0FFFF' => 'azure',
                  '#F5F5DC' => 'beige',
                  '#A52A2A' => 'brown',
                  '#FF7F50' => 'coral',
                  '#FFD700' => 'gold',
                  '#808080' => 'gray',
                  '#008000' => 'green',
                  '#4B0082' => 'indigo',
                  '#FFFFF0' => 'ivory',
                  '#F0E68C' => 'khaki',
                  '#FAF0E6' => 'linen',
                  '#800000' => 'maroon',
                  '#000080' => 'navy',
                  '#808000' => 'olive',
                  '#CD853F' => 'peru',
                  '#FFC0CB' => 'pink',
                  '#DDA0DD' => 'plum',
                  '#800080' => 'purple',
                  '#F00' => 'red',
                  '#FA8072' => 'salmon',
                  '#A0522D' => 'sienna',
                  '#C0C0C0' => 'silver',
                  '#FFFAFA' => 'snow',
                  '#D2B48C' => 'tan',
                  '#FF6347' => 'tomato',
                  '#EE82EE' => 'violet',
                  '#F5DEB3' => 'wheat',
            );
            return preg_replace_callback(
                  '/(?<=[: ])('.implode(array_keys($colors), '|').')(?=[; }])/i',
                  function ($match) use ($colors) {
                      return $colors[strtoupper($match[0])];
                  },
                  $content
            );
      }

      /**
       * Shorthand 0 values to plain 0, instead of e.g. -0em.
       * Thanks for Matthias Mullie (https://github.com/matthiasmullie)
       * @param string $content The CSS content to shorten the zero values for
       * @return string
       */
      public static function shorten_zeroes($content){
         // we don't want to strip units in `calc()` expressions:
         // `5px - 0px` is valid, but `5px - 0` is not
         // `10px * 0` is valid (equates to 0), and so is `10 * 0px`, but
         // `10 * 0` is invalid
         // best to just leave `calc()`s alone, even if they could be optimized
         // (which is a whole other undertaking, where units & order of
         // operations all need to be considered...)
         $calcs = self::find_calcs($content);
         $content = str_replace($calcs, array_keys($calcs), $content);
         // reusable bits of code throughout these regexes:
         // before & after are used to make sure we don't match lose unintended
         // 0-like values (e.g. in #000, or in http://url/1.0)
         // units can be stripped from 0 values, or used to recognize non 0
         // values (where wa may be able to strip a .0 suffix)
         $before = '(?<=[:(, ])';
         $after = '(?=[ ,);}])';
         $units = '(em|ex|%|px|cm|mm|in|pt|pc|ch|rem|vh|vw|vmin|vmax|vm)';
         // strip units after zeroes (0px -> 0)
         // NOTE: it should be safe to remove all units for a 0 value, but in
         // practice, Webkit (especially Safari) seems to stumble over at least
         // 0%, potentially other units as well. Only stripping 'px' for now.
         // @see https://github.com/matthiasmullie/minify/issues/60
         $content = preg_replace('/'.$before.'(-?0*(\.0+)?)(?<=0)px'.$after.'/', '\\1', $content);
         // strip 0-digits (.0 -> 0)
         $content = preg_replace('/'.$before.'\.0+'.$units.'?'.$after.'/', '0\\1', $content);
         // strip trailing 0: 50.10 -> 50.1, 50.10px -> 50.1px
         $content = preg_replace('/'.$before.'(-?[0-9]+\.[0-9]+)0+'.$units.'?'.$after.'/', '\\1\\2', $content);
         // strip trailing 0: 50.00 -> 50, 50.00px -> 50px
         $content = preg_replace('/'.$before.'(-?[0-9]+)\.0+'.$units.'?'.$after.'/', '\\1\\2', $content);
         // strip leading 0: 0.1 -> .1, 01.1 -> 1.1
         $content = preg_replace('/'.$before.'(-?)0+([0-9]*\.[0-9]+)'.$units.'?'.$after.'/', '\\1\\2\\3', $content);
         // strip negative zeroes (-0 -> 0) & truncate zeroes (00 -> 0)
         $content = preg_replace('/'.$before.'-?0+'.$units.'?'.$after.'/', '0\\1', $content);
         // IE doesn't seem to understand a unitless flex-basis value (correct -
         // it goes against the spec), so let's add it in again (make it `%`,
         // which is only 1 char: 0%, 0px, 0 anything, it's all just the same)
         // @see https://developer.mozilla.org/nl/docs/Web/CSS/flex
         $content = preg_replace('/flex:([0-9]+\s[0-9]+\s)0([;\}])/', 'flex:${1}0%${2}', $content);
         $content = preg_replace('/flex-basis:0([;\}])/', 'flex-basis:0%${1}', $content);
         // restore `calc()` expressions
         $content = str_replace(array_keys($calcs), $calcs, $content);
         return $content;
      }

      /**
       * Shorten CSS font weights.
       * Thanks for Matthias Mullie (https://github.com/matthiasmullie)
       * @param string $content The CSS content to shorten the font weights for
       * @return string
       */
      public static function shorten_font_weights($content){
        $weights = array(
            'normal' => 400,
            'bold' => 700,
        );
        return preg_replace_callback('/(font-weight\s*:\s*)('.implode('|', array_keys($weights)).')(?=[;}])/', function ($match) use ($weights) {
            return $match[1].$weights[$match[2]];
        }, $content);
      }

      /**
       * Strip empty tags from source code.
       * Thanks for Matthias Mullie (https://github.com/matthiasmullie)
       * @param string $content
       * @return string
       */
      public static function strip_empty_tags($content){
            $content = preg_replace('/(?<=^)[^\{\};]+\{\s*\}/', '', $content);
            $content = preg_replace('/(?<=(\}|;))[^\{\};]+\{\s*\}/', '', $content);
            return $content;
      }

      /**
       * Find all `calc()` occurrences.
       * Thanks for Matthias Mullie (https://github.com/matthiasmullie)
       * @param string $content The CSS content to find `calc()`s in.
       * @return array
       */
      public static function find_calcs($content){
            $results = array();
            preg_match_all('/calc(\(.+?)(?=$|;|calc\()/', $content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $length = strlen($match[1]);
                $expr = '';
                $opened = 0;
                for ($i = 0; $i < $length; $i++) {
                    $char = $match[1][$i];
                    $expr .= $char;
                    if ($char === '(') {
                        $opened++;
                    } elseif ($char === ')' && --$opened === 0) {
                        break;
                    }
                }
                $results['calc('.count($results).')'] = 'calc'.$expr;
            }
            return $results;
      }



}

?>
