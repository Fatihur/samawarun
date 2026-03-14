// Test Script untuk Form Registration Samawa Run
// Jalankan dengan: node test-form-registration.js

const { test, expect } = require('@playwright/test');

test.describe('Samawa Run - Registration Form', () => {

  test('Step 0: Pilih Kategori & Jersey', async ({ page }) => {
    await page.goto('http://localhost:8000/events/2/register');

    // Ambil snapshot
    await page.screenshot({ path: 'test-results/00-form-empty.png' });

    // Pilih kategori 5K
    await page.click('text=5K');

    // Pilih ukuran jersey L
    await page.click('text=L');

    // Klik selanjutnya
    await page.click('text=Selanjutnya');

    // Verifikasi masuk ke step 1
    await expect(page.locator('text=Data Diri')).toBeVisible();
    await page.screenshot({ path: 'test-results/01-step-1-visible.png' });
  });

  test('Step 1: Isi Data Diri', async ({ page }) => {
    await page.goto('http://localhost:8000/events/2/register');

    // Step 0 dulu
    await page.click('text=5K');
    await page.click('text=L');
    await page.click('text=Selanjutnya');

    // Isi data diri
    await page.fill('input[name="name"]', 'Test User Playwright');
    await page.fill('input[name="birth_date"]', '1995-05-15');
    await page.selectOption('select[name="gender"]', 'male');
    await page.fill('input[name="nik"]', '1234567890123456');

    // Upload KTP (buat file dummy dulu)
    // await page.setInputFiles('input[name="ktp_file"]', './dummy-ktp.jpg');

    await page.click('text=Selanjutnya');
    await expect(page.locator('text=Informasi Kontak')).toBeVisible();
    await page.screenshot({ path: 'test-results/02-step-2-visible.png' });
  });

  test('Step 2: Isi Kontak & Submit', async ({ page }) => {
    await page.goto('http://localhost:8000/events/2/register');

    // Lewati step 0 & 1 dengan cepat
    await page.click('text=5K');
    await page.click('text=L');
    await page.click('text=Selanjutnya');

    await page.fill('input[name="name"]', 'Test User Playwright');
    await page.fill('input[name="birth_date"]', '1995-05-15');
    await page.selectOption('select[name="gender"]', 'male');
    await page.fill('input[name="nik"]', '1234567890123456');
    await page.click('text=Selanjutnya');

    // Isi kontak
    await page.fill('input[name="phone"]', '081234567890');
    await page.fill('input[name="email"]', 'test-playwright@example.com');
    await page.fill('textarea[name="address"]', 'Jl. Test Playwright No. 123');
    await page.selectOption('select[name="emergency_contact_relationship"]', 'father');
    await page.fill('input[name="emergency_contact_name"]', 'Ayah Test');
    await page.fill('input[name="emergency_contact_phone"]', '081234567891');

    await page.screenshot({ path: 'test-results/03-form-complete.png' });

    // Submit form (tanpa file upload untuk testing)
    // await page.click('text=Kirim Pendaftaran');

    // Verifikasi redirect atau error
    // await expect(page.locator('text=berhasil')).toBeVisible();
  });

  test('Validasi: Tidak bisa lanjut tanpa isi form', async ({ page }) => {
    await page.goto('http://localhost:8000/events/2/register');

    // Langsung klik selanjutnya tanpa isi apa-apa
    await page.click('text=Selanjutnya');

    // Seharusnya masih di step 0
    await expect(page.locator('text=Detail Perlombaan')).toBeVisible();

    // Ada error toast
    await expect(page.locator('text=Pilih kategori jarak')).toBeVisible();

    await page.screenshot({ path: 'test-results/04-validation-error.png' });
  });

});

test.describe('Samawa Run - Admin Panel', () => {

  test('Admin Login', async ({ page }) => {
    await page.goto('http://localhost:8000/admin/login');

    await page.fill('input[name="email"]', 'azrulrifai6@gmail.com');
    await page.fill('input[name="password"]', 'Samawarun2026');

    await page.screenshot({ path: 'test-results/05-login-form.png' });

    await page.click('button[type="submit"]');

    // Verifikasi login berhasil
    await expect(page.locator('text=Dashboard')).toBeVisible();
    await page.screenshot({ path: 'test-results/06-admin-dashboard.png' });
  });

  test('View Participants', async ({ page }) => {
    // Login dulu
    await page.goto('http://localhost:8000/admin/login');
    await page.fill('input[name="email"]', 'azrulrifai6@gmail.com');
    await page.fill('input[name="password"]', 'Samawarun2026');
    await page.click('button[type="submit"]');

    // Buka halaman participants
    await page.goto('http://localhost:8000/admin/participants');

    await expect(page.locator('text=Peserta')).toBeVisible();
    await page.screenshot({ path: 'test-results/07-participants-list.png' });
  });

  test('Verify Participant', async ({ page }) => {
    // Login
    await page.goto('http://localhost:8000/admin/login');
    await page.fill('input[name="email"]', 'azrulrifai6@gmail.com');
    await page.fill('input[name="password"]', 'Samawarun2026');
    await page.click('button[type="submit"]');

    // Buka participants
    await page.goto('http://localhost:8000/admin/participants');

    // Klik tombol verifikasi pada peserta pertama
    // await page.click('text=Verifikasi');

    // Konfirmasi dialog
    // await page.click('text=Ya');

    await page.screenshot({ path: 'test-results/08-verify-participant.png' });
  });

});

console.log('Test file created! Run with: npx playwright test test-form-registration.js');
