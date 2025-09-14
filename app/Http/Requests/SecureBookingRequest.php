<?php

namespace App\Http\Requests;

use App\Rules\SecureInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SecureBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'chalet_id' => 'required|integer|exists:chalets,id',
            'check_in_date' => 'required|date|after:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guests_count' => 'required|integer|min:1|max:20',
            'special_requests' => ['nullable', 'string', 'max:1000', new SecureInput()],
            'booking_details' => ['nullable', 'string', 'max:2000', new SecureInput()],
            'total_amount' => 'required|numeric|min:0|max:999999.99',
            'discount_amount' => 'nullable|numeric|min:0|max:999999.99',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'chalet_id.required' => 'يجب تحديد الشاليه',
            'chalet_id.exists' => 'الشاليه المحدد غير موجود',
            'check_in_date.required' => 'يجب تحديد تاريخ الوصول',
            'check_in_date.after' => 'تاريخ الوصول يجب أن يكون في المستقبل',
            'check_out_date.required' => 'يجب تحديد تاريخ المغادرة',
            'check_out_date.after' => 'تاريخ المغادرة يجب أن يكون بعد تاريخ الوصول',
            'guests_count.required' => 'يجب تحديد عدد الضيوف',
            'guests_count.min' => 'عدد الضيوف يجب أن يكون على الأقل 1',
            'guests_count.max' => 'عدد الضيوف لا يمكن أن يتجاوز 20',
            'total_amount.required' => 'يجب تحديد المبلغ الإجمالي',
            'total_amount.min' => 'المبلغ الإجمالي يجب أن يكون أكبر من صفر',
            'total_amount.max' => 'المبلغ الإجمالي كبير جداً',
            'special_requests.max' => 'الطلبات الخاصة طويلة جداً (الحد الأقصى 1000 حرف)',
            'booking_details.max' => 'تفاصيل الحجز طويلة جداً (الحد الأقصى 2000 حرف)',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input data
        $this->merge([
            'special_requests' => $this->sanitizeInput($this->special_requests),
            'booking_details' => $this->sanitizeInput($this->booking_details),
        ]);
    }

    /**
     * Sanitize input to prevent XSS
     */
    private function sanitizeInput(?string $input): ?string
    {
        if (!$input) {
            return null;
        }

        // Remove HTML tags
        $input = strip_tags($input);

        // Remove extra whitespace
        $input = trim($input);

        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

        return $input;
    }
}
