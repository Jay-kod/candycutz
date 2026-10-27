<template>
  <div>
    <!-- Grid of Articles -->
    <div 
      v-if="posts.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
    >
      <BlogCard
        v-for="post in posts"
        :key="post.id"
        :post="post"
        @edit="$emit('edit', $event)"
        @toggle-publish="$emit('togglePublish', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>

    <!-- Empty State: Search Results -->
    <div 
      v-else-if="searchQuery"
      class="flex flex-col items-center justify-center py-20 px-4 text-center rounded-3xl border border-dashed border-white/10 bg-[#12161C]/50 backdrop-blur-sm"
    >
      <div class="h-16 w-16 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center text-white/40 mb-4">
        <MagnifyingGlassIcon class="h-8 w-8" />
      </div>
      <h3 class="font-display text-xl text-white font-semibold mb-2">No Matching Articles</h3>
      <p class="text-sm text-white/50 max-w-md mb-6">
        No articles matched your search query "<span class="text-white font-medium">{{ searchQuery }}</span>". Try different keywords or clear the filter.
      </p>
      <button
        @click="$emit('reset')"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white hover:bg-white/10 transition-colors"
      >
        <ArrowPathIcon class="h-4 w-4" />
        <span>Clear Search</span>
      </button>
    </div>

    <!-- Empty State: Drafts Queue -->
    <div 
      v-else-if="statusFilter === 'draft'"
      class="flex flex-col items-center justify-center py-20 px-4 text-center rounded-3xl border border-dashed border-white/10 bg-[#12161C]/50 backdrop-blur-sm"
    >
      <div class="h-16 w-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-4">
        <CheckCircleIcon class="h-8 w-8" />
      </div>
      <h3 class="font-display text-xl text-white font-semibold mb-2">Drafts Queue Cleared</h3>
      <p class="text-sm text-white/50 max-w-md mb-6">
        There are currently no unpublished drafts. All your articles are published live to the public blog.
      </p>
      <button
        @click="$emit('reset')"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white hover:bg-white/10 transition-colors"
      >
        <span>View All Articles</span>
      </button>
    </div>

    <!-- Empty State: Zero Articles Overall -->
    <div 
      v-else
      class="flex flex-col items-center justify-center py-24 px-4 text-center rounded-3xl border border-dashed border-white/10 bg-[#12161C]/50 backdrop-blur-sm"
    >
      <div class="h-20 w-20 rounded-3xl bg-admin/10 border border-admin/20 flex items-center justify-center text-admin mb-5 shadow-[0_0_30px_rgba(255,103,0,0.15)]">
        <DocumentTextIcon class="h-10 w-10" />
      </div>
      <h3 class="font-display text-2xl text-white font-semibold mb-2">No Articles Yet</h3>
      <p class="text-sm text-white/50 max-w-md mb-6 leading-relaxed">
        Share grooming tips, saloon trends, haircut styling advice and announcements to connect with your clients.
      </p>
      <button
        @click="$emit('newPost')"
        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-admin to-admin-light text-obsidian text-xs font-bold shadow-[0_4px_25px_rgba(255,103,0,0.3)] hover:shadow-[0_6px_35px_rgba(255,103,0,0.45)] hover:scale-105 transition-all"
      >
        <PlusIcon class="h-4 w-4 stroke-[2.5]" />
        <span>Create First Article</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { 
  MagnifyingGlassIcon, 
  ArrowPathIcon, 
  CheckCircleIcon, 
  DocumentTextIcon, 
  PlusIcon 
} from '@heroicons/vue/24/outline';
import BlogCard from './BlogCard.vue';

defineProps({
  posts: {
    type: Array,
    required: true
  },
  searchQuery: {
    type: String,
    default: ''
  },
  statusFilter: {
    type: String,
    default: 'all'
  }
});

defineEmits(['edit', 'togglePublish', 'delete', 'reset', 'newPost']);
</script>
