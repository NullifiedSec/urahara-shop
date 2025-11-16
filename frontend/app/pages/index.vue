<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Welcome to UraharaShop</h1>
    
    <!-- Featured Products Section -->
    <section class="mb-12">
      <h2 class="text-2xl font-semibold mb-6">Featured Products</h2>
      <div v-if="pending" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="i in 4" :key="i" class="animate-pulse">
          <div class="bg-gray-200 h-64 rounded-lg"></div>
        </div>
      </div>
      <div v-else-if="featuredProducts?.data" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <ProductCard
          v-for="product in featuredProducts.data"
          :key="product.id"
          :product="product"
        />
      </div>
      <div v-else class="text-center py-12">
        <p class="text-gray-500">No featured products available</p>
      </div>
    </section>

    <!-- Categories Section -->
    <section>
      <h2 class="text-2xl font-semibold mb-6">Shop by Category</h2>
      <div v-if="categoriesPending" class="flex gap-4">
        <div v-for="i in 4" :key="i" class="animate-pulse bg-gray-200 h-32 w-32 rounded-lg"></div>
      </div>
      <div v-else-if="categories" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <NuxtLink
          v-for="category in categories"
          :key="category.id"
          :to="`/products?category=${category.id}`"
          class="p-6 border rounded-lg hover:shadow-lg transition-shadow"
        >
          <h3 class="font-semibold text-lg">{{ category.name }}</h3>
          <p v-if="category.description" class="text-sm text-gray-600 mt-2">
            {{ category.description }}
          </p>
        </NuxtLink>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
// Fetch featured products
const { data: featuredProducts, pending } = await useAsyncData('featured-products', () =>
  productApi.getAll({ featured: true, per_page: 4 })
)

// Fetch categories
const { data: categories, pending: categoriesPending } = await useAsyncData('categories', () =>
  categoryApi.getAll()
)

// Set page metadata
useHead({
  title: 'UraharaShop - Home',
  meta: [
    { name: 'description', content: 'Welcome to UraharaShop - Your one-stop shop for all your needs' },
  ],
})
</script>

