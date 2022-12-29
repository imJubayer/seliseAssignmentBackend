<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Account;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(User $user)
    {
        $totalDeposit = Deposit::where([
            ['status', 1]
        ])->when($user->id, function ($q) use($user){
            return $q->where('user_id', $user->id);
        })->sum('amount');

        $lastMonthDeposit = Deposit::where([
            ['status', 1]
        ])->when($user->id, function ($q) use($user){
            return $q->where('user_id', $user->id);
        })->whereMonth('deposit_for', Carbon::now()->subMonth()->format('m'))->sum('amount');

        $totalFundRaising = Deposit::where([
            ['status', 1]
        ])->when($user->id, function ($q) use($user){
            return $q->where('user_id', $user->id);
        })->sum('fund_raising');

        $totalFine = Deposit::where([
            ['status', 1]
        ])->when($user->id, function ($q) use($user){
            return $q->where('user_id', $user->id);
        })->sum('fine');

        $totalUsers = User::count('id');
        $totalAccounts = Account::count('id');
        $ifsa1Accounts = Account::where([
            ['account_type', 1]
        ])->count('id');
        $ifsa2Accounts = Account::where([
            ['account_type', 2]
        ])->count('id');

        $deposits = Deposit::with(['user' => function ($query) {
            $query->select('id', 'name', 'phone');
        }, 'account'])
            ->when(!$user->id, function ($q){
                return $q->where('status', 0)
                        ->whereMonth('deposit_for', Carbon::now()->month)
                        ->whereYear('deposit_for', Carbon::now()->year);
            })
            ->when($user->id, function ($q) use($user){
                return $q->where('user_id', $user->id);
            })
            ->limit(5)
            ->get();

        $grandTotal = $totalDeposit + $totalFundRaising + $totalFine;

        $response = apiResponse(true, "Dashboard data returned", ['totalDeposit' => $totalDeposit, 'lastMonthDeposit' => $lastMonthDeposit, 'totalFundRaising' => $totalFundRaising, 'totalFine' => $totalFine, 'totalUsers' => $totalUsers, 'totalAccounts' => $totalAccounts, 'ifsa1Accounts' => $ifsa1Accounts, 'ifsa2Accounts' => $ifsa2Accounts, 'deposits' => $deposits]);
        return $response;
    }
}
