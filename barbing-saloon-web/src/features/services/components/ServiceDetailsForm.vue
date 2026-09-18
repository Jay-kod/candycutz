<template>
  <div class="space-y-8">
    <!-- Section 1: Basic Info -->
    <div class="rounded-[2.5rem] border border-white/[0.05] bg-[#151515] overflow-hidden shadow-2xl relative">
      <div class="absolute -right-32 -top-32 h-64 w-64 rounded-full bg-admin/5 blur-[80px]"></div>

      <div class="px-8 py-6 border-b border-white/[0.05] flex items-center gap-3 bg-black/20">
        <div class="p-2.5 rounded-xl bg-admin/10 border border-admin/20">
          <SparklesIcon class="h-5 w-5 text-admin" />
        </div>
        <div>
          <h2 class="text-lg font-bold text-white">Service Details</h2>
          <p class="text-xs text-white/40 mt-0.5">Name, description, and category</p>
        </div>
      </div>

      <div class="p-8 space-y-6 relative z-10">
        <div class="space-y-2">
          <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Service Name</label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <SparklesIcon class="h-5 w-5 text-white/30 group-focus-within:text-admin/70 transition-colors" />
            </div>
            <input v-model="form.name" required type="text" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 hover:bg-white/[0.04]" placeholder="e.g. Classic Fade Haircut" />
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Description</label>
          <textarea v-model="form.description" rows="4" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] p-5 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 resize-none hover:bg-white/[0.04]" placeholder="Describe what this service includes..."></textarea>
        </div>

        <div class="space-y-2">
          <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Category</label>
          <div class="relative">
            <select v-model="form.category_id" class="w-full rounded-2xl border border-white/[0.08] bg-[#1a1a1a] px-4 py-4 text-sm font-bold text-white outline-none transition-all focus:border-admin/50 focus:ring-4 focus:ring-admin/10 appearance-none cursor-pointer hover:border-white/20">
              <option :value="null">No Category (General)</option>
              <option v-for="cat in serviceCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Section 2: Media & Gallery -->
    <div class="rounded-[2.5rem] border border-white/[0.05] bg-[#151515] overflow-hidden shadow-2xl relative">
      <div class="absolute -right-32 top-0 h-64 w-64 rounded-full bg-purple-500/5 blur-[80px]"></div>

      <div class="px-8 py-6 border-b border-white/[0.05] flex items-center gap-3 bg-black/20">
        <div class="p-2.5 rounded-xl bg-purple-500/10 border border-purple-500/20">
          <PhotoIcon class="h-5 w-5 text-purple-400" />
        </div>
        <div>
          <h2 class="text-lg font-bold text-white">Media Gallery</h2>
          <p class="text-xs text-white/40 mt-0.5">Upload up to 3 pictures to show customers what to expect</p>
        </div>
      </div>
      
      <div class="p-8 relative z-10 grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Image 1 -->
        <div class="space-y-3">
          <p class="text-[10px] uppercase tracking-[0.2em] text-white/40 font-bold text-center">Primary Image</p>
          <div class="relative aspect-square rounded-2xl border-2 border-dashed border-white/10 overflow-hidden bg-black/20 hover:bg-white/5 hover:border-admin/30 transition-all flex flex-col items-center justify-center group cursor-pointer" @click="triggerUpload(1)">
            <img v-if="images.url1" :src="images.url1" class="absolute inset-0 w-full h-full object-cover z-10" />
            <div v-if="images.url1" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center justify-center">
              <button @click.stop="$emit('removeImage', 1)" class="p-2 bg-red-500/20 text-red-400 rounded-full hover:bg-red-500 hover:text-white transition-all">
                <TrashIcon class="h-5 w-5" />
              </button>
            </div>
            
            <div v-if="!images.url1" class="flex flex-col items-center text-white/30 group-hover:text-admin/70 transition-colors">
              <PhotoIcon class="h-8 w-8 mb-2" />
              <span class="text-xs font-bold">Upload</span>
            </div>
            <input type="file" ref="file1" @change="e => $emit('fileChange', e, 1)" accept="image/jpeg,image/png,image/webp" class="hidden" />
          </div>
        </div>
        
        <!-- Image 2 -->
        <div class="space-y-3">
          <p class="text-[10px] uppercase tracking-[0.2em] text-white/40 font-bold text-center">Image 2</p>
          <div class="relative aspect-square rounded-2xl border-2 border-dashed border-white/10 overflow-hidden bg-black/20 hover:bg-white/5 hover:border-admin/30 transition-all flex flex-col items-center justify-center group cursor-pointer" @click="triggerUpload(2)">
            <img v-if="images.url2" :src="images.url2" class="absolute inset-0 w-full h-full object-cover z-10" />
            <div v-if="images.url2" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center justify-center">
              <button @click.stop="$emit('removeImage', 2)" class="p-2 bg-red-500/20 text-red-400 rounded-full hover:bg-red-500 hover:text-white transition-all">
                <TrashIcon class="h-5 w-5" />
              </button>
            </div>
            
            <div v-if="!images.url2" class="flex flex-col items-center text-white/30 group-hover:text-admin/70 transition-colors">
              <PhotoIcon class="h-8 w-8 mb-2" />
              <span class="text-xs font-bold">Upload</span>
            </div>
            <input type="file" ref="file2" @change="e => $emit('fileChange', e, 2)" accept="image/jpeg,image/png,image/webp" class="hidden" />
          </div>
        </div>
        
        <!-- Image 3 -->
        <div class="space-y-3">
          <p class="text-[10px] uppercase tracking-[0.2em] text-white/40 font-bold text-center">Image 3</p>
          <div class="relative aspect-square rounded-2xl border-2 border-dashed border-white/10 overflow-hidden bg-black/20 hover:bg-white/5 hover:border-admin/30 transition-all flex flex-col items-center justify-center group cursor-pointer" @click="triggerUpload(3)">
            <img v-if="images.url3" :src="images.url3" class="absolute inset-0 w-full h-full object-cover z-10" />
            <div v-if="images.url3" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center justify-center">
              <button @click.stop="$emit('removeImage', 3)" class="p-2 bg-red-500/20 text-red-400 rounded-full hover:bg-red-500 hover:text-white transition-all">
                <TrashIcon class="h-5 w-5" />
              </button>
            </div>
            
            <div v-if="!images.url3" class="flex flex-col items-center text-white/30 group-hover:text-admin/70 transition-colors">
              <PhotoIcon class="h-8 w-8 mb-2" />
              <span class="text-xs font-bold">Upload</span>
            </div>
            <input type="file" ref="file3" @change="e => $emit('fileChange', e, 3)" accept="image/jpeg,image/png,image/webp" class="hidden" />
          </div>
        </div>
        
      </div>
    </div>

    <!-- Section 3: Pricing & Duration -->
    <div class="rounded-[2.5rem] border border-white/[0.05] bg-[#151515] overflow-hidden shadow-2xl relative">
      <div class="absolute -left-32 bottom-0 h-64 w-64 rounded-full bg-emerald-500/5 blur-[80px]"></div>

      <div class="px-8 py-6 border-b border-white/[0.05] flex items-center gap-3 bg-black/20">
        <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
          <CurrencyDollarIcon class="h-5 w-5 text-emerald-500" />
        </div>
        <div>
          <h2 class="text-lg font-bold text-white">Pricing & Duration</h2>
          <p class="text-xs text-white/40 mt-0.5">Set the cost and estimated time</p>
        </div>
      </div>

      <div class="p-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Price (₦)</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <span class="text-white/30 text-lg font-bold group-focus-within:text-emerald-400/70 transition-colors">₦</span>
              </div>
              <input v-model.number="form.price" required type="number" min="0" step="0.01" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 hover:bg-white/[0.04]" placeholder="e.g. 5000" />
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Duration (minutes)</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <ClockIcon class="h-5 w-5 text-white/30 group-focus-within:text-admin/70 transition-colors" />
              </div>
              <input v-model.number="form.duration_minutes" required type="number" min="5" step="5" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 hover:bg-white/[0.04]" placeholder="e.g. 30" />
            </div>
          </div>
        </div>

        <!-- Availability Toggle -->
        <div class="mt-8 p-5 rounded-2xl border border-white/[0.05] bg-black/30 flex items-center justify-between">
          <div>
            <h4 class="text-sm font-bold text-white">Availability</h4>
            <p class="text-xs text-white/40 mt-0.5">When disabled, customers cannot book this service</p>
          </div>
          <button
            type="button"
            @click="form.is_available = !form.is_available"
            class="relative h-8 w-14 rounded-full border transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-admin/20 shrink-0"
            :class="form.is_available
              ? 'bg-emerald-500/20 border-emerald-500/40'
              : 'bg-white/5 border-white/10'"
          >
            <span
              class="absolute top-1 h-6 w-6 rounded-full transition-all duration-300 shadow-lg"
              :class="form.is_available
                ? 'left-7 bg-emerald-400'
                : 'left-1 bg-white/40'"
            ></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { 
  SparklesIcon, PhotoIcon, TrashIcon, CurrencyDollarIcon, ClockIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  form: { type: Object, required: true },
  serviceCategories: { type: Array, required: true },
  images: { type: Object, required: true }
});

defineEmits(['fileChange', 'removeImage']);

const file1 = ref(null);
const file2 = ref(null);
const file3 = ref(null);

function triggerUpload(num) {
  if (num === 1) file1.value?.click();
  if (num === 2) file2.value?.click();
  if (num === 3) file3.value?.click();
}
</script>
