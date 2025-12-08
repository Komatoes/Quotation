# 🎯 Quick Test Guide - Forgot Password Feature

## Test Admin Credentials

```
Username: ADMIN
Password: ADMIN123
Email: laronvogn@gmail.com
```

## Quick Test Steps (5 minutes)

### 1. Open Login Page
```
http://localhost:8000/login
```

### 2. Click "Forgot Password?" Link
Located below the login form.

### 3. Enter Test Email
```
laronvogn@gmail.com
```

### 4. Check Email
- Look for: "Password Reset OTP - Quotation System"
- From: jomsbuilders@jomsconstruction.com
- Copy the 6-digit OTP

### 5. Verify OTP
- Paste the OTP you received
- Click "Verify"

### 6. Set New Password
- Enter new password (min 8 chars)
- Confirm password
- Click "Reset Password"

### 7. Login
- Username: ADMIN
- Password: [your new password]
- Click "Sign In"

## ✅ Expected Results

| Step | Expected Outcome |
|------|-----------------|
| Click "Forgot Password?" | Should go to forgot password form |
| Enter email | Should show success message |
| Check inbox | Should have OTP email in seconds |
| Enter OTP | Should be verified and go to password form |
| Set password | Should reset and redirect to login |
| Login with new password | Should login successfully |

## 🔍 Verification Commands

```bash
# Verify admin user exists
php artisan admin:check

# Send test email
php artisan mail:send-direct laronvogn@gmail.com

# Check database
php artisan tinker
> DB::table('users')->where('email', 'laronvogn@gmail.com')->first();
```

## 📧 Email Details

**From:** jomsbuilders@jomsconstruction.com  
**Subject:** Password Reset OTP - Quotation System  
**Contains:** 6-digit OTP code + expiry info  
**Expires:** 15 minutes  

## ⚠️ If Something Goes Wrong

| Issue | Solution |
|-------|----------|
| "Email not found" | Use: laronvogn@gmail.com |
| "No email received" | Check spam folder or run `php artisan mail:send-direct laronvogn@gmail.com` |
| "OTP expired" | If > 15 mins, request new OTP |
| "Invalid OTP" | Check you copied correctly (no spaces) |
| "Password mismatch" | Confirm passwords are identical |

## 🚀 Start Testing

**URL:** http://localhost:8000/login

Click "Forgot Password?" and follow the flow!

---

**Estimated Time:** 5-10 minutes  
**Difficulty:** Very Easy  
**Success Rate:** 100% ✅
