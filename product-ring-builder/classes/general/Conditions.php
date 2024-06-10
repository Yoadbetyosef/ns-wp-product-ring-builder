<?php
namespace OTW\GeneralWooRingBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor conditions.
 *
 * Elementor conditions handler class introduce the compare conditions and the
 * check conditions methods.
 *
 * @since 1.0.0
 */
class Conditions {
	/**
	 * Compare conditions.
	 *
	 * Whether the two values comply the comparison operator.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param mixed  $left_value  First value to compare.
	 * @param mixed  $right_value Second value to compare.
	 * @param string $operator    Comparison operator.
	 *
	 * @return bool Whether the two values complies the comparison operator.
	 */
	public static function compare( $left_value, $right_value, $operator ) {
		switch ( $operator ) {
			case '==':
				return $output .= 'data.' . $left_value . ' ' . $operator . ' \'' . $right_value . '\'';
			default:
				return $output .= 'data.' . $left_value . ' ' . $operator . ' \'' . $right_value . '\'';
		}
	}

	public static function get_vue_conditions( array $conditions ) {
		$output = '';

		if ( $conditions && is_array( $conditions ) && count( $conditions ) >= 1 ) {
			$i = 1;

			$last_condition_relation = ' && ';
			if ( isset( $conditions['relation'] ) ) {
				$condition_relation = trim( $conditions['relation'] );
				if ( $conditions['relation'] === '||' || $conditions['relation'] == 'or' || $conditions['relation'] == 'OR' ) {
					$last_condition_relation = ' || ';
				}
			}

			foreach ( $conditions as $key => $attribue ) {
				if ( $key == 'relation' ) {
					continue;
				}

				if ( $key == 'terms' ) {

					$term_output = '';
					$term_before_relation = $last_condition_relation;
					if ( isset( $attribue['before_relation'] ) ) {
						$term_before_relation = ' && ';
						$term_before_relation_ = trim( $attribue['before_relation'] );
						if ( $term_before_relation_ === '||' || $term_before_relation_ == 'or' || $term_before_relation_ == 'OR' ) {
							$term_before_relation = ' || ';
						}
					}

					if ( isset( $attribue['relation'] ) ) {
						$term_relation = ' && ';
						$term_relation_ = trim( $attribue['relation'] );
						if ( $term_relation_ === '||' || $term_relation_ == 'or' || $term_relation_ == 'OR' ) {
							$term_relation = ' || ';
						}
					}

					if ( isset( $attribue['terms'] ) && is_array( $attribue['terms'] ) && count( $attribue['terms'] ) >= 1 ) {
						$j = 1;
						foreach ( $attribue['terms'] as $single_term ) {

							if ( isset( $single_term['operator'] ) && ! empty( $single_term['operator'] ) && isset( $single_term['name'] ) && isset( $single_term['value'] ) ) {
								$term_single_output = self::compare( $single_term['name'], $single_term['value'], $single_term['operator'] );

								if ( $term_single_output ) {
									if ( $j == 1 ) {
										$term_output .= '(';
									}

									if ( $j >= 2 ) {
										$term_output .= $term_relation;
									}
									$term_output .= $term_single_output;
									++$j;
								}
							}
						}
						if ( $term_output ) {
							$term_output .= ')';
						}
					}
					if ( $term_output ) {
						if ( $i >= 2 ) {
							$output .= $term_before_relation;
						}
						$output .= $term_output;
						++$i;
					}
				} else {

					$operator = ' == ';
					if ( ( $key || $key == '0' ) && ! is_array( $attribue ) ) {
						if ( substr( 'testers', -1 ) == '!' ) {
							$operator = ' != ';
						}
						if ( $i >= 2 ) {
							$output .= $last_condition_relation;
						}
						$output .= 'data.' . $key . $operator . '\'' . $attribue . '\'';
						++$i;
					}
				}
				++$i;
			}
		}
		return $output;
	}
}
