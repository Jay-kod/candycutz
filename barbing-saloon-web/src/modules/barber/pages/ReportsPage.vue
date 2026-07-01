<template>
  <BarberLayout>
    <section class="space-y-6 animate-fade-in pb-12">
      <!-- Header Banner & Controls -->
      <div class="relative overflow-hidden rounded-3xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-bold">Reports</p>
          <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg">
            Operational <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-admin-light">Analytics</span>
          </h1>
          <p class="mt-2 text-sm text-ivory/60">Comprehensive overview of revenue, performance, and trends.</p>
        </div>

        <div class="relative z-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <div class="flex items-center gap-2 bg-black/40 p-2 rounded-xl border border-white/10 backdrop-blur-md">
            <button 
              v-for="option in rangeOptions" 
              :key="option.value"
              @click="setRange(option.value)"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300"
              :class="selectedRange === option.value 
                ? 'bg-gold text-obsidian shadow-[0_0_15px_rgba(255,103,0,0.3)]' 
                : 'text-ivory/60 hover:text-gold hover:bg-white/5'"
            >
              {{ option.label }}
            </button>
          </div>

          <!-- Generate Report Button -->
          <button 
            @click="showReportPreview = true"
            :disabled="loading"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-gold to-amber-500 text-obsidian font-bold text-sm shadow-[0_0_20px_rgba(255,103,0,0.3)] hover:shadow-[0_0_30px_rgba(255,103,0,0.5)] transition-all duration-300 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <DocumentArrowDownIcon class="h-5 w-5" />
            Generate Report
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="h-12 w-12 rounded-full border-4 border-gold/20 border-t-admin animate-spin"></div>
      </div>

      <template v-else>
        <!-- KPI Cards -->
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
          <!-- Revenue -->
          <div class="group relative overflow-hidden rounded-2xl border border-gold/20 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-gold/40 hover:bg-black/40 hover:shadow-[0_10px_30px_rgba(255,103,0,0.12)]">
            <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10">
              <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                  <BanknotesIcon class="h-5 w-5 text-emerald-400" />
                </div>
                <p class="text-sm text-ivory/60 font-medium">Total Revenue</p>
              </div>
              <p class="font-display text-3xl text-emerald-400">₦{{ Number(performanceMetrics.total_revenue || 0).toLocaleString() }}</p>
              <p class="mt-2 text-xs text-ivory/40">From completed bookings</p>
            </div>
          </div>

          <!-- Appointments -->
          <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-gold/30 hover:bg-black/40">
            <div class="relative z-10">
              <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-xl bg-gold/10 border border-gold/20 flex items-center justify-center">
                  <CalendarDaysIcon class="h-5 w-5 text-gold" />
                </div>
                <p class="text-sm text-ivory/60 font-medium">Appointments</p>
              </div>
              <p class="font-display text-3xl text-gold-light">{{ performanceMetrics.clients_served || 0 }}</p>
              <p class="mt-2 text-xs text-ivory/40">Successfully completed</p>
            </div>
          </div>

          <!-- Customers -->
          <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-gold/30 hover:bg-black/40">
            <div class="relative z-10">
              <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
                  <UsersIcon class="h-5 w-5 text-blue-400" />
                </div>
                <p class="text-sm text-ivory/60 font-medium">Completion Rate (%)</p>
              </div>
              <p class="font-display text-3xl text-blue-400">{{ performanceMetrics.completion_rate || 0 }}</p>
              <p class="mt-2 text-xs text-ivory/40">Appointments completed</p>
            </div>
          </div>

          <!-- Avg Rating -->
          <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-gold/30 hover:bg-black/40">
            <div class="relative z-10">
              <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center">
                  <UserGroupIcon class="h-5 w-5 text-sky-400" />
                </div>
                <p class="text-sm text-ivory/60 font-medium">Avg Rating</p>
              </div>
              <p class="font-display text-3xl text-sky-400">{{ performanceMetrics.avg_rating || 0 }}</p>
              <p class="mt-2 text-xs text-ivory/40">Client feedback</p>
            </div>
          </div>

        </div>

        <div class="grid gap-6 lg:grid-cols-1">
          <!-- Top Services -->
          <div class="rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm">
            <h2 class="text-lg font-bold text-theme-text mb-6 flex items-center gap-2">
              <SparklesIcon class="h-5 w-5 text-gold" /> Most Popular Services
            </h2>
            
            <div class="space-y-4" v-if="topServices.length > 0">
              <div v-for="(service, index) in topServices" :key="index" 
                   class="flex items-center justify-between p-4 rounded-xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.04] transition-colors">
                <div class="flex items-center gap-4">
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10 font-bold text-emerald-400">
                    #{{ index + 1 }}
                  </div>
                  <div>
                    <p class="font-medium text-theme-text">{{ service.name }}</p>
                    <p class="text-xs text-theme-muted">{{ service.count }} times booked</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-display text-lg text-gold-light">₦{{ Number(service.revenue).toLocaleString() }}</p>
                  <p class="text-xs text-theme-muted">Generated revenue</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-10 text-theme-muted">
              No service data available for this period.
            </div>
          </div>
        </div>

        <!-- Booking Status Breakdown -->
        <div class="rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm">
          <h2 class="text-lg font-bold text-theme-text mb-6 flex items-center gap-2">
            <ChartPieIcon class="h-5 w-5 text-gold" /> Appointment Status Breakdown
          </h2>
          
          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" v-if="statusBreakdown.length > 0">
            <div v-for="status in statusBreakdown" :key="status.status" 
                 class="p-5 rounded-xl border bg-white/[0.02]"
                 :class="{
                   'border-emerald-500/20': status.status === 'completed',
                   'border-yellow-500/20': status.status === 'pending',
                   'border-red-500/20': status.status === 'cancelled',
                   'border-blue-500/20': status.status === 'approved'
                 }">
              <p class="text-xs uppercase tracking-widest mb-1 text-theme-muted">{{ status.status }}</p>
              <p class="font-display text-3xl"
                 :class="{
                   'text-emerald-400': status.status === 'completed',
                   'text-yellow-400': status.status === 'pending',
                   'text-red-400': status.status === 'cancelled',
                   'text-blue-400': status.status === 'approved'
                 }">
                {{ status.count }}
              </p>
            </div>
          </div>
          <div v-else class="text-center py-10 text-theme-muted">
            No status breakdown available.
          </div>
        </div>

      </template>
    </section>

    <!-- ========================================== -->
    <!-- PDF REPORT PREVIEW MODAL -->
    <!-- ========================================== -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showReportPreview" class="fixed inset-0 z-[9999] flex items-start justify-center overflow-y-auto bg-black/80 backdrop-blur-md" @click.self="showReportPreview = false">
          
          <!-- Floating Action Bar -->
          <div class="fixed top-0 left-0 right-0 z-[10000] flex items-center justify-between px-6 py-4 bg-gradient-to-b from-black/90 via-black/70 to-transparent">
            <div class="flex items-center gap-3">
              <div class="h-8 w-8 rounded-lg bg-gold/20 flex items-center justify-center">
                <DocumentTextIcon class="h-4 w-4 text-gold" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-white">Report Preview</h3>
                <p class="text-[10px] text-white/50">{{ activeRangeLabel }} — Generated {{ todayFormatted }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <button 
                @click="downloadPdf" 
                :disabled="isGeneratingPdf"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-gold to-amber-500 text-obsidian font-bold text-sm shadow-[0_0_20px_rgba(255,103,0,0.4)] hover:shadow-[0_0_30px_rgba(255,103,0,0.6)] transition-all hover:scale-105 disabled:opacity-60"
              >
                <ArrowDownTrayIcon v-if="!isGeneratingPdf" class="h-4 w-4" />
                <div v-else class="h-4 w-4 rounded-full border-2 border-obsidian/30 border-t-obsidian animate-spin"></div>
                {{ isGeneratingPdf ? 'Generating...' : 'Download PDF' }}
              </button>
              <button @click="showReportPreview = false" class="flex items-center justify-center h-10 w-10 rounded-xl bg-white/10 border border-white/10 text-white hover:bg-white/20 transition-colors">
                <XMarkIcon class="h-5 w-5" />
              </button>
            </div>
          </div>

          <!-- Report Document -->
          <div class="mt-20 mb-12 w-full max-w-[800px] mx-4">
            <div ref="reportRef" class="report-document bg-white text-gray-900 rounded-2xl shadow-2xl overflow-hidden">
              
              <!-- Page 1: Cover & Summary -->
              <div class="report-page relative">
                <div class="report-watermark"></div>
                <div class="relative z-10">
                  <!-- Header Banner -->
                <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d1a00 50%, #1a1a1a 100%); padding: 48px 40px; position: relative; overflow: hidden;">
                  <div style="position: absolute; top: -40px; right: -40px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,103,0,0.15), transparent 70%); border-radius: 50%;"></div>
                  <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(255,103,0,0.1), transparent 70%); border-radius: 50%;"></div>
                  
                  <div style="position: relative; z-index: 1; text-align: center;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; justify-content: center;">
                      <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(255,103,0,0.2); border: 1px solid rgba(255,103,0,0.3); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 18px;">✂️</span>
                      </div>
                      <span style="font-size: 11px; letter-spacing: 0.3em; text-transform: uppercase; color: rgba(255,103,0,0.8); font-weight: 700;">CandyCutz Barbing Saloon</span>
                    </div>
                    <h1 style="font-size: 36px; font-weight: 800; color: white; margin: 0 0 8px 0; line-height: 1.1; text-align: center;">Operational Report</h1>
                    <p style="font-size: 18px; color: #FF6700; font-weight: 600; margin: 0 0 16px 0; text-align: center;">For {{ barberName }}</p>
                    <p style="font-size: 14px; color: rgba(255,255,255,0.5); margin: 0; text-align: center;">{{ activeRangeLabel }} • Generated on {{ todayFormatted }}</p>
                  </div>
                </div>

                <!-- Executive Summary -->
                <div style="padding: 32px 40px;">
                  <h2 style="font-size: 16px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 24px 0; padding-bottom: 8px; border-bottom: 2px solid #FF6700;">Executive Summary</h2>
                  
                  <!-- KPI Grid -->
                  <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 32px;">
                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);">
                      <p style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Revenue</p>
                      <p style="font-size: 18px; font-weight: 800; color: #059669; margin: 0;">₦{{ Number(performanceMetrics.total_revenue || 0).toLocaleString() }}</p>
                      <p style="font-size: 9px; color: #9ca3af; margin: 4px 0 0 0;">From bookings</p>
                    </div>
                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);">
                      <p style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Completed</p>
                      <p style="font-size: 18px; font-weight: 800; color: #ea580c; margin: 0;">{{ performanceMetrics.clients_served || 0 }}</p>
                      <p style="font-size: 9px; color: #9ca3af; margin: 4px 0 0 0;">Appointments</p>
                    </div>
                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);">
                      <p style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">New Clients</p>
                      <p style="font-size: 18px; font-weight: 800; color: #2563eb; margin: 0;">{{ performanceMetrics.completion_rate || 0 }}</p>
                      <p style="font-size: 9px; color: #9ca3af; margin: 4px 0 0 0;">In period</p>
                    </div>
                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: linear-gradient(135deg, #e0f2fe 0%, #ffffff 100%);">
                      <p style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Total Clients</p>
                      <p style="font-size: 18px; font-weight: 800; color: #0284c7; margin: 0;">{{ performanceMetrics.avg_rating || 0 }}</p>
                      <p style="font-size: 9px; color: #9ca3af; margin: 4px 0 0 0;">All time</p>
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
              </div>

              <!-- Page 2: Top Barbers & Services -->
              <div class="report-page relative border-t border-gray-200">
                <div class="report-watermark"></div>
                <div class="relative z-10 px-10 py-8">
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

            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </BarberLayout>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { useAuthStore } from '../../auth/store/auth.store';
import { 
  BanknotesIcon, CalendarDaysIcon, UsersIcon, 
  ChartBarIcon, StarIcon, SparklesIcon, ChartPieIcon,
  DocumentArrowDownIcon, DocumentTextIcon, ArrowDownTrayIcon, XMarkIcon
} from '@heroicons/vue/24/outline';

const authStore = useAuthStore();
const barberName = computed(() => authStore.user?.name || 'Barber');

const loading = ref(true);
const showReportPreview = ref(false);
const isGeneratingPdf = ref(false);
const reportRef = ref(null);

const rangeOptions = [
  { label: '7 Days', value: '7d' },
  { label: '30 Days', value: '30d' },
  { label: 'This Month', value: 'month' },
  { label: 'All Time', value: 'all' }
];

const selectedRange = ref('7d');

const performanceMetrics = ref({});
const topServices = ref([]);
const statusBreakdown = ref([]);

const activeRangeLabel = computed(() => {
  const match = rangeOptions.find(o => o.value === selectedRange.value);
  return match ? match.label : selectedRange.value;
});

const todayFormatted = computed(() => {
  return new Date().toLocaleDateString('en-GB', { 
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
  });
});

const currentTime = computed(() => {
  return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
});

// Generate a unique authenticity ID for the report based on time and random hash
const reportId = computed(() => {
  const timestamp = Date.now().toString(36).toUpperCase();
  const hash = Math.random().toString(36).substring(2, 8).toUpperCase();
  return `REF-CCZ-${timestamp}-${hash}`;
});

const totalAppointments = computed(() => {
  return statusBreakdown.value.reduce((sum, s) => sum + Number(s.count), 0);
});

const statusBarColor = (status) => {
  const colors = {
    completed: 'linear-gradient(135deg, #059669, #10b981)',
    pending: 'linear-gradient(135deg, #d97706, #f59e0b)',
    cancelled: 'linear-gradient(135deg, #dc2626, #ef4444)',
    approved: 'linear-gradient(135deg, #2563eb, #3b82f6)',
    confirmed: 'linear-gradient(135deg, #2563eb, #3b82f6)',
    no_show: 'linear-gradient(135deg, #6b7280, #9ca3af)'
  };
  return colors[status] || 'linear-gradient(135deg, #6b7280, #9ca3af)';
};

const fetchAnalytics = async () => {
  loading.value = true;
  try {
    const response = await barberApi.analytics(selectedRange.value);
    const data = response.data.data || response.data;
    performanceMetrics.value = data.performance_metrics || {};
    // no platform stats
    // no top barbers
    topServices.value = data.top_services || [];
    
    // Convert status_breakdown from object to array if needed
    const sb = data.status_breakdown || {};
    if (Array.isArray(sb)) {
      statusBreakdown.value = sb;
    } else {
      statusBreakdown.value = Object.entries(sb)
        .filter(([, count]) => count > 0)
        .map(([status, count]) => ({ status, count }));
    }
  } catch (err) {
    console.error('Failed to fetch analytics:', err);
  } finally {
    loading.value = false;
  }
};

const setRange = (range) => {
  if (selectedRange.value === range) return;
  selectedRange.value = range;
  fetchAnalytics();
};

const downloadPdf = async () => {
  if (!reportRef.value) return;
  isGeneratingPdf.value = true;
  
  try {
    const html2pdf = (await import('html2pdf.js')).default;
    
    const opt = {
      margin: 0,
      filename: `CandyCutz_Report_${selectedRange.value}_${new Date().toISOString().slice(0, 10)}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { 
        scale: 2, 
        useCORS: true,
        backgroundColor: '#ffffff',
        letterRendering: true
      },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak: { mode: ['css', 'legacy'] }
    };
    
    await html2pdf().set(opt).from(reportRef.value).save();
  } catch (err) {
    console.error('PDF generation failed:', err);
  } finally {
    isGeneratingPdf.value = false;
  }
};

onMounted(() => {
  fetchAnalytics();
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from .report-document,
.modal-leave-to .report-document {
  transform: translateY(40px) scale(0.95);
  opacity: 0;
}

.report-document {
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  position: relative;
  width: 794px;
  margin: 0 auto;
}

.report-page {
  width: 794px;
  position: relative;
  box-sizing: border-box;
}

.report-watermark {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  opacity: 0.06;
  /* Extremely tight, interlocking, dense diagonal grid pattern */
  background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScxNjAnIGhlaWdodD0nMTAwJz48ZyB0cmFuc2Zvcm09J3JvdGF0ZSgtMzAgODAgNTApJz48dGV4dCB4PSc4MCcgeT0nLTI1JyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzAnIHk9Jy0xMCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9Jy0xMCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PSc4MCcgeT0nNScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PScyMCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9JzIwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PSczNScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PSc1MCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9JzUwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PSc2NScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PSc4MCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9JzgwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PSc5NScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PScxMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9JzExMCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PSc4MCcgeT0nMTI1JyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0PjwvZz48L3N2Zz4=");
  background-repeat: repeat;
}
</style>
