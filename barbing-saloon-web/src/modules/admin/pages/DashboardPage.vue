<template>
  <AdminLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Welcome Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-10 shadow-2xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/5 blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-admin/8 blur-[80px]"></div>
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[radial-gradient(circle_at_top_right,rgba(255,103,0,0.06),transparent_70%)]"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
          <div>
            <p class="text-[11px] uppercase tracking-[0.35em] text-admin/70 font-bold">Admin Control Center</p>
            <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg leading-tight">
              Operational <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-amber-400">Overview</span>
            </h1>
            <p class="mt-3 max-w-xl text-sm text-ivory/50 leading-relaxed">
              Monitor real-time metrics, track revenue, manage appointments and keep the saloon running at peak performance.
            </p>
          </div>

          <div class="flex items-center gap-5 shrink-0">
            <!-- Current Time -->
            <div class="hidden sm:flex flex-col items-end">
              <span class="text-2xl font-display text-theme-text tabular-nums">{{ currentTime }}</span>
              <span class="text-[10px] uppercase tracking-widest text-ivory/40 font-bold mt-0.5">{{ currentDate }}</span>
            </div>

            <div class="h-10 w-px bg-white/10 hidden sm:block"></div>

            <!-- System Status -->
            <div class="flex items-center gap-3 bg-emerald-500/5 px-4 py-2.5 rounded-xl border border-emerald-500/15">
              <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
              </span>
              <span class="text-xs font-semibold text-emerald-400 whitespace-nowrap">All Systems Operational</span>
            </div>
            
            <!-- Refresh Button -->
            <button @click="loadDashboardData(true)" class="flex items-center justify-center gap-2 rounded-xl bg-white/[0.05] border border-white/10 px-4 py-2 text-xs font-bold text-white transition-all hover:bg-white/10 shrink-0 group">
              <ArrowPathIcon class="h-4 w-4 group-hover:rotate-180 transition-transform duration-500" :class="{ 'animate-spin': isRefreshing }" />
              <span class="hidden sm:inline">Refresh</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid gap-5 grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 8" :key="i" class="h-36 rounded-2xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
      </div>

      <template v-else>
        <!-- KPI Row 1 - Primary Metrics -->
        <div class="grid gap-5 grid-cols-2 lg:grid-cols-4">
          <article v-for="card in primaryKpis" :key="card.label" @click="card.link && router.push(card.link)" class="group relative overflow-hidden rounded-3xl border p-6 backdrop-blur-xl transition-all duration-500 hover:shadow-2xl hover:-translate-y-1" :class="[card.bgClass || 'bg-charcoal border-white/[0.08] hover:border-white/20', card.link ? 'cursor-pointer' : '']">
            
            <!-- Watermark icon top-right background -->
            <div class="absolute -right-4 -top-4 opacity-[0.06] transition-all duration-700 group-hover:opacity-[0.12] group-hover:scale-110 group-hover:rotate-6 pointer-events-none" :class="card.iconColor">
              <component :is="card.icon" class="w-28 h-28" />
            </div>

            <div class="relative z-10 flex flex-col h-full justify-between gap-5">
              <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border" :class="card.iconWrap">
                  <component :is="card.icon" class="h-6 w-6" :class="card.iconColor" />
                </div>
                <div>
                  <h3 class="text-[11px] font-bold uppercase tracking-[0.25em] mb-1" :class="card.labelColor || 'text-ivory/60'">{{ card.label }}</h3>
                  <div class="flex items-baseline gap-2">
                    <span class="font-display text-4xl font-bold" :class="card.valueColor || 'text-white'">
                      {{ card.value }}
                    </span>
                    <span v-if="card.suffix" class="text-sm font-medium" :class="card.labelColor || 'text-ivory/40'">{{ card.suffix }}</span>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-between border-t border-white/5 pt-4">
                <div class="flex items-center gap-2">
                  <div v-if="card.badge" class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold border shadow-sm transition-all duration-300" :class="card.badgeClass || 'border-current/10'">
                    <component :is="card.badgeIcon" class="h-3 w-3" />
                    {{ card.badge }}
                  </div>
                  <span class="text-[11px] font-medium" :class="card.subColor || 'text-white/40'">{{ card.sub }}</span>
                </div>
              </div>
            </div>
          </article>
        </div>

        <!-- Revenue Row -->
        <div class="grid gap-5 grid-cols-1 sm:grid-cols-3">
          <article v-for="rev in revenueCards" :key="rev.label" class="group relative overflow-hidden rounded-3xl border border-emerald-500/15 bg-gradient-to-br from-[#112419] to-[#0a120d] p-6 backdrop-blur-xl transition-all duration-500 hover:border-emerald-400/30 hover:shadow-[0_8px_40px_rgba(16,185,129,0.15)] hover:-translate-y-1">
            <!-- Background mesh -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.08),transparent_50%)] opacity-50 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-emerald-500/10 blur-[40px] group-hover:bg-emerald-500/20 transition-colors duration-500"></div>
            
            <div class="relative z-10 flex flex-col justify-between h-full gap-6">
              <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-emerald-400/80">{{ rev.label }}</p>
                <BanknotesIcon class="h-8 w-8 text-emerald-400 group-hover:scale-110 transition-transform" />
              </div>
              <p class="text-4xl lg:text-5xl font-black tracking-tight font-sans text-white flex items-baseline gap-1.5 drop-shadow-md">
                <span class="text-2xl font-bold text-emerald-500/70">₦</span>
                {{ formatCurrency(rev.value) }}
              </p>
            </div>
          </article>
        </div>

        <!-- Main Grid -->
        <div class="grid gap-6 lg:grid-cols-5">
          <!-- Left Column (3/5) - Charts & Trends -->
          <div class="lg:col-span-3 space-y-6">
            <!-- Weekly Revenue Chart -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm overflow-hidden">
              <div class="flex items-center justify-between border-b border-white/[0.04] px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-admin/10 border border-admin/15">
                    <ChartBarIcon class="h-4.5 w-4.5 text-admin" />
                  </div>
                  <div>
                    <h2 class="text-sm font-bold text-theme-text">Weekly Revenue</h2>
                    <p class="text-[10px] text-ivory/35 mt-0.5">Last 7 days performance</p>
                  </div>
                </div>
                <span class="text-xs text-ivory/30 font-mono">{{ todayFormatted }}</span>
              </div>

              <div class="p-6 relative h-56">
                <!-- Line Graph Container -->
                <div class="absolute left-8 right-6 top-6 bottom-12 z-10">
                  <svg class="w-full h-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <!-- Area fill -->
                    <path :d="revenueGraphAreaPath" fill="url(#revLineGradient)" class="transition-all duration-500 ease-out opacity-40" />
                    <!-- Line -->
                    <path :d="revenueGraphPath" fill="none" stroke="#FF6700" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-out" style="filter: drop-shadow(0 0 8px rgba(255,103,0,0.5))" />
                    <defs>
                      <linearGradient id="revLineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#FF6700" stop-opacity="0.8" />
                        <stop offset="100%" stop-color="#FF6700" stop-opacity="0" />
                      </linearGradient>
                    </defs>
                  </svg>
                  
                  <!-- Interactive Data Points -->
                  <div v-for="(p, idx) in revenueGraphPoints" :key="'rev-point-'+idx" 
                       class="absolute w-3.5 h-3.5 -ml-[7px] -mt-[7px] rounded-full bg-charcoal border-2 border-admin shadow-[0_0_10px_rgba(255,103,0,0.6)] group/point cursor-pointer transition-all duration-300 hover:scale-[1.8] hover:bg-admin z-20"
                       :style="{ left: `${p.x}%`, top: `${p.y}%` }">
                    <!-- Tooltip -->
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 opacity-0 group-hover/point:opacity-100 transition-all duration-200 bg-obsidian border border-admin/30 text-xs px-3 py-1.5 rounded-lg shadow-xl text-admin font-bold whitespace-nowrap z-30 pointer-events-none scale-50 group-hover/point:scale-100 origin-bottom">
                      ₦{{ formatCurrency(p.revenue) }}
                    </div>
                  </div>

                  <!-- X-axis labels -->
                  <div v-for="(p, idx) in revenueGraphPoints" :key="'rev-label-'+idx"
                       class="absolute -bottom-8 w-16 -ml-8 flex justify-center"
                       :style="{ left: `${p.x}%` }">
                    <span class="text-[10px] text-ivory/40 uppercase tracking-wider font-bold">{{ p.day }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Weekly Bookings Chart -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm overflow-hidden">
              <div class="flex items-center justify-between border-b border-white/[0.04] px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500/10 border border-blue-500/15">
                    <CalendarDaysIcon class="h-4.5 w-4.5 text-blue-400" />
                  </div>
                  <div>
                    <h2 class="text-sm font-bold text-theme-text">Booking Activity</h2>
                    <p class="text-[10px] text-ivory/35 mt-0.5">Appointments per day</p>
                  </div>
                </div>
              </div>

              <div class="p-6 relative h-48">
                <!-- Line Graph Container -->
                <div class="absolute left-8 right-6 top-6 bottom-10 z-10">
                  <svg class="w-full h-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <!-- Area fill -->
                    <path :d="bookingGraphAreaPath" fill="url(#bookLineGradient)" class="transition-all duration-500 ease-out opacity-40" />
                    <!-- Line -->
                    <path :d="bookingGraphPath" fill="none" stroke="#60A5FA" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-out" style="filter: drop-shadow(0 0 8px rgba(96,165,250,0.5))" />
                    <defs>
                      <linearGradient id="bookLineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#60A5FA" stop-opacity="0.8" />
                        <stop offset="100%" stop-color="#60A5FA" stop-opacity="0" />
                      </linearGradient>
                    </defs>
                  </svg>
                  
                  <!-- Interactive Data Points -->
                  <div v-for="(p, idx) in bookingGraphPoints" :key="'book-point-'+idx" 
                       class="absolute w-3.5 h-3.5 -ml-[7px] -mt-[7px] rounded-full bg-charcoal border-2 border-blue-400 shadow-[0_0_10px_rgba(96,165,250,0.6)] group/point cursor-pointer transition-all duration-300 hover:scale-[1.8] hover:bg-blue-400 z-20"
                       :style="{ left: `${p.x}%`, top: `${p.y}%` }">
                    <!-- Tooltip -->
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 opacity-0 group-hover/point:opacity-100 transition-all duration-200 bg-obsidian border border-blue-400/30 text-xs px-3 py-1.5 rounded-lg shadow-xl text-blue-400 font-bold whitespace-nowrap z-30 pointer-events-none scale-50 group-hover/point:scale-100 origin-bottom">
                      {{ p.count }} bookings
                    </div>
                  </div>

                  <!-- X-axis labels -->
                  <div v-for="(p, idx) in bookingGraphPoints" :key="'book-label-'+idx"
                       class="absolute -bottom-8 w-16 -ml-8 flex justify-center"
                       :style="{ left: `${p.x}%` }">
                    <span class="text-[10px] text-ivory/40 uppercase tracking-wider font-bold">{{ p.day }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Appointments Table -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm overflow-hidden">
              <div class="flex items-center justify-between border-b border-white/[0.04] px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-500/10 border border-purple-500/15">
                    <ClipboardDocumentListIcon class="h-4.5 w-4.5 text-purple-400" />
                  </div>
                  <div>
                    <h2 class="text-sm font-bold text-theme-text">Recent Appointments</h2>
                    <p class="text-[10px] text-ivory/35 mt-0.5">Latest bookings across the platform</p>
                  </div>
                </div>
                <RouterLink to="/admin/appointments" class="text-[11px] font-semibold text-admin/70 hover:text-admin transition-colors">
                  View All →
                </RouterLink>
              </div>

              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="border-b border-white/[0.04]">
                      <th class="text-left px-6 py-3 text-[10px] uppercase tracking-widest text-ivory/30 font-bold">Customer</th>
                      <th class="text-left px-4 py-3 text-[10px] uppercase tracking-widest text-ivory/30 font-bold">Service</th>
                      <th class="text-left px-4 py-3 text-[10px] uppercase tracking-widest text-ivory/30 font-bold">Barber</th>
                      <th class="text-left px-4 py-3 text-[10px] uppercase tracking-widest text-ivory/30 font-bold">Date</th>
                      <th class="text-left px-4 py-3 text-[10px] uppercase tracking-widest text-ivory/30 font-bold">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="appt in dashboard.recent_appointments" :key="appt.id" class="border-b border-white/[0.03] hover:bg-white/[0.02] transition-colors">
                      <td class="px-6 py-3.5">
                        <div class="flex items-center gap-3">
                          <div class="h-8 w-8 rounded-full bg-gradient-to-br from-admin/30 to-purple-500/30 flex items-center justify-center text-[11px] font-bold text-white shrink-0">
                            {{ getInitials(appt.customer_name) }}
                          </div>
                          <span class="text-theme-text font-medium text-sm truncate max-w-[120px]">{{ appt.customer_name || 'Unknown' }}</span>
                        </div>
                      </td>
                      <td class="px-4 py-3.5 text-ivory/60 text-sm truncate max-w-[140px]">{{ appt.service_name || '—' }}</td>
                      <td class="px-4 py-3.5 text-ivory/60 text-sm truncate max-w-[100px]">{{ appt.barber_name || '—' }}</td>
                      <td class="px-4 py-3.5 text-ivory/50 text-xs font-mono whitespace-nowrap">{{ formatDate(appt.appointment_date) }}</td>
                      <td class="px-4 py-3.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" :class="statusClass(appt.status)">
                          <span class="h-1.5 w-1.5 rounded-full" :class="statusDot(appt.status)"></span>
                          {{ appt.status }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <div v-if="!dashboard.recent_appointments?.length" class="flex flex-col items-center justify-center py-12 text-ivory/25">
                  <CalendarDaysIcon class="h-10 w-10 mb-2" />
                  <p class="text-xs font-medium">No appointments yet</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column (2/5) - Sidebar Widgets -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Status Breakdown Donut -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm p-6">
              <h3 class="text-sm font-bold text-theme-text mb-5 flex items-center gap-2">
                <ChartPieIcon class="h-4 w-4 text-admin" />
                Appointment Status
              </h3>

              <div class="flex items-center gap-6">
                <!-- Donut -->
                <div class="relative w-32 h-32 shrink-0">
                  <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                    <circle cx="50" cy="50" r="38" fill="transparent" stroke="rgba(255,255,255,0.04)" stroke-width="14" />
                    <circle v-for="(seg, idx) in donutSegments" :key="idx"
                            cx="50" cy="50" r="38" fill="transparent"
                            :stroke="seg.color"
                            stroke-width="14"
                            :stroke-dasharray="`${seg.length} ${seg.gap}`"
                            :stroke-dashoffset="seg.offset"
                            stroke-linecap="round"
                            class="transition-all duration-1000 ease-out" />
                  </svg>
                  <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-display text-theme-text">{{ dashboard.stats?.total_appointments || 0 }}</span>
                    <span class="text-[9px] uppercase tracking-widest text-ivory/30">Total</span>
                  </div>
                </div>

                <!-- Legend -->
                <div class="flex-1 space-y-2.5">
                  <div v-for="item in donutLegend" :key="item.label" class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <div class="h-2.5 w-2.5 rounded-sm" :style="{ backgroundColor: item.color }"></div>
                      <span class="text-xs text-ivory/50 capitalize">{{ item.label.replace('_', ' ') }}</span>
                    </div>
                    <span class="text-xs font-bold text-theme-text tabular-nums">{{ item.count }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Top Barbers -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm overflow-hidden">
              <div class="border-b border-white/[0.04] px-6 py-4 flex items-center justify-between">
                <h3 class="text-sm font-bold text-theme-text flex items-center gap-2">
                  <TrophyIcon class="h-4 w-4 text-amber-400" />
                  Top Barbers
                </h3>
                <RouterLink to="/admin/barbers" class="text-[10px] font-semibold text-admin/60 hover:text-admin transition-colors">View All</RouterLink>
              </div>
              <div class="divide-y divide-white/[0.03]">
                <div v-for="(barber, idx) in dashboard.top_barbers" :key="barber.id" class="flex items-center gap-3 px-6 py-3.5 hover:bg-white/[0.02] transition-colors">
                  <span class="text-xs font-bold w-5 text-center" :class="idx === 0 ? 'text-amber-400' : idx === 1 ? 'text-gray-400' : idx === 2 ? 'text-amber-700' : 'text-ivory/20'">#{{ idx + 1 }}</span>
                  <div class="h-9 w-9 rounded-full bg-gradient-to-br from-admin/20 to-purple-500/20 flex items-center justify-center text-[11px] font-bold text-white shrink-0">
                    {{ getInitials(barber.name) }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-theme-text truncate">{{ barber.name }}</p>
                    <p class="text-[10px] text-ivory/35">{{ barber.bookings || 0 }} bookings · ⭐ {{ Number(barber.rating || 0).toFixed(1) }}</p>
                  </div>
                  <span class="text-xs font-bold text-emerald-400 tabular-nums whitespace-nowrap">₦{{ formatCurrency(barber.revenue || 0) }}</span>
                </div>
                <div v-if="!dashboard.top_barbers?.length" class="flex flex-col items-center py-8 text-ivory/20">
                  <UsersIcon class="h-8 w-8 mb-2" />
                  <p class="text-xs">No barber data</p>
                </div>
              </div>
            </div>

            <!-- Top Services -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm overflow-hidden">
              <div class="border-b border-white/[0.04] px-6 py-4">
                <h3 class="text-sm font-bold text-theme-text flex items-center gap-2">
                  <SparklesIcon class="h-4 w-4 text-purple-400" />
                  Popular Services
                </h3>
              </div>
              <div class="p-5 space-y-3">
                <div v-for="service in dashboard.top_services" :key="service.name" class="group">
                  <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs text-theme-text font-medium truncate max-w-[160px]">{{ service.name }}</span>
                    <span class="text-[10px] text-ivory/40 font-bold tabular-nums">{{ service.bookings || 0 }} booked</span>
                  </div>
                  <div class="h-1.5 w-full rounded-full bg-white/[0.04] overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-purple-500 to-admin transition-all duration-700 group-hover:opacity-90"
                         :style="{ width: `${serviceBarWidth(service)}%` }"></div>
                  </div>
                </div>
                <div v-if="!dashboard.top_services?.length" class="flex flex-col items-center py-6 text-ivory/20">
                  <SparklesIcon class="h-8 w-8 mb-2" />
                  <p class="text-xs">No services data</p>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm p-5">
              <h3 class="text-sm font-bold text-theme-text mb-4 flex items-center gap-2">
                <BoltIcon class="h-4 w-4 text-admin" />
                Quick Actions
              </h3>
              <div class="grid grid-cols-2 gap-2.5">
                <RouterLink v-for="action in quickActions" :key="action.label" :to="action.to" class="flex flex-col items-center justify-center gap-1.5 rounded-xl bg-white/[0.02] p-4 border border-white/[0.04] hover:bg-admin/8 hover:border-admin/20 transition-all group/action">
                  <component :is="action.icon" class="h-5 w-5 text-ivory/40 group-hover/action:text-admin transition-colors" />
                  <span class="text-[10px] font-bold text-ivory/50 uppercase tracking-wider group-hover/action:text-admin-light transition-colors">{{ action.label }}</span>
                </RouterLink>
              </div>
            </div>

            <!-- Recent Activity Feed -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm overflow-hidden">
              <div class="border-b border-white/[0.04] px-6 py-4">
                <h3 class="text-sm font-bold text-theme-text flex items-center gap-2">
                  <ClockIcon class="h-4 w-4 text-cyan-400" />
                  Recent Activity
                </h3>
              </div>
              <div class="divide-y divide-white/[0.03]">
                <div v-for="(activity, idx) in dashboard.recent_activity" :key="idx" class="px-6 py-3 flex items-start gap-3">
                  <div class="mt-1 h-6 w-6 rounded-md flex items-center justify-center shrink-0" :class="activityIconClass(activity.action)">
                    <component :is="activityIcon(activity.action)" class="h-3.5 w-3.5" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-ivory/60 leading-relaxed">
                      <span class="font-semibold text-theme-text">{{ activity.user_name || 'System' }}</span>
                      {{ activityVerb(activity.action) }}
                    </p>
                    <p class="text-[10px] text-ivory/25 mt-0.5">{{ timeAgo(activity.created_at) }}</p>
                  </div>
                </div>
                <div v-if="!dashboard.recent_activity?.length" class="flex flex-col items-center py-10 text-ivory/20">
                  <ClockIcon class="h-8 w-8 mb-2" />
                  <p class="text-xs">No recent activity</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </section>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import {
  CalendarDaysIcon,
  ClockIcon,
  SparklesIcon,
  UsersIcon,
  ChartBarIcon,
  BanknotesIcon,
  UserPlusIcon,
  BoltIcon,
  ClipboardDocumentListIcon,
  UserGroupIcon,
  CheckCircleIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  ExclamationTriangleIcon,
  ChartPieIcon,
  TrophyIcon,
  Cog6ToothIcon,
  DocumentTextIcon,
  PhotoIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline';

const router = useRouter();
const dashboard = ref({});
const loading = ref(true);
const isRefreshing = ref(false);
const currentTime = ref('');
const currentDate = ref('');
let clockInterval = null;
let dashboardInterval = null;

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
};

const todayFormatted = computed(() => {
  return new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
});

const formatCurrency = (value) => {
  return Number(value).toLocaleString('en-NG', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const getInitials = (name) => {
  if (!name) return '?';
  const parts = name.split(' ');
  return parts.map(p => p[0]).join('').toUpperCase().slice(0, 2);
};

const timeAgo = (dateStr) => {
  if (!dateStr) return '';
  const now = new Date();
  const then = new Date(dateStr);
  const diff = Math.floor((now - then) / 1000);
  if (diff < 60) return 'Just now';
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
  return formatDate(dateStr);
};

// KPI Cards
const primaryKpis = computed(() => {
  const s = dashboard.value.stats || {};
  const todayDelta = s.appointments_today - (s.appointments_yesterday || 0);
  return [
    {
      label: "Today's Bookings",
      value: s.appointments_today ?? 0,
      icon: CalendarDaysIcon,
      iconColor: 'text-white/70',
      iconWrap: 'bg-white/10 border-white/20',
      bgClass: 'bg-obsidian border-white/[0.08] hover:border-white/20',
      badge: todayDelta !== 0 ? `${todayDelta > 0 ? '+' : ''}${todayDelta}` : null,
      badgeClass: todayDelta > 0 ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/20' : 'bg-red-500/20 text-red-300 border-red-500/20',
      badgeIcon: todayDelta > 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon,
      sub: 'vs yesterday',
      link: '/admin/appointments'
    },
    {
      label: 'Pending Approval',
      value: s.pending_appointments ?? 0,
      icon: ExclamationTriangleIcon,
      iconColor: (s.pending_appointments || 0) > 0 ? 'text-red-400' : 'text-white',
      iconWrap: (s.pending_appointments || 0) > 0 ? 'bg-red-500/10 border-red-500/20' : 'bg-white/20 border-white/30',
      bgClass: (s.pending_appointments || 0) > 0 
        ? 'bg-gradient-to-br from-red-950/60 to-charcoal border-red-500/30 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_0_20px_rgba(239,68,68,0.2)]' 
        : 'bg-admin-dark border-white/[0.08] hover:border-white/20',
      valueColor: (s.pending_appointments || 0) > 0 ? 'text-red-400' : 'text-white',
      labelColor: (s.pending_appointments || 0) > 0 ? 'text-red-400/70' : 'text-white/70',
      subColor: (s.pending_appointments || 0) > 0 ? 'text-red-400/60' : 'text-white/60',
      badge: (s.pending_appointments || 0) > 0 ? 'Action Required' : null,
      badgeClass: (s.pending_appointments || 0) > 0 ? 'bg-red-500/10 text-red-400 border-red-500/30 animate-pulse' : 'bg-black/20 text-white border-white/10',
      badgeIcon: ExclamationTriangleIcon,
      sub: 'Requires admin review',
      actionNeeded: (s.pending_appointments || 0) > 0,
      link: '/admin/appointments'
    },
    {
      label: 'Total Customers',
      value: s.total_customers ?? 0,
      icon: UserGroupIcon,
      iconColor: 'text-obsidian',
      iconWrap: 'bg-black/10 border-black/20',
      bgClass: 'bg-admin-light border-black/10 hover:border-black/20',
      valueColor: 'text-obsidian',
      labelColor: 'text-obsidian/70',
      subColor: 'text-obsidian/60',
      badge: (s.new_customers_week || 0) > 0 ? `+${s.new_customers_week} this week` : null,
      badgeClass: 'bg-black/10 text-obsidian border-black/10',
      badgeIcon: ArrowTrendingUpIcon,
      sub: `${s.new_customers_week || 0} new this week`,
      link: '/admin/customers'
    },
    {
      label: 'Completion Rate',
      value: s.completion_rate ?? 0,
      suffix: '%',
      icon: CheckCircleIcon,
      iconColor: 'text-emerald-500',
      iconWrap: 'bg-emerald-500/10 border-emerald-500/20',
      bgClass: 'bg-obsidian border-white/[0.08] hover:border-white/20',
      valueColor: (s.completion_rate || 0) >= 80 ? 'text-emerald-400' : (s.completion_rate || 0) >= 50 ? 'text-amber-400' : 'text-red-400',
      sub: `${s.total_appointments ?? 0} total appointments`,
    },
  ];
});

// Revenue bar chart
const normalizedRevenueTrend = computed(() => {
  const trend = dashboard.value.revenue_trend || [];
  if (!trend.length) return [];
  const max = Math.max(...trend.map(t => t.revenue), 1);
  const today = new Date().toISOString().slice(0, 10);
  return trend.map(t => ({
    ...t,
    percentage: Math.max((t.revenue / max) * 100, 4),
    isToday: t.date === today
  }));
});

// Booking bar chart
const normalizedBookingTrend = computed(() => {
  const trend = dashboard.value.booking_trend || [];
  if (!trend.length) return [];
  const max = Math.max(...trend.map(t => t.count), 1);
  const today = new Date().toISOString().slice(0, 10);
  return trend.map(t => ({
    ...t,
    percentage: Math.max((t.count / max) * 100, 4),
    isToday: t.date === today
  }));
});

// Revenue line graph
const revenueGraphPoints = computed(() => {
  const trends = normalizedRevenueTrend.value;
  if (trends.length === 0) return [];
  if (trends.length === 1) {
    return [{ x: 50, y: 100 - trends[0].percentage, revenue: trends[0].revenue, day: trends[0].day }];
  }
  return trends.map((t, idx) => {
    const x = (idx / (trends.length - 1)) * 100;
    const y = 100 - t.percentage;
    return { x, y, revenue: t.revenue, day: t.day };
  });
});

const revenueGraphPath = computed(() => {
  const points = revenueGraphPoints.value;
  if (points.length === 0) return '';
  if (points.length === 1) return `M 0,${points[0].y} L 100,${points[0].y}`;
  return points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x},${p.y}`).join(' ');
});

const revenueGraphAreaPath = computed(() => {
  const points = revenueGraphPoints.value;
  if (points.length === 0) return '';
  const path = revenueGraphPath.value;
  return `${path} L 100,100 L 0,100 Z`;
});

// Booking line graph
const bookingGraphPoints = computed(() => {
  const trends = normalizedBookingTrend.value;
  if (trends.length === 0) return [];
  if (trends.length === 1) {
    return [{ x: 50, y: 100 - trends[0].percentage, count: trends[0].count, day: trends[0].day }];
  }
  return trends.map((t, idx) => {
    const x = (idx / (trends.length - 1)) * 100;
    const y = 100 - t.percentage;
    return { x, y, count: t.count, day: t.day };
  });
});

const bookingGraphPath = computed(() => {
  const points = bookingGraphPoints.value;
  if (points.length === 0) return '';
  if (points.length === 1) return `M 0,${points[0].y} L 100,${points[0].y}`;
  return points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x},${p.y}`).join(' ');
});

const bookingGraphAreaPath = computed(() => {
  const points = bookingGraphPoints.value;
  if (points.length === 0) return '';
  const path = bookingGraphPath.value;
  return `${path} L 100,100 L 0,100 Z`;
});

// Donut chart
const donutColors = {
  completed: '#34d399',
  confirmed: '#60a5fa',
  pending: '#fbbf24',
  cancelled: '#f87171',
  no_show: '#c084fc'
};

const donutLegend = computed(() => {
  const bd = dashboard.value.status_breakdown || {};
  return Object.entries(bd)
    .map(([label, count]) => ({ label, count, color: donutColors[label] || '#666' }))
    .filter(item => item.count > 0);
});

const donutSegments = computed(() => {
  const total = donutLegend.value.reduce((s, i) => s + i.count, 0);
  if (total === 0) return [];
  const circumference = 2 * Math.PI * 38;
  let offset = 0;
  return donutLegend.value.map(item => {
    const pct = item.count / total;
    const length = pct * circumference;
    const gap = circumference - length;
    const seg = { color: item.color, length, gap, offset: -offset };
    offset += length;
    return seg;
  });
});

// Services bar
const serviceBarWidth = (service) => {
  const services = dashboard.value.top_services || [];
  const max = Math.max(...services.map(s => s.bookings || 0), 1);
  return Math.max(((service.bookings || 0) / max) * 100, 3);
};

// Status badge classes
const statusClass = (status) => {
  const map = {
    completed: 'bg-emerald-500/10 text-emerald-400',
    confirmed: 'bg-blue-500/10 text-blue-400',
    pending: 'bg-amber-500/10 text-amber-400',
    cancelled: 'bg-red-500/10 text-red-400',
    no_show: 'bg-purple-500/10 text-purple-400'
  };
  return map[status] || 'bg-white/5 text-ivory/40';
};

const statusDot = (status) => {
  const map = {
    completed: 'bg-emerald-400',
    confirmed: 'bg-blue-400',
    pending: 'bg-amber-400',
    cancelled: 'bg-red-400',
    no_show: 'bg-purple-400'
  };
  return map[status] || 'bg-white/20';
};

// Activity feed helpers
const activityVerb = (action) => {
  const verbs = {
    login: 'logged in',
    approve_booking: 'approved a booking',
    cancel_booking: 'cancelled a booking',
    update_settings: 'updated system settings',
    create_barber: 'added a new barber',
    delete_barber: 'removed a barber',
  };
  return verbs[action] || action?.replace(/_/g, ' ') || 'performed an action';
};

const activityIcon = (action) => {
  if (action === 'login') return ArrowTrendingUpIcon;
  if (action === 'approve_booking') return CheckCircleIcon;
  if (action === 'cancel_booking') return ExclamationTriangleIcon;
  if (action === 'update_settings') return Cog6ToothIcon;
  return ClockIcon;
};

const activityIconClass = (action) => {
  if (action === 'login') return 'bg-blue-500/10 text-blue-400';
  if (action === 'approve_booking') return 'bg-emerald-500/10 text-emerald-400';
  if (action === 'cancel_booking') return 'bg-red-500/10 text-red-400';
  if (action === 'update_settings') return 'bg-amber-500/10 text-amber-400';
  return 'bg-white/5 text-ivory/30';
};

// Quick actions
const quickActions = [
  { label: 'Add Barber', to: '/admin/barbers', icon: UserPlusIcon },
  { label: 'New Service', to: '/admin/services', icon: SparklesIcon },
  { label: 'Blog Post', to: '/admin/blog', icon: DocumentTextIcon },
  { label: 'Gallery', to: '/admin/gallery', icon: PhotoIcon },
];

const loadDashboardData = async (silent = false) => {
  if (!silent) {
    loading.value = true;
  } else {
    isRefreshing.value = true;
  }
  
  try {
    const response = await adminApi.dashboard();
    dashboard.value = response.data.data;
  } catch (err) {
    console.error('Failed to load dashboard data:', err);
  } finally {
    loading.value = false;
    isRefreshing.value = false;
  }
};

onMounted(async () => {
  updateClock();
  clockInterval = setInterval(updateClock, 30000);
  
  loadDashboardData();
  
  // Auto-reload dashboard every 30 seconds silently
  dashboardInterval = setInterval(() => {
    loadDashboardData(true);
  }, 30000);
});

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval);
  if (dashboardInterval) clearInterval(dashboardInterval);
});
</script>