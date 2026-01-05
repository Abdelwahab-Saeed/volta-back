<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Traits\ApiResponse;

class AddressController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->successResponse(Auth::user()->addresses, 'تم جلب العناوين بنجاح');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'backup_phone_number' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        $address = Auth::user()->addresses()->create($validated);

        return $this->successResponse($address, 'تم إضافة العنوان بنجاح', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)   
    {
        $address = Address::findOrFail($id);
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('غير مصرح لك بالوصول لهذا العنوان', 403);
        }
        return $this->successResponse($address, 'تم جلب بيانات العنوان بنجاح');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $address = Address::findOrFail($id);
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('غير مصرح لك بتعديل هذا العنوان', 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'recipient_name' => 'sometimes|string|max:255',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'sometimes|string|max:255',
            'state' => 'sometimes|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'phone_number' => 'sometimes|string|max:20',
            'backup_phone_number' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            Auth::user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return $this->successResponse($address, 'تم تحديث العنوان بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $address = Address::findOrFail($id);
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('غير مصرح لك بحذف هذا العنوان', 403);
        }

        $address->delete();

        return $this->successResponse(null, 'تم حذف العنوان بنجاح');
    }

    /**
     * Get all addresses for the authenticated user.
     */
    public function myAddresses()
    {
        $addresses = Address::where('user_id', Auth::id())->get();
        return $this->successResponse($addresses, 'تم جلب عناوينك الخاصة بنجاح');
    }
}
