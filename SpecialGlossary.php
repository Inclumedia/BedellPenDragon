<?php
if ( !defined( 'MEDIAWIKI' ) ) {
   die( 'This file is a MediaWiki extension. It is not a valid entry point' );
}

class SpecialGlossary extends SpecialPage {
   function __construct( $name = 'Glossary' ) {
      parent::__construct( $name );
   }

   function execute( $par ) {
      global $wgBedellPenDragonGlossaryIntros,
         $wgBedellPenDragonGlossaryTitlePropnames,
         $wgBedellPenDragonGlossarySummaryPropnames,
         $wgBedellPenDragonGlossaryWikifyTitles,
         $wgBedellPenDragonGlossaryStripFromFront,
         $wgBedellPenDragonGlossaryReplace,
         $wgBedellPenDragonGlossaryAuthor,
         $wgBedellPenDragonGlossaryByline;
      $titlePropname = $wgBedellPenDragonGlossaryTitlePropnames[self::getName()];
      $intro = $wgBedellPenDragonGlossaryIntros[self::getName()];
      $wikifyTitle = $wgBedellPenDragonGlossaryWikifyTitles[self::getName()];
      $summaryPropname = $wgBedellPenDragonGlossarySummaryPropnames[self::getName()];
      $stripFromFront = $wgBedellPenDragonGlossaryStripFromFront[self::getName()];
      $replace = $wgBedellPenDragonGlossaryReplace[self::getName()];
      $author = $wgBedellPenDragonGlossaryAuthor[self::getName()];
      $byline = $wgBedellPenDragonGlossaryByline[self::getName()];
      $this->setHeaders();
      $viewOutput = $this->getOutput();
      $viewOutput->setRobotPolicy ( 'index, follow' );

      // Display introductory material
      $output = $intro . "\n";
      // Get list of pages in glossary
      $dbr = wfGetDB( DB_SLAVE );
      $res = $dbr->select( 'page_props', array ( 'pp_page', 'pp_value' ),
         array (
            'pp_propname' => $titlePropname
         )
      );
      // Get page titles
      $titles = array();
      if ( $res ) {
         foreach ( $res as $row ) {
            $titles[$row->pp_page] = $row->pp_value;
         }
      }
      // Get displaytitles
      $displayTitles = array();
      $res = $dbr->select ( 'page_props', array ( 'pp_page', 'pp_value' ),
         array ( 'pp_propname' => 'displaytitle' ) );
      if ( $res ) {
         foreach ( $res as $row ) {
            $displayTitles[$row->pp_page] = $row->pp_value;
         }
      }
      // Get authors
      $authors = array();
      if ( $author || $byline ) {
         if ( $author ) {
            $authorProp = $author;
         } else {
            $authorProp = $byline;
         }
         $res = $dbr->select ( 'page_props', array ( 'pp_page', 'pp_value' ),
         array ( 'pp_propname' => $authorProp ) );
         if ( $res ) {
            foreach ( $res as $row ) {
               if ( isset ( $titles[$row->pp_page] ) ) {
                  $authors[$row->pp_page] = $row->pp_value;
               }
            }
         }
      }
      // Get summaries
      $res = $dbr->select( 'page_props', array ( 'pp_page', 'pp_value' ),
         array (
            'pp_propname' => $summaryPropname
         )
      );
      // Match up summaries to titles
      $summaries = array();
      $authorList = array();
      $renderDisplayTitles = array();
      if ( $res ) {
         foreach ( $res as $row ) {
            $id = $row->pp_page;
            if ( isset ( $titles[$id] ) ) {
               if ( isset ( $displayTitles[$id] ) ) {
                  $renderDisplayTitles[$titles[$id]] = $displayTitles[$id];
               } else {
                  $renderDisplayTitles[$titles[$id]] = $titles[$id];
               }
               if ( $author ) {
                  $summaries[$authors[$id]][$titles[$id]] = $row->pp_value;
               } else {
                  $summaries[$titles[$id]] = $row->pp_value;
               }
               if ( $byline ) {
                  $authorList[$titles[$id]] = $authors[$id];
               }
            }
         }
      }
      if ( !$summaries ) {
         $output .= "\n\n" . wfMessage ( 'bedellpendragon-noitems' );
      }
      if ( $author ) {
         foreach ( $summaries as $thisAuthor => $summary ) {
            ksort ( $summary );
            $summaries[$thisAuthor] = $summary;
         }
      }
      ksort ( $summaries );
      // Generate the glossary
      if ( $author ) {
         $currentAuthor = '';
         $currentLetter = '';
         foreach ( $summaries as $thisAuthor => $summary ) {
            $firstLetter = substr ( $thisAuthor, 0, 1 );
            if ( $firstLetter != $currentLetter ) {
               $currentLetter = $firstLetter;
               $output .= "==$firstLetter==\n";
            }
            if ( $thisAuthor != $currentLetter ) {
               $currentAuthor = $thisAuthor;
               $output .= "===$thisAuthor===\n";
            }
            $output .= SpecialGlossary::processSummaries ( $summary, $renderDisplayTitles,
               $stripFromFront, $wikifyTitle, $replace, false, $authorList );
         }
      } else {
         $output .= SpecialGlossary::processSummaries ( $summaries, $renderDisplayTitles,
            $stripFromFront, $wikifyTitle, $replace, true, $authorList );
      }
      $viewOutput->addWikiText( $output );
   }

   // Process the bottom levels of the hierarchy
   function processSummaries ( $summaries, $renderDisplayTitles, $stripFromFront, $wikifyTitle,
      $replace, $topLevel, $authorList ) {
      $output = '';
      $currentLetter = '';
      foreach ( $summaries as $title => $summary ) {
         $summary = BedellPenDragon::stripRefTags( $summary );
         $firstLetter = substr ( $title, strlen ( $stripFromFront ), 1 );
         if ( $firstLetter !== false && $topLevel ) {
            if ( $firstLetter != $currentLetter ) {
               $currentLetter = $firstLetter;
               $output .= "==$firstLetter==\n";
            }
         }
         $output .= ';{{Anchor|' . $title . '}}';
         if ( substr ( $renderDisplayTitles[$title], 0, strlen ( $stripFromFront ) ) ==
            $stripFromFront ) {
            $renderDisplayTitles[$title] = substr ( $renderDisplayTitles[$title],
               strlen ( $stripFromFront ), strlen ( $renderDisplayTitles[$title] ) -
               strlen ( $stripFromFront ) );
         }
         if ( $wikifyTitle ) {
            $output .= "''[[" . $title . '|' . $renderDisplayTitles[$title] . "]]''";
         } else {
            $output .= $renderDisplayTitles[$title];
         }
         if ( isset ( $authorList[$title] ) ) {
            if ( $authorList[$title] ) {
               $output .= wfMessage ( 'bedellpendragon-byline' )->plain() . $authorList[$title];
            }
         }
         foreach ( $replace as $replaceThis => $replaceThat ) {
            $summary = str_replace ( $replaceThis, $replaceThat, $summary );
         }
         $output .= "\n:$summary\n";
      }
      return $output;
   }
}
