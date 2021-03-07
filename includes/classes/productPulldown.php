<?php

	class productPulldown extends pulldown
	{
		private $allowed_sort_array = ['products_name', 'products_model',];
		private $db_table_prefix_array = ['pd.', 'p.'];

		function __construct()
		{
			parent::__construct();

			$this->show_model = false;
			$this->show_price = false;
			$this->set_selected = 0;
			$this->categories_join = '';

			$this->sort = ' ORDER BY pd.products_name';

			$this->keyword_search_fields = [
				'pd.products_name',
				'p.products_model',
				'pd.products_description',
				'p.products_id',
			];
		}

		public function setSort($fieldnameArray)
		{
		    if (empty($fieldnameArray)) {
		        return $this;
            }

		    $first = true;
		    $this->sort = '';
		    foreach ($fieldnameArray as $fieldname) {
                if (in_array($fieldname, $this->allowed_sort_array)) {
                    $key = array_search($fieldname, $this->allowed_sort_array);
                    $this->sort = ($first ? ' ORDER BY ' : ', ') . $this->db_table_prefix_array[$key] . $fieldname;
                    $first = false;
                }
            }
			return $this;
		}

		public function setCategory(int $category_id)
		{
			$this->categories_join = " LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc ON (ptc.products_id = p.products_id)";
			$this->condition .= " AND ptc.categories_id = " . (int)$category_id;
			return $this;
		}

		public function showModel()
		{
			$this->show_model = true;
			return $this;
		}

		public function showPrice()
		{
			$this->show_price = true;
			return $this;
		}

		public function onlyActive()
		{
			$this->condition .= " AND p.products_status = 1";
			return $this;
		}

		function setSQL()
		{
			$this->sql = "SELECT DISTINCT pd.products_id, p.products_sort_order, p.products_price, p.products_model
                FROM " . TABLE_PRODUCTS . " p"
                . $this->categories_join . "
                INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id) 
				" . $this->attributes_join . "
				WHERE pd.language_id = " . (int)$_SESSION['languages_id'];
		}


		function processSQL()
		{
			global $currencies;

			$this->setSQL();
			$this->runSQL();

			$parm_2 = '';
			$parm_3 = '';

			if ($this->show_model) {
				$parm_2 = '%2$s';
			}

			if ($this->show_price) {
			   $parm_3 = ' (%3$s)';
            }

			$this->output_string = '%1$s ' . $parm_2 . $parm_3; // format string with name first

			if (strpos($this->sort, 'model')) {
				$this->output_string = (!empty($parm_2) ? $parm_2 . '-' : '') . ' %1$s' . $parm_3; // format string with model first
			}


			foreach ($this->results as $result) {
				if (in_array($result['products_id'], $this->exclude)) {
					continue;
				}
				$display_price = $this->showPrice() ? zen_get_products_base_price($result['products_id']) : '';
				$name = zen_get_products_name($result['products_id']);
				$this->values[] = [
					'id' => $result['products_id'],
					'text' => sprintf($this->output_string, trim(zen_clean_html($name)),
							($this->show_model ? ' [' . $result['products_model'] . '] ' : ''),
							$currencies->format($display_price)) . ($this->show_id ? ' - ID# ' . $result['products_id'] : ''),
				];
			}
		}
	}