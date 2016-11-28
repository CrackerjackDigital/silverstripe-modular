<?php
namespace Modular\Fields;

class QueueStatus extends StateEngineField {
	const SingleFieldName = 'QueueStatus';

	const StatusQueued = 'Queued';
	const StatusScheduled = 'Scheduled';
	const StatusProcessing = 'Processing';
	const StatusCompleted = 'Completed';
	const StatusFailed = 'Failed';
	const StatusPaused = 'Paused';
	const StatusHeld = 'Held';
	const StatusCancelled = 'Cancelled';

	private static $states = [
		self::StatusQueued    => [       // ready to be picked up by a task
			self::StatusHeld,
			self::StatusScheduled,
			self::StatusCancelled,
		],
		self::StatusScheduled => [       // task has picked up and will process
			self::StatusProcessing,
			self::StatusHeld,
			self::StatusCancelled,
		],
		self::StatusProcessing=> [       // task is processing this entry
			self::StatusProcessing,
			self::StatusPaused,
			self::StatusCompleted,
			self::StatusFailed,
		],
		self::StatusCompleted => [       // completed succesfully, all thinks passed
			self::StatusQueued,
		],
		self::StatusFailed    => [       // one or more things failed, or a condition was not met
			self::StatusQueued,
		],
		self::StatusPaused    => [       // paused while processing, may be Queued
			self::StatusQueued,
			self::StatusCancelled,
		],
		self::StatusHeld      => [       // paused before processing started, may be queued
			self::StatusQueued,
			self::StatusCancelled,
		],
		self::StatusCancelled => [       // cancelled before or during processing
			self::StatusQueued,
		],
	];

	private static $notify_on_state_events = [
		'*' => [
			'Failed'    => self::NotifyEmailSystemAdmin,
			'Completed' => self::NotifyEmailSystemAdmin,
		    '*'         => self::NotifyEmailWatcher
		],
	];

	// if we can change from 'Failed' to 'Queued'
	private static $can_retry = true;

	// if we can change from 'Cancelled' or 'Completed' to 'Queued'
	private static $can_rerun = true;

	/**
	 * Check if we can retry or rerun the extended model (Task, Service etc)
	 * @param $event
	 * @param $from
	 * @param $to
	 * @return array|bool
	 */
	public function canChangeState($event, $from, $to) {
		if ($to == self::StatusQueued && $from == self::StatusFailed) {
			return (bool)$this->config()->get('can_retry');
		}
		if ($to == self::StatusQueued && in_array($from, [self::StatusCancelled, self::StatusCompleted])) {
			return (bool)$this->config()->get('can_rerun');
		}
		return true;
	}
}