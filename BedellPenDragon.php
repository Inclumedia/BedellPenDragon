<?php
/**
 * BedellPenDragon MediaWiki extension.
 *
 * Adds <randompageincat> tag and {{#setbpdprop ... }} and {{#getbpdprop ...}} parser functions
 * and Special:Glossary, Special:EssayList, Special:MiscellanyList, Special:QuoteList and
 * Special:VideoList
 *
 * Written by Leucosticte
 * https://www.mediawiki.org/wiki/User:Leucosticte
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Extensions
 */

if( !defined( 'MEDIAWIKI' ) ) {
        echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
        die( 1 );
}

$wgExtensionCredits['parserhook'][] = array(
        'path' => __FILE__,
        'name' => 'BedellPenDragon',
        'author' => 'Nathan Larson',
        'url' => 'https://mediawiki.org/wiki/Extension:BedellPenDragon',
        'descriptionmsg' => 'bedellpendragon-desc',
        'version' => '1.0.12'
);

$wgExtensionMessagesFiles['BedellPenDragon'] = __DIR__ . '/BedellPenDragon.i18n.php';
$wgAutoloadClasses['BedellPenDragon'] = __DIR__ . '/BedellPenDragon.classes.php';
$wgAutoloadClasses['SpecialGlossary'] = __DIR__ . '/SpecialGlossary.php';
$wgAutoloadClasses['SpecialEssayList'] = __DIR__ . '/SpecialEssayList.php';
$wgAutoloadClasses['SpecialMiscellanyList'] = __DIR__ . '/SpecialMiscellanyList.php';
$wgAutoloadClasses['SpecialQuoteList'] = __DIR__ . '/SpecialQuoteList.php';
$wgAutoloadClasses['SpecialVideoList'] = __DIR__ . '/SpecialVideoList.php';
$wgExtensionFunctions[] = "BedellPenDragon::randomPageInCatSetHook";
$wgHooks['ParserFirstCallInit'][] = 'BedellPenDragon::setupParserFunctions';
$wgHooks['RefCallback'][] = 'BedellPenDragon::refCallback';

define( 'BPD_NOPROPSET', 'No prop set!' );
$wgBedellPenDragonResident = true;
$wgBedellPenDragonDisableRef = false;
$wgBedellPenDragonGlossaryIntros = array (
        'Glossary' => '{{MediaWiki:Glossary-intro}}',
        'EssayList' => '{{MediaWiki:Essaylist-intro}}',
        'MiscellanyList' => '{{MediaWiki:Miscellanylist-intro}}',
        'QuoteList' => '{{MediaWiki:Quotelist-intro}}',
        'VideoList' => '{{MediaWiki:Videolist-intro}}'
);
$wgBedellPenDragonGlossaryTitlePropnames = array (
        'Glossary' => 'bpd_page_title',
        'EssayList' => 'bpd_essay_title',
        'MiscellanyList' => 'bpd_miscellany_title',
        'QuoteList' => 'bpd_quote_title',
        'VideoList' => 'bpd_video_title',
);
$wgBedellPenDragonGlossarySummaryPropnames = array (
        'Glossary' => 'bpd_short_summary',
        'EssayList' => 'bpd_essay_summary',
        'MiscellanyList' => 'bpd_miscellany_summary',
        'QuoteList' => 'bpd_quote_summary',
        'VideoList' => 'bpd_video_summary'
);
$wgBedellPenDragonGlossaryWikifyTitles = array (
        'Glossary' => false,
        'EssayList' => true,
        'MiscellanyList' => true,
        'QuoteList' => true,
        'VideoList' => true
);
$wgBedellPenDragonGlossaryStripFromFront = array (
        'Glossary' => '',
        'EssayList' => 'Essay:',
        'MiscellanyList' => 'Miscellany:',
        'QuoteList' => 'Quote:',
        'VideoList' => 'Video:'
);
$wgBedellPenDragonGlossaryReplace = array (
        'Glossary' => array ( "\n\n" => "\n\n:" ),
        'EssayList' => array ( "\n\n" => "\n\n:" ),
        'MiscellanyList' => array ( "\n\n" => "\n\n:" ),
        'QuoteList' => array ( "\n\n" => "\n\n:" ),
        'VideoList' => array ( "\n\n" => "\n\n:" )
);
$wgBedellPenDragonGlossaryAuthor = array (
        'Glossary' => false,
        'EssayList' => false,
        'MiscellanyList' => false,
        'QuoteList' => 'bpd_quote_author',
        'VideoList' => 'bpd_video_author'
);
$wgBedellPenDragonGlossaryByline = array (
        'Glossary' => false,
        'EssayList' => 'bpd_essay_author',
        'MiscellanyList' => false,
        'QuoteList' => false,
        'VideoList' => false
);
$wgSpecialPages['Glossary'] = 'SpecialGlossary';
$wgSpecialPages['EssayList'] = 'SpecialEssayList';
$wgSpecialPages['MiscellanyList'] = 'SpecialMiscellanyList';
$wgSpecialPages['QuoteList'] = 'SpecialQuoteList';
$wgSpecialPages['VideoList'] = 'SpecialVideoList';
$wgSpecialPageGroups['Glossary'] = 'other';
$wgSpecialPageGroups['EssayList'] = 'other';
$wgSpecialPageGroups['MiscellanyList'] = 'other';
$wgSpecialPageGroups['VideoList'] = 'other';
