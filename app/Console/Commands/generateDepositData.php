<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Deposit;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateDepositData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deposit:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Montly deposit data generate';

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $accounts = Account::with('share')->get();
        $current_date_time = Carbon::now()->toDateTimeString();
        echo "Deposit data for ". Carbon::now()->format('M, Y') . "\n";
        foreach ($accounts as $key => $account) {
            $deposits = Deposit::where('user_id', $account->user_id)
                            ->where('ifsa_id', $account->id)
                            ->whereMonth('deposit_for', Carbon::now()->month)
                            ->whereYear('deposit_for', Carbon::now()->year)
                            ->first();
            if(!$deposits){
                try {
                    $deposit = Deposit::create([
                        'user_id' => $account->user_id,
                        'ifsa_id' => $account->id,
                        'amount' => $account->share->lot ? $account->share->lot * 500 : 0,
                        'deposit_for' => $current_date_time,
                        'approved_by' => 2,
                    ]);
                    echo "Deposit inserted - ". $account->id ." \n";
                } catch (\Throwable $th) {
                    $response = apiResponse(false, $th->getMessage(), (object)[], 409);
                    echo "Error for ifsa_id " . $account->id . " - ". $th->getMessage() ." \n";
                }
            }
        }
        echo "Insertion Finished \n";
        return 1;
    }
}
