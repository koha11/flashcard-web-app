<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
  protected AccountService $service;

  public function __construct(AccountService $service)
  {
    $this->service = $service;
  }

  public function index()
  {
    return $this->service->getAll();
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'email' => ['required', 'email', 'unique:accounts,email'],
      'password' => ['required', 'min:6'],
      'email_verified_at' => ['nullable', 'date'],
    ]);

    return response()->json(
      $this->service->create($data),
      201
    );
  }

  public function show($id)
  {
    return $this->service->find($id);
  }

  public function update(Request $request, Account $account)
  {
    $data = $request->validate([
      'email' => ['sometimes', 'email', 'unique:accounts,email,' . $account->id],
      'password' => ['sometimes', 'min:6'],
      'email_verified_at' => ['nullable', 'date'],
    ]);

    return $this->service->update($account, $data);
  }

  public function destroy(Account $account)
  {
    $this->service->delete($account);

    return response()->noContent();
  }
}
