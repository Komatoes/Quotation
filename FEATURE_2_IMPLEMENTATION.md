# Feature 2: Quotation Management Enhancements

## Implementation Guide

This guide covers the implementation of the three main requirements for quotation management enhancements:

1. **UI & Input Improvements** - Price formatting, form validation
2. **Rejection Handling** - Required "Reason for Rejection" field
3. **Linked Quotations** - Support nested/associated quotations

---

## Database Changes

### Migration Files Created

#### 1. `add_rejection_to_quotations.php`
This migration adds columns to the `quotations` table:

- **`rejection_reason`** (TEXT, nullable) - Stores the reason why a quotation was rejected
- **`rejected_at`** (TIMESTAMP, nullable) - Records when the quotation was rejected
- **`rejected_by`** (BIGINT unsigned, nullable) - References the user who rejected it
- **`parent_quotation_id`** (BIGINT unsigned, nullable) - Links to parent quotation for add-ons
- **`quotation_type`** (STRING) - 'standalone' or 'addon' (default: 'standalone')

**Run Migration:**
```bash
php artisan migrate
```

---

## Model Updates

### Quotation.php Enhancements

#### New Attributes
```php
protected $fillable = [
    // ... existing fields ...
    'rejection_reason',
    'rejected_at',
    'rejected_by',
    'parent_quotation_id',
    'quotation_type'
];

protected $casts = [
    'rejected_at' => 'datetime',
    'customer_approved' => 'boolean',
    'provider_approved' => 'boolean',
];
```

#### New Relationships
```php
// Get the user who rejected this quotation
public function rejectedBy()

// Get the parent quotation if this is an add-on
public function parentQuotation()

// Get all linked/add-on quotations
public function linkedQuotations()
```

#### New Helper Methods
```php
// Check if quotation has been rejected
public function isRejected(): bool

// Reject the quotation with a reason
public function reject(string $reason, int $rejectedById): void

// Get all linked quotations (parent + children)
public function getAllLinkedQuotations()
```

---

## Controller Updates

### QuotationController.php Enhancements

#### 1. New `reject()` Method
**Endpoint:** `POST /api/quotations/{quotation}/reject`

**Request Body:**
```json
{
    "rejection_reason": "The quotation exceeds the budget constraints..."
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Quotation rejected successfully",
    "quotation": { /* quotation data with rejectedBy user */ }
}
```

**Features:**
- Validates rejection reason (min 10, max 1000 characters)
- Only allows creator to reject their quotation
- Prevents re-rejection
- Logs rejection with user ID and timestamp

#### 2. New `createLinkedQuotation()` Method
**Endpoint:** `POST /api/quotations/{parentQuotationId}/linked`

**Request Body:**
```json
{
    "subject": "Add-on Installation Service",
    "description": "Additional installation charges...",
    "labor_fee": 500.00,
    "delivery_fee": 50.00,
    "status_id": 1,
    "quotation_type": "addon"
}
```

**Response (Success - 201):**
```json
{
    "success": true,
    "message": "Add-on quotation created successfully",
    "quotation": { /* new quotation data */ }
}
```

**Features:**
- Creates a child quotation linked to parent
- Inherits client from parent quotation
- Generates unique public token
- Only allows creator to add add-ons
- Logs creation with parent/child relationship

#### 3. New `getLinkedQuotations()` Method
**Endpoint:** `GET /api/quotations/{quotation}/linked`

**Response:**
```json
{
    "success": true,
    "quotations": [
        { /* parent quotation */ },
        { /* current quotation */ },
        { /* linked add-ons */ }
    ]
}
```

---

## Form Request Validation

### StoreQuotationRequest.php
Comprehensive validation for quotation creation/update:

**Validation Rules:**
- `subject` - Required, max 255, alphanumeric + spaces/hyphens/commas/ampersands
- `description` - Optional, max 5000 characters
- `client_id` - Required, must exist in clients table
- `status_id` - Required, must exist in quotation_statuses table
- `labor_fee` - Optional, numeric, min 0, max 999999.99
- `delivery_fee` - Optional, numeric, min 0, max 999999.99
- `parent_quotation_id` - Optional, must exist in quotations table
- `quotation_type` - Required, must be 'standalone' or 'addon'

**Features:**
- Strips commas from price fields before validation
- Sets default quotation_type to 'standalone'
- Custom error messages for each rule
- Prevents negative values at validation level

### RejectQuotationRequest.php
Validation for quotation rejection:

**Validation Rules:**
- `rejection_reason` - Required, min 10 characters, max 1000 characters

**Features:**
- Enforces meaningful rejection reasons
- Custom error messages
- Prevents empty or trivial rejections

---

## Frontend Components

### 1. Rejection Modal (`rejection-modal.blade.php`)

**Features:**
- Modal form for rejection reason input
- Real-time character count (10-1000)
- Form validation before submission
- Loading state during API call
- Error handling and display

**Usage in Blade:**
```blade
@include('quotations.partials.rejection-modal')

<!-- Add reject button to quotation -->
<button class="btn btn-danger btn-reject-quotation" data-quotation-id="{{ $quotation->id }}">
    <i class="fas fa-times-circle"></i> Reject
</button>
```

### 2. Linked Quotations Display (`linked-quotations.blade.php`)

**Features:**
- Shows parent quotation if exists
- Lists all linked add-on quotations
- Displays status badges with color coding
- Shows financial details (labor fee, delivery fee)
- Action buttons to view linked quotations
- Button to add new add-ons (creator only)

**Usage in Blade:**
```blade
@include('quotations.partials.linked-quotations', ['quotation' => $quotation])
```

### 3. Add Linked Quotation Modal (`add-linked-quotation-modal.blade.php`)

**Features:**
- Form to create new add-on quotation
- Subject, description, fees, status fields
- Real-time validation feedback
- Currency formatting for fee inputs
- Client-side validation before API call
- Loading state during submission

**Usage in Blade:**
```blade
@include('quotations.partials.add-linked-quotation-modal', [
    'quotation' => $quotation,
    'statuses' => $statuses
])
```

---

## JavaScript Utilities

### quotation-validation.js

**Features:**

#### Price Formatting
```javascript
QuotationValidation.formatPrice('10000')  // Returns: '10,000'
QuotationValidation.formatPrice('1000000.50')  // Returns: '1,000,000.50'
QuotationValidation.getNumericValue('10,000')  // Returns: 10000
```

**Auto-applied to:**
- `.price-input` class
- `input[type="currency"]`

**Behavior:**
- Adds thousand separators while typing
- Limits to 2 decimal places
- Prevents negative values
- Formats on blur for consistency

#### Name Field Validation
```javascript
QuotationValidation.isValidName('John Doe')  // Returns: true
QuotationValidation.isValidName('John123')  // Returns: false
```

**Auto-applied to:**
- `.name-input` class
- `input[data-validate="name"]`

**Behavior:**
- Allows: letters, spaces, hyphens, apostrophes
- Removes invalid characters in real-time
- Shows validation error messages
- Clears errors on valid input

#### Quantity Validation
**Auto-applied to:**
- `.quantity-input` class
- `input[type="number"][data-validate="positive"]`

**Behavior:**
- Prevents negative values
- Resets to 0 if negative entered
- Shows validation error

#### Error Display
```javascript
QuotationValidation.showValidationError(input, 'Error message');
QuotationValidation.clearValidationError(input);
```

**Dynamic Initialization:**
- MutationObserver watches for new form elements
- Auto-initializes validation on dynamically added inputs
- No manual re-initialization needed

---

## CSS Styles

### quotation-management.css

**Styling for:**
- Price input formatting (right-aligned, monospace font)
- Validation error feedback (red text, error icons)
- Rejection reason display box (red background)
- Linked quotations cards (blue accent)
- Modal headers and footers
- Button states and hover effects
- Responsive adjustments for mobile

---

## API Routes

Add these to `routes/api.php`:

```php
Route::middleware('auth:sanctum')->group(function () {
    // Reject a quotation with required reason
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])
        ->name('quotations.reject');
    
    // Create a linked/add-on quotation
    Route::post('/quotations/{parentQuotationId}/linked', [QuotationController::class, 'createLinkedQuotation'])
        ->name('quotations.createLinked');
    
    // Get all linked quotations for a quotation
    Route::get('/quotations/{quotation}/linked', [QuotationController::class, 'getLinkedQuotations'])
        ->name('quotations.getLinked');
});
```

---

## Implementation Checklist

### Database Setup
- [ ] Run migration: `php artisan migrate`
- [ ] Verify new columns in `quotations` table

### Code Integration
- [ ] Updated `Quotation.php` with new relationships
- [ ] Updated `QuotationController.php` with new methods
- [ ] Created `StoreQuotationRequest.php`
- [ ] Created `RejectQuotationRequest.php`
- [ ] Added API routes to `routes/api.php`

### Frontend Integration
- [ ] Add partial includes to quotation view:
  - `rejection-modal.blade.php`
  - `linked-quotations.blade.php`
  - `add-linked-quotation-modal.blade.php`

### Asset Integration
- [ ] Link CSS: `<link rel="stylesheet" href="{{ asset('assets/css/quotation-management.css') }}">`
- [ ] Link JS: `<script src="{{ asset('assets/js/quotation-validation.js') }}"></script>`

### Testing
- [ ] Test price formatting with various inputs
- [ ] Test name validation blocking numbers
- [ ] Test rejection flow with validation
- [ ] Test creating linked quotations
- [ ] Test preventing negative values
- [ ] Test all error messages display correctly

---

## Usage Examples

### Rejecting a Quotation
```javascript
// JavaScript
fetch('/api/quotations/1/reject', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({
        rejection_reason: 'Budget exceeded. Please provide a revised quotation.'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Creating Add-on Quotation
```javascript
fetch('/api/quotations/1/linked', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({
        subject: 'Installation Service',
        description: 'Professional installation of delivered items',
        labor_fee: 500,
        delivery_fee: 100,
        status_id: 1,
        quotation_type: 'addon'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Getting Linked Quotations
```javascript
fetch('/api/quotations/1/linked')
    .then(response => response.json())
    .then(data => console.log(data.quotations));
```

---

## Error Handling

### Common Errors and Solutions

**Error:** "You are not authorized to reject this quotation"
- **Cause:** Only quotation creator can reject
- **Solution:** Use the appropriate user account that created the quotation

**Error:** "This quotation has already been rejected"
- **Cause:** Attempting to reject a previously rejected quotation
- **Solution:** Cannot re-reject; use a different quotation

**Error:** "Rejection reason must be at least 10 characters long"
- **Cause:** Rejection reason too short
- **Solution:** Provide more detailed rejection reason

**Error:** "Subject can only contain letters, numbers, spaces, hyphens, dots, commas, and ampersands"
- **Cause:** Invalid characters in quotation subject
- **Solution:** Remove special characters from subject

**Error:** "Labor fee cannot be negative"
- **Cause:** Negative value entered in price field
- **Solution:** Enter positive values only (negative values auto-corrected)

---

## Performance Considerations

1. **Eager Loading**: Use `load()` or `with()` for relationships
   ```php
   $quotation->load('linkedQuotations', 'parentQuotation', 'rejectedBy');
   ```

2. **Pagination**: For quotations with many add-ons:
   ```php
   $quotation->linkedQuotations()->paginate(15);
   ```

3. **Caching**: Cache quotation relationships if frequently accessed
   ```php
   Cache::remember("quotation.{$id}.linked", 3600, function() {
       return $quotation->getAllLinkedQuotations();
   });
   ```

---

## Security Notes

1. **Authorization**: Only quotation creator can reject or manage linked quotations
2. **Validation**: All inputs validated server-side using Form Requests
3. **CSRF Protection**: All API endpoints protected with CSRF token
4. **Rate Limiting**: Consider adding rate limiting to rejection and creation endpoints
5. **Audit Logging**: All rejections logged with user ID and timestamp

---

## Future Enhancements

1. **Batch Operations**: Reject multiple quotations at once
2. **Workflows**: Automatic status updates when linked quotations reach certain states
3. **Versioning**: Track quotation versions when creating add-ons
4. **Templates**: Create quotation templates with pre-configured add-ons
5. **Notifications**: Notify users when quotations are rejected or add-ons created
6. **Analytics**: Track rejection reasons and reasons statistics

---

## Support & Troubleshooting

For detailed code review, check:
- `app/Models/Quotation.php` - Model relationships
- `app/Http/Controllers/QuotationController.php` - Controller methods
- `app/Http/Requests/StoreQuotationRequest.php` - Validation rules
- `resources/views/quotations/partials/` - Blade templates
- `public/assets/js/quotation-validation.js` - Frontend validation

---

**Last Updated:** December 5, 2024
**Version:** 1.0
