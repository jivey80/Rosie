<?php

namespace App\Libraries;


class Calc
{
	/**
	 * Checks and generates adjacent price 
	 * computation.
	 * 
	 * @param  array  $slotlist
	 * @param  array  $affected
	 */
	public static function adjacent($slotlist = array(), $affected = array())
	{
		if ($slotlist and $affected) {

			$recalculated = array();

			foreach ($affected as $timetable_id => $slots) {

				$calc_adj_side = self::adjacent_side($slots);

				$calc_adj_rest = self::adjacent_rest($calc_adj_side);

				$calc_adj_balance = self::adjacent_rebalance($calc_adj_rest);

				$recalculated[$timetable_id] = self::strip_non_adjacent($calc_adj_balance);

			}

			return self::merge($slotlist, $recalculated);
		}

		return $slotlist;
	}

	/**
	 * Basically just adding the new price
	 * for the adjacent slots to the booked slots.
	 */
	private static function adjacent_side($slots = array())
	{
		if ($slots) {

			// Set price of the adjacent slots (previous and next only) 
			// to the default pricing value
			foreach ($slots as $i => $slot) {

				if ($slot['is_booked']) {


					// Check if the travel fee can be applied
					// to the current slots.
					$travel_flag = isset($slot['travel']) ? $slot['travel'] : 0;
					$surge_rate  = 0;

					if (defined('TRAVEL_FEE') && defined('TRAVEL_FEE_TIME')) {

						if ($travel_flag >= TRAVEL_FEE_TIME) {

							$surge_rate = typecast(TRAVEL_FEE, 'float');
						}
					}


					$prev = isset($slots[$i - 1]) ? $slots[$i - 1] : false;
					
					if ($prev and !isset($prev['_flg_adjacent'])) {

						$prev_hours = typecast($prev['hours'], 'float');
						
						$slots[$i - 1]['travel'] = $travel_flag;
						$slots[$i - 1]['_flg_adjacent'] = 'modified_as_prev';
						$slots[$i - 1]['price'] = (PRICE_RATE_PER_HOUR * $prev_hours) + $surge_rate;
					}


					$next = isset($slots[$i + 1]) ? $slots[$i + 1] : false;
					
					if ($next and !isset($next['_flg_adjacent'])) {

						$next_hours = typecast($next['hours'], 'float');
						
						$slots[$i + 1]['travel'] = $travel_flag;
						$slots[$i + 1]['_flg_adjacent'] = 'modified_as_next';
						$slots[$i + 1]['price'] = (PRICE_RATE_PER_HOUR * $next_hours) + $surge_rate;
					}
				}
			}
		}

		return $slots;
	}

	/**
	 * Tags all the slots by adding flag on the 
	 * array to identify/guide the next process (fn::adjacent_rest_computation)
	 * how the price recomputation will be done.
	 */
	private static function adjacent_rest($slots = array())
	{

		if ($slots) {

			// LEVEL 1
			// --------------
			$groups = array();
			$order = 'reverse';
			foreach ($slots as $s => $slot) {

				$prev = isset($slots[$s - 1]) ? $slots[$s - 1] : false;
				$next = isset($slots[$s + 1]) ? $slots[$s + 1] : false;


				// One of the epic solution i've done
				// in the history. Feel free to delete.
				if ($slot['is_booked'] or isset($slot['_flg_adjacent'])) {

					if (in_array($order, array('normal', 'reverse'))) {

						$order = "not_included:{$order}";
					}
				}

				$slot['order'] = $order;
				
				$groups[$s] = $slot;

				if ($next and !$next['is_booked'] and !isset($next['_flg_adjacent'])) {

					if (! in_array($order, array('normal', 'reverse'))) {

						$order = (str_replace('not_included:', '', $order) === 'reverse') ? 'normal' : 'reverse';
					}
				}
				// ...
				// End of shit.
			}


			// LEVEL 2
			// --------------
			$regroup = array();
			$sub = array();
			$cnt = count($groups) - 1;
			$f = (array_values($groups)[0]);
			$o = $f['order'];

			foreach ($groups as $ctr => $group) {

				if (! isset($sub[$o])) {
					$sub[$o] = array();
				}


				if ($group['order'] == $o) {
				
					$sub[$o][] = $group;
				
				} else {

					$regroup[] = $sub;
					$sub = array();

					$o = $group['order'];
					$sub[$o][] = $group;
				}

				if ($cnt === $ctr) {
					$regroup[] = $sub;
				}
			}


			// LEVEL 3
			// --------------
			foreach ($regroup as $rgroup) {

				foreach ($rgroup as $type => $subgroup) {

					if ($type === 'reverse') {

						$reverse = array_reverse($subgroup);
						$subgroup = self::adjacent_rest_computation($reverse);

					} else if ($type === 'normal') {

						$subgroup = self::adjacent_rest_computation($subgroup);
					}

					// Modify the main slots array
					// with the recomputed slots data
					// from above.
					foreach ($subgroup as $sgroup) {

						$index = $sgroup['index'];
						$slots[$index] = $sgroup;
					}
				}
			}

		}

		return $slots;
	}

	/**
	 * Based from the tags inserted per slot array, 
	 * this function recomputes the prices adding the
	 * +5 USD rules to each slots.
	 */
	private static function adjacent_rest_computation($slots = array())
	{

		if ($slots) {

			foreach ($slots as $i => $slot) {

				if (!$slot['is_booked'] and !isset($slot['_flg_adjacent'])) {

					$hours = typecast($slot['hours'], 'float');

					$prev = isset($slots[$i - 1]) ? $slots[$i - 1] : false;

					$flag = isset($prev['_flg_adjacent']) ? typecast($prev['_flg_adjacent'], 'int') : 1;
					$increment = $flag ? $flag : 1;
					
					$surge_rate = PRICE_RATE_INCREMENT * $increment;
					$slots[$i]['price'] = (PRICE_RATE_PER_HOUR + $surge_rate) * $hours;
					$slots[$i]['_flg_adjacent'] = $increment + 1;
				}
			}

		}

		return $slots;
	}

	/**
	 * All of the process above will be reverified
	 * recursively comparing each prices from
	 * previous to next time slot calibrating 
	 * the price value if possible.
	 */
	private static function adjacent_rebalance($slots = array(), $run_in_recursion = true)
	{	
		if ($slots) {

			if ($run_in_recursion) {

				// Tracks the number of slots
				// modified while running this function
				// recursively.
				// 
				// No modification means the recursion must stop.
				$slot_modified = 0;

				foreach ($slots as $i => $slot) {

					if (strpos($slot['order'], 'not_included:') === false) {

						$prev = isset($slots[$i - 1]) ? $slots[$i - 1] : false;
						$next = isset($slots[$i + 1]) ? $slots[$i + 1] : false;

						$hours = typecast($slot['hours'], 'float');

						$price_prev = ($prev and isset($prev['price'])) ? typecast($prev['price'], 'float') / $hours : 0;
						$price_next = ($next and isset($next['price'])) ? typecast($next['price'], 'float') / $hours : 0;
						$price_now = typecast($slot['price'], 'float') / $hours;


						// Correct the price of slot based from 
						// the next slot price
						if (!$prev and $next) {

							if ($price_now > $price_next) {

								if (($price_now - $price_next) > PRICE_RATE_INCREMENT) {

									if (($price_now - PRICE_RATE_INCREMENT) >= PRICE_RATE_PER_HOUR) {

										$slots[$i]['price'] = ($price_now - PRICE_RATE_INCREMENT) * $hours;

										$slot_modified++;
									}
								}
							}
						}


						// Correct the price of slot based
						// from both previous and next slot price
						if ($prev and $next) {

							if ($price_now > $price_prev and $price_now > $price_next) {

								$offset_prev = $price_now - $price_prev;
								$offset_next = $price_now - $price_next;

								if ($offset_prev > PRICE_RATE_INCREMENT) {
									$slots[$i]['price'] = ($price_prev + PRICE_RATE_INCREMENT) * $hours;

									$slot_modified++;
								}

								if ($offset_next > PRICE_RATE_INCREMENT) {
									$slots[$i]['price'] = ($price_next + PRICE_RATE_INCREMENT) * $hours;

									$slot_modified++;
								}
							}
						}


						// Correct the price of slot based from
						// the previous slot price
						if ($prev and !$next) {
							
							if ($price_now == $price_prev) {

								$slots[$i]['price'] = ($price_prev + PRICE_RATE_INCREMENT) * $hours;

								$slot_modified++;
							}
						}
					}
				}


				if ($slot_modified > 0) {

					self::adjacent_rebalance($slots, true);
				
				} else {

					self::adjacent_rebalance($slots, false);
				}

			} else {

				return $slots;
			}
		}

		return $slots;
	}

	private static function strip_non_adjacent($slots = array())
	{

		// Get first all active slots which includes
		// booked and the adjacent non-booked slots.
		$active = array();

		if ($slots) {

			foreach ($slots as $index => $slot) {
				if (strpos($slot['order'], 'not_included:') !== false) {
					$active[$index] = $slot;
				}
			}
		}

		return $active;
	}

	/**
	 * Just simply merges all of the slot schedule
	 * array to get the final result to be
	 * displayed on the app.
	 */
	private static function merge($slotlist = array(), $updated_slots = array(), $is_simplified = true)
	{
		if ($slotlist and $updated_slots) {

			$new_slotlist = array();

			if ($is_simplified) {

				// Get all updated slots and drop the rest.
				$summary = array();
				$details = array();
				$filterd = array();

				foreach ($updated_slots as $timetable_id => $slots) {
					
					if (! isset($summary[$timetable_id])) {
						$summary[$timetable_id] = array();
						$filterd[] = $timetable_id; 
					}

					if (! isset($details[$timetable_id])) {
						$details[$timetable_id] = array();
					}

					foreach ($slots as $slot) {
						$group_id = $slot['group_id'];

						$summary[$timetable_id][] = $group_id;
						$details[$timetable_id][$group_id] = $slot;
					}
				}

				// Another foreach here
				foreach ($slotlist as $i_list => $listed) {

					$list_group_id = $listed['group_id'];

					$list_timetable_id = $listed['timetable_id'];


					if (isset($summary[$list_timetable_id], $details[$list_timetable_id])) {

						$list_filter = $summary[$list_timetable_id];
						$slot_filter = $details[$list_timetable_id];

						if (in_array($list_group_id, $list_filter)) {

							$new_slotlist[] = $slot_filter[$list_group_id];
						
						} else {

							unset($slotlist[$i_list]);
						}

					} else if (! in_array($list_timetable_id, $filterd)) {

						$new_slotlist[] = $listed;
					}
				}

			} else {

				// Old logic where all of the slots
				// are included on the merging
				foreach ($updated_slots as $slots) {

					foreach ($slots as $slot) {

						$new_id = $slot['group_id'];

						foreach ($slotlist as $i => $listed) {

							$old_id = $listed['group_id'];

							if ($old_id === $new_id) {

								unset($slot['_flg_adjacent']);

								$new_slotlist[$i] = $slot;
							}
						}

					}
				}
			}
		}

		return $new_slotlist;
	}

	/**
	 * Reviews the slot list array fetching the
	 * lowest price slot and adding the flag to
	 * identify it.
	 * 
	 * @param  array   $slots
	 * @param  boolean $get_price_only
	 */
	public static function get_cheapest_price($slots = array(), $get_price_only = false)
	{	

		if ($slots) {

			// Get the minimum price from
			// the slot list.
			$hours = 0;
			$min_price = 0;
			foreach ($slots as $s) {

				// Disregard booked slots for
				// the price comparison gauge.
				$is_accepted = false;
				
				if (is_array($s) and isset($s['is_booked']) and !$s['is_booked']) {
					$is_accepted = true;
				}

				if (is_object($s) and isset($s->is_booked) and !$s->is_booked) {
					$is_accepted = true;
				}

				
				if ($is_accepted) {

					$p = is_array($s) ? typecast($s['price'], 'float') : typecast($s->price, 'float');
					$h = is_array($s) ? typecast($s['hours'], 'float') : typecast($s->hours, 'float');


					if ($min_price === 0 || $p < $min_price) {

						$min_price = $p;

						$hours = $h;
					}
				}
			}


			// Set is_cheapest flag 
			$slots = array_map(function ($value) use ($min_price) {

				if (is_array($value)) {

					if (isset($value['price'])) {

						$value['is_cheapest'] = (typecast($value['price'], 'float') === $min_price) ? true : false;
					}

				} else {

					if (isset($value->price)) {

						$value->is_cheapest = (typecast($value->price, 'float') === $min_price) ? true : false;
					}

				}

				return $value;

			}, $slots);


			if ($min_price > 0) {

				// @temp
				// Round of price to nearest 2 decimal
				// places while waiting for decision about
				// how multiple hour booking price should be
				// handled.
				$cheapest_price = ($min_price / $hours);
				$cheapest_price_simplified = round($cheapest_price, 2);
			
			} else {

				log_write('err_zero_price', array(
					'min_price' => $min_price,
					'slots' 	=> $slots
				));

				error_mailer('Zero Price Detection', json_encode(array(
					'min_price' => $min_price,
					'slots' 	=> $slots
				)));
			}


			if ($get_price_only) {

				return $min_price ? $cheapest_price_simplified : $min_price;
			
			} else {

				return $slots;
			}
		}

		return $slots;
	}
}
