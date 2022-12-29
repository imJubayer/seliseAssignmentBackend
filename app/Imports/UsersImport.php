<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Share;
use App\Models\Account;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) 
        {
            $user =  User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'gender' => $row['gender'],
                'father_name' => $row['father_name'],
                'mother_name' => $row['mother_name'],
                'address' => $row['address'],
                'national_id' => $row['national_id'],
                'password' => Hash::make('12345678')
            ]);
            $user->assignRole('member');

            if($row['ifsa_1']){
                $accountExist = Account::where([
                    ['user_id', $user->id],
                    ['account_type', 1]
                ])->first();
                if(!$accountExist){
                    try {
                        $account = Account::create([
                            'user_id' => $user->id,
                            'account_type' => 1
                        ]);
                        
                        $share = Share::create([
                            'ifsa_id' => $account->id,
                            'lot' => 10 // $row['ifsa_1_share']
                        ]);
                    } catch (\Throwable $th) {
                        $response = apiResponse(false, $th->getMessage(), (object)[], 409);
                    }
                }
            }

            if($row['ifsa_2']){
                $accountExist = Account::where([
                    ['user_id', $user->id],
                    ['account_type', 2]
                ])->first();
                if(!$accountExist){
                    try {
                        $account = Account::create([
                            'user_id' => $user->id,
                            'account_type' => 2
                        ]);
                        $share = Share::create([
                            'ifsa_id' => $account->id,
                            'lot' => 10 // $row['ifsa_1_share']
                        ]);
                    } catch (\Throwable $th) {
                        $response = apiResponse(false, $th->getMessage(), (object)[], 409);
                    }
                }
            }
        }
    }
}
