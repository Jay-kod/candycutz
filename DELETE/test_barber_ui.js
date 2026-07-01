const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  
  // Login as barber
  await page.goto('http://localhost:5173/barber/login');
  await page.fill('input[type="email"]', 'barber@gmail.com');
  await page.fill('input[type="password"]', 'password123');
  await page.click('button[type="submit"]');
  
  // Wait for login to complete
  await page.waitForTimeout(3000);
  
  // Go to Payments page
  await page.goto('http://localhost:5173/barber/payments');
  await page.waitForTimeout(2000);
  
  // Screenshot the payments page
  await page.screenshot({ path: 'barber_payments.png' });
  
  // Click 'Verify Payment' button if exists
  const verifyButton = await page.$('button:has-text("Verify Payment")');
  if (verifyButton) {
      console.log('Verify Payment button found! Clicking it...');
      await verifyButton.click();
      await page.waitForTimeout(2000);
      
      // Screenshot the modal
      await page.screenshot({ path: 'barber_receipt_modal.png' });
      
      // Check for action buttons in modal
      const approveBtn = await page.$('button:has-text("Approve Payment")');
      const rejectBtn = await page.$('button:has-text("Reject")');
      
      console.log('Approve Button:', approveBtn ? 'Found' : 'Not Found');
      console.log('Reject Button:', rejectBtn ? 'Found' : 'Not Found');
  } else {
      console.log('Verify Payment button not found on the page.');
  }

  await browser.close();
})();
