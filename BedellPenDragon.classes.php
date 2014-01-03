<?php
if( !defined( 'MEDIAWIKI' ) ) {
        echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
        die( 1 );
}

class BedellPenDragon {
        // Cause <ref>, </ref> and everything in between to render as blank when rendering <getbpdprop>
        public static function refCallback ( $str, $argv, $parser ) {
                global $wgBedellPenDragonDisableRef;
                if ( $wgBedellPenDragonDisableRef ) {
                        return false;
                } else {
                        return true;
                }
        }

        // Establish the <randompageincategory> and <randompageincat> tags (both do the same thing)
        public static function randomPageinCatSetHook() {
                global $wgParser;
                $wgParser->setHook( "randompageincategory",
                        "BedellPenDragon::renderRandomPageInCat" );
                $wgParser->setHook( "randompageincat",
                        "BedellPenDragon::renderRandomPageInCat" );
                $wgHooks['ParserFirstCallInit'][] = 'BedellPenDragon::setupParserFunctions';
        }

        // Establish the {{#setbpdprop ... }} and {{#getbpdprop: ... }} parser functions
        public static function setupParserFunctions ( &$parser ) {
                 $parser->setFunctionHook( 'setbpdprop', 'BedellPenDragon::RenderSetBpdProp' );
                 $parser->setFunctionHook( 'getbpdprop', 'BedellPenDragon::RenderGetBpdProp' );
                 return true;
        }

        // Renders {{#setbdp prop: .... }}
        public static function renderSetBpdProp( $parser, $key = '', $value = '' ) {
                if ( $key == '' ) {
                        return;
                }
                $key = 'bpd_' . $key;
                $parser->getOutput()->setProperty ( $key, $value );
                return '';
        }

        // Renders {{#getgetbpdprop: ... }}
        public static function renderGetBpdProp( $parser, $title = '', $key = '', $errorsAsConstants = false, $raw = false ) {
                global $wgBedellPenDragonDisableRef;
                $key = 'bpd_' . $key;
                $title = Title::newFromText( $title );
                        if ( $title ) {
                                if ( !$title || $title->getArticleID() === 0 ) {
                                // In a real extension, this would be i18n-ized.
                                return '<span class="error">Invalid page ' . htmlspecialchars( $title ) . ' specified.</span>';
                        }

                        // Do for some page other then current one.
                        $dbr = wfGetDB( DB_SLAVE );
                        $propValue = $dbr->selectField( 'page_props', // table to use
                                'pp_value', // Field to select
                                array( 'pp_page' => $title->getArticleID(), 'pp_propname' => $key ), // where conditions
                                __METHOD__
                        );
                        if ( $propValue === false ) {
                                // No prop stored for this page
                                // TODO: i18n-ize
                                if ( $errorsAsConstants ) {
                                        return BPD_NOPROPSET;
                                }
                                return '<span class="error">No prop set for page ' . htmlspecialchars( $title ) . ' specified.</span>';
                        }
                        $prop = $propValue;
                        $wgBedellPenDragonDisableRef = true;
                        if ( $raw ) {
                                return $prop;
                        }
                        $prop = BedellPenDragon::stripRefTags ( $prop );
                        $parsed = $parser->recursiveTagParse ( $prop );
                        #$parsed = $parser->internalParse ( $prop );
                        $wgBedellPenDragonDisableRef = false;
                        return $parsed;
                } else {
                        // Second case, current page.
                        // Can't query db, because could be set earlier in the page and not saved yet.
                        // So have to use the parserOutput object.
                        $prop = $parser->getOutput()->getProperty( $key );
                        if ( $raw ) {
                                return $prop;
                        }
                        $wgBedellPenDragonDisableRef = true;
                        $prop = BedellPenDragon::stripRefTags ( $prop );
                        $parsed = $parser->recursiveTagParse ( $prop );
                        #$parsed = $parser->internalParse ( $prop );
                        $wgBedellPenDragonDisableRef = false;
                        return $parsed;
                }
        }

        // Renders <randompageincategory>
        // TODO: Add a namespace parameter and turn "exclude" into a list of page titles rather
        // than page IDs. The implementation of this will probably involve something analogous to
        // $wgRecentPagesMaxAttempts.
        public static function renderRandomPageInCat( $input, $params, $parser ) {
                $parser->disableCache();
                $allowed_types = array ( 'file', 'subcat', 'page' );
                if ( !isset( $params['cat'] ) ) { // No category selected
                        return '';
                }
                $type = 'page'; // By default, look up a page
                if ( isset( $params['type'] ) ) {
                        if ( in_array ( $params['type'], $allowed_types ) ) {
                                $type = $params['type'];
                        }
                }
                $content = false;
                if ( isset ( $params['content'] ) ) {
                        $content = true;
                }
                $firstItem = '';
                if ( isset( $params['firstitem'] ) ) {
                        $firstItem = $params['firstitem'];
                }
                $delimiter = '';
                if ( isset( $params['delimiter'] ) ) {
                        $delimiter = $params['delimiter'];
                }
                $parse = false;
                if ( isset( $params['parse'] ) ) {
                        $parse = true;
                }
                $sort = false;
                if ( isset( $params['sort'] ) ) {
                        $sort = true;
                }
                $lastItem = '';
                if ( isset( $params['lastitem'] ) ) {
                        $lastItem = $params['lastitem'];
                }
                $replace = '';
                if ( isset( $params['replace'] ) ) {
                        $replace = $params['replace'];
                }
                $template = '';
                if ( isset( $params['template'] ) ) {
                        $template = $params['template'];
                }
                $propReplace = '';
                if ( isset( $params['propreplace'] ) ) {
                        $propReplace = $params['propreplace'];
                }
                $propReplaceWith = '';
                if ( isset( $params['propreplacewith'] ) ) {
                        $propReplaceWith = $params['propreplacewith'];
                }
                $urlEncode = '';
                if ( isset( $params['urlencode'] ) ) {
                        $urlEncode = $params['urlencode'];
                }
                if ( isset( $input ) ) {
                        if ( $input ) {
                                $delimiter = $input;
                        }
                }
                $exclude = '';
                if ( isset( $params['exclude'] ) ) {
                        $exclude = $params['exclude'];
                }
                $stripRefTags = false;
                if ( isset( $params['stripreftags'] ) ) {
                        $stripRefTags = true;
                }
                // By default, only return one element
                $number = 1;
                if ( isset( $params['number'] ) ) {
                        if ( is_numeric( $params['number'] ) ) {
                                $number = $params['number'];
                        }
                }
                // Capitalize the first letter in the category argument, convert spaces to _
                $params['cat'] = str_replace ( ' ', '_', ucfirst( $params['cat'] ) );
                // Retrieve category members from database
                $dbr = wfGetDB( DB_SLAVE );
                $res = $dbr->select( 'categorylinks', 'cl_from',
                        array (
                               'cl_to' => $params['cat'],
                               'cl_type' => $type
                        )
                );
                $ids = array();
                if ( $res ) {
                        foreach ( $res as $row ) {
                                $ids[] = $row->cl_from;
                        }
                }
                if ( !$ids ) {
                        return '';
                }
                $excludeThese = explode ( ',', $exclude );
                foreach ( $excludeThese as $excludeThis ) {
                        $key = array_search( $excludeThis, $ids );
                        if( $key !== false ) {
                                unset( $ids[$key] );
                        }
                }
                $count = count ( $ids );
                // Don't try to return more elements than there are
                if ( $number > $count ) {
                        $number = $count;
                }
                // Randomly pick one or more elements
                $returnArr = array();
                while ( $number > 0 ) {
                        $randomId = $ids[array_rand ( $ids )];
                        // Make sure we don't pick the same element twice
                        $key = array_search( $randomId, $ids );
                        unset ( $ids[$key] );
                        $returnArr[] = $randomId;
                        $number--;
                }
                $firstElement = true;
                $output = '';
                if ( $sort ) {
                        sort ( $returnArr, SORT_STRING );
                }
                // If the content parameter is set, just return the contents of the first
                // randomly-chosen page
                if ( $content && $returnArr ) {
                        $titleValues = array_values( $returnArr );
                        $titleId = array_shift ( $titleValues );
                        $title = Title::newFromID ( $titleId );
                        $revision = Revision::newfromTitle ( $title );
                        $user = $parser->getUser ();
                        $content = $revision->getContent( Revision::FOR_PUBLIC, $user );
                        $output = ContentHandler::getContentText( $content );
                } else {
                        $outputArr = array();
                        foreach ( $returnArr as $returnArrElement ) {
                                $title = Title::newFromID( $returnArrElement );
                                $outputArr[] = $title->getFullText();
                        }
                        sort ( $outputArr );
                        foreach ( $outputArr as $outPutArrElement ) {
                                if ( $firstElement ) {
                                        $output .= $firstItem;
                                } else {
                                        $output .= $delimiter;
                                }
                                $output .= $outPutArrElement;
                                $firstElement = false;
                        }
                        $output .= $lastItem;
                }
                if ( $template ) {
                        $templateTitle = Title::newFromText ( $template );
                        if ( !$templateTitle || !$templateTitle->exists() ) {
                                $output = "Error: Template [[$template]] not found";
                        } else {
                                $revision = Revision::newFromTitle ( $templateTitle );
                                $user = $parser->getUser ();
                                $content = $revision->getContent( Revision::FOR_PUBLIC, $user );
                                $output = ContentHandler::getContentText( $content );
                        }
                }
                if ( $propReplace && $propReplaceWith ) {
                        $replaceWith = BedellPenDragon::renderGetBpdProp ( $parser,
                                $title->getFullText(), $propReplaceWith, true, true );
                        if ( $stripRefTags ) {
                                $replaceWith = BedellPenDragon::stripRefTags ( $replaceWith );
                        }
                        $output = str_replace ( $propReplace, $replaceWith, $output );
                }
                if ( $replace ) {
                        $output = str_replace ( $replace, $title->getFullText(), $output );
                }
                if ( $parse ) {
                        $output = $parser->internalParse ( $output );
                }
                if ( $urlEncode ) {
                        $output = str_replace ( $urlEncode, $title->getPrefixedUrl(), $output );
                }
                return $output;
        }

        // Hacky way of getting around the <ref> tag glitch. Hopefully your text doesn't include
        // "UNIQ" anywhere
        public static function stripRefTags( $string ) {
                while ( $beginning = strpos ( $string, 'UNIQ' ) ) {
                        $beginning--;
                        $end = $beginning + 40;
                        $string = substr ( $string, 0, $beginning )
                                . substr ( $string, $end, strlen ( $string ) - $end );
                }
                return $string;
        }
}
