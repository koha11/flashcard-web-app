<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\Hash;

class AccountService
{
  public function getAll()
  {
    return Account::latest()->get();
  }

  public function find(int $id)
  {
    return Account::findOrFail($id);
  }

  public function findByEmail(string $email)
  {
    return Account::where('email', $email)->first();
  }

  public function create(array $data)
  {

    if (isset($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    }

    return Account::create($data);
  }

  public function update(Account $account, array $data)
  {
    $data['password'] = Hash::make($data['password']);
    $account->update($data);


    return $account->fresh();
  }

  public function delete(Account $account)
  {
    return $account->delete();
  }
}
