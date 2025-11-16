<template>
  <div>
    <NuxtRouteAnnouncer />
    <!-- Navigation Header -->
    <header class="border-b">
      <nav class="container mx-auto px-4 py-4 flex items-center justify-between">
        <NuxtLink to="/" class="text-2xl font-bold">UraharaShop</NuxtLink>
        <div class="flex items-center gap-4">
          <NuxtLink to="/products" class="hover:underline">Products</NuxtLink>
          <NuxtLink to="/cart" class="hover:underline">Cart</NuxtLink>
        </div>
      </nav>
    </header>

    <!-- Main Content -->
    <main>
      <NuxtPage />
    </main>

    <!-- Footer -->
    <footer class="border-t mt-12">
      <div class="container mx-auto px-4 py-8 text-center text-gray-600">
        <p>&copy; 2025 UraharaShop. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>
