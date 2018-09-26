<?php

class BruteSqliteSearch extends SearchSqlite {
	/**
	 * Parse the user's query and transform it into an SQL fragment which will
	 * become part of a WHERE clause
	 *
	 * @param string $filteredText
	 * @param bool $fulltext
	 * @return string
	 */
	function parseQuery( $filteredText, $fulltext ) {
		global $wgContLang;
		$lc = $this->legalSearchChars( self::CHARS_NO_SYNTAX ); // Minus syntax chars (" and *)
		$searchon = '';
		$this->searchTerms = [];

		$m = [];
		if ( preg_match_all( '/([-+<>~]?)(([' . $lc . ']+)(\*?)|"[^"]*")/',
				$filteredText, $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $bits ) {
				Wikimedia\suppressWarnings();
				list( /* all */, $modifier, $term, $nonQuoted, $wildcard ) = $bits;
				Wikimedia\restoreWarnings();

				if ( $nonQuoted != '' ) {
					$term = $nonQuoted;
					$quote = '';
				} else {
					$term = str_replace( '"', '', $term );
					$quote = '"';
				}

				if ( $searchon !== '' ) {
					$searchon .= ' ';
				}

				// Some languages such as Serbian store the input form in the search index,
				// so we may need to search for matches in multiple writing system variants.
				$convertedVariants = $wgContLang->autoConvertToAllVariants( $term );
				if ( is_array( $convertedVariants ) ) {
					$variants = array_unique( array_values( $convertedVariants ) );
				} else {
					$variants = [ $term ];
				}

				// The low-level search index does some processing on input to work
				// around problems with minimum lengths and encoding in MySQL's
				// fulltext engine.
				// For Chinese this also inserts spaces between adjacent Han characters.
				$strippedVariants = array_map(
					[ $wgContLang, 'normalizeForSearch' ],
					$variants );

				// Some languages such as Chinese force all variants to a canonical
				// form when stripping to the low-level search index, so to be sure
				// let's check our variants list for unique items after stripping.
				$strippedVariants = array_unique( $strippedVariants );

				$searchon .= $modifier;
				if ( count( $strippedVariants ) > 1 ) {
					$searchon .= '(';
				}
				foreach ( $strippedVariants as $stripped ) {
					if ( $nonQuoted && strpos( $stripped, ' ' ) !== false ) {
						// Hack for Chinese: we need to toss in quotes for
						// multiple-character phrases since normalizeForSearch()
						// added spaces between them to make word breaks.
						$stripped = '"' . trim( $stripped ) . '"';
					}
					$searchon .= "$quote%$stripped%$quote$wildcard";
				}
				if ( count( $strippedVariants ) > 1 ) {
					$searchon .= ')';
				}

				// Match individual terms or quoted phrase in result highlighting...
				// Note that variants will be introduced in a later stage for highlighting!
				$regexp = $this->regexTerm( $term, $wildcard );
				$this->searchTerms[] = $regexp;
			}

		} else {
			wfDebug( __METHOD__ . ": Can't understand search query '{$filteredText}'\n" );
		}

		$searchon = $this->db->addQuotes( $searchon );
		$field = $this->getIndexField( $fulltext );

		$field = str_replace('si_title','page_title',$field);
		$field = str_replace('si_text','old_text',$field);

		return " $field LIKE $searchon ";
	}

	/**
	 * Get the base part of the search query.
	 *
	 * @param string $filteredTerm
	 * @param bool $fulltext
	 * @return string
	 */
	function queryMain( $filteredTerm, $fulltext ) {
		$match = $this->parseQuery( $filteredTerm, $fulltext );
		$page = $this->db->tableName( 'page' );
		$searchindex = $this->db->tableName( 'text' );
		$query_str = "SELECT page_id, page_namespace, page_title " .
			"FROM page join revision on page_latest = rev_id join text on rev_text_id = old_id " .
			"WHERE $match";
		return $query_str;
	}

	function getCountQuery( $filteredTerm, $fulltext ) {
		$match = $this->parseQuery( $filteredTerm, $fulltext );
		$page = $this->db->tableName( 'page' );
		$searchindex = $this->db->tableName( 'text' );
		return "SELECT COUNT(*) AS c " .
			"FROM page join revision on page_latest = rev_id join text on rev_text_id = old_id " .
			"WHERE $match " .
			$this->queryNamespaces();
	}
}
?>
