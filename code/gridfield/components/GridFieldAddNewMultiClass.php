<?php

namespace Modular\GridField\Components;

use ClassInfo;
use Config;
use GridField;
use GridFieldAddNewMultiClassHandler;
use Modular\Exceptions\Exception;
use Object;
use ReflectionClass;
use SS_HTTPRequest;
use SS_HTTPResponse_Exception;

/**
 * GridFieldAddNewMultiClass override some functions in the gridfieldextensions class so we can handle namespaces in class names
 *
 * @package Modular\GridField\Components
 */
class GridFieldAddNewMultiClass extends \GridFieldAddNewMultiClass {

	private $classes;

	private $defaultClass;

	/**
	 * Sets the classes that can be created using this button.
	 *
	 * @param array $classes a set of class names, optionally mapped to titles
	 *
	 * @return GridFieldAddNewMultiClass $this
	 */
	public function setClasses( array $classes, $default = null ) {
		$this->classes = $classes;
		if ( $default ) {
			$this->defaultClass = $default;
		}

		return $this;
	}

	/**
	 * Sets the default class that is selected automatically.
	 *
	 * @param string $default the class name to use as default
	 *
	 * @return GridFieldAddNewMultiClass $this
	 */
	public function setDefaultClass( $default ) {
		$this->defaultClass = $default;

		return $this;
	}

	/**
	 * Gets the classes that can be created using this button, defaulting to the model class and
	 * its subclasses.
	 *
	 * @param GridField $grid
	 *
	 * @return array a map of class name to title
	 * @throws \LogicException
	 */
	public function getClasses( GridField $grid ) {
		$result = array();

		if ( is_null( $this->classes ) ) {
			$classes = array_values( ClassInfo::subclassesFor( $grid->getModelClass() ) );
			sort( $classes );
		} else {
			$classes = $this->classes;
		}

		$kill_ancestors = array();
		foreach ( $classes as $class => $title ) {
			if ( ! is_string( $class ) ) {
				$class = $title;
			}
			if ( ! class_exists( $class ) ) {
				continue;
			}
			$is_abstract = ( ( $reflection = new ReflectionClass( $class ) ) && $reflection->isAbstract() );
			if ( ! $is_abstract && $class === $title ) {
				$title = singleton( $class )->i18n_singular_name();
			}

			if ( $ancestor_to_hide = Config::inst()->get( $class, 'hide_ancestor', Config::FIRST_SET ) ) {
				$kill_ancestors[ $ancestor_to_hide ] = true;
			}

			if ( $is_abstract || ! singleton( $class )->canCreate() ) {
				continue;
			}

			$result[ $class ] = $title;
		}

		if ( $kill_ancestors ) {
			foreach ( $kill_ancestors as $class => $bool ) {
				unset( $result[ $class ] );
			}
		}

		$sanitised = array();
		foreach ( $result as $class => $title ) {
			$sanitised[ $this->sanitiseClassName( $class ) ] = $title;
		}

		return $sanitised;
	}

	/**
	 * Handles adding a new instance of a selected class.
	 *
	 * @param GridField      $grid
	 * @param SS_HTTPRequest $request
	 *
	 * @return \GridFieldAddNewMultiClassHandler
	 * @throws \LogicException
	 * @throws \Modular\Exceptions\Exception
	 * @throws \SS_HTTPResponse_Exception
	 */
	public function handleAdd( $grid, $request ) {
		$class     = $request->param( 'ClassName' );
		$classes   = $this->getClasses( $grid );

		/** @var \GridFieldDetailForm $component */
		$component = $grid->getConfig()->getComponentByType( 'GridFieldDetailForm' );

		if ( ! $component ) {
			throw new Exception( 'The add new multi class component requires the detail form component.' );
		}

		if ( ! $class || ! array_key_exists( $class, $classes ) ) {
			throw new SS_HTTPResponse_Exception( 400 );
		}

		$unsanitisedClass = $this->unsanitiseClassName( $class );

		/** @var GridFieldAddNewMultiClassHandler $handler */
		$handler          = Object::create(
			$this->itemRequestClass,
			$grid,
			$component,
			new $unsanitisedClass(),
			$grid->getForm()->getController(),
			'add-multi-class'
		);
		$handler->setTemplate( $component->getTemplate() );

		return $handler;
	}

	public function getURLHandlers( $grid ) {
		return parent::getURLHandlers( $grid );
	}
}