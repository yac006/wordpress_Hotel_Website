<?php
/** 
 * @package     VikBooking
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * VikBooking mail controller.
 *
 * @since 	1.15.2 (J) - 1.5.5 (WP)
 */
class VikBookingControllerMail extends JControllerAdmin
{
	/**
	 * Given the content received through the AJAX request,
	 * parses the visual editor wrapper symbols and contents.
	 * 
	 * @return 	void
	 */
	public function preview_visual_editor()
	{
		$dbo   = JFactory::getDbo();
		$app   = JFactory::getApplication();
		$input = $app->input;

		// the raw email content
		$content = $input->get('content', '', 'raw');

		// an optional booking ID to use for the simulation
		$bid = $input->getInt('bid', 0);

		// replace visual editor placeholders for special tags and conditional text rules
		$content = preg_replace_callback("/(<strong class=\"vbo-editor-hl-specialtag\">)([^<]+)(<\/strong>)/", function($match) {
			return $match[2];
		}, $content);

		// grab the latest confirmed reservation
		$clauses = [
			"`status`='confirmed'",
			"`closure`=0",
		];
		if (!empty($bid)) {
			$clauses[] = "`id`=" . $bid;
		}
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE " . implode(' AND ', $clauses) . " ORDER BY `id` DESC";
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$booking = $dbo->loadAssoc();
			$booking_rooms = VikBooking::loadOrdersRoomsData($booking['id']);
			// inject properties for parsing the conditional text rules later
			VikBooking::getConditionalRulesInstance()->set(['booking', 'rooms'], [$booking, $booking_rooms]);
		}

		// wrap dummy mail data with proper content
		$mail_data = new VBOMailWrapper([
			'sender'      => ['dummy@email.com', __METHOD__],
			'recipient'   => 'dummy@email.com',
			'bcc'         => [],
			'reply'       => null,
			'subject'     => __METHOD__,
			'content'     => $content,
			'attachments' => null,
		]);

		// prepare the final email content
		$mail_content = VBOFactory::getPlatform()->getMailer()->prepare($mail_data);

		// send JSON response to output
		VBOHttpDocument::getInstance($app)->json([$mail_content]);
	}

	/**
	 * AJAX request made by the configuration page when updating the
	 * visual editor mail content wrapper symbols (HTML code). From
	 * a whole HTML string, we need to be able to detect the opening
	 * and closing HTML tags, usually DIV tags with inline styles.
	 * 
	 * @return 	void
	 */
	public function update_ve_contwraper()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		// the raw wrapper content HTML code
		$wrapper_content = $input->get('wrapper_content', '', 'raw');

		if (empty($wrapper_content)) {
			VBOHttpDocument::getInstance()->close(500, 'Empty mail content wrapper HTML code');
		}

		// regex pattern to match only HTML tags, inclusive of tab and new line feeds
		$rgx_pattern = '/(\t*<\/?[a-z]+\s?[A-Za-z0-9=:"%;#\- ]*?>\n?)/';

		// find occurrences
		preg_match_all($rgx_pattern, $wrapper_content, $matches);

		if (empty($matches[1]) || (count($matches[1]) % 2) !== 0) {
			// no matches or odd matches count, this is an error
			VBOHttpDocument::getInstance()->close(500, 'Invalid HTML code detected. Make sure to open and close all the HTML tags.');
		}

		// split the HTML tags in half to get the opening and closing content wrapper code
		$tags_per_layout = floor(count($matches[1]) / 2);

		$opening_tags = array_slice($matches[1], 0, $tags_per_layout);
		$closing_tags = array_slice($matches[1], $tags_per_layout, $tags_per_layout);

		$opening_layout = implode('', $opening_tags);
		$closing_layout = implode('', $closing_tags);

		// access the configuration object
		$config = VBOFactory::getConfig();

		// update opening and closing layouts
		$config->set('mail_wrapper_layout_opening', $opening_layout);
		$config->set('mail_wrapper_layout_closing', $closing_layout);

		// send JSON confirmation response to output
		VBOHttpDocument::getInstance($app)->json([$opening_layout, $closing_layout]);
	}

	/**
	 * AJAX endpoint for the visual editor to get the default logo URL.
	 * 
	 * @return 	void
	 */
	public function get_default_logo()
	{
		$app = JFactory::getApplication();
		$logo_info = new stdClass;
		$logo_info->url = null;

		$sitelogo = VikBooking::getSiteLogo();
		$backlogo = VikBooking::getBackendLogo();
		if (!empty($sitelogo) && is_file(VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources'. DIRECTORY_SEPARATOR . $sitelogo)) {
			$logo_info->url = VBO_ADMIN_URI . 'resources/' . $sitelogo;
		} elseif (!empty($backlogo) && is_file(VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources'. DIRECTORY_SEPARATOR . $backlogo)) {
			$logo_info->url = VBO_ADMIN_URI . 'resources/' . $backlogo;
		} else {
			// default logo
			$logo_info->url = VBO_ADMIN_URI . 'vikbooking.png';
		}

		// send JSON response to output
		VBOHttpDocument::getInstance($app)->json($logo_info);
	}
}
