<?php

function grid_json($rows = array(), $columns = array(), $editable_rows = array())
{
	$data = array();

	$_header = array();


	// Create dummy data to prevent
	// the datatable library from getting
	// an error due to empty AJAX response.
	if (!$rows and $columns) {

		$_rows = array();
		foreach ($columns as $column) {
			$_rows[$column] = '';
		}

		$rows = array(
			(object) $_rows
		);
	}


	if ($rows) {
		
		if (isset($rows[0])) {

			$header = (array) $rows[0];
			$_header = array_keys($header);
			$_editable = array_flip($_header);

			foreach ($_header as $head) {

				// Conver underscore to spaces
				$_head = str_replace('_', ' ', $head);

				// For header
				$data['header'][] = array(
					'title' => ucwords($_head)
				);


				// For editable columns
				if (in_array($head, $editable_rows)) {
					$data['editable'][] = $_editable[$head];
				}

			}
		}

		// For row data
		$_rows = array();

		foreach ($rows as $row) {

			$_row = (array) $row;

			$_rows[] = array_values($_row);
		}

		$data['rows'] = $_rows;
	}

	return $data;
}

/**
 * Converts row data to graphical data.
 * 
 * @param  array  $rows       Row object that is expected
 *                            to contain properties x_axis (label) 
 *                            and y_axis (data to plot with respect
 *                            to the `x_axis` label).
 *                            
 * @param  array  $parameters Can contain the following acceptable info:
 *                            `title` of the graph.
 *                            `tooltip` label when hovering the plotted data.
 *                            `x_axis` label
 *                            `y_axis` label
 *                            
 *                            `days` in a month (to filter out date 
 *                            related graph) [OPTIONAL]
 * 
 * @return json
 */
function grid_data($rows = array(), $parameters = array())
{
	if ($rows) {

		foreach ($rows as $row) {

			$data['label'][] = $row->x_axis;
			$data['data'][] = $row->y_axis;
		}
	}

	if ($parameters) {

		foreach ($parameters as $parameter => $value) {

			$data[$parameter] = is_string($value) ? strtoupper($value) : $value;
		}
	}

	return json_encode($data);
}