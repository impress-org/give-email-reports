<?php

/**
 * @package     Give_Email_Reports
 * @subpackage  Stats
 * @copyright   Copyright (c) 2019, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.1.3
 */
class Give_Email_Report_Donation_Stats extends Give_Donation_Stats {

	/**
	 * Give_Annual_Report_Donation_Stats constructor.
	 * @since 1.1.3
	 *
	 * @param array $query
	 */
	public function __construct( array $query = array() ) {
		parent::__construct( $query );
	}

	/**
	 * Get donation forms orders with last donation date
	 * @since 1.1.3
	 *
	 * @param array $query
	 *
	 * @return stdClass
	 */
	public function get_cold_donation_forms( $query = array() ) {
		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']  = $this->get_db()->posts;
		$this->query_vars['column'] = 'ID';

		$this->pre_query( $query );

		if ( $cache = $this->get_cache() ) {
			$this->reset_query();

			return $cache;
		}

		$column             = "{$this->query_vars['table']}.{$this->query_vars['column']}";
		$meta_table_counter = 'm' . $this->get_counter( $this->get_db()->donationmeta );

		$this->sql = "
			SELECT DISTINCT CAST( {$meta_table_counter}.meta_value as SIGNED ) as form, {$column}, {$this->query_vars['table']}.post_date as date
			FROM {$this->query_vars['table']}
			INNER JOIN {$this->get_db()->donationmeta} as {$meta_table_counter} ON {$meta_table_counter}.{$this->get_donation_id_column($this->get_db()->donationmeta)}={$this->query_vars['table']}.{$this->get_donation_id_column()}
			{$this->query_vars['where_sql']}
			AND {$meta_table_counter}.meta_key='_give_payment_form_id'
			GROUP BY form
			ORDER BY YEAR(date), MONTH(date), DAY(date)
			{$this->query_vars['limit_sql']}
		";

		$results         = new stdClass();
		$results->result = $this->get_db()->get_results( $this->sql );

		// Reset query vars.
		$this->build_result( $results );
		$this->reset_query();

		return $results;
	}
}
