<?php

namespace Seat\Commands\Scheduled;

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Seat\EveApi;
use Seat\EveApi\Account;

class EveCorporationWalletUpdater extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'seatscheduled:api-update-corporation-wallets';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update Corporation Wallet Journals and Transactions.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * When a command should run
	 *
	 * @param Scheduler $scheduler
	 * @return \Indatus\Dispatcher\Schedulable
	 */
	public function schedule(Schedulable $scheduler)
	{
		return $scheduler->setSchedule('*/30', '*', '*', '*', '*');
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{

		\Log::info('Started command ' . $this->name, array('src' => __CLASS__));
		
		// Get the keys, and process them
		foreach (\SeatKey::where('isOk', '=', 1)->get() as $key) {

			$access = EveApi\BaseApi::determineAccess($key->keyID);
			if (!isset($access['type'])) {
				//TODO: Log this key's problems and disable it
				continue;
			}

			// Only process Corporation keys and only update the Assets from Partial\
			if ($access['type'] == 'Corporation')
				\App\Services\Queue\QueueHelper::addToQueue(array('Partial', 'CorporationWallet'), $key->keyID, $key->vCode, 'CorporationWallet', 'Eve');

		}
	}
}
