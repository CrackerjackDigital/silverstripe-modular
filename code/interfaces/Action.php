<?php

namespace Modular\Interfaces;

use Modular\Model;

/**
 * Interface Action generally implemented by an extension which is attached to a Model or a Controller,
 * such as one which implements a 'like' or 'delete' operation, via a route such as 'person/$ID/like'.
 *
 *
 * @package Modular\Interfaces
 */
interface Action extends RouteProvider {
	// crud actions
	const ActionCreate = 'create';
	const ActionRead   = self::ActionView;
	const ActionUpdate = self::ActionEdit;
	const ActionDelete = 'remove';

	// usefull synonyms
	const ActionView = 'view';
	const ActionEdit = 'edit';

	/**
	 * Return the model which action is to be applied to, e.g. the owner on a model extension or the model
	 * from a ModelController.
	 *
	 * @return Model
	 */
	public function model();

	/**
	 * Return the class name for the implementing model (PHP >= 5.5 just use ::class)
	 *
	 * @return mixed
	 */
	public static function class_name();

	/**
	 * Apply the action to the target model.
	 *
	 * @param string $alias
	 * @param null   $data
	 *
	 * @return mixed
	 */
	public function apply( $alias, $data = null );

	/**
	 * Reverse the action on the target model.
	 *
	 * @param string $alias
	 * @param null   $data
	 *
	 * @return mixed
	 */
	public function revert( $alias, $data = null );
}
