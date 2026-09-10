# Candycutz — Operator Triage & Salon Incident Runbook

## 1. Document Scope & Audience
This runbook is authored specifically for **Salon Managers, Front Desk Receptionists, and Support Staff** operating the Candycutz flagship salon in Angwan Kare, Keffi, Nasarawa State.
No software development or DevOps knowledge is assumed. Follow the numbered decision matrices below for rapid incident resolution.

---

## 2. Salon Grounding & Operational Topology
- **Salon Location**: Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa State, Nigeria.
- **Operating Hours**: Monday – Saturday: 08:00 – 20:00 | Sunday: 10:00 – 18:00.
- **Home Service Operational Zones**: Angwan Kare, High Court, Total, Gidan Zakara, NSUK Main Campus, NSUK Pyanku Campus.
- **Primary Operator Dashboard**: `https://candycutz.com/admin` (or local fallback `http://localhost:8000/admin`).

---

## 3. Incident Triage Matrices

### Scenario 1: Power Outage or Local Network Disconnection
*Keffi municipal grid downtime or generator switchover causing temporary loss of salon Wi-Fi.*

1. **Keep Physical Queue Running**:
   - Do NOT turn away walk-in customers.
   - Use the physical salon logbook at the reception desk to note: Customer Name, Phone Number, Barber Requested, Service Type, and Agreed Fee.
2. **Mobile Hotspot Switchover**:
   - Reception staff should immediately connect the salon tablet/computer to the designated backup cellular hotspot (MTN/Airtel 4G/5G).
3. **Queue Reconciliation**:
   - Once internet is restored, open the Barber or Admin Dashboard.
   - Navigate to **Walk-In Entry** (`/barber/walk-in` or `/admin/appointments`).
   - Log each walk-in using the exact timestamps noted in the physical logbook. The backend will automatically apply pessimistic locks and reserve those slots in the central database.

---

### Scenario 2: Walk-In vs Online Booking Slot Conflict
*A walk-in customer is seated in a chair, but an online client arrives claiming they booked the exact same 14:00 slot with the same barber.*

1. **Verify Official Booking Status**:
   - Ask the arriving online customer to show their booking confirmation on their CandyCutz Mobile App or email receipt.
   - Check the **Booking Reference** (e.g., `CC-XXXXX`) in the Admin CMS under **Appointments**.
2. **Determine Priority**:
   - **Case A: Online Booking is `CONFIRMED` with Deposit Paid**:
     - The online customer has locked the slot via the platform's pessimistic concurrency engine.
     - Graciously offer the walk-in customer the next available chair with another master barber, or offer complimentary luxury lounge seating and refreshments.
   - **Case B: Online Booking is `PENDING` (Deposit Unpaid)**:
     - Unpaid reservations expire automatically after the 15-minute checkout window.
     - The seated walk-in takes priority if the online slot was not locked with a confirmed deposit.

---

### Scenario 3: Customer Debited but Appointment Shows "Pending Payment"
*A customer completed payment via card or bank transfer, funds were deducted from their account, but the app or website shows "Pending".*

1. **Do NOT Ask the Customer to Pay Again Immediately**.
2. **Inspect Payment Gateway**:
   - Log into the Stripe or Paystack merchant dashboard.
   - Search for the customer's email or phone number in recent transactions.
3. **If Payment is Marked "Successful" on the Gateway**:
   - The webhook event may have suffered a brief network retry delay.
   - In the CandyCutz Admin CMS:
     1. Search for the customer's appointment under **Appointments**.
     2. Click **View Details**.
     3. Click **Mark Deposit Paid** and set status to `Confirmed`.
     4. Enter the Gateway Reference (e.g., `ch_3M...` or `pay_...`) in the notes field.
4. **If Payment is "Failed" or "Abandoned" on Gateway**:
   - The customer's bank has placed a hold that will be reversed automatically by their bank within 24–48 hours.
   - Offer the customer to pay at the front-desk POS or cash, and provide a printed/electronic receipt.

---

### Scenario 4: Barber Sudden Absence / Emergency Schedule Block
*A master stylist falls ill or has an emergency and cannot service scheduled clients for the afternoon.*

1. **Block Future Slots Immediately**:
   - Open Admin CMS -> **Barbers** -> Select Barber -> Click **Schedule / Block Time**.
   - Select the date and time range (e.g., Today 13:00 – 20:00).
   - Reason: "Emergency / Sick Leave". This instantly prevents online users from reserving these slots.
2. **Reassign or Reschedule Existing Appointments**:
   - In the Appointments list, filter by that barber for the affected date.
   - For each booked appointment:
     - Call the customer immediately using the phone number in their profile.
     - Offer:
       1. Reassignment to another on-duty barber at the same time.
       2. Rescheduling to a future date/time of their choice.
       3. Full refund of their deposit (processed via Admin CMS -> Refund Deposit).

---

### Scenario 5: Customer Requests Account Deactivation or Data Deletion
*A customer requests account closure in person or by phone.*

1. **Self-Service Option (Preferred)**:
   - Guide the customer to open their **CandyCutz Mobile App**.
   - Navigate to **Profile** -> **Security & Privacy** -> **Deactivate Account**.
   - Confirming their password soft-deactivates the account instantly and revokes all active login sessions.
2. **Web Option**:
   - Direct the customer to `https://candycutz.com/account-deletion` to submit a self-service deactivation request.
3. **Admin Assisted Option**:
   - SuperAdmins can deactivate accounts directly in the CMS:
     - Go to **Users / Customers** -> Select Customer -> Click **Deactivate Account**.
     - The system sets `is_active = 0`, `status = 'deactivated'`, and wipes active tokens while preserving historical invoices for statutory tax records.

---

### Scenario 6: Home Service Dispatch Verification
*A customer requests luxury grooming at their residence.*

1. **Verify Allowed Zone**:
   - Home service is strictly limited to verified zones in Keffi:
     - Angwan Kare (Flagship zone — ₦0 travel fee)
     - High Court (₦1,000 travel surcharge)
     - Total / Market area (₦1,500 travel surcharge)
     - Gidan Zakara (₦2,000 travel surcharge)
     - NSUK Main Campus (₦2,500 travel surcharge)
     - NSUK Pyanku Campus (₦2,500 travel surcharge)
2. **Dispatch Protocol**:
   - Ensure the assigned barber has the portable luxury grooming kit, battery-backed clippers (fully charged), and sanitation equipment ready at least 30 minutes prior to the appointment.

---

## 4. Contact & Escalation Protocol

| Severity | Situation | Contact Person | Channel |
|---|---|---|---|
| **P1 — Critical** | Platform completely inaccessible, power/generator failure | General Manager / DevOps On-Call | Direct Phone / WhatsApp Emergency Group |
| **P2 — High** | Stripe/Paystack payment gateway errors affecting all clients | Lead Administrator | Urgent WhatsApp / Call |
| **P3 — Normal** | Individual customer booking dispute, refund approval | Salon Front-Desk Supervisor | Admin Portal / Internal Slack |
| **P4 — Low** | General feedback, catalog price adjustment requests | Content / Marketing Lead | Email: `concierge@candycutz.com` |
