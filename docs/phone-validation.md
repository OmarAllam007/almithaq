# Phone Number Validation

## Package

[propaganistas/laravel-phone](https://github.com/propaganistas/laravel-phone) v6 — backed by Google's libphonenumber.

## How It Works

The signup form collects the phone number as two separate fields:

| Field | Example |
|---|---|
| `country_code` | `+966` (dial code) |
| `phone_number` | `501234567` (local number) |

Because libphonenumber requires a full international number, `RegisterRequest::prepareForValidation()` merges them into a virtual field `full_phone` (e.g. `+966501234567`) before validation runs.

```php
protected function prepareForValidation(): void
{
    if ($this->country_code && $this->phone_number) {
        $this->merge([
            'full_phone' => $this->country_code.$this->phone_number,
        ]);
    }
}
```

`full_phone` is then validated with:

```php
'full_phone' => [(new Phone)->international()->type('mobile')]
```

- `international()` — accepts any internationally formatted number (country code prefix required)
- `type('mobile')` — rejects landlines and other non-mobile types

`full_phone` is excluded from `User::create()` by overriding `validated()`:

```php
public function validated($key = null, $default = null): array
{
    $data = parent::validated($key, $default);
    unset($data['full_phone']);
    return $data;
}
```

## Error Remapping

Validation errors on `full_phone` are moved to `phone_number` via `withValidator`, so the frontend error display works without any changes:

```php
public function withValidator(\Illuminate\Validation\Validator $validator): void
{
    $validator->after(function ($validator) {
        if ($validator->errors()->has('full_phone')) {
            $message = $validator->errors()->first('full_phone');
            $validator->errors()->forget('full_phone');
            $validator->errors()->add('phone_number', $message);
        }
    });
}
```

## Uniqueness

The composite unique constraint on `['country_code', 'phone_number']` is enforced at the validation layer:

```php
Rule::unique('users', 'phone_number')->where('country_code', $this->country_code)
```

## Translations

Custom messages are defined in `RegisterRequest::messages()` and resolved from the signup language files:

| Key | EN | AR |
|---|---|---|
| `signup.validation-phone-invalid` | Please enter a valid mobile number for the selected country code | يرجى إدخال رقم هاتف محمول صحيح لرمز الدولة المحدد |
| `signup.validation-phone-taken` | This phone number is already registered | رقم الهاتف مسجل بالفعل |

## Files Changed

| File | Change |
|---|---|
| `composer.json` | Added `propaganistas/laravel-phone ^6.0` |
| `app/Http/Requests/RegisterRequest.php` | Added phone validation logic |
| `resources/lang/en/signup.php` | Added `validation-phone-invalid`, `validation-phone-taken` |
| `resources/lang/ar/signup.php` | Added `validation-phone-invalid`, `validation-phone-taken` |
