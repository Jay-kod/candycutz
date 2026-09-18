<template>
  <div class="report-page">
    <div class="report-watermark"></div>
    <div class="relative z-10 px-10 py-12">
      <!-- Header -->
      <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #f3f4f6; padding-bottom: 24px;">
        <div>
          <h1 style="font-size: 28px; font-weight: 900; color: #111827; margin: 0; letter-spacing: -0.02em;">CANDYCUTZ</h1>
          <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.2em; color: #FF6700; margin: 4px 0 0 0; font-weight: 800;">Management System</p>
        </div>
        <div style="text-align: right;">
          <h2 style="font-size: 20px; font-weight: 700; color: #374151; margin: 0;">Operational Analytics</h2>
          <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">{{ todayFormatted }}</p>
          <p style="display: inline-block; background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 12px; margin-top: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Period: {{ activeRangeLabel }}</p>
        </div>
      </div>

      <!-- Executive Summary (KPIs) -->
      <h2 style="font-size: 16px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 16px 0; padding-bottom: 8px; border-bottom: 2px solid #FF6700;">Executive Summary</h2>
      
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 32px;">
        <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);">
          <p style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Total Revenue</p>
          <p style="font-size: 24px; font-weight: 800; color: #059669; margin: 0;">₦{{ Number(businessStats.total_revenue || 0).toLocaleString() }}</p>
        </div>
        <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);">
          <p style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Completed Appointments</p>
          <p style="font-size: 24px; font-weight: 800; color: #ea580c; margin: 0;">{{ businessStats.total_appointments || 0 }}</p>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 40px;">
        <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);">
          <p style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">New Clients</p>
          <p style="font-size: 18px; font-weight: 800; color: #2563eb; margin: 0;">{{ businessStats.new_customers || 0 }}</p>
          <p style="font-size: 9px; color: #9ca3af; margin: 4px 0 0 0;">In period</p>
        </div>
        <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: linear-gradient(135deg, #e0f2fe 0%, #ffffff 100%);">
          <p style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Total Clients</p>
          <p style="font-size: 18px; font-weight: 800; color: #0284c7; margin: 0;">{{ businessStats.total_customers || 0 }}</p>
          <p style="font-size: 9px; color: #9ca3af; margin: 4px 0 0 0;">All time</p>
        </div>
        <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: linear-gradient(135deg, #faf5ff 0%, #ffffff 100%);">
          <p style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Blog Posts</p>
          <p style="font-size: 18px; font-weight: 800; color: #7c3aed; margin: 0;">{{ platformStats.total_blog_posts || 0 }}</p>
          <p style="font-size: 9px; color: #9ca3af; margin: 4px 0 0 0;">Published</p>
        </div>
      </div>

      <!-- Appointment Status -->
      <h2 style="font-size: 16px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 16px 0; padding-bottom: 8px; border-bottom: 2px solid #FF6700;">Appointment Status Breakdown</h2>
      
      <div v-if="statusBreakdown.length > 0" style="margin-bottom: 32px;">
        <!-- Visual Bar Chart -->
        <div style="display: flex; flex-direction: column; gap: 12px;">
          <div v-for="status in statusBreakdown" :key="'pdf-status-' + status.status" style="display: flex; align-items: center; gap: 12px;">
            <span style="width: 90px; text-align: right; font-size: 12px; font-weight: 600; text-transform: capitalize; color: #374151;">{{ status.status }}</span>
            <div style="flex: 1; height: 28px; background: #f3f4f6; border-radius: 8px; overflow: hidden; position: relative;">
              <div :style="{ 
                width: totalAppointments > 0 ? Math.max((status.count / totalAppointments) * 100, 4) + '%' : '4%', 
                height: '100%', 
                borderRadius: '8px',
                background: statusBarColor(status.status),
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'flex-end',
                paddingRight: '10px',
                transition: 'width 0.5s ease'
              }">
                <span style="font-size: 11px; font-weight: 700; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">{{ status.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <p v-else style="color: #9ca3af; font-size: 13px; text-align: center; padding: 20px 0;">No appointment data available.</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  activeRangeLabel: { type: String, required: true },
  todayFormatted: { type: String, required: true },
  businessStats: { type: Object, required: true },
  platformStats: { type: Object, required: true },
  statusBreakdown: { type: Array, required: true },
  totalAppointments: { type: Number, required: true },
  statusBarColor: { type: Function, required: true }
})
</script>
