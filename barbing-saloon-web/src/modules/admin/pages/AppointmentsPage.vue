<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <!-- Premium Header Banner -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 backdrop-blur-3xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/10 blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-purple-500/10 blur-[80px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,103,0,0.05),transparent_60%)]"></div>
        
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-admin/20 text-admin border border-admin/30">
              <CalendarDaysIcon class="h-3.5 w-3.5" />
            </span>
            <p class="text-[10px] uppercase tracking-[0.3em] text-admin/80 font-bold">Booking Management</p>
          </div>
          <h1 class="font-display text-4xl lg:text-5xl text-white drop-shadow-md leading-tight">
            Saloon <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-amber-400">Appointments</span>
          </h1>
          <p class="mt-3 text-sm text-white/40 max-w-lg leading-relaxed">
            Review, verify payments, and manage all customer bookings in real-time.
          </p>
        </div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-4 shrink-0 w-full md:w-auto">
          <div class="relative w-full sm:w-64">
            <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-white/30" />
            <input
              v-model="search"
              type="text"
              placeholder="Search by client name..."
              class="w-full bg-white/[0.03] border border-white/[0.08] text-sm text-white rounded-2xl py-3.5 pl-11 pr-4 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 placeholder:text-white/20"
            />
          </div>
          <button @click="$router.push('/admin/walk-in')" class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-400 px-6 py-3.5 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(16,185,129,0.3)] shrink-0 group">
            <UserPlusIcon class="h-4.5 w-4.5 group-hover:scale-110 transition-transform" />
            New Walk-In
          </button>
          <button @click="showSettingsModal = true" class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-admin to-admin-light px-6 py-3.5 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(255,103,0,0.3)] shrink-0 group">
            <BanknotesIcon class="h-4.5 w-4.5 group-hover:scale-110 transition-transform" />
            Bank Settings
          </button>
        </div>
      </div>

      <!-- Filters & Stats -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-2 md:pb-0 hide-scrollbar">
          <button 
            v-for="tab in tabs" 
            :key="tab.value"
            @click="currentFilter = tab.value"
            class="relative px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap overflow-hidden"
            :class="currentFilter === tab.value ? 'text-white' : 'text-white/40 hover:text-white hover:bg-white/5'"
          >
            <div v-if="currentFilter === tab.value" class="absolute inset-0 bg-white/10 border border-white/20 rounded-xl"></div>
            <span class="relative z-10 flex items-center gap-2">
              {{ tab.label }}
              <span v-if="tab.count !== undefined" class="flex items-center justify-center h-5 px-1.5 rounded-md bg-black/40 text-[10px] tabular-nums" :class="currentFilter === tab.value ? 'text-white' : 'text-white/40'">
                {{ tab.count }}
              </span>
            </span>
          </button>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="rounded-[2rem] border border-white/[0.05] bg-[#1a1a1a]/80 backdrop-blur-2xl shadow-2xl overflow-hidden min-h-[400px]">
        
        <!-- Loading Skeleton -->
        <div v-if="loading" class="divide-y divide-white/[0.02]">
          <div v-for="i in 5" :key="i" class="p-6 grid grid-cols-1 md:grid-cols-[1.5fr_1.5fr_1fr_1fr_auto] gap-6 items-center animate-pulse">
            <div class="flex items-center gap-4">
              <div class="h-12 w-12 rounded-full bg-white/[0.03]"></div>
              <div class="space-y-2"><div class="h-4 w-24 bg-white/[0.03] rounded"></div><div class="h-3 w-16 bg-white/[0.02] rounded"></div></div>
            </div>
            <div class="space-y-2 hidden md:block"><div class="h-4 w-32 bg-white/[0.03] rounded"></div><div class="h-3 w-20 bg-white/[0.02] rounded"></div></div>
            <div class="space-y-2 hidden md:block"><div class="h-4 w-24 bg-white/[0.03] rounded"></div><div class="h-3 w-16 bg-white/[0.02] rounded"></div></div>
            <div class="h-6 w-20 bg-white/[0.03] rounded-full hidden md:block"></div>
            <div class="h-10 w-24 bg-white/[0.03] rounded-xl justify-self-end"></div>
          </div>
        </div>

        <!-- Appointments Table -->
        <template v-else>
          <div v-if="filteredAppointments.length > 0" class="overflow-x-auto pb-4">
            <table class="w-full text-left border-collapse min-w-[800px]">
              <thead>
                <tr class="border-b border-white/[0.05] bg-black/20 text-[10px] uppercase tracking-[0.2em] text-white/30 font-bold">
                  <th class="px-6 py-4 font-bold rounded-tl-3xl">Client Details</th>
                  <th class="px-6 py-4 font-bold">Service & Barber</th>
                  <th class="px-6 py-4 font-bold">Schedule</th>
                  <th class="px-6 py-4 font-bold">Status</th>
                  <th class="px-6 py-4 text-right font-bold rounded-tr-3xl">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/[0.02]">
                <tr
                  v-for="appointment in filteredAppointments"
                  :key="appointment.id"
                  class="group transition-colors hover:bg-white/[0.02]"
                >
                  <!-- Client details -->
                  <td class="px-6 py-4 align-top">
                    <div class="flex items-center gap-3">
                      <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-white/10 to-white/5 border border-white/10 text-white font-bold text-sm shadow-inner group-hover:scale-105 transition-transform">
                        {{ initials(appointment.customer?.name || appointment.client_name) }}
                        <div v-if="isNewClient(appointment)" class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-admin border-2 border-[#1a1a1a]" title="New Client"></div>
                      </div>
                      <div>
                        <p class="text-sm font-bold text-white">{{ appointment.customer?.name || appointment.client_name }}</p>
                        <p class="text-[10px] font-mono text-white/40 flex items-center gap-1 mt-0.5"><HashtagIcon class="h-3 w-3"/> {{ String(appointment.id).padStart(5, '0') }}</p>
                      </div>
                    </div>
                  </td>
                  
                  <!-- Service & Barber -->
                  <td class="px-6 py-4 align-top">
                    <p class="text-sm text-white/90 font-medium truncate max-w-[200px]" :title="appointment.service?.name">{{ appointment.service?.name || 'General Booking' }}</p>
                    <p class="text-[11px] text-white/40 mt-1 flex items-center gap-1"><UserIcon class="h-3 w-3" /> {{ appointment.barber?.name || 'Any Barber' }}</p>
                  </td>

                  <!-- Schedule -->
                  <td class="px-6 py-4 align-top">
                    <p class="text-sm text-white/80 font-medium flex items-center gap-1.5 mb-1"><CalendarDaysIcon class="h-3.5 w-3.5 text-white/30" /> {{ formatDate(appointment.appointment_date) }}</p>
                    <p class="text-[11px] text-admin font-bold flex items-center gap-1.5"><ClockIcon class="h-3.5 w-3.5 opacity-60" /> {{ appointment.appointment_time }}</p>
                  </td>

                  <!-- Status -->
                  <td class="px-6 py-4 align-top">
                    <div class="flex flex-col items-start gap-2">
                      <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-bold uppercase tracking-widest border shadow-sm" :class="statusClass(appointment.status)">
                        <span class="h-1.5 w-1.5 rounded-full" :class="statusDot(appointment.status)"></span>
                        {{ appointment.status }}
                      </span>
                      <div class="flex flex-wrap items-center gap-1.5">
                        <span v-if="appointment.booking_type === 'walk_in'" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[8px] font-bold uppercase tracking-wider border border-purple-500/30 bg-purple-500/10 text-purple-400">
                          <UserPlusIcon class="h-2.5 w-2.5" />
                          Walk-In
                        </span>
                        <span v-else class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[8px] font-bold uppercase tracking-wider border border-cyan-500/30 bg-cyan-500/10 text-cyan-400">
                          <GlobeAltIcon class="h-2.5 w-2.5" />
                          Online
                        </span>
                        <span v-if="appointment.payment_method" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[8px] font-bold uppercase tracking-wider border border-white/10 bg-white/[0.04] text-white/70">
                          {{ appointment.payment_method === 'pos' ? 'POS' : appointment.payment_method }}
                        </span>
                      </div>
                      <span v-if="appointment.payment_status" class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[8px] font-bold uppercase tracking-wider border bg-[#111] shadow-sm" :class="paymentStatusClass(appointment.payment_status)">
                        <BanknotesIcon class="h-3 w-3 opacity-70" />
                        PAY: {{ appointment.payment_status.replace('_', ' ') }}
                      </span>
                    </div>
                  </td>

                  <!-- Actions -->
                  <td class="px-6 py-4 text-right align-top">
                    <div class="flex flex-wrap items-center justify-end gap-2">
                      <button @click="openDetails(appointment)" class="rounded-lg bg-white/5 border border-white/10 px-4 py-2 text-xs font-bold text-white/70 hover:bg-white/10 hover:text-white transition-all shadow-sm">
                        Details
                      </button>
                      
                      <template v-if="appointment.status === 'pending'">
                        <button v-if="!appointment.receipt_image || appointment.payment_status !== 'awaiting_verification'" @click="approve(appointment.id)" class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-2 text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all shadow-sm" title="Approve">
                          <CheckIcon class="h-4 w-4" />
                        </button>
                        <button v-if="appointment.receipt_image && appointment.payment_status === 'awaiting_verification'" @click="viewReceipt(appointment)" class="rounded-lg bg-blue-500/10 border border-blue-500/20 p-2 text-blue-400 hover:bg-blue-500 hover:text-white transition-all shadow-sm" title="Verify Receipt">
                          <DocumentTextIcon class="h-4 w-4" />
                        </button>
                        <button @click="cancel(appointment.id)" class="rounded-lg border border-red-500/20 p-2 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all" title="Cancel Booking">
                          <XMarkIcon class="h-4 w-4" />
                        </button>
                      </template>
                      <template v-else-if="appointment.status === 'confirmed' || appointment.status === 'completed'">
                        <button v-if="appointment.receipt_image" @click="viewReceipt(appointment)" class="rounded-lg bg-white/5 border border-white/10 p-2 text-white/60 hover:bg-white/10 hover:text-white transition-all" title="View Receipt">
                          <DocumentTextIcon class="h-4 w-4" />
                        </button>
                        <button v-if="appointment.status === 'confirmed'" @click="cancel(appointment.id)" class="rounded-lg border border-red-500/20 p-2 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all" title="Cancel Booking">
                          <XMarkIcon class="h-4 w-4" />
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-else class="flex flex-col items-center justify-center py-24 px-6 text-center">
            <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-white/[0.02] border border-white/[0.05] mb-6">
              <div class="absolute inset-0 rounded-full border border-dashed border-white/10 animate-[spin_10s_linear_infinite]"></div>
              <ClipboardDocumentCheckIcon class="h-8 w-8 text-white/20" />
            </div>
            <h3 class="text-xl font-bold text-white mb-2">No Appointments Found</h3>
            <p class="text-sm text-white/40 max-w-sm mx-auto">
              {{ search ? `No results match your search "${search}".` : `There are no ${currentFilter !== 'all' ? currentFilter : ''} appointments to display right now.` }}
            </p>
            <button v-if="search || currentFilter !== 'all'" @click="resetFilters" class="mt-6 text-admin hover:text-admin-light text-sm font-semibold transition-colors">
              Clear filters
            </button>
          </div>
        </template>
      </div>
    </section>

    <!-- Details Modal -->
    <transition name="fade">
      <div v-if="detailsModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-[#050505]/90 backdrop-blur-md" @click="detailsModal = null"></div>
        <div class="relative w-full max-w-lg rounded-[2rem] border border-white/10 bg-[#151515] shadow-[0_30px_100px_rgba(0,0,0,0.8)] overflow-hidden animate-slide-up p-8 text-white z-10">
          <div class="flex justify-between items-start mb-6">
            <h3 class="text-2xl font-display font-bold text-white">Booking Details</h3>
            <button @click="detailsModal = null" class="p-2 bg-white/5 hover:bg-white/10 rounded-full transition-colors border border-white/5 text-white/50 hover:text-white">
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>

          <div class="space-y-1 bg-black/30 rounded-2xl border border-white/[0.05] p-2">
            <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Client</span>
              <span class="font-bold text-sm">{{ detailsModal.customer?.name || detailsModal.client_name }} (ID: {{ String(detailsModal.id).padStart(5, '0') }})</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Service</span>
              <span class="font-bold text-sm">{{ detailsModal.service?.name || 'General Booking' }}</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Price</span>
              <span class="font-bold text-emerald-400 text-sm">₦{{ (detailsModal.service?.price || 0).toLocaleString() }}</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Provider</span>
              <span class="font-medium text-admin text-sm">{{ detailsModal.barber?.name || 'Any Barber' }}</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Schedule</span>
              <span class="font-medium text-sm">{{ formatDate(detailsModal.appointment_date) }} at {{ detailsModal.appointment_time }}</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Type</span>
              <span v-if="detailsModal.booking_type === 'walk_in'" class="font-bold text-sm text-purple-400">Walk-In</span>
              <span v-else class="font-bold text-sm text-cyan-400">Online</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Status</span>
              <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest border" :class="statusClass(detailsModal.status)">{{ detailsModal.status }}</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Payment</span>
              <div class="flex items-center gap-2">
                <span v-if="detailsModal.payment_method" class="text-[10px] font-bold uppercase text-white/60">{{ detailsModal.payment_method === 'pos' ? 'POS' : detailsModal.payment_method }}</span>
                <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest border bg-[#111]" :class="paymentStatusClass(detailsModal.payment_status)">{{ (detailsModal.payment_status || 'Unpaid').replace('_', ' ') }}</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-6 flex flex-wrap gap-3">
            <template v-if="detailsModal.status === 'pending'">
              <button v-if="detailsModal.receipt_image && detailsModal.payment_status === 'awaiting_verification'" @click="viewReceipt(detailsModal); detailsModal = null" class="flex-1 rounded-xl bg-blue-500/10 border border-blue-500/20 py-3.5 text-xs font-bold uppercase tracking-widest text-blue-400 hover:bg-blue-500 hover:text-white transition-all flex items-center justify-center gap-2 shadow-sm">
                <DocumentTextIcon class="h-4 w-4" /> Verify Receipt
              </button>
              <button v-else-if="!detailsModal.receipt_image || detailsModal.payment_status !== 'awaiting_verification'" @click="approve(detailsModal.id); detailsModal = null" class="flex-1 rounded-xl bg-emerald-500/10 border border-emerald-500/20 py-3.5 text-xs font-bold uppercase tracking-widest text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center gap-2 shadow-sm">
                <CheckIcon class="h-4 w-4" /> Approve Booking
              </button>
              <button @click="cancel(detailsModal.id); detailsModal = null" class="px-5 rounded-xl border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all flex items-center justify-center" title="Cancel Booking">
                <XMarkIcon class="h-5 w-5" />
              </button>
            </template>
            <template v-else-if="(detailsModal.status === 'confirmed' || detailsModal.status === 'completed') && detailsModal.receipt_image">
              <button @click="viewReceipt(detailsModal); detailsModal = null" class="flex-1 rounded-xl bg-white/5 border border-white/10 py-3.5 text-xs font-bold uppercase tracking-widest text-white/60 hover:bg-white/10 hover:text-white transition-all flex items-center justify-center gap-2">
                <DocumentTextIcon class="h-4 w-4" /> View Receipt
              </button>
              <button v-if="detailsModal.status === 'confirmed'" @click="cancel(detailsModal.id); detailsModal = null" class="px-5 rounded-xl border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all flex items-center justify-center" title="Cancel Booking">
                <XMarkIcon class="h-5 w-5" />
              </button>
            </template>
            <button v-else @click="detailsModal = null" class="flex-1 rounded-xl bg-white/5 border border-white/10 py-3.5 text-xs font-bold uppercase tracking-widest text-white hover:bg-white/10 transition-all">
              Close Details
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Receipt Viewer Modal -->
    <ReceiptViewer 
      v-if="selectedBooking" 
      :booking="selectedBooking" 
      @close="selectedBooking = null" 
      @approve="handleReceiptApproved" 
      @reject="handleReceiptRejected"
      portal="admin"
    />

    <!-- Premium Payment Settings Modal -->
    <transition name="fade">
      <div v-if="showSettingsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-[#050505]/90 backdrop-blur-md" @click="showSettingsModal = false"></div>
        <div class="relative w-full max-w-xl rounded-[2.5rem] border border-white/10 bg-[#151515] shadow-[0_30px_100px_rgba(0,0,0,0.8)] overflow-hidden animate-slide-up">
          
          <div class="absolute -right-32 -top-32 h-64 w-64 rounded-full bg-admin/10 blur-[80px]"></div>
          <div class="absolute -left-32 -bottom-32 h-64 w-64 rounded-full bg-purple-500/10 blur-[80px]"></div>
          
          <!-- Header -->
          <div class="relative px-10 pt-10 pb-6 flex justify-between items-start z-10">
            <div>
              <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-admin/20 to-purple-500/20 border border-white/10 mb-4">
                <BuildingLibraryIcon class="h-6 w-6 text-admin" />
              </div>
              <h3 class="font-display text-3xl text-white">Bank Details</h3>
              <p class="text-xs text-white/40 mt-1">Configure where customer payments are routed.</p>
            </div>
            <button @click="showSettingsModal = false" class="text-white/30 hover:text-white hover:bg-white/10 rounded-full p-2.5 transition-all bg-white/5 border border-white/5">
              <XMarkIcon class="h-5 w-5"/>
            </button>
          </div>
          
          <div class="px-10 pb-10 relative z-10">
            <!-- Glass Credit Card Preview -->
            <div class="mb-8 relative group mx-auto max-w-md perspective-1000">
              <div class="relative rounded-3xl overflow-hidden border border-white/10 bg-gradient-to-br from-[#2A1100]/80 via-[#111] to-[#0a0a0a] p-8 shadow-2xl backdrop-blur-xl transition-transform duration-500 hover:rotate-y-12 hover:scale-[1.02] transform-style-3d">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20 mix-blend-overlay"></div>
                <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
                
                <div class="relative z-10 flex justify-between items-start mb-8">
                  <div class="h-8 w-12 rounded bg-gradient-to-r from-amber-200 to-amber-500 shadow-sm opacity-80"></div>
                  <svg class="w-10 h-10 text-white/20" viewBox="0 0 24 24" fill="currentColor"><path d="M2 10V8a2 2 0 012-2h16a2 2 0 012 2v2H2zm0 2v4a2 2 0 002 2h16a2 2 0 002-2v-4H2zm4 2h4v2H6v-2z"/></svg>
                </div>
                
                <div class="relative z-10 space-y-5">
                  <p class="text-[22px] font-mono text-white tracking-[0.2em] drop-shadow-md">
                    {{ settingsForm.account_number || '0000 0000 0000' }}
                  </p>
                  <div class="flex items-end justify-between">
                    <div>
                      <p class="text-[8px] uppercase tracking-[0.2em] text-white/40 mb-1">Account Name</p>
                      <p class="text-sm font-bold text-white tracking-widest uppercase">{{ settingsForm.account_name || 'CANDYCUTZ SALOON' }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-[8px] uppercase tracking-[0.2em] text-white/40 mb-1">Bank</p>
                      <p class="text-xs font-bold text-admin tracking-wider uppercase">{{ settingsForm.bank_name || 'BANK NAME' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Fields -->
            <div class="space-y-4">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <BuildingLibraryIcon class="h-5 w-5 text-white/30" />
                </div>
                <input v-model="settingsForm.bank_name" type="text" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 placeholder:text-white/20 font-medium" placeholder="Bank Name (e.g. Zenith Bank)" />
              </div>
              
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <UserIcon class="h-5 w-5 text-white/30" />
                </div>
                <input v-model="settingsForm.account_name" type="text" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 placeholder:text-white/20 font-medium" placeholder="Account Name" />
              </div>
              
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <HashtagIcon class="h-5 w-5 text-white/30" />
                </div>
                <input v-model="settingsForm.account_number" type="text" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 placeholder:text-white/20 font-mono tracking-widest text-lg" placeholder="Account Number" />
              </div>
            </div>
            
            <div class="flex items-center gap-3 pt-8 mt-4">
              <button @click="showSettingsModal = false" class="flex-1 py-4 px-6 rounded-2xl border border-white/10 text-sm font-bold text-white/60 hover:text-white hover:bg-white/5 transition-all">
                Cancel
              </button>
              <button @click="saveSettings" :disabled="savingSettings" class="flex-[2] flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-admin to-amber-500 px-6 py-4 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(255,103,0,0.4)] disabled:opacity-50 active:scale-[0.98]">
                <span v-if="savingSettings" class="h-5 w-5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
                <CheckCircleIcon v-else class="h-5 w-5" />
                {{ savingSettings ? 'Saving...' : 'Update Bank Details' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import ReceiptViewer from '../../../core/components/ReceiptViewer.vue';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import { 
  BanknotesIcon,
  CheckCircleIcon,
  UserIcon,
  DocumentTextIcon,
  CheckIcon,
  XMarkIcon,
  ClipboardDocumentCheckIcon,
  BuildingLibraryIcon,
  HashtagIcon,
  MagnifyingGlassIcon,
  ClockIcon,
  CalendarDaysIcon,
  UserPlusIcon,
  GlobeAltIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();

const appointments = ref([]);
const loading = ref(true);
const search = ref('');
const currentFilter = ref('all');
const showSettingsModal = ref(false);
const savingSettings = ref(false);
const selectedBooking = ref(null);
const detailsModal = ref(null);

function openDetails(appt) {
  detailsModal.value = appt;
}

const settingsForm = ref({
  bank_name: '',
  account_name: '',
  account_number: ''
});

// Walk-in state
const showWalkInModal = ref(false);
const walkInSubmitting = ref(false);
const servicesList = ref([]);
const barbersList = ref([]);
const walkInForm = ref({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  barber_id: '',
  service_id: '',
  appointment_date: new Date().toISOString().split('T')[0],
  appointment_time: new Date().toTimeString().slice(0, 5),
  payment_method: 'cash'
});

const tabs = computed(() => {
  const list = appointments.value || [];
  return [
    { label: 'All', value: 'all', count: list.length },
    { label: 'Pending', value: 'pending', count: list.filter(a => a.status === 'pending').length },
    { label: 'Confirmed', value: 'confirmed', count: list.filter(a => a.status === 'confirmed').length },
    { label: 'Completed', value: 'completed', count: list.filter(a => a.status === 'completed').length },
    { label: 'Cancelled', value: 'cancelled', count: list.filter(a => a.status === 'cancelled').length },
    { label: '🟣 Walk-In', value: 'walk_in', count: list.filter(a => a.booking_type === 'walk_in').length },
    { label: '🔵 Online', value: 'online', count: list.filter(a => a.booking_type !== 'walk_in').length }
  ];
});

const filteredAppointments = computed(() => {
  const list = appointments.value || [];
  if (!Array.isArray(list)) return [];
  
  let result = list;
  
  // Apply tab filter
  if (currentFilter.value !== 'all') {
    if (currentFilter.value === 'walk_in') {
      result = result.filter(a => a.booking_type === 'walk_in');
    } else if (currentFilter.value === 'online') {
      result = result.filter(a => a.booking_type !== 'walk_in');
    } else {
      result = result.filter(a => a.status === currentFilter.value);
    }
  }
  
  // Apply search filter
  if (search.value) {
    const q = search.value.toLowerCase();
    result = result.filter(a => {
      const name = a.customer?.name || a.client_name || '';
      return name.toLowerCase().includes(q) || String(a.id).includes(q);
    });
  }
  
  return result;
});

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function isNewClient(appt) {
  // Simple check: if customer created_at is within last 7 days
  if (!appt.customer?.created_at) return false;
  const created = new Date(appt.customer.created_at);
  const now = new Date();
  return (now - created) / (1000 * 60 * 60 * 24) < 7;
}

function resetFilters() {
  search.value = '';
  currentFilter.value = 'all';
}

const statusClass = (status) => {
  const map = {
    completed: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    confirmed: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    pending: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    cancelled: 'bg-red-500/10 text-red-400 border-red-500/20',
    no_show: 'bg-purple-500/10 text-purple-400 border-purple-500/20'
  };
  return map[status] || 'bg-white/5 text-white/50 border-white/10';
};

const statusDot = (status) => {
  const map = {
    completed: 'bg-emerald-400',
    confirmed: 'bg-blue-400',
    pending: 'bg-amber-400',
    cancelled: 'bg-red-400',
    no_show: 'bg-purple-400'
  };
  return map[status] || 'bg-white/30';
};

const paymentStatusClass = (status) => {
  const map = {
    verified: 'text-emerald-400 border-emerald-500/30',
    approved: 'text-emerald-400 border-emerald-500/30',
    awaiting_verification: 'text-amber-400 border-amber-500/30',
    pending: 'text-amber-400 border-amber-500/30',
    rejected: 'text-red-400 border-red-500/30',
    failed: 'text-red-400 border-red-500/30'
  };
  return map[status] || 'text-white/40 border-white/10';
};

async function loadAppointments() {
  loading.value = true;
  try {
    const response = await adminApi.appointments();
    appointments.value = response.data.data;
  } catch (err) {
    toast.error('Failed to load appointments');
  } finally {
    loading.value = false;
  }
}

async function loadSettings() {
  try {
    const response = await adminApi.settings();
    const data = response.data.data || {};
    settingsForm.value.bank_name = data.bank_name || '';
    settingsForm.value.account_name = data.account_name || '';
    settingsForm.value.account_number = data.account_number || '';
  } catch (err) {
    console.error(err);
  }
}

async function saveSettings() {
  if (!settingsForm.value.bank_name || !settingsForm.value.account_name || !settingsForm.value.account_number) {
    return toast.error('Please fill out all fields');
  }
  
  savingSettings.value = true;
  try {
    await adminApi.updateSettings(settingsForm.value);
    toast.success('Payment settings updated');
    showSettingsModal.value = false;
  } catch (err) {
    toast.error('Failed to save settings');
  } finally {
    savingSettings.value = false;
  }
}

const viewReceipt = (booking) => {
  selectedBooking.value = booking;
};

const handleReceiptApproved = async () => {
  selectedBooking.value = null;
  await loadAppointments();
  toast.success('Payment approved and booking confirmed!');
};

const handleReceiptRejected = async () => {
  selectedBooking.value = null;
  await loadAppointments();
  toast.error('Payment rejected and booking cancelled');
};

async function approve(id) {
  if (await confirm('Approve Booking', 'Are you sure you want to approve this booking manually?')) {
    try {
      await adminApi.approveAppointment(id);
      await loadAppointments();
      toast.success('Booking approved');
    } catch (err) {
      toast.error('Failed to approve');
    }
  }
}

async function cancel(id) {
  if (await confirm('Cancel Booking', 'Are you sure you want to cancel this booking? This action cannot be undone.')) {
    try {
      await adminApi.cancelAppointment(id);
      await loadAppointments();
      toast.success('Booking cancelled');
    } catch (err) {
      toast.error('Failed to cancel');
    }
  }
}

async function loadServicesAndBarbers() {
  try {
    const [sRes, bRes] = await Promise.all([
      adminApi.services(),
      adminApi.barbers()
    ]);
    servicesList.value = sRes.data.data || [];
    barbersList.value = (bRes.data.data || []).map(b => ({ id: b.id, name: b.name || b.user?.name || 'Barber' }));
  } catch (e) {
    // silently fail
  }
}

async function submitWalkIn() {
  if (!walkInForm.value.customer_name || !walkInForm.value.customer_phone || !walkInForm.value.service_id || !walkInForm.value.barber_id) {
    toast.error('Please fill in all required fields');
    return;
  }
  walkInSubmitting.value = true;
  try {
    await adminApi.createWalkIn(walkInForm.value);
    toast.success('Walk-in appointment created successfully!');
    showWalkInModal.value = false;
    walkInForm.value = {
      customer_name: '',
      customer_phone: '',
      customer_email: '',
      barber_id: '',
      service_id: '',
      appointment_date: new Date().toISOString().split('T')[0],
      appointment_time: new Date().toTimeString().slice(0, 5),
      payment_method: 'cash'
    };
    await loadAppointments();
  } catch (err) {
    toast.error(err.response?.data?.error || 'Failed to create walk-in appointment');
  } finally {
    walkInSubmitting.value = false;
  }
}

onMounted(() => {
  loadAppointments();
  loadSettings();
  loadServicesAndBarbers();
});
</script>

<style scoped>
.perspective-1000 {
  perspective: 1000px;
}
.transform-style-3d {
  transform-style: preserve-3d;
}

.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-slide-up {
  animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Custom hide scrollbar */
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>