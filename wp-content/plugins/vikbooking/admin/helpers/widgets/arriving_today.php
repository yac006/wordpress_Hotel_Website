<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class handler for admin widget "arriving today".
 * 
 * @since 	1.4.0
 */
class VikBookingAdminWidgetArrivingToday extends VikBookingAdminWidget
{
	/**
	 * The instance counter of this widget. Since we do not load individual parameters
	 * for each widget's instance, we use a static counter to determine its settings.
	 *
	 * @var 	int
	 */
	protected static $instance_counter = -1;

	/**
	 * Class constructor will define the widget name and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->widgetName = JText::translate('VBO_W_ARRIVETOD_TITLE');
		$this->widgetDescr = JText::translate('VBO_W_ARRIVETOD_DESCR');
		$this->widgetId = basename(__FILE__, '.php');

		/**
		 * Define widget and icon and style name.
		 * 
		 * @since 	1.15.0 (J) - 1.5.0 (WP)
		 */
		$this->widgetIcon = '<i class="' . VikBookingIcons::i('plane-arrival') . '"></i>';
		$this->widgetStyleName = 'light-red';
	}

	public function render(VBOMultitaskData $data = null)
	{
		// increase widget's instance counter
		static::$instance_counter++;

		// check whether the widget is being rendered via AJAX when adding it through the customizer
		$is_ajax = $this->isAjaxRendering();

		// widget instance identifier
		$widget_instance = $is_ajax ? -1 : static::$instance_counter;

		// get arrivals for today
		$checkin_today = $this->getArrivals();

		// count arrivals
		$tot_arrivals = 0;
		foreach ($checkin_today as $in_today) {
			if ($in_today['status'] == 'confirmed') {
				$tot_arrivals++;
			}
		}

		// render the necessary PHP/JS code for the modal window only once
		if (!defined('VBO_JMODAL_CHECKIN_BOOKING')) {
			define('VBO_JMODAL_CHECKIN_BOOKING', 1);
			?>
			<script type="text/javascript">
			function vboJModalShowCallback() {
				if (typeof vbo_t_on == "undefined") {
					return;
				}
				// simulate STOP click
				if (vbo_t_on) {
					vbo_t_on = false;
					clearTimeout(vbo_t);
					jQuery(".vbo-dashboard-refresh-play").fadeIn();
				}
			}
			function vboJModalHideCallback() {
				if (typeof vbo_t_on == "undefined") {
					return;
				}
				// simulate PLAY click
				if (!vbo_t_on) {
					vboStartTimer();
					jQuery(".vbo-dashboard-refresh-play").fadeOut();
				}
			}
			</script>
			<?php
			echo $this->vbo_app->getJmodalScript('', 'vboJModalHideCallback();', 'vboJModalShowCallback();');
			echo $this->vbo_app->getJmodalHtml('vbo-checkin-booking', JText::translate('VBOMANAGECHECKSINOUT'));
		}

		// prepare tristate toggle switch values
		$multistate_vals = array(
			'today',
			'tomorrow',
			'yesterday',
		);
		$multistate_lbls = array(
			array(
				'value' => JText::translate('VBTODAY'),
			),
			array(
				'value' => JText::translate('VBOTOMORROW'),
			),
			array(
				'value' => JText::translate('VBOYESTERDAY'),
			),
		);
		$multistate_attrs = array(
			array(
				'label_class' => 'vik-multiswitch-text vik-multiswitch-radiobtn-today',
				'input' 	  => array(
					'onchange' => 'vboWidgetLoadArrivals(this.value, \'' . $widget_instance . '\')',
				),
			),
			array(
				'label_class' => 'vik-multiswitch-text vik-multiswitch-radiobtn-tomorrow',
				'input' 	  => array(
					'onchange' => 'vboWidgetLoadArrivals(this.value, \'' . $widget_instance . '\')',
				),
			),
			array(
				'label_class' => 'vik-multiswitch-text vik-multiswitch-radiobtn-yesterday',
				'input' 	  => array(
					'onchange' => 'vboWidgetLoadArrivals(this.value, \'' . $widget_instance . '\')',
				),
			),
		);
		$wrap_attrs = array(
			'class' => 'vik-multiswitch-noanimation',
		);

		?>
		<div class="vbo-admin-widget-wrapper" id="vbo-widget-today-checkin-<?php echo $widget_instance; ?>">
			<div class="vbo-admin-widget-head vbo-dashboard-today-checkin-head">
				<div class="vbo-admin-widget-head-inline">
					<h4>
						<?php echo $this->widgetIcon; ?>
						<span class="arrivals-when"><?php echo JText::translate('VBOARRIVING'); ?></span> 
						<span class="arrivals-tot"><?php echo $tot_arrivals; ?></span>
					</h4>
					<div class="vbo-dashboard-search-input vbo-dashboard-search-checkin">
						<div class="btn-wrapper input-append pull-right">
							<input type="text" class="checkin-search form-control" placeholder="<?php echo JText::translate('VBODASHSEARCHKEYS'); ?>">
							<button type="button" class="btn" onclick="jQuery('.checkin-search').val('').trigger('keyup');"><i class="icon-remove"></i></button>
						</div>
					</div>
					<div class="vbo-widget-today-checkin-tristate"><?php echo $this->vbo_app->multiStateToggleSwitchField('when_arriving' . $widget_instance, 'today', $multistate_vals, $multistate_lbls, $multistate_attrs, $wrap_attrs); ?></div>
				</div>
			</div>
			<div class="vbo-dashboard-today-checkin table-responsive">
				<table class="table vbo-table-search-cin">
					<thead>
						<tr class="vbo-dashboard-today-checkin-firstrow">
							<th class="left"><?php echo JText::translate('VBDASHUPRESONE'); ?></th>
							<th class="left"><?php echo JText::translate('VBCUSTOMERNOMINATIVE'); ?></th>
							<th class="center"><?php echo JText::translate('VBDASHUPRESSIX'); ?></th>
							<th class="center"><?php echo JText::translate('VBDASHUPRESTWO'); ?></th>
							<th class="center"><?php echo JText::translate('VBDASHUPRESFOUR'); ?></th>
							<th class="center"><?php echo JText::translate('VBDASHUPRESFIVE'); ?></th>
							<th class="vbo-tdright"> </th>
						</tr>
						<tr class="warning no-results">
							<td colspan="7"><i class="vboicn-warning"></i> <?php echo JText::translate('VBONORESULTS'); ?></td>
						</tr>
					</thead>
					<tbody>
					<?php
					// render the rows for the arrivals (if any)
					echo $this->buildArrivalRows($checkin_today);
					?>
					</tbody>
				</table>
			</div>
		</div>

		<?php
		if (static::$instance_counter === 0 || $is_ajax) {
			?>
		<script type="text/javascript">
			/**
			 * Retrieves the arrivals for the given day by making an AJAX request
			 */
			function vboWidgetLoadArrivals(when, winstance) {
				// the widget method to call
				var call_method = 'loadArrivals';

				// make a silent request to get the arrivals
				VBOCore.doAjax(
					"<?php echo $this->getExecWidgetAjaxUri(); ?>",
					{
						widget_id: "<?php echo $this->getIdentifier(); ?>",
						call: call_method,
						tmpl: "component",
						when: when,
					},
					function(response) {
						try {
							var obj_res = typeof response === 'string' ? JSON.parse(response) : response;
							if (!obj_res.hasOwnProperty(call_method)) {
								console.error('Unexpected JSON response', obj_res);
								return;
							}

							var widget_cont = jQuery('#vbo-widget-today-checkin-' + winstance);
							if (!widget_cont || !widget_cont.length) {
								console.error('Widget instance not found', winstance);
								return;
							}

							// display the list of arrivals for the given day
							widget_cont.find('table.vbo-table-search-cin tbody').html(obj_res[call_method]);

							// update counter
							var tot_arrivals = widget_cont.find('table.vbo-table-search-cin tbody').find('tr').not('.warning').length;
							widget_cont.find('.arrivals-tot').text(tot_arrivals);
						} catch(err) {
							console.error('could not parse JSON response', err, response);
						}
					},
					function(error) {
						console.error(error);
					}
				);
			}

			jQuery(function() {
				/* Attempt to append the modal container to the body for the multitask panel */
				let modal_container = jQuery('[id*="vbo-checkin-booking"][class*="modal"]');
				if (modal_container.length) {
					modal_container.first().appendTo('body');
				}

				/* Check-in Search */
				jQuery(".checkin-search").keyup(function() {
					var inp_elem = jQuery(this);
					var instance_elem = inp_elem.closest('.vbo-admin-widget-wrapper');
					var searchTerm = inp_elem.val();
					var listItem = instance_elem.find('.vbo-table-search-cin tbody').children('tr');
					var searchSplit = searchTerm.replace(/ /g, "'):containsi('");
					jQuery.extend(jQuery.expr[':'], {'containsi': 
						function(elem, i, match, array) {
							return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
						}
					});
					instance_elem.find(".vbo-table-search-cin tbody tr td.searchable").not(":containsi('" + searchSplit + "')").each(function(e) {
						jQuery(this).parent('tr').attr('visible', 'false');
					});
					instance_elem.find(".vbo-table-search-cin tbody tr td.searchable:containsi('" + searchSplit + "')").each(function(e) {
						jQuery(this).parent('tr').attr('visible', 'true');
					});
					var jobCount = parseInt(instance_elem.find('.vbo-table-search-cin tbody tr[visible="true"]').length);
					instance_elem.find('.arrivals-tot').text(jobCount);
					if (jobCount > 0) {
						instance_elem.find('.vbo-table-search-cin').find('.no-results').hide();
					} else {
						instance_elem.find('.vbo-table-search-cin').find('.no-results').show();
					}
				});
			});
		</script>
		<?php
		}
	}

	/**
	 * Custom method for this widget only to load the arrivals for a given day.
	 * The method is called by the admin controller through an AJAX request.
	 * The visibility should be public, it should not exit the process, and
	 * any content sent to output will be returned to the AJAX response.
	 */
	public function loadArrivals()
	{
		$when = VikRequest::getString('when', 'today', 'request');

		echo $this->buildArrivalRows($this->getArrivals($when), $when);
	}

	/**
	 * Returns the list of arrivals for the given day.
	 * 
	 * @param 	string 	$when 	either today, tomorrow, or yesterday.
	 * 
	 * @return 	array 			list of arrivals, if any.
	 * 
	 * @since 	1.15.0 (J) - 1.5.0 (WP)
	 */
	protected function getArrivals($when = 'today')
	{
		$dbo = JFactory::getDbo();

		$arrivals = array();

		if ($when == 'tomorrow') {
			$today_start_ts = mktime(0, 0, 0, date("n"), (date("j") + 1), date("Y"));
			$today_end_ts = mktime(23, 59, 59, date("n"), (date("j") + 1), date("Y"));
		} elseif ($when == 'yesterday') {
			$today_start_ts = mktime(0, 0, 0, date("n"), (date("j") - 1), date("Y"));
			$today_end_ts = mktime(23, 59, 59, date("n"), (date("j") - 1), date("Y"));
		} else {
			// today
			$today_start_ts = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
			$today_end_ts = mktime(23, 59, 59, date("n"), date("j"), date("Y"));
		}
		
		$q = "SELECT `o`.`id`,`o`.`custdata`,`o`.`status`,`o`.`checkin`,`o`.`checkout`,`o`.`roomsnum`,`o`.`country`,`o`.`closure`,`o`.`checked`,(SELECT CONCAT_WS(' ',`or`.`t_first_name`,`or`.`t_last_name`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id` LIMIT 1) AS `nominative`,(SELECT SUM(`or`.`adults`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `tot_adults`,(SELECT SUM(`or`.`children`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `tot_children` FROM `#__vikbooking_orders` AS `o` WHERE `o`.`checkin`>=".$today_start_ts." AND `o`.`checkin`<=".$today_end_ts." AND `o`.`closure`=0 ORDER BY `o`.`checkin` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$arrivals = $dbo->loadAssocList();
		}

		return $arrivals;
	}

	/**
	 * Builds the HTML string for the given arrivals. We use a method
	 * so that the AJAX requests will be able to rely on this as well.
	 * 
	 * @param 	array 	$arrivals 	the list of records found.
	 * @param 	string 	$when 		either today, tomorrow, or yesterday.
	 * 
	 * @return 	string 				the composed HTML string.
	 * 
	 * @since 	1.15.0 (J) - 1.5.0 (WP)
	 */
	protected function buildArrivalRows($arrivals, $when = 'today')
	{
		$rows = '';

		$arrivals = !is_array($arrivals) ? array() : $arrivals;

		// check permissions
		$vbo_auth_bookings = JFactory::getUser()->authorise('core.vbo.bookings', 'com_vikbooking');

		foreach ($arrivals as $ink => $intoday) {
			$totpeople_str = $intoday['tot_adults']." ".($intoday['tot_adults'] > 1 ? JText::translate('VBMAILADULTS') : JText::translate('VBMAILADULT')).($intoday['tot_children'] > 0 ? ", ".$intoday['tot_children']." ".($intoday['tot_children'] > 1 ? JText::translate('VBMAILCHILDREN') : JText::translate('VBMAILCHILD')) : "");
			$room_names = array();
			$rooms = VikBooking::loadOrdersRoomsData($intoday['id']);
			foreach ($rooms as $rr) {
				$room_names[] = $rr['room_name'];
			}
			if ($intoday['roomsnum'] == 1) {
				// parse distintive features
				$unit_index = '';
				if (strlen($rooms[0]['roomindex']) && !empty($rooms[0]['params'])) {
					$room_params = json_decode($rooms[0]['params'], true);
					if (is_array($room_params) && array_key_exists('features', $room_params) && @count($room_params['features']) > 0) {
						foreach ($room_params['features'] as $rind => $rfeatures) {
							if ($rind == $rooms[0]['roomindex']) {
								foreach ($rfeatures as $fname => $fval) {
									if (strlen($fval)) {
										$unit_index = ' #'.$fval;
										break;
									}
								}
								break;
							}
						}
					}
				}
				//
				$roomstr = '<span class="vbo-smalltext">'.$room_names[0].$unit_index.'</span>';
			} else {
				$roomstr = '<span class="hasTooltip vbo-tip-small" title="'.implode(', ', $room_names).'">'.$intoday['roomsnum'].'</span><span class="hidden-for-search">'.implode(', ', $room_names).'</span>';
			}
			$act_status = '';
			if ($intoday['status'] == 'confirmed') {
				switch ($intoday['checked']) {
					case -1:
						$ord_status = '<span class="label label-error vbo-status-label" style="background-color: #d9534f;">'.JText::translate('VBOCHECKEDSTATUSNOS').'</span>';
						break;
					case 1:
						$ord_status = '<span class="label label-success vbo-status-label">'.JText::translate('VBOCHECKEDSTATUSIN').'</span>';
						break;
					case 2:
						$ord_status = '<span class="label label-success vbo-status-label">'.JText::translate('VBOCHECKEDSTATUSOUT').'</span>';
						break;
					default:
						$ord_status = '<span class="label label-success vbo-status-label">'.JText::translate('VBCONFIRMED').'</span>';
						break;
				}
				if ($vbo_auth_bookings && $intoday['closure'] != 1) {
					$act_status = '<button type="button" class="btn btn-small btn-primary" onclick="vboOpenJModal(\'vbo-checkin-booking\', \'index.php?option=com_vikbooking&task=bookingcheckin&cid[]='.$intoday['id'].'&tmpl=component\');">'.JText::translate('VBOMANAGECHECKIN').'</button>';
				}
			} elseif ($intoday['status'] == 'standby') {
				$ord_status = '<span class="label label-warning vbo-status-label">'.JText::translate('VBSTANDBY').'</span>';
			} else {
				$ord_status = '<span class="label label-error vbo-status-label" style="background-color: #d9534f;">'.JText::translate('VBCANCELLED').'</span>';
			}
			$nominative = strlen($intoday['nominative']) > 1 ? $intoday['nominative'] : VikBooking::getFirstCustDataField($intoday['custdata']);
			$country_flag = '';
			if (file_exists(VBO_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$intoday['country'].'.png')) {
				$country_flag = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$intoday['country'].'.png'.'" title="'.$intoday['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
			}
			
			$rows .= '<tr class="vbo-dashboard-today-checkin-rows">' . "\n";
			$rows .= '	<td class="searchable left"><a href="index.php?option=com_vikbooking&amp;task=editorder&amp;cid[]=' . $intoday['id'] . '">' . $intoday['id'] . '</a></td>' . "\n";
			$rows .= '	<td class="searchable left">' . $country_flag . $nominative . '</td>' . "\n";
			$rows .= '	<td class="center">' . $totpeople_str . '</td>' . "\n";
			$rows .= '	<td class="searchable center">' . $roomstr . '</td>' . "\n";
			$rows .= '	<td class="searchable center">' . date(str_replace("/", $this->datesep, $this->df).' H:i', $intoday['checkout']) . '</td>' . "\n";
			$rows .= '	<td class="searchable center" data-status="' . $intoday['id'] . '">' . $ord_status . '</td>' . "\n";
			$rows .= '	<td class="vbo-tdright pro-feature">' . $act_status . '</td>' . "\n";
			$rows .= '</tr>' . "\n";
		}

		if (!count($arrivals)) {
			// display just the TR with the warning message
			$no_arrivals_when = JText::translate('VBONOCHECKINSTODAY');
			if ($when == 'yesterday') {
				$no_arrivals_when = JText::translate('VBONOCHECKINSYESTERDAY');
			} elseif ($when == 'tomorrow') {
				$no_arrivals_when = JText::translate('VBONOCHECKINSTOMORROW');
			}

			$rows .= '<tr class="warning">' . "\n";
			$rows .= '	<td colspan="7"><i class="vboicn-warning"></i> ' . $no_arrivals_when . '</td>' . "\n";
			$rows .= '</tr>' . "\n";
		}

		return $rows;
	}
}
