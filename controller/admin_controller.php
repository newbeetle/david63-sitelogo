<?php
/**
*
* @package Site Logo Extension
* @copyright (c) 2014 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\sitelogo\controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \phpbb\config\config;
use \phpbb\config\db_text;
use \phpbb\request\request;
use \phpbb\template\template;
use \phpbb\user;
use \phpbb\log\log;
use \phpbb\language\language;
use \david63\sitelogo\ext;

/**
* Admin controller
*/
class admin_controller implements admin_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for admin controller
	*
	* @param \phpbb\config\config		$config			Config object
	* @param \phpbb\config\db_text		$config_text	Config text object
	* @param \phpbb\request\request		$request		Request object
	* @param \phpbb\template\template	$template		Template object
	* @param \phpbb\user				$user			User object
	* @param \phpbb\log\log				$log			Log object
	* @param \phpbb\language\language	$language		Language object
	*
	* @return \david63\sitelogo\controller\admin_controller
	* @access public
	*/
	public function __construct(config $config, db_text $config_text, request $request, template $template, user $user, log $log, language $language)
	{
		$this->config		= $config;
		$this->config_text 	= $config_text;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->log			= $log;
		$this->language		= $language;
	}

	/**
	* Display the options a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function display_options()
	{
		// Add the language file
		$this->language->add_lang('acp_sitelogo', 'david63/sitelogo');

		// Create a form key for preventing CSRF attacks
		$form_key = 'sitelogo';
		add_form_key($form_key);

		// Is the form being submitted
		if ($this->request->is_set_post('submit'))
		{
			// Is the submitted form is valid
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// If no errors, process the form data
			// Set the options the user configured
			$this->set_options();

			// Add option settings change action to the admin log
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'SITE_LOGO_LOG');

			// Option settings have been updated and logged
			// Confirm this to the user and provide link back to previous page
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		// Set output vars for display in the template
		// Positions
		$positions = array();

		$positions[ext::LOGO_POSITION_LEFT] 	= $this->language->lang('LOGO_LEFT');
		$positions[ext::LOGO_POSITION_CENTER]	= $this->language->lang('LOGO_CENTRE');
		$positions[ext::LOGO_POSITION_RIGHT] 	= $this->language->lang('LOGO_RIGHT');

		foreach ($positions as $value => $label)
		{
			$this->template->assign_block_vars('positions', array(
				'S_CHECKED'	=> ($this->config['site_logo_position'] == $value) ? true : false,
				'LABEL'		=> $label,
				'VALUE'		=> $value,
			));
		}

		$extended_site_description_data = $this->config_text->get_array(array('site_logo_extended_site_description'));

		$this->template->assign_vars(array(
			'BANNER_HEIGHT'				=> isset($this->config['site_logo_banner_height']) ? $this->config['site_logo_banner_height'] : '',
			'BANNER_RADIUS'				=> isset($this->config['site_logo_banner_radius']) ? $this->config['site_logo_banner_radius'] : '',
			'BANNER_URL'				=> isset($this->config['site_logo_banner_url']) ? $this->config['site_logo_banner_url'] : '',

			'EXTENED_SITE_DESC'			=> isset($this->config['site_logo_use_extended_desc']) ? $this->config['site_logo_use_extended_desc'] : '',
			'EXTENED_SITE_DESCRIPTION'	=> $extended_site_description_data['site_logo_extended_site_description'],

			'HEADER_COLOUR'				=> isset($this->config['site_logo_header_colour']) ? $this->config['site_logo_header_colour'] : '',

			'OVERRIDE_COLOUR'			=> isset($this->config['site_logo_override_colour']) ? $this->config['site_logo_override_colour'] : '',

			'REMOVE_HEADER_BAR'			=> isset($this->config['site_logo_remove_header']) ? $this->config['site_logo_remove_header'] : '',
			'REPEAT_BACKGROUND'			=> isset($this->config['site_logo_background_repeat']) ? $this->config['site_logo_background_repeat'] : '',

			'SEARCH_TO_NAVBAR'			=> isset($this->config['site_logo_move_search']) ? $this->config['site_logo_move_search'] : '',
			'SITE_BACKGROUND_IMAGE'		=> isset($this->config['site_logo_background_image']) ? $this->config['site_logo_background_image'] : '',
			'SITE_LOGO_HEIGHT'			=> isset($this->config['site_logo_height']) ? $this->config['site_logo_height'] : '',
			'SITE_LOGO_IMAGE'			=> isset($this->config['site_logo_image']) ? $this->config['site_logo_image'] : '',
			'SITE_LOGO_LEFT'			=> isset($this->config['site_logo_left']) ? $this->config['site_logo_left'] : '',
			'SITE_LOGO_PIXELS'			=> isset($this->config['site_logo_pixels']) ? $this->config['site_logo_pixels'] : '',
			'SITE_LOGO_REMOVE'			=> isset($this->config['site_logo_remove']) ? $this->config['site_logo_remove'] : '',
			'SITE_LOGO_RESPONSIVE'		=> isset($this->config['site_logo_responsive']) ? $this->config['site_logo_responsive'] : '',
			'SITE_LOGO_RIGHT'			=> isset($this->config['site_logo_right']) ? $this->config['site_logo_right'] : '',
			'SITE_LOGO_URL'				=> isset($this->config['site_logo_logo_url']) ? $this->config['site_logo_logo_url'] : '',
			'SITE_LOGO_WIDTH'			=> isset($this->config['site_logo_width']) ? $this->config['site_logo_width'] : '',
			'SITE_LOGO_VERSION'			=> ext::SITE_LOGO_VERSION,
			'SITE_NAME_BELOW'			=> isset($this->config['site_logo_site_name_below']) ? $this->config['site_logo_site_name_below'] : '',
			'SITE_NAME_SUPRESS'			=> isset($this->config['site_name_supress']) ? $this->config['site_name_supress'] : '',
			'SITE_SEARCH_REMOVE'		=> isset($this->config['site_search_remove']) ? $this->config['site_search_remove'] : '',

			'USE_BANNER'				=> isset($this->config['site_logo_use_banner']) ? $this->config['site_logo_use_banner'] : '',
			'USE_HEADER_COLOURS'		=> isset($this->config['site_logo_header']) ? $this->config['site_logo_header'] : '',
			'USE_OVERRIDE_COLOUR'		=> isset($this->config['site_logo_use_override_colour']) ? $this->config['site_logo_use_override_colour'] : '',
			'USE_SITE_BACKGROUND'		=> isset($this->config['site_logo_use_background']) ? $this->config['site_logo_use_background'] : '',
			'USE_SOLID_HEADER_COLOURS'	=> isset($this->config['site_logo_header_solid']) ? $this->config['site_logo_header_solid'] : '',
			'U_ACTION'					=> $this->u_action,
		));
	}

	/**
	* Set the options a user can configure
	*
	* @return null
	* @access protected
	*/
	protected function set_options()
	{
		$this->config->set('site_logo_background_image', $this->request->variable('site_logo_background_image', ''));
		$this->config->set('site_logo_background_repeat', $this->request->variable('site_logo_background_repeat', 0));
		$this->config->set('site_logo_banner_height', $this->request->variable('site_logo_banner_height', ''));
		$this->config->set('site_logo_banner_radius', $this->request->variable('site_logo_banner_radius', ''));
		$this->config->set('site_logo_banner_url', $this->request->variable('site_logo_banner_url', '', true));
		$this->config->set('site_logo_header', $this->request->variable('site_logo_header', 0));
		$this->config->set('site_logo_header_colour', $this->request->variable('site_logo_header_colour', '#12A3EB'));
		$this->config->set('site_logo_header_solid', $this->request->variable('site_logo_header_solid', 0));
		$this->config->set('site_logo_height', $this->request->variable('site_logo_height', ''));
		$this->config->set('site_logo_image', $this->request->variable('site_logo_image', '', true));
		$this->config->set('site_logo_left', $this->request->variable('site_logo_left', 0));
		$this->config->set('site_logo_logo_url', $this->request->variable('site_logo_logo_url', ''));
		$this->config->set('site_logo_move_search', $this->request->variable('site_logo_move_search', ''));
		$this->config->set('site_logo_override_colour', $this->request->variable('site_logo_override_colour', '000000'));
		$this->config->set('site_logo_pixels', $this->request->variable('site_logo_pixels', 0));
		$this->config->set('site_logo_position', $this->request->variable('site_logo_position', 0));
		$this->config->set('site_logo_remove', $this->request->variable('site_logo_remove', 0));
		$this->config->set('site_logo_remove_header', $this->request->variable('site_logo_remove_header', 0));
		$this->config->set('site_logo_responsive', $this->request->variable('site_logo_responsive', 1));
		$this->config->set('site_logo_right', $this->request->variable('site_logo_right', 0));
		$this->config->set('site_logo_site_name_below', $this->request->variable('site_logo_site_name_below', 0));
		$this->config->set('site_logo_use_extended_desc', $this->request->variable('site_logo_use_extended_desc', 0));
		$this->config->set('site_logo_width', $this->request->variable('site_logo_width', ''));
		$this->config->set('site_logo_use_background', $this->request->variable('site_logo_use_background', 0));
		$this->config->set('site_logo_use_banner', $this->request->variable('site_logo_use_banner', ''));
		$this->config->set('site_logo_use_override_colour', $this->request->variable('site_logo_use_override_colour', 0));
		$this->config->set('site_name_supress', $this->request->variable('site_name_supress', 0));
		$this->config->set('site_search_remove', $this->request->variable('site_search_remove', 0));

		$this->config_text->set_array(array('site_logo_extended_site_description' => ($this->request->variable('site_logo_extended_site_description', '', true))));
	}
}
