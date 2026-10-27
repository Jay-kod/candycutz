<template>
  <article 
    class="group relative flex flex-col overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] transition-all duration-300 hover:border-admin/40 hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(0,0,0,0.5)]"
  >
    <!-- Ambient card highlight -->
    <div class="absolute inset-0 bg-gradient-to-b from-admin/[0.02] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>

    <!-- Image Cover / Header Container -->
    <div class="relative h-48 w-full overflow-hidden bg-black/40 border-b border-white/[0.06]">
      <img
        v-if="coverImage"
        :src="coverImage"
        :alt="post.title"
        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
        @error="hasImageError = true"
      />
      <!-- Fallback when no image or image load fails -->
      <div 
        v-else 
        class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-black/60 via-obsidian to-charcoal p-4 text-center"
      >
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/[0.04] border border-white/[0.08] text-white/30 group-hover:text-admin/60 transition-colors">
          <PhotoIcon class="h-6 w-6" />
        </div>
        <span class="mt-2 text-[11px] font-medium text-white/40">No cover image uploaded</span>
      </div>

      <!-- Top Overlay Badges -->
      <div class="absolute inset-x-3 top-3 flex items-center justify-between gap-2 z-10">
        <!-- Status Pill -->
        <div 
          class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase backdrop-blur-md border shadow-lg"
          :class="isPublished 
            ? 'bg-emerald-950/80 text-emerald-400 border-emerald-500/30' 
            : 'bg-amber-950/80 text-amber-400 border-amber-500/30'"
        >
          <span 
            class="h-1.5 w-1.5 rounded-full" 
            :class="isPublished ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse'"
          ></span>
          <span>{{ isPublished ? 'Published' : 'Draft' }}</span>
        </div>

        <!-- Loves / Reactions badge -->
        <div 
          v-if="Number(post.loves_count) > 0"
          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold text-rose-300 bg-black/70 backdrop-blur-md border border-rose-500/20"
        >
          <HeartIcon class="h-3 w-3 fill-rose-500 text-rose-500" />
          <span>{{ post.loves_count }}</span>
        </div>
      </div>
    </div>

    <!-- Card Content Body -->
    <div class="flex flex-1 flex-col p-5">
      <!-- Author & Date Metadata -->
      <div class="flex items-center justify-between gap-2 text-xs text-white/40 mb-3">
        <div class="flex items-center gap-1.5 truncate">
          <UserCircleIcon class="h-4 w-4 shrink-0 text-admin/70" />
          <span class="truncate font-medium text-white/70">{{ authorName }}</span>
        </div>
        <div class="flex items-center gap-1.5 shrink-0 text-[11px]">
          <CalendarIcon class="h-3.5 w-3.5" />
          <span>{{ formattedDate }}</span>
        </div>
      </div>

      <!-- Article Title -->
      <h2 class="font-display text-lg text-white font-semibold leading-snug group-hover:text-admin transition-colors line-clamp-2 mb-2">
        {{ post.title }}
      </h2>

      <!-- Excerpt or Content Preview -->
      <p class="text-xs text-white/60 leading-relaxed line-clamp-3 mb-4 flex-1">
        {{ excerptText }}
      </p>

      <!-- Reading info & Slug -->
      <div class="flex items-center justify-between text-[11px] text-white/40 pb-3 mb-3 border-b border-white/[0.06]">
        <span class="font-mono text-white/30 truncate max-w-[65%]">/{{ post.slug || 'untitled' }}</span>
        <span class="shrink-0 text-white/50">{{ readTimeEstimate }}</span>
      </div>

      <!-- Card Action Toolbar -->
      <div class="mt-auto flex items-center justify-between pt-1">
        <!-- Left: Public Link (if published) -->
        <div>
          <a
            v-if="isPublished && post.slug"
            :href="`/blog/${post.slug}`"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-1 text-[11px] font-semibold text-white/50 hover:text-white transition-colors"
            title="Preview live article"
          >
            <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
            <span>Preview</span>
          </a>
          <span v-else class="text-[11px] text-white/30 italic">Not public</span>
        </div>

        <!-- Right: Actions (Toggle, Edit, Delete) -->
        <div class="flex items-center gap-1.5">
          <!-- Toggle Publish Button -->
          <button
            @click="$emit('togglePublish', post)"
            class="p-2 rounded-xl text-white/60 hover:text-white transition-colors border border-transparent hover:border-white/10"
            :class="isPublished ? 'hover:bg-amber-500/10 hover:text-amber-400' : 'hover:bg-emerald-500/10 hover:text-emerald-400'"
            :title="isPublished ? 'Unpublish (move to draft)' : 'Publish to live website'"
          >
            <EyeSlashIcon v-if="isPublished" class="h-4 w-4" />
            <EyeIcon v-else class="h-4 w-4" />
          </button>

          <!-- Edit Button -->
          <button
            @click="$emit('edit', post)"
            class="p-2 rounded-xl text-white/60 hover:text-admin hover:bg-admin/10 hover:border-admin/20 border border-transparent transition-colors"
            title="Edit article"
          >
            <PencilSquareIcon class="h-4 w-4" />
          </button>

          <!-- Delete Button -->
          <button
            @click="$emit('delete', post.id)"
            class="p-2 rounded-xl text-white/60 hover:text-rose-400 hover:bg-rose-500/10 hover:border-rose-500/20 border border-transparent transition-colors"
            title="Delete article"
          >
            <TrashIcon class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed, ref } from 'vue';
import { 
  UserCircleIcon, 
  CalendarIcon, 
  PhotoIcon, 
  EyeIcon, 
  EyeSlashIcon, 
  PencilSquareIcon, 
  TrashIcon, 
  ArrowTopRightOnSquareIcon,
  HeartIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  post: {
    type: Object,
    required: true
  }
});

defineEmits(['edit', 'togglePublish', 'delete']);

const hasImageError = ref(false);

const isPublished = computed(() => {
  return props.post.is_published == 1 || props.post.status === 'published';
});

const coverImage = computed(() => {
  if (hasImageError.value) return null;
  return props.post.featured_image_url || props.post.featured_image || null;
});

const authorName = computed(() => {
  return props.post.author_display || props.post.author?.name || 'CandyCutz Team';
});

const formattedDate = computed(() => {
  if (!props.post.created_at) return 'Recently';
  try {
    return new Date(props.post.created_at).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  } catch {
    return 'Recently';
  }
});

const excerptText = computed(() => {
  if (props.post.excerpt) return props.post.excerpt;
  const raw = props.post.content || props.post.body || '';
  const clean = raw.replace(/<[^>]*>?/gm, '').trim();
  return clean ? clean.slice(0, 160) + (clean.length > 160 ? '...' : '') : 'No summary provided.';
});

const readTimeEstimate = computed(() => {
  const content = props.post.content || props.post.body || '';
  const words = content.replace(/<[^>]*>?/gm, '').split(/\s+/).filter(Boolean).length;
  const mins = Math.max(1, Math.ceil(words / 180));
  return `~${mins} min read`;
});
</script>
