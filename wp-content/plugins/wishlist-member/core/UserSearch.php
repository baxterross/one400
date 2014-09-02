<?php

if (!defined('ABSPATH'))
	die();
if (!class_exists('WishListMemberUserSearch')) {
	require_once(ABSPATH . '/wp-admin/includes/user.php');
	if (get_bloginfo('version') >= 3) { //start check for wp3
		if (!class_exists('WP_User_Search')) {
			require_once(ABSPATH . '/wp-admin/includes/deprecated.php');
		}

		class WishListMemberUserSearch extends WP_User_Search {

			function WishListMemberUserSearch($search_term = '', $page = '', $role = '', $ids = '', $sortby = '', $sortorder = '', $howmany = 15) {
				if (is_array($ids)) {
					$this->IDs = (array) $ids;
					$this->IDs[] = 0;
					$this->IDs = array_unique($this->IDs);
				} else {
					$this->IDs = '';
				}
				$this->SortBy = $sortby;
				$this->SortOrder = $sortorder ? $sortorder : 'ASC';
				$this->users_per_page = $howmany;
				$this->WP_User_Search($search_term, $page, $role);
			}

			function prepare_query() {
				global $wpdb;

				$this->first_user = ($this->page - 1) * $this->users_per_page;

				$this->query_limit = $wpdb->prepare(" LIMIT %d, %d", $this->first_user, $this->users_per_page);

				$search_sql = '';
				if ($this->search_term) {
					$searches = array();
					$search_sql = 'AND (';
					foreach (array('user_login', 'user_nicename', 'user_email', 'user_url', 'display_name') as $col)
						$searches[] = $col . " LIKE '%$this->search_term%'";
					$search_sql .= implode(' OR ', $searches);
					$search_sql .= ')';
				}

				$this->query_from = " FROM $wpdb->users";
				$this->query_where = " WHERE 1=1 $search_sql";

				if ($this->IDs) {
					$x = $this->IDs;
					if ((string) $x[0] == '-') {
						unset($x[0]);
						$in = 'NOT IN';
					} else {
						$in = 'IN';
					}
					$key = array_search('', $x, true);
					if ($key !== false)
						unset($x[$key]);
					$x = implode(',', $x);
					$this->query_where.=" AND `ID` {$in} ({$x}) ";
				}

				if ($this->SortBy) {
					$this->query_orderby = " ORDER BY {$this->SortBy} {$this->SortOrder}";
				}

				if (!$this->users_per_page)
					$this->query_limit = '';
			}

		}

		// end check for wp3
	}else {

		// start wp2.9- this section is for wp2.9 -

		class WishListMemberUserSearch extends WP_User_Search {

			function WishListMemberUserSearch($search_term = '', $page = '', $role = '', $ids = '', $sortby = '', $sortorder = '', $howmany = 15) {
				if (is_array($ids)) {
					$this->IDs = (array) $ids;
					$this->IDs[] = 0;
					$this->IDs = array_unique($this->IDs);
				} else {
					$this->IDs = '';
				}
				$this->SortBy = $sortby;
				$this->SortOrder = $sortorder ? $sortorder : 'ASC';

				$this->users_per_page = $howmany;

				$this->WP_User_Search($search_term, $page, $role);
			}

			function prepare_query() {
				parent::prepare_query();
				if ($this->IDs) {
					$x = $this->IDs;
					if ((string) $x[0] == '-') {
						unset($x[0]);
						$in = 'NOT IN';
					} else {
						$in = 'IN';
					}
					$key = array_search('', $x, true);
					if ($key !== false)
						unset($x[$key]);
					$x = implode(',', $x);
					$this->query_from_where.=" AND `ID` {$in} ({$x}) ";
				}
				if ($this->SortBy) {
					$this->query_sort = " ORDER BY {$this->SortBy} {$this->SortOrder}";
				}
				if (!$this->users_per_page)
					$this->query_limit = '';
			}

		}

		// end wp2.9
	}
}
?>