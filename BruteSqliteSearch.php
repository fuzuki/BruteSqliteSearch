<?php

/**
 * Full text search using SQL 'LIKE' statement for SQLite3
 * Based on BruteSearch(http://macwiki.osdn.jp/wiki/index.php/MacWiki:MediaWiki%E3%81%AE%E3%82%A4%E3%83%B3%E3%82%B9%E3%83%88%E3%83%BC%E3%83%AB)
 *
 * @addtogroup Extensions
 * @author Fuzuki <fuzuki@hiruandon.net>
 * @copyright © 2018 Fuzuki
 * @licence GNU General Public Licence 2.0+
 */

if( !defined( 'MEDIAWIKI' ) ) {
        echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
        die( 1 );
}
$wgExtensionCredits['other'][] = array(
        'path' => __FILE__,
        'name' => 'BruteSqliteSearch',
        'author' => 'fuzuki',
        'version' => '1.31-20180918',
        'url' => 'https://github.com/fuzuki/BruteSqliteSearch',
        'description' => 'Full text search using SQL \'LIKE\' statement',
);
$wgAutoloadClasses['BruteSqliteSearch'] = dirname( __FILE__ ) . '/BruteSqliteSearch.class.php';

?>
