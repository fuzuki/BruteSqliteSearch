==BruteSqliteSearch==
 MediawikiのSQLite用拡張。
 主に日本語検索のために、LIKE句を使った力ずくの全文検索。

==参考==
http://macwiki.osdn.jp/wiki/index.php/MacWiki:MediaWiki%E3%81%AE%E3%82%A4%E3%83%B3%E3%82%B9%E3%83%88%E3%83%BC%E3%83%AB

==Usage==
 Insert BruteSqliteSearch folder into extentions.

[LocalSettings.php]
...
require_once("$IP/extensions/BruteSqliteSearch/BruteSqliteSearch.php");
$wgSearchType       = "BruteSqliteSearch";
#$wgDisableSearchUpdate = true;
