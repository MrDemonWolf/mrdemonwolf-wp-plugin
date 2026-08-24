<?php
/**
 * Register all actions and filters for the plugin.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TailSignal_Loader {

	/**
	 * Array of actions registered with WordPress.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Array of filters registered with WordPress.
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @param string $hook          The name of the WordPress action.
	 * @param object $component     A reference to the instance of the object.
	 * @param string $callback      The name of the function definition.
	 * @param int    $priority      Optional. The priority. Default 10.
	 * @param int    $accepted_args Optional. The number of arguments. Default 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @param string $hook          The name of the WordPress filter.
	 * @param object $component     A reference to the instance of the object.
	 * @param string $callback      The name of the function definition.
	 * @param int    $priority      Optional. The priority. Default 10.
	 * @param int    $accepted_args Optional. The number of arguments. Default 1.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Utility to register the actions and hooks into a single collection.
	 *
	 * @param array  $hooks         The collection being registered.
	 * @param string $hook          The name of the WordPress hook.
	 * @param object $component     A reference to the instance of the object.
	 * @param string $callback      The name of the function definition.
	 * @param int    $priority      The priority.
	 * @param int    $accepted_args The number of arguments.
	 * @return array The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return $hooks;
	}

	/**
	 * Register the filters and actions with WordPress.
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
