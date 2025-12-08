# Feature 2 Quick Reference Guide

## Fast Lookup Reference

### Database Migration
```bash
# Run the migration
php artisan migrate

# Rollback if needed
php artisan migrate:rollback
```

---

## Model Usage

### Quotation Model

```php
// Check rejection status
if ($quotation->isRejected()) { }

// Reject a quotation
$quotation->reject('Reason here', auth()->id());

// Get all linked quotations (parent + self + children)
$allLinked = $quotation->getAllLinkedQuotations();

// Access relationships
$quotation->rejectedBy;           // User who rejected
$quotation->parentQuotation;      // Parent quotation
$quotation->linkedQuotations;     // Child quotations
```

---

## API Endpoints

### Quick Call Examples

```javascript
// Reject quotation
fetch('/api/quotations/1/reject', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        rejection_reason: 'Budget exceeded'
    })
}).then(r => r.json()).then(d => console.log(d));

// Create add-on quotation
fetch('/api/quotations/1/linked', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        subject: 'Installation',
        labor_fee: 500,
        delivery_fee: 100,
        status_id: 1
    })
}).then(r => r.json()).then(d => console.log(d));

// Get linked quotations
fetch('/api/quotations/1/linked')
    .then(r => r.json())
    .then(d => console.log(d.quotations));
```

---

## Blade Template Integration

### Add to Quotation Show View

```blade
<!-- Add rejection modal -->
@include('quotations.partials.rejection-modal')

<!-- Display linked quotations -->
@include('quotations.partials.linked-quotations', ['quotation' => $quotation])

<!-- Add-on creation modal -->
@include('quotations.partials.add-linked-quotation-modal', [
    'quotation' => $quotation,
    'statuses' => $statuses
])

<!-- Reject button -->
<button class="btn btn-danger btn-reject-quotation" data-quotation-id="{{ $quotation->id }}">
    <i class="fas fa-times-circle"></i> Reject
</button>
```

### Add to Layout Head

```blade
<link rel="stylesheet" href="{{ asset('assets/css/quotation-management.css') }}">
```

### Add to Layout Body End

```blade
<script src="{{ asset('assets/js/quotation-validation.js') }}"></script>
```

---

## Form Request Usage

### In Controller

```php
// Use StoreQuotationRequest for validation
public function store(StoreQuotationRequest $request) {
    // Validated data is automatically cleaned
    $quotation = Quotation::create($request->validated());
}

// Use RejectQuotationRequest for rejection
public function reject($id, RejectQuotationRequest $request) {
    $quotation = Quotation::findOrFail($id);
    $quotation->reject($request->rejection_reason, auth()->id());
}
```

---

## Validation Rules Reference

### Price Input Validation
```php
'labor_fee' => 'numeric|min:0|max:999999.99'
'delivery_fee' => 'numeric|min:0|max:999999.99'
```

### Quotation Subject
```php
'subject' => 'required|max:255|regex:/^[a-zA-Z0-9\s\-\.\,&()]+$/'
```

### Rejection Reason
```php
'rejection_reason' => 'required|min:10|max:1000'
```

---

## JavaScript Utilities

### Price Formatting

```javascript
// Format price
QuotationValidation.formatPrice('10000')
// Output: '10,000'

// Get numeric value
QuotationValidation.getNumericValue('10,000')
// Output: 10000
```

### Name Validation

```javascript
// Check if valid name
QuotationValidation.isValidName('John Doe')
// Output: true

QuotationValidation.isValidName('John123')
// Output: false
```

### Error Handling

```javascript
// Show error
QuotationValidation.showValidationError(input, 'Error message');

// Clear error
QuotationValidation.clearValidationError(input);

// Validate form
const isValid = QuotationValidation.validateForm(form);
```

---

## HTML Classes for Auto-formatting

### Price Inputs
```html
<input class="price-input" type="number" name="labor_fee">
<input type="text" class="price-input" placeholder="0.00">
```

### Name Inputs
```html
<input class="name-input" type="text" name="client_name">
<input type="text" data-validate="name" name="name">
```

### Quantity Inputs
```html
<input class="quantity-input" type="number" name="quantity">
<input type="number" data-validate="positive" name="count">
```

---

## Common Tasks

### Reject a Quotation in Code
```php
$quotation = Quotation::find($id);
if (!$quotation->isRejected()) {
    $quotation->reject('Reason for rejection', auth()->id());
}
```

### Create Add-on Quotation
```php
$parent = Quotation::find($parentId);
$addon = Quotation::create([
    'subject' => 'Add-on Service',
    'employee_id' => auth()->id(),
    'client_id' => $parent->client_id,
    'status_id' => $statusId,
    'parent_quotation_id' => $parent->id,
    'quotation_type' => 'addon',
    'public_token' => bin2hex(random_bytes(16)),
]);
```

### Get All Linked Quotations
```php
$quotation = Quotation::with('linkedQuotations', 'parentQuotation')->find($id);
$all = $quotation->getAllLinkedQuotations();
```

---

## Relationship Queries

### Get Quotations with Linked Count
```php
$quotations = Quotation::withCount('linkedQuotations')->get();
// Use: $quotation->linked_quotations_count
```

### Get Only Rejected Quotations
```php
$rejected = Quotation::whereNotNull('rejected_at')->get();
```

### Get Only Add-ons
```php
$addons = Quotation::where('quotation_type', 'addon')->get();
```

### Get Quotations Rejected by Specific User
```php
$rejected = Quotation::where('rejected_by', $userId)->get();
```

---

## Files Location Reference

| Task | File |
|------|------|
| Database schema | `database/migrations/2025_12_05_000001_add_rejection_to_quotations.php` |
| Model logic | `app/Models/Quotation.php` |
| Controller methods | `app/Http/Controllers/QuotationController.php` |
| Validation rules | `app/Http/Requests/StoreQuotationRequest.php` |
| Rejection validation | `app/Http/Requests/RejectQuotationRequest.php` |
| Rejection modal | `resources/views/quotations/partials/rejection-modal.blade.php` |
| Linked quotations view | `resources/views/quotations/partials/linked-quotations.blade.php` |
| Add-on modal | `resources/views/quotations/partials/add-linked-quotation-modal.blade.php` |
| JavaScript utils | `public/assets/js/quotation-validation.js` |
| CSS styles | `public/assets/css/quotation-management.css` |
| API routes | `routes/api.php` |

---

## Debugging Tips

### Check Validation Errors
```php
// In controller
if ($validator->fails()) {
    return response()->json(['errors' => $validator->errors()], 422);
}
```

### Log Rejection Details
```php
Log::info('Quotation rejected', [
    'quotation_id' => $quotation->id,
    'rejected_by' => auth()->id(),
    'reason' => $reason
]);
```

### Test Price Formatting
```javascript
console.log(QuotationValidation.formatPrice('10000'));    // '10,000'
console.log(QuotationValidation.formatPrice('1000000.50')); // '1,000,000.50'
```

### Test Name Validation
```javascript
console.log(QuotationValidation.isValidName('John Doe'));    // true
console.log(QuotationValidation.isValidName('John123'));     // false
```

---

## Troubleshooting

### Price formatting not working
- ✅ Ensure CSS is loaded: `public/assets/css/quotation-management.css`
- ✅ Ensure JS is loaded: `public/assets/js/quotation-validation.js`
- ✅ Check input has correct class: `.price-input`

### Rejection modal not showing
- ✅ Verify modal include in Blade template
- ✅ Ensure reject button has data-quotation-id attribute
- ✅ Check browser console for JavaScript errors

### Validation not triggering
- ✅ Verify Form Request is used in controller
- ✅ Check validation rules syntax
- ✅ Ensure input names match validation rules

### API endpoint 404
- ✅ Verify routes added to `routes/api.php`
- ✅ Check controller method names
- ✅ Ensure middleware is applied (auth:sanctum)

---

## Performance Optimization

### Eager Load Related Data
```php
$quotation = Quotation::with([
    'linkedQuotations',
    'parentQuotation',
    'rejectedBy',
    'client',
    'employee'
])->find($id);
```

### Cache Linked Quotations
```php
$cached = Cache::remember(
    "quotation.{$id}.linked",
    3600,
    fn() => $quotation->getAllLinkedQuotations()
);
```

### Paginate Add-ons
```php
$addons = $quotation->linkedQuotations()
    ->orderBy('created_at', 'desc')
    ->paginate(15);
```

---

## Testing Commands

```bash
# Run migrations
php artisan migrate

# Test API endpoint
curl -X POST http://localhost:8000/api/quotations/1/reject \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"rejection_reason":"Budget exceeded"}'

# Clear cache if needed
php artisan cache:clear

# Clear config cache if .env changed
php artisan config:clear
```

---

**Quick Reference Version:** 1.0
**Last Updated:** December 5, 2024
