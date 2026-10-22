<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Merchant;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Merchant owner manages the staff accounts of THEIR merchant (PRD 4.1 / prerequisite #4).
 * Staff are users with the 'staff' role, users.merchant_id = this merchant and an optional branch.
 * Accounts are deactivated, never deleted, so the history of who awarded/redeemed stays intact.
 * The package's staff_limit (-1 = unlimited) is enforced when staff are created or re-activated.
 */
class MerchantStaffController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:merchant']);
    }

    private function merchant(): Merchant
    {
        $merchant = Merchant::find(Auth::user()->merchant_id);
        abort_unless($merchant, 403, 'No merchant associated.');
        return $merchant;
    }

    /** staff_limit of the merchant's package; merchants without a package follow the Free package. -1 = unlimited */
    private function limit(Merchant $merchant): int
    {
        $package = $merchant->package ?: Package::where('name', 'Free')->first();
        return $package ? (int) $package->staff_limit : -1;
    }

    private function activeCount(Merchant $merchant): int
    {
        return User::where('merchant_id', $merchant->id)->where('status', '!=', 'inactive')->role('staff')->count();
    }

    private function staffMember(Merchant $merchant, $id): User
    {
        return User::where('merchant_id', $merchant->id)->role('staff')->findOrFail($id);
    }

    private function validBranch(Merchant $merchant, ?int $branchId): bool
    {
        return $branchId === null || Branch::where('id', $branchId)->where('merchant_id', $merchant->id)->exists();
    }

    public function index(): View
    {
        $merchant = $this->merchant();

        return view('merchant.staff', [
            'staff'    => User::where('merchant_id', $merchant->id)->role('staff')->with('branch')->orderBy('name')->get(),
            'branches' => Branch::where('merchant_id', $merchant->id)->orderBy('name')->get(),
            'limit'    => $this->limit($merchant),
            'used'     => $this->activeCount($merchant),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $merchant = $this->merchant();

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', 'max:254', 'not_regex:/[\r\n]/', 'unique:users,email'],
            'phone'     => 'nullable|string|max:20',
            'branch_id' => 'nullable|integer',
            'password'  => 'required|string|min:8|confirmed',
        ]);

        $branchId = isset($data['branch_id']) ? (int) $data['branch_id'] : null;
        if (!$this->validBranch($merchant, $branchId)) {
            return back()->withErrors(['branch_id' => 'Cawangan tidak sah.'])->withInput();
        }

        $limit = $this->limit($merchant);
        if ($limit >= 0 && $this->activeCount($merchant) >= $limit) {
            return back()->withErrors(['limit' => "Had staff pakej anda ({$limit}) telah dicapai. Nyahaktifkan staff lain atau naik taraf pakej."])->withInput();
        }

        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'phone'       => $data['phone'] ?? null,
            'password'    => $data['password'],        // hashed by the model cast
            'merchant_id' => $merchant->id,
            'branch_id'   => $branchId,
            'status'      => 'active',
            'role'        => 'staff',
        ]);
        $user->assignRole('staff');

        Log::info("Merchant {$merchant->id} created staff {$user->id}", ['by' => Auth::id()]);

        return back()->with('success', "Staff {$user->name} ditambah. Kongsi emel dan kata laluan sementara itu dengannya.");
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $merchant = $this->merchant();
        $staff = $this->staffMember($merchant, $id);

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'branch_id' => 'nullable|integer',
            'password'  => 'nullable|string|min:8|confirmed',
        ]);

        $branchId = isset($data['branch_id']) ? (int) $data['branch_id'] : null;
        if (!$this->validBranch($merchant, $branchId)) {
            return back()->withErrors(['branch_id' => 'Cawangan tidak sah.']);
        }

        $staff->name = $data['name'];
        $staff->phone = $data['phone'] ?? null;
        $staff->branch_id = $branchId;
        if (!empty($data['password'])) {
            $staff->password = $data['password'];
        }
        $staff->save();

        return back()->with('success', "Maklumat {$staff->name} dikemas kini.");
    }

    public function toggle($id): RedirectResponse
    {
        $merchant = $this->merchant();
        $staff = $this->staffMember($merchant, $id);

        if ($staff->status === 'inactive') {
            $limit = $this->limit($merchant);
            if ($limit >= 0 && $this->activeCount($merchant) >= $limit) {
                return back()->withErrors(['limit' => "Had staff pakej anda ({$limit}) telah dicapai."]);
            }
            $staff->status = 'active';
            $msg = "{$staff->name} diaktifkan semula.";
        } else {
            $staff->status = 'inactive';
            $msg = "{$staff->name} dinyahaktifkan (tidak boleh log masuk).";
        }
        $staff->save();

        Log::info("Merchant {$merchant->id} set staff {$staff->id} to {$staff->status}", ['by' => Auth::id()]);

        return back()->with('success', $msg);
    }
}
