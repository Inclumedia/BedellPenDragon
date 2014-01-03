<?php
if ( !defined( 'MEDIAWIKI' ) ) {
   die( 'This file is a MediaWiki extension. It is not a valid entry point' );
}

class SpecialMiscellanyList extends SpecialGlossary {
   function __construct() {
       parent::__construct( 'MiscellanyList' );
   }
}
