<template>
  <div class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
    <!-- Product Image Placeholder -->
    <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
      <span class="text-gray-400">Image</span>
    </div>
    
    <!-- Product Info -->
    <div class="p-4">
      <h3 class="font-semibold text-lg mb-2">{{ product.name }}</h3>
      <p v-if="product.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
        {{ product.description }}
      </p>
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xl font-bold">${{ product.price }}</p>
          <p v-if="product.compare_at_price" class="text-sm text-gray-500 line-through">
            ${{ product.compare_at_price }}
          </p>
        </div>
        <NuxtLink
          :to="`/products/${product.id}`"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          View
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  product: {
    id: number
    name: string
    description?: string
    price: number
    compare_at_price?: number
  }
}>()
</script>

