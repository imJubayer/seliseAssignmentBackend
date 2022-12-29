<?php
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Deposit;
use Carbon\Carbon;

function apiResponse($success, $msg, $data, $httpStatusCode=null){
    $response = [
        'success' => $success,
        'msg' => $msg,
        'response' => $data
    ];
    return response()->json($response, $httpStatusCode ? $httpStatusCode : 200);
}

function getProfileInfo($userId=null){
    if($userId){
        $id = $userId;
    } else {
        $id = Auth::user()->id;
    }
    $user = User::where('id', $id)->with(['accounts.share', 'accounts.deposits' => function ($query) {
        $query->orderBy('deposit_for');
    }])->first();

    foreach ($user->accounts as $key => $account) {
        $account->amountDetails = getAccountTotalAmount($account->id);
    }

    $usr = User::find($id);
    if($user){
        $roles = $usr->getRoleNames();
        $user->totalDeposit = (int)$totalDeposit;
        $user->totalDue = (int)$totalDue;
        $user->totalFine = (int)$totalFine;
        $user->currentBalance = ($totalDeposit + $totalFundRaising) - $totalFine;
        $user->role = getPriorityRole($roles);
        $user->profile_image = $user->profile_image;
    } else {
        $user = new stdClass();
    }
    return $user;
}

function getAccountTotalAmount($ifsaId){
    $totalDeposit = Deposit::where([
        ['ifsa_id', $ifsaId],
        ['status', 1]
    ])->sum('amount');

    $totalFundRaising = Deposit::where([
        ['ifsa_id', $ifsaId],
        ['status', 1]
    ])->sum('fund_raising');

    $totalFine = Deposit::where([
        ['ifsa_id', $ifsaId],
        ['status', 1]
    ])->sum('fine');

    return ['totalDeposit' => $totalDeposit, 'totalFundRaising' => $totalFundRaising, 'totalFine' => $totalFine, 'totalAmount' => ($totalDeposit + $totalFundRaising) - $totalFine];
}

function getPriorityRole($roles){
    if($roles->contains('superadmin')){
        $role = 'superadmin';
    } else if($roles->contains('superadmin')){
        $role = 'admin';
    } else {
        $role = 'member';
    }
    return $role;
}
