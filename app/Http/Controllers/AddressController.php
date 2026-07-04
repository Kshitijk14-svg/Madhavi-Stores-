<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    private array $rules = [
        'label'       => 'nullable|string|max:50',
        'first_name'  => 'required|string|max:100',
        'last_name'   => 'required|string|max:100',
        'phone'       => ['required', 'digits:10', 'regex:/^[6-9]\d{9}$/'],
        'address'     => 'required|string',
        'city'        => 'required|string|max:100',
        'postal_code' => 'required|string|max:20',
    ];

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $userId = Auth::id();
        $wantsDefault = $request->boolean('is_default');

        DB::transaction(function () use ($data, $userId, $wantsDefault) {
            // Lock the owning user row so two concurrent submits (double-click,
            // duplicate tab) serialize instead of both seeing "no addresses yet"
            // and both inserting a default.
            User::where('id', $userId)->lockForUpdate()->first();

            $makeDefault = $wantsDefault || Address::where('user_id', $userId)->doesntExist();

            if ($makeDefault) {
                Address::where('user_id', $userId)->update(['is_default' => false]);
            }
            Address::create([
                ...$data,
                'user_id'    => $userId,
                'is_default' => $makeDefault,
            ]);
        });

        return redirect()->route('account', ['tab' => 'addresses'])->with('success', 'Address saved.');
    }

    public function update(Request $request, $id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        $data = $request->validate($this->rules);

        $makeDefault = $request->boolean('is_default');

        DB::transaction(function () use ($address, $data, $makeDefault) {
            User::where('id', $address->user_id)->lockForUpdate()->first();

            if ($makeDefault && !$address->is_default) {
                Address::where('user_id', $address->user_id)->update(['is_default' => false]);
            }
            $address->update([
                ...$data,
                'is_default' => $makeDefault ? true : $address->is_default,
            ]);
        });

        return redirect()->route('account', ['tab' => 'addresses'])->with('success', 'Address updated.');
    }

    public function destroy($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        $wasDefault = $address->is_default;
        $userId = $address->user_id;
        $address->delete();

        if ($wasDefault) {
            Address::where('user_id', $userId)->latest()->first()?->update(['is_default' => true]);
        }

        return redirect()->route('account', ['tab' => 'addresses'])->with('success', 'Address removed.');
    }

    public function setDefault($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);

        DB::transaction(function () use ($address) {
            User::where('id', $address->user_id)->lockForUpdate()->first();
            Address::where('user_id', $address->user_id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return redirect()->route('account', ['tab' => 'addresses'])->with('success', 'Default address updated.');
    }
}
