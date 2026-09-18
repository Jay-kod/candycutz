<template>
  <div class="report-page relative border-t border-gray-200">
    <div class="report-watermark"></div>
    <div class="relative z-10 px-10 py-8">
      <!-- Top Barbers Section -->
      <h2 style="font-size: 16px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 20px 0; padding-bottom: 8px; border-bottom: 2px solid #FF6700;">
        Top Performing Barbers
      </h2>

      <div v-if="topBarbers.length > 0" style="margin-bottom: 40px;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px;">
          <thead>
            <tr>
              <th style="text-align: left; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Rank</th>
              <th style="text-align: left; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Barber Name</th>
              <th style="text-align: center; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Bookings</th>
              <th style="text-align: center; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Rating</th>
              <th style="text-align: right; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Revenue</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(barber, index) in topBarbers" :key="'pdf-barber-' + barber.id" style="background: transparent;">
              <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db;">
                <div :style="{ 
                  width: '28px', height: '28px', borderRadius: '8px', display: 'flex', alignItems: 'center', justifyContent: 'center', 
                  fontWeight: 700, fontSize: '12px', color: 'white',
                  background: index === 0 ? '#FF6700' : index === 1 ? '#6b7280' : '#9ca3af'
                }">{{ index + 1 }}</div>
              </td>
              <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; font-weight: 600; color: #111827;">{{ barber.name }}</td>
              <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; text-align: center; color: #374151;">{{ barber.bookings }}</td>
              <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; text-align: center; color: #d97706;">⭐ {{ barber.rating || 'N/A' }}</td>
              <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; text-align: right; font-weight: 700; color: #059669;">₦{{ Number(barber.revenue).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else style="color: #9ca3af; font-size: 13px; text-align: center; padding: 20px 0; margin-bottom: 40px;">No barber performance data available for this period.</p>

      <!-- Top Services Section -->
      <h2 style="font-size: 16px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 20px 0; padding-bottom: 8px; border-bottom: 2px solid #FF6700;">
        Most Popular Services
      </h2>

      <div v-if="topServices.length > 0" style="margin-bottom: 32px;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px;">
          <thead>
            <tr>
              <th style="text-align: left; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Rank</th>
              <th style="text-align: left; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Service Name</th>
              <th style="text-align: center; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Times Booked</th>
              <th style="text-align: right; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Revenue</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(service, index) in topServices" :key="'pdf-service-' + index" style="background: transparent;">
              <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db;">
                <div :style="{ 
                  width: '28px', height: '28px', borderRadius: '8px', display: 'flex', alignItems: 'center', justifyContent: 'center', 
                  fontWeight: 700, fontSize: '12px', color: 'white',
                  background: index === 0 ? '#059669' : index === 1 ? '#6b7280' : '#9ca3af'
                }">{{ index + 1 }}</div>
              </td>
              <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; font-weight: 600; color: #111827;">{{ service.name }}</td>
              <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; text-align: center; color: #374151;">{{ service.count }}</td>
              <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; text-align: right; font-weight: 700; color: #ea580c;">₦{{ Number(service.revenue).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else style="color: #9ca3af; font-size: 13px; text-align: center; padding: 20px 0;">No service data available for this period.</p>

      <!-- Footer & Authentication -->
      <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
          <p style="font-size: 10px; color: #9ca3af; margin: 0;">This report is auto-generated by CandyCutz Management System.</p>
          <p style="font-size: 10px; color: #9ca3af; margin: 2px 0 0 0;">Report Period: {{ activeRangeLabel }} • Generated: {{ todayFormatted }} at {{ currentTime }}</p>
          
          <!-- Digital Signature / Authenticity stamp -->
          <div style="margin-top: 12px; display: flex; gap: 8px; align-items: center; background: #f9fafb; padding: 6px 10px; border-radius: 6px; border: 1px dashed #d1d5db; width: fit-content;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; color: #059669;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>
            <div>
              <p style="font-size: 8px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.1em; margin: 0; font-weight: 700;">Authenticity Verification ID</p>
              <p style="font-size: 11px; font-family: monospace; font-weight: 800; color: #111827; margin: 2px 0 0 0; letter-spacing: 0.05em;">{{ reportId }}</p>
            </div>
          </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <div style="width: 24px; height: 24px; border-radius: 6px; background: #FF6700; display: flex; align-items: center; justify-content: center;">
            <span style="font-size: 12px;">✂️</span>
          </div>
          <span style="font-size: 11px; font-weight: 700; color: #374151;">CandyCutz</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  topBarbers: { type: Array, required: true },
  topServices: { type: Array, required: true },
  activeRangeLabel: { type: String, required: true },
  todayFormatted: { type: String, required: true },
  currentTime: { type: String, required: true },
  reportId: { type: String, required: true }
})
</script>
