<?php

// app/Http/Controllers/Frontend/AccountController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Show the user account dashboard
     */
    public function index(): View|RedirectResponse
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login')->with('message', 'Please log in to access your account.');
        }

        $user = Auth::user();
        $customer = $user->customer;

        // Create customer record if it doesn't exist
        if (! $customer) {
            $customer = Customer::create([
                'user_id' => $user->id,
                'first_name' => $this->extractFirstName($user->name),
                'last_name' => $this->extractLastName($user->name),
                'email' => $user->email,
            ]);
        }

        // Get recent orders
        $recentOrders = $customer->orders()
            ->with(['items.product', 'items.productVariant'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get addresses
        $addresses = $customer->addresses()->orderBy('is_default', 'desc')->get();

        return view('frontend.account.index', compact('user', 'customer', 'recentOrders', 'addresses'));
    }

    /**
     * Show the edit account form
     */
    public function edit(): View|RedirectResponse
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login')->with('message', 'Please log in to access your account.');
        }

        $user = Auth::user();
        $customer = $user->customer;

        if (! $customer) {
            $customer = Customer::create([
                'user_id' => $user->id,
                'first_name' => $this->extractFirstName($user->name),
                'last_name' => $this->extractLastName($user->name),
                'email' => $user->email,
            ]);
        }

        return view('frontend.account.edit', compact('user', 'customer'));
    }

    /**
     * Update the user account
     */
    public function update(Request $request): RedirectResponse
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login')->with('message', 'Please log in to access your account.');
        }

        $user = Auth::user();
        $customer = $user->customer;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Check current password if trying to change password
        if (! empty($validated['password'])) {
            if (empty($validated['current_password']) || ! Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
        }

        // Update user
        $userData = [
            'name' => $validated['first_name'].' '.$validated['last_name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        // Update or create customer
        if ($customer) {
            $customer->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);
        } else {
            Customer::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);
        }

        return redirect()->route('account.index')->with('success', 'Account updated successfully!');
    }

    /**
     * Extract first name from full name
     */
    private function extractFirstName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));

        return $parts[0] ?? '';
    }

    /**
     * Extract last name from full name
     */
    private function extractLastName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        if (count($parts) > 1) {
            array_shift($parts); // Remove first name

            return implode(' ', $parts);
        }

        return '';
    }
}
