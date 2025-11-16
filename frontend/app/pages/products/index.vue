<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Products</h1>

    <!-- Filters -->
    <div class="mb-8 flex flex-col md:flex-row gap-4">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search products..."
        class="flex-1 px-4 py-2 border rounded-lg"
        @input="handleSearch"
      />
      <select
        v-model="selectedCategory"
        class="px-4 py-2 border rounded-lg"
        @change="handleCategoryChange"
      >
        <option value="">All Categories</option>
        <option
          v-for="category in categories"
          :key="category.id"
          :value="category.id"
        >
          {{ category.name }}
        </option>
      </select>
    </div>

    <!-- Products Grid -->
    <div v-if="pending" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="i in 8" :key="i" class="animate-pulse">
        <div class="bg-gray-200 h-64 rounded-lg"></div>
      </div>
    </div>
    <div v-else-if="products?.data" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <ProductCard
        v-for="product in products.data"
        :key="product.id"
        :product="product"
      />
    </div>
    <div v-else class="text-center py-12">
      <p class="text-gray-500">No products found</p>
    </div>

    <!-- Pagination -->
    <div v-if="products?.links" class="mt-8 flex justify-center">
      <Pagination :links="products.links" />
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const router = useRouter()

// Get query parameters
const searchQuery = ref(route.query.search as string || '')
const selectedCategory = ref(route.query.category ? Number(route.query.category) : null)

// Fetch categories for filter
const { data: categories } = await useAsyncData('categories', () =>
  categoryApi.getAll()
)

// Fetch products with filters
const { data: products, pending, refresh } = await useAsyncData(
  'products',
  () => productApi.getAll({
    search: searchQuery.value || undefined,
    category_id: selectedCategory.value || undefined,
    per_page: 12,
  }),
  {
    watch: [searchQuery, selectedCategory],
  }
)

// Handle search with debounce
let searchTimeout: NodeJS.Timeout
function handleSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.push({ query: { ...route.query, search: searchQuery.value } })
    refresh()
  }, 500)
}

// Handle category change
function handleCategoryChange() {
  router.push({
    query: {
      ...route.query,
      category: selectedCategory.value || undefined,
    },
  })
  refresh()
}

useHead({
  title: 'Products - UraharaShop',
})
</script>

